


Il faudrait créer ce VPS dans un Docker de façon à pouvoir l'installer rapidement sur ubuntu.









# VPS « domaines personnalisés » — synthèse

Objectif : un créatif achète un nom de domaine (ex. `jeanne-dupont.fr`)
qui affiche son book. La plateforme reste sur le serveur mutualisé ; le
VPS ne fait que recevoir le trafic des domaines personnalisés et le
relayer.

## 1. Principe

```
Visiteur ──https──> jeanne-dupont.fr  (DNS A → IP du VPS)
                        │
                     VPS (Caddy)
                        │  certificat HTTPS automatique
                        │  Host réécrit + X-Forwarded-Host: jeanne-dupont.fr
                        ▼
              passerelle.ultra-book.com  (mutualisé, Laravel)
                        │  domaine → créatif → book
                        ▼
                     page du book
Images : servies directement depuis le domaine Ultra-book (pas de transit VPS).
```

Le VPS ne contient **aucune logique métier ni base de données**. Toute
la décision (« ce domaine existe-t-il, à quel book correspond-il ? »)
reste dans Laravel. Conséquence : rien à écrire ni à modifier sur le VPS
quand un créatif ajoute un domaine.

## 2. Rôle du VPS

| Fonction | Outil | Détail |
|---|---|---|
| Recevoir le trafic des domaines | Caddy | ports 80/443 uniquement |
| HTTPS | Caddy on-demand TLS | certificat Let's Encrypt créé à la première visite, renouvelé seul |
| Autoriser un domaine | Caddy → Laravel | avant tout certificat, Caddy appelle `GET https://passerelle.ultra-book.com/api/v1/domaines/autorise?domain=…` ; 200 = oui, 404 = refus |
| Relayer vers le mutualisé | Caddy reverse_proxy | vers `passerelle.ultra-book.com`, en-tête `X-Forwarded-Host` = domaine du créatif |
| Cache des pages publiques | Caddy (option, phase 2) | soulage le mutualisé |
| Sécurité | UFW, fail2ban, SSH par clé, mises à jour auto | voir `02_installation_vps.md` |
| Supervision | Uptime Kuma ou sonde externe | alerte si le VPS tombe |

Pourquoi Caddy et pas Apache : Apache impose un vhost et un certificat
par domaine, donc un script sur mesure qui écrit des fichiers et recharge
le serveur. Caddy gère des milliers de domaines avec **une seule
configuration**, sans rechargement.

Une seule cible pour Ultra-book et Dustfolio : la passerelle sur le
mutualisé retrouve le créatif (et donc la marque) à partir du domaine.

## 3. Ce qui est fait côté Laravel (mutualisé)

1. **Table `domaines`** : `user_id`, `nom`, `statut`
   (`en_attente`, `dns_ok`, `actif`, `suspendu`, `expire`), `source`
   (`ovh` ou `externe`), `ovh_commande_id`, `expire_le`,
   `verifie_le`.
2. **Alias `passerelle.ultra-book.com`** sur l'hébergement mutualisé,
   pointant vers la même application.
3. **Secret partagé** : seul le VPS, qui connaît `PROXY_SECRET`
   (en-tête `X-Proxy-Secret`), peut imposer `X-Forwarded-Host`.
4. **Résolution du book** : un middleware lit `X-Forwarded-Host`,
   retrouve le domaine `actif`, sert le book correspondant.
5. **SEO** : sur un domaine actif, canonical, sitemap et liens absolus
   utilisent le domaine personnalisé ; `login.ultra-book.com` répond en
   301 vers lui (pas de contenu en double).
6. **Endpoint `domaines/autorise`** : appelé par Caddy, limité en débit,
   répond 200 seulement pour un domaine `dns_ok` ou `actif`.
7. **Service OVH** (`app/Services/Domaines/Ovh`, paquet `ovh/ovh`), en
   file d'attente :
   - vérifier la disponibilité et le prix ;
   - créer le contact titulaire au nom du créatif (voir §4) ;
   - commander le domaine ;
   - créer l'enregistrement DNS `A` (et `AAAA`) vers l'IP du VPS.
8. **Domaine déjà possédé** par le créatif (`source = externe`) : on lui
   indique l'IP à saisir chez son registrar ; une tâche planifiée
   vérifie le DNS et active le domaine.
9. **Tâche planifiée quotidienne** : contrôle DNS, dates d'expiration,
   relances avant échéance.
10. **Espace créatif** : page « Mon nom de domaine » (recherche,
    achat, statut, instructions DNS).

## 4. Titulaire du domaine : le créatif

Le créatif est **propriétaire légal** du domaine (contact `owner` chez
OVH) ; le compte OVH d'Ultra-book reste **gestionnaire technique et
administratif**. Conséquences :

- l'achat exige l'identité complète du créatif (nom, adresse, mail,
  téléphone ; SIRET pour une entreprise) : à collecter avant commande,
  avec consentement explicite (RGPD, données transmises au registre) ;
- `.fr` : titulaire résidant dans l'UE (règle Afnic) — à vérifier selon
  l'extension choisie ;
- le créatif peut demander le **code de transfert (AuthCode)** pour
  partir avec son domaine : prévoir la procédure ;
- résiliation de l'abonnement Ultra-book : le domaine reste au créatif,
  il cesse simplement de pointer vers le VPS.

## 5. Points ouverts

- Renouvellement et facturation des domaines : à rattacher aux formules
  (sujet « Ma formule », traité plus tard).
- Extensions proposées au départ (`.fr`, `.com` seulement ?).
- Mail sur le domaine : hors périmètre (pas de MX créé).
- Le jour où la plateforme quitte le mutualisé : seule la cible du
  `reverse_proxy` change.

## 6. État côté Laravel (2026-10-14)

Fait et testé : table `domaines`, passerelle `ServirDomainePerso`
(secret `PROXY_SECRET`), `GET /api/v1/domaines/autorise`, service OVH
(signature, disponibilité, commande, DNS), job `CommanderDomaine`,
commande `ubdf:verifier-domaines` (horaire).

Écarts avec le plan initial :
- pas d'alias `trustProxies` nécessaire : la passerelle se fie au secret,
  pas à l'IP (plus sûr sur un mutualisé) ;
- `dns_ok` et `actif` sont tous deux servis ; la commande horaire passe
  directement en `actif` quand le DNS vise le VPS.

Reste : alias `passerelle.ultra-book.com` sur le mutualisé, page « Mon
nom de domaine », SEO (canonical, 301), premier achat OVH réel pour
valider l'enchaînement de commande.

Détail : `_doc/_doc_API/01_plan_et_avancement.md`. Achat par le créatif : `03_achat_domaine.md`.

## 7. Ordre de réalisation

1. Installer et sécuriser le VPS (`02_installation_vps.md`).
2. Laravel : table `domaines`, passerelle, proxy de confiance,
   middleware, endpoint `autorise`.
3. Test de bout en bout avec un domaine de test acheté à la main et
   inséré en base.
4. Service OVH + page « Mon nom de domaine ».
5. Domaines externes + tâche planifiée.
