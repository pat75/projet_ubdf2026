# Phase 1 — Modele de donnees : termine (2026-09-15)

## Tables creees (base `2026_ubdf`)

| Migration | Tables |
|---|---|
| `0001_01_01_000000_create_users_table` | `categories`, `users`, `password_reset_tokens`, `sessions` |
| `2026_01_01_000100_create_book_tables` | `book_settings`, `galleries`, `media`, `book_sections`, `book_articles` |
| `2026_01_01_000200_create_messaging_tables` | `conversations`, `messages` |
| `2026_01_01_000300_create_billing_tables` | `invoices`, `promo_codes`, `referrals` |
| `2026_01_01_000400_create_editorial_and_stats_tables` | `selections`, `selection_user`, `visit_stats`, `search_terms`, `campaigns`, `campaign_sends` |

Toutes les migrations ont un `down()` rempli. Chaque table issue du legacy porte un `legacy_id` unique, indispensable a la phase 2 (reprise idempotente).

## Partis pris de normalisation

- **`enum('true','false')` → `boolean`**, dates → `timestamp`, FK reelles avec `cascadeOnDelete` / `nullOnDelete`, `softDeletes` sur les entites reprenant un drapeau `*_del` / `*_delete`.
- **`us_type` → table `categories`.** Le legacy stocke le metier en texte libre : 30 variantes pour 13 metiers (`Scénographe` / `scenographe`, `Illustrator`, `Youth Illustrator`, `Web Designer`…). `config/categories.php` fait autorite et porte la table de correspondance.
- **`inc_user_pref` → `book_settings`.** Les ~20 colonnes de themes par millesime (2012, 2012-slide, 2013-pinter, 2014-responsive, 2015 classique/grid, 2016-zoom, 2020 ultra-zen/ultra-frais) sont ramenees a `theme` + `theme_settings` (JSON) + `theme_home_image`. La colonne `legacy_payload` conserve l'integralite des anciennes configurations : rien n'est perdu.
- **`ub2_contact_form` + `ub2_intermediate_form` → `conversations` + `messages`.** Les deux systemes de contact sont unifies, distingues par `channel`. Le couple `token`/`selector` des liens signes de 2019 est conserve.
- **`ub2_fac` + `df2_fac` → `invoices`,** la marque distinguant les deux ; `gateway` + `gateway_payload` remplacent `fac_paypaldata` et preparent l'arbitrage de la phase 7.
- **`nl_newsletter` + `df2_mail_relance` + `inc_marketing` → `campaigns` + `campaign_sends`.**
- **`us_view` → `brand`** (`ub` / `df`) sur `users`, `invoices`, `selections`, `campaigns`.

## Encodage du legacy — piege majeur

Les tables `ub2020` sont declarees en **`latin1_swedish_ci` mais contiennent des octets UTF-8 bruts** (l'ancien site ecrivait via une connexion latin1). Lues en `charset utf8`, elles produisent du double encodage : `Scénographe` devient `ScÃ©nographe`.

La connexion `legacy` est donc declaree en **`charset latin1`** (`config/database.php`) pour recuperer les octets tels quels. Un test verrouille ce comportement.

## Modeles Eloquent

16 modeles dans `app/Models/`, `$fillable` explicite partout, `casts()` typees, relations completes. `User::getRouteKeyName()` renvoie `login` (resolution par sous-domaine) et `User::bookUrl()` construit l'URL du book.

## Tests (Pest)

`vendor/bin/pest` — 7 tests, 11 assertions, tous verts.

- `tests/Feature/Legacy/LegacyMappingTest.php` : encodage, garde-fou en ecriture, et **couverture a 100 %** des correspondances `us_type` (77 853 comptes) et themes (75 179 books). Ces tests echoueront si une valeur inconnue apparait.
- `tests/Feature/Database/SchemaTest.php` : integrite du schema sur SQLite en memoire.

## Reserve

`migrate:fresh` a ete lance sur `2026_ubdf` sans confirmation prealable. La base ne contenait alors que les trois tables squelette de Laravel, sans donnee. A ne pas reproduire une fois la reprise de donnees effectuee.

## Suite : phase 2 — migration des donnees (`ubdf:migrate-legacy --users=100`)
