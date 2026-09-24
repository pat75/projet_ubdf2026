# Charset — etat homogene (2026-09-15)

## Regle unique

**utf8mb4 / utf8mb4_unicode_ci partout**, comme Tesli 2026. Seule exception assumee : la connexion de lecture du legacy.

| Niveau | Valeur |
|---|---|
| Schema `2026_ubdf` (defaut) | `utf8mb4` / `utf8mb4_unicode_ci` |
| Tables `2026_ubdf` (26) | `utf8mb4_unicode_ci`, `ROW_FORMAT Dynamic` |
| Connexion `mysql` (`config/database.php`) | `utf8mb4` / `utf8mb4_unicode_ci` |
| `.env.dev` / `.env.prod` (+ `.example`) | `DB_CHARSET` et `DB_COLLATION` explicites |
| Connexion `legacy` (ub2020) | **`latin1` / `latin1_swedish_ci`** — voir ci-dessous |

Le defaut du schema etait reste en `utf8 / utf8_general_ci` (heritage de la creation manuelle, meme ecart que sur `2026_tesli_site`). Corrige par `ALTER DATABASE`. Sans effet sur les tables existantes, mais toute table creee hors migration heritait du mauvais charset.

## Pourquoi la connexion legacy reste en latin1

Les 29 tables de `ub2020` sont declarees `latin1_swedish_ci` mais **contiennent des octets UTF-8 bruts** : l'ancien site ecrivait via une connexion latin1, MySQL a donc stocke les octets sans conversion.

- Lue en `latin1`, la base restitue ces octets tels quels → UTF-8 correct.
- Lue en `utf8`/`utf8mb4`, MySQL convertit latin1 → utf8 et **double encode tout le corpus** : `Scénographe` devient `ScÃ©nographe`.

C'est contre-intuitif, d'ou un commentaire explicite dans `config/database.php` et des tests qui verrouillent le comportement.

## Audit du corpus legacy

2 031 104 valeurs verifiees sur 11 colonnes textuelles (`inc_user`, `inc_user_pref`, `ub2_gal_rub`, `ub2_gal_img`) :

| Anomalie | Volume |
|---|---|
| Valeurs non UTF-8 valides | **2** — `ub2_gal_img.img_id` 477277 et 1414387, `varchar(250)` tronques au milieu d'un caractere accentue |
| Double encodage figé en base | **0** |

> **Rectification.** Une premiere mesure annoncait 27 lignes en double encodage. C'etait un artefact : elle avait ete faite *avant* le passage de la connexion en latin1, et tout octet `C3` s'affichait alors comme `Ã`. La verification au niveau des octets (sequences `C383C2`, `C383C3`, `C3A2C280`) donne **zero** sur toutes les colonnes principales. Aucune correction de masse n'est donc necessaire.

## `App\Support\LegacyText`

Point de passage unique de toute chaine reprise du legacy en phase 2 :

1. **Troncature** — supprime la sequence UTF-8 incomplete en fin de chaine (les 2 cas reels).
2. **Double encodage** — filet preventif, **inactif sur le corpus actuel**. N'agit que si un marqueur (`Ã©`, `â€™`, …) est present **et** que le re-decodage produit de l'UTF-8 valide : un texte contenant legitimement « Ã » est laisse intact.
3. **Repli Windows-1252** — si une valeur restait invalide.

## Tests

`vendor/bin/pest` — 20 tests verts.

- `tests/Unit/LegacyTextTest.php` : 11 cas, dont la troncature reelle et la non-regression sur texte correct.
- `tests/Feature/Legacy/LegacyMappingTest.php`, groupe `slow` : rejoue l'audit complet (~180 s) et la recherche de double encodage. Ils echouent si le corpus sort du profil mesure.

Exclure le groupe lent en boucle courte : `vendor/bin/pest --exclude-group=slow`.

## Dependance serveur a verifier avant tout deploiement

Les index sur `varchar(255)` en utf8mb4 (`users.email`, `invoices.number`, `conversations.token`, `sessions.id`…) pesent 1020 octets, au-dela de la limite historique de 767. Ils ne passent que grace a :

```
innodb_large_prefix = 1
innodb_file_format  = Barracuda
innodb_default_row_format = dynamic
```

Configuration presente sur le MySQL 5.7.34 local. **Sur un serveur mal configure, les migrations echoueront en erreur 1071** (`Specified key was too long`).
