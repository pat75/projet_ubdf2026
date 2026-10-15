# Achat d'un nom de domaine par le créatif

Bloc « Option — Réserver mon nom de domaine » (fond noir, arrondi des cartes de formules) sous les formules
(`/espace/formule`). **Développement seulement** pour l'instant
(`Domaine::achatOuvert()` : local, development, testing ; filet rouge
`dev_only`). En production : rien n'est affiché, `/espace/domaine`
répond 404.

## Parcours

1. **Recherche** (champ en pastille, comme la newsletter du pied de page, sélecteur `.fr`/`.com` avant le bouton) : le créatif saisit un nom (« Jeanne Dupont » →
   `jeanne-dupont`) ; disponibilité et prix de `.fr` (16 € TTC) et `.com`
   (19 € TTC), `config/domaines.php`.
2. **Titulaire** : pré-rempli depuis Mon compte, aux formats OVH (pays
   ISO, téléphone `+33.612345678`). Le créatif est propriétaire du
   domaine. Un `.fr` exige un pays de l'Union européenne (Afnic).
3. **Consentements** obligatoires : CGU (page
   `conditions-dutilisations` / `conditions-of-use`) et renonciation
   expresse au droit de rétractation (art. L.221-28 C. conso.). Date, IP
   et version des textes gardées (`cgu_acceptees_le`,
   `renonciation_le`, `consentement_ip`, `consentement_version`).
4. **Vérification OVH** (`AchatDomaine::preparer`) : domaine toujours
   libre, panier validé à blanc. Domaine enregistré `a_payer`.
5. **Paiement Payplug** (`PaiementDomaine`) : `metadata.product_id = 2`.
   Payé = relu chez Payplug (notification serveur, ou relecture au retour
   sur `?domaine=retour`, utile en local). Montant vérifié, rejeu sans
   effet, paiement de test refusé en production. Facture « Nom de
   domaine x (1 an) », statut `paye`.
6. **Commande OVH** (job `CommanderDomaine`, un seul essai) : statut
   `commande`, `reserve_le`, `expire_le` (+1 an). Échec OVH :
   `echec_commande`, remboursement Payplug automatique (`rembourse`),
   mail au créatif (copie à l'adresse d'envoi du site).
7. **DNS** : `ubdf:verifier-domaines` (horaire) pointe le domaine livré
   vers le VPS et l'active.
8. **Gestion** (`/espace/domaine`, menu Mon portfolio > Nom de domaine,
   visible dès le paiement) : statut, dates, prix, facture, titulaire ;
   **résilier** (pas de renouvellement, actif jusqu'à l'échéance,
   statut `resilie`) ; **transférer** (verrou levé, code AuthCode
   affiché, statut `transfert`).

## Relances avant échéance

`ubdf:relancer-domaines` (tous les jours, 10 h 30) : mail
`RelanceDomaine` à J-30, J-15 et J-5 (`Domaine::RELANCES`) au titulaire
(à défaut, au mail du compte). Chaque palier une seule fois ; un domaine
découvert tard ne reçoit qu'une relance. Pas de relance pour un domaine
résilié ou en transfert. Compteur `relances_envoyees` et
`derniere_relance_le`.

À prévoir avec le renouvellement : remettre `relances_envoyees` à 0 quand
`expire_le` avance d'un an.

## Administration

Créatifs > **Noms de domaine** (`DomaineResource`, lecture seule) :
bouton « se connecter en tant que » devant l'avatar, créatif, domaine,
statut, prix, dates d'achat, d'échéance et de résiliation, relances
envoyées (« 2 / 3 », date de la dernière), filtre par statut.

## Modes OVH (`OVH_MODE`)

| Mode | Usage | Appels OVH |
|---|---|---|
| `simulation` (défaut) | local sans clés, tests | aucun (`FauxOvh`) : un nom contenant « pris » est indisponible, « echec » fait échouer la commande (recette du remboursement) |
| `dry-run` | local avec clés | vrais appels jusqu'à `GET /order/cart/{id}/checkout` (validation à blanc), jamais le `POST` |
| `reel` | production | commande définitive, payée par le moyen de paiement par défaut du compte OVH |

OVH n'a pas d'environnement de test : `dry-run` est le seul moyen de
valider signature, panier et contact sans acheter.

## Recette locale

`OVH_MODE=simulation` + clé Payplug **de test** (`sk_test_…`) : parcours
complet avec une carte de test Payplug, rien n'est acheté ni débité.
Si Payplug ne peut pas joindre la notification en local, le retour sur
la page formule relit le paiement et termine le parcours.

## À faire avant production

- Valider l'enchaînement en `dry-run`, puis un premier achat `reel`.
- Routes OVH de résiliation et de transfert (`/domain/{nom}/serviceInfos`,
  `/domain/{nom}/authInfo`, `transferLockStatus`) : à confirmer en
  console.
- Renouvellement annuel et sa facturation : avec Ma formule.
- Retirer la restriction `achatOuvert()`.

## Fichiers

| Fichier | Rôle |
|---|---|
| `config/domaines.php` | prix, TVA, mode OVH, pays, UE, version des textes |
| migration `add_achat_to_domaines_table` | colonnes d'achat |
| `app/Models/Domaine.php` | statuts, prix, validation du libellé, `achatOuvert()` |
| `app/Services/Domaines/AchatDomaine.php` | recherche, titulaire, préparation |
| `app/Services/Domaines/Ovh.php`, `FauxOvh.php` | OVH réel / dry-run / simulé |
| `app/Services/Paiement/PaiementDomaine.php` | Payplug, facture, déclenchement de la commande |
| `app/Jobs/CommanderDomaine.php` | commande OVH, remboursement |
| `app/Livewire/Espace/NomDeDomaine.php` + vue | bloc sous les formules |
| `app/Livewire/Espace/MonDomaine.php` + vue | page `/espace/domaine` |
| `tests/Feature/Espace/NomDeDomaineTest.php` | parcours complet (17 tests) |
| `app/Console/Commands/RelancerDomainesCommand.php`, `app/Mail/RelanceDomaine.php` | relances J-30/15/5 |
| `app/Filament/Resources/Domaines/*` | liste d'administration |
| `tests/Feature/Admin/DomainesAdminTest.php` | administration et relances (5 tests) |

## Relances et renouvellement

- `ubdf:relancer-domaines` (chaque jour, 10 h 30) : mail à J-30, J-15, J-5
  (`Domaine::RELANCES`), une fois par palier, compteur `relances_envoyees`
  visible dans l'admin (Créatifs > Noms de domaine).
- Mail `RelanceDomaine` traduit : envoyé dans la langue de la marque
  (anglais pour Dustfolio, `lang/en.json`).
- **À faire, avec « Ma formule »** : renouvellement annuel du domaine
  (paiement Payplug puis renouvellement OVH), bouton « Renouveler » sur la
  page Nom de domaine, nouvelle `expire_le`, remise à zéro de
  `relances_envoyees` et `derniere_relance_le`.

## Domaine déjà possédé ailleurs (source externe)

Gratuit : le domaine reste chez le registrar du créatif (Gandi, IONOS…),
qui le fait pointer vers le VPS.

1. Bloc « Réserver mon nom de domaine » > « J'ai déjà un nom de domaine » :
   saisie du domaine (`NomDeDomaine::brancher`, service `BrancherDomaine`).
   Refusés : nom invalide, domaine de la plateforme, domaine déjà utilisé,
   créatif qui a déjà un domaine.
2. Ligne `domaines` : `source = externe`, `statut = a_verifier`, `jeton`
   aléatoire (32 caractères).
3. Page Nom de domaine : deux réglages DNS chez le registrar :
   - `TXT  _ultra-book.<domaine>  ultra-book-verification=<jeton>`
     (Dustfolio : `_dustfolio.<domaine>  dustfolio-verification=<jeton>`) : preuve
     que le créatif a la main sur le domaine (sinon n'importe qui pourrait
     attacher le domaine d'un autre à son book) ;
   - `A  <domaine>  <VPS_IP>` (et `AAAA` vers `VPS_IP6` si renseigné).
4. `ubdf:verifier-domaines` (horaire) ou bouton « Vérifier maintenant » :
   TXT vu → `en_attente` ; puis A vers le VPS → `actif`, servi par la
   passerelle (`autorise` répond 200).
5. Une déclaration jamais prouvée est supprimée après 7 jours
   (`Domaine::DELAI_VERIFICATION`) et ne bloque pas le vrai propriétaire :
   s'il déclare le même domaine, la déclaration non prouvée est remplacée.
6. Pas de facture, de relance d'échéance, de transfert ni de résiliation :
   c'est le registrar qui gère. « Retirer ce domaine » le détache du book.

Transfert d'un domaine vers le compte OVH d'Ultra-book (cas B) : non fait.
