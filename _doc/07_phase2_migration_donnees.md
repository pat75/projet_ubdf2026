# Phase 2 — Reprise des donnees : termine (2026-09-16)

## Commande

```bash
php artisan ubdf:migrate-legacy --users=100 [--fresh] [--skip-files]
```

Idempotente (appui sur `legacy_id`), relançable. La base `ub2020` n'est jamais ecrite : la connexion `legacy` le garantit techniquement.

## Resultat sur 100 comptes

| Element | Volume |
|---|---|
| comptes creatifs | 100 |
| reglages de book | 100 |
| rubriques | 902 |
| visuels | 13 724 |
| pages / actualites | 145 / 1 811 |
| conversations / messages | 399 / 455 |
| factures | 371 |
| statistiques | 100 |
| fichiers copies | 12 986 (4,2 Go) |
| books introuvables | 0 |
| mots de passe perdus | 0 |

Couverture : **13/13 categories**, les deux marques (92 ub / 8 df), accents preserves (`Amélie Falière`, `Réalisations graphiques`).

## Mots de passe — aucune reinitialisation necessaire

Le legacy ne hashe pas : il **chiffre de facon reversible** (`inc/inc_user.php:7496`, `encryptClass`). Deux formats coexistent :

| Format | Algorithme | Particularite |
|---|---|---|
| `encode()` | Rijndael-256 ECB | cle de 18 octets **completee a 24** par mcrypt (taille valide suivante, pas 32) ; clair prefixe `"<len>-"` |
| `encode_2()` | AES-256-CBC | ajoute au passage a PHP 7.2 ; **pas** de prefixe de longueur |

mcrypt n'existe plus en PHP 8.3 et openssl ne gere pas les blocs de 256 bits : `phpseclib3` est la seule voie. `App\Support\LegacyPassword` gere les deux formats et rehashe en bcrypt.

**Couverture : 77 772 / 77 775 (99,996 %)**. 3 echecs seulement (2 comptes au chiffre identique corrompu, 1 compte de test), qui passeront par « mot de passe oublie ». 84 comptes ont un mot de passe vide.

> Cette classe n'a aucune raison d'exister au-dela de la reprise : **a supprimer** une fois la migration de production faite.

## Pieges rencontres

**1. `cf_us_id` / `mf_us_id` sont des `varchar` heterogenes.** Selon les lignes : un login (`aalex`), un `us_id` numerique (`1419`), ou rien. `LegacyUserResolver` absorbe les trois cas et distingue desormais deux situations, qui etaient confondues au depart :
- *hors echantillon* (10 995) — reference valide, compte simplement non repris en developpement ;
- *orpheline* (498) — reference ne correspondant a aucun compte, meme dans `ub2020`.

**2. Compteurs negatifs.** `us_img_size` descend a **-31 276** (108 comptes) et `us_formule_nbmois` a **-48** (8 comptes). Les colonnes cibles etant non signees, l'insertion echouait. Valeurs ramenees a zero plutot que rejetees.

**3. Le sampler ignorait le disque.** Premiere execution : 88 books sur 100 sans fichiers. Le poste de developpement ne detient que **5 276 books sur 6 585**, et le sampler classait par richesse en base sans verifier la presence du dossier. Il filtre maintenant sur l'existence de `users_2/<a>/<b>/<login>/img_`.

**4. `->each()` exige un `orderBy`** sur une requete Query Builder — corrige sur la resolution des parents.

## Selection de l'echantillon

`LegacySampler` compose l'echantillon plutot que de prendre les 100 premiers :
au moins un compte par categorie metier, 15 abonnes, 10 Dustfolio, 10 en ultra-selection, complete par les books les plus fournis. Ne retient que les comptes vivants, au login conforme, avec au moins 5 visuels publies et un dossier present sur le disque.

## Fichiers

Seuls les **originaux** sont repris (`img_`, puis `img_adm_medium`, `img_ptf_medium` en repli). Les 9 dossiers de declinaisons pre-generees sont ignores : elles seront regenerees a la demande en phase 4.

> 4,2 Go pour 100 books, soit ~42 Mo par book. Si l'empreinte disque doit baisser, le levier est de ne copier que `img_ptf_medium` (visuels deja redimensionnes) au lieu des originaux.

## Tests

`vendor/bin/pest --exclude-group=slow` — 25 tests verts, dont le dechiffrement des deux formats de mot de passe sur des valeurs reelles de `ub2020`.
