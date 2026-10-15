# VPS — sauvegardes

Le VPS ne contient **ni base de données ni donnée de créatif** : la liste
des domaines vit dans la base Ultra-book (table `domaines`). Il n'y a donc
presque rien à sauvegarder.

| Élément | Où | Change ? | Sauvegarde |
|---|---|---|---|
| Scripts et `Caddyfile` | `deploy/vps/` | Non | Dépôt Git |
| `vps.config` (avec `PROXY_SECRET`) | `/etc/vps-proxy/vps.config` | Non | Gestionnaire de mots de passe |
| Certificats Let's Encrypt | `/var/lib/caddy/.local/share/caddy/` | Oui, un par domaine | **rsync quotidien** (ci-dessous) |
| Journaux Caddy | `/var/log/caddy/` | Oui | Aucune (rotation 10 × 50 Mo) |

## 1. Pourquoi sauvegarder les certificats

Ils se recréent seuls : à la première visite d'un domaine, Caddy en
redemande un. Mais Let's Encrypt limite les demandes (notamment
300 nouvelles commandes par 3 heures et 5 certificats identiques par
semaine). Après une reconstruction du VPS sans sauvegarde, avec beaucoup
de domaines, certains books resteraient sans HTTPS le temps que les
limites se libèrent. La sauvegarde évite ce trou.

Contenu copié : certificats, clés privées, compte ACME. Ce sont des
**secrets** : la destination doit être privée (hors dossier web).

## 2. Fonctionnement

- Script : `/usr/local/sbin/sauvegarder-certificats`
  (source : `deploy/vps/sauvegarder-certificats.sh`).
- Lancé chaque nuit par le timer systemd `sauvegarde-certificats.timer`
  (heure `SAUVEGARDE_HEURE`, 03:15 par défaut, ± 10 min ; rattrapé au
  démarrage si le VPS était éteint).
- `rsync -a --delete` sur SSH vers `SAUVEGARDE_DEST`, avec une clé dédiée
  `/root/.ssh/sauvegarde_ed25519` (sans phrase de passe, ne sert qu'à ça).
- Côté destination :

```
<SAUVEGARDE_DEST>/
├── actuel/                  copie à jour du stockage Caddy
└── supprimes/2026-10-14/    fichiers effacés côté VPS ce jour-là
```

  Un certificat supprimé côté VPS (domaine résilié, expiré) n'est pas perdu
  tout de suite : il passe dans `supprimes/<date>`, effacé après 30 jours.

## 3. Mise en place

Faite par `installer.sh` (étape 10) si `SAUVEGARDE_DEST` est renseigné.
Destination recommandée : l'hébergement o2switch (déjà accessible en SSH,
voir `deploy/TUTO_RSYNC_SSH.txt`), dans un dossier hors `public_html` :

```
SAUVEGARDE_DEST="<compte>@<compte>.universe.wf:sauvegardes/vps-proxy"
```

Après `installer.sh`, qui affiche la clé publique du VPS :

1. cPanel o2switch > **Accès SSH** > Gérer les clés SSH > Importer une clé :
   nom `vps-proxy`, coller la clé publique affichée. Puis **Autoriser**.
2. Si l'accès SSH est filtré par IP : cPanel > **Autorisation SSH** >
   ajouter l'IP du VPS.
3. Premier essai, sur le VPS :

```bash
sudo sauvegarder-certificats
# Sauvegarde OK : N certificat(s) vers ... (2026-10-14)
```

Sans `SAUVEGARDE_DEST`, le script n'est pas planifié : le renseigner dans
`vps.config` et relancer `installer.sh`.

## 4. Vérifier

```bash
systemctl list-timers sauvegarde-certificats.timer   # prochaine exécution
journalctl -u sauvegarde-certificats -n 20           # derniers comptes rendus
```

Côté o2switch : `ls sauvegardes/vps-proxy/actuel/certificates/`.

À contrôler une fois par mois (ou via la sonde de supervision) : la date
du dernier « Sauvegarde OK » dans le journal.

## 5. Restaurer (VPS reconstruit)

1. Nouveau VPS : `installer.sh` avec le même `vps.config` (même
   `PROXY_SECRET`, même `SAUVEGARDE_DEST`).
2. Autoriser la **nouvelle** clé publique du VPS sur o2switch (§3).
3. Restaurer, **avant** de faire pointer le DNS vers le nouveau VPS :

```bash
sudo restaurer-certificats
# Restauration OK : N certificat(s). Caddy redémarre.
```

4. Basculer le DNS : `proxy.ultra-book.com` et les domaines des créatifs
   vers la nouvelle IP (l'IP du VPS est dans `VPS_IP` du `.env` de prod ;
   les enregistrements des domaines achetés passent par l'API OVH).

Les certificats restaurés restent valides jusqu'à leur échéance ; Caddy
les renouvelle ensuite normalement.

## 6. Ce qui n'est pas sauvegardé, volontairement

- La configuration système (UFW, fail2ban, SSH) : `installer.sh` la refait.
- Les journaux : diagnostic seulement, perte sans conséquence.
- Les données des books et des domaines : elles sont sur le mutualisé,
  sauvegardées avec la base Ultra-book.
