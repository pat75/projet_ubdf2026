# Ultra-book 2026 — Plan de codage

Périmètre de travail : **exclusivement** `/Users/pat/Sites_2026/projet_ubdf2026`.
Base cible : `2026_ubdf` (vide). La base `ub2020` est en **lecture seule**, jamais modifiée.

## Phase 0 — Socle (0,5 j)

1. `composer create-project laravel/laravel` dans le répertoire cible (préserver `public/` existant), PHP 8.3, Laravel 12.
2. `.env` : `DB_DATABASE=2026_ubdf`, `DB_USERNAME/PASSWORD` = ceux d'UB2020, socket MAMP port 8889.
3. Second connection Eloquent `legacy` (lecture seule) vers `ub2020` — indispensable pour les scripts de migration.
4. Arborescence calquée sur Tesli : `app/{Actions,Services,Repository,Rules,Policies,Support,Helpers}`.
5. **Validation bloquante** : faire répondre `pat10.ubdf2026.ultra-book.name` sous Valet (driver custom ou dnsmasq wildcard). Tant que ce point n'est pas résolu, tout le reste est théorique.

## Phase 1 — Modèle de données (2 j)

Migrations Laravel propres, nommage normalisé, FK, timestamps, softDeletes :

| Legacy | Nouveau |
|---|---|
| `inc_user` | `users` (+ `profiles`) |
| `inc_user_pref` | `book_settings` |
| `ub2_gal_rub` | `galleries` |
| `ub2_gal_img` | `media` |
| `bn_ultranews_*` / `bn_ultrabook_art_portefolio` | `pages`, `posts`, `blocks` |
| `ub2_fac`, `df2_fac` | `invoices` (+ `brand`) |
| `ub2_contact_form`, `ub2_intermediate_form` | `messages`, `conversations` |
| `inc_stats`, `ub2_stats_mcles` | `visits`, `search_terms` |
| `inc_marketing`, `nl_newsletter`, `df2_mail_relance` | `campaigns`, `campaign_sends` |
| `inc_auto_selection*` | `selections`, `selection_user` |
| `ub2_codepromo`, `ub2_parrainage`, `inc_user_token` | `promo_codes`, `referrals`, `tokens` |

Règles : `enum('true','false')` → `boolean`, dates → `timestamp`, `down()` toujours rempli, `$fillable` explicite.

## Phase 2 — Migration des données (1,5 j)

Commande `php artisan ubdf:migrate-legacy --users=100`.

- Sélection d'un échantillon de 100 comptes représentatifs (formules variées, books remplis, au moins un par catégorie métier).
- Mots de passe : `us_pass` est **chiffré et réversible** (Rijndael-256 ECB, secret en dur dans `inc_user.php:7496`), pas hashé. → déchiffrement via `phpseclib3` puis re-hash `bcrypt`. Aucune réinitialisation utilisateur nécessaire.
- Images : copie physique des seuls 100 books retenus dans `storage/app/public/books/`. Aucun lien vers le projet 2019, jamais les 30 Go. Un compte sans images migrees affiche les visuels par defaut.
- Idempotence : la commande doit pouvoir être relancée (`truncate` de la base cible uniquement).
- Rapport de fin : comptage ligne à ligne source → cible.

## Phase 3 — Front public portail (4 j)

Routes nommées reproduisant la table d'URL de `01_etat_des_lieux.md` (redirections 301 des anciennes URL comprises — enjeu SEO majeur).

1. `FrontController` : accueil, portfolios, pagination AJAX des sélections.
2. Catégories métier + landings SEO (table de config `config/categories.php` plutôt que 30 routes en dur).
3. Annuaire alphabétique, recherche, mots-clés.
4. Fiche book SEO `/portfolio/{login}/{slug}`, minibook, microbook.
5. Contact + captcha + messagerie intermédiée (tokens).
6. Pages CMS (`/page__`, `/doc/`, actus).
7. Multi-marque (ultra-book / dustfolio) via middleware de résolution de domaine ; multi-langue `fr/en/ja` via `lang/`.

Vues : portage des templates `html_pages_v2018/tpl*` en Blade, **CSS et jQuery repris tels quels** (copiés dans `public/`, servis sans Vite dans un premier temps).

## Phase 4 — Books sur sous-domaines (4 j)

1. `Route::domain('{login}.'.config('app.book_domain'))` + `BookController`.
2. Résolution du créatif par `login`, 404 dédiée si inexistant/suspendu.
3. Rendu : accueil, portfolio, rubrique/page, actus, contact, PDF, version mobile.
4. Modèles de design du book (`inc_user_pref` → `book_settings`) appliqués via Blade + variables CSS.
5. Service d'images : génération des déclinaisons à la demande (Intervention Image + cache) — remplace les 12 dossiers pré-générés et `phpThumb`.
6. Suppression des `index.php` générés par book : plus aucun fichier PHP dans `users_2/`, gain de sécurité immédiat.

## Phase 5 — Espace créatif (5 j)

Auth Breeze. Édition du book (rubriques, upload drag & drop, réordonnancement), pages CMS, préférences de design, statistiques, messages, formules et factures, parrainage, dispo.

## Phase 6 — Back-office admin (4 j)

Remplace les ~60 scripts de `admin_/` : utilisateurs, modération, factures, relances, sélections éditoriales, newsletters, codes promo, exports.

## Phase 7 — Paiement & emailing (3 j)

Formules d'abonnement, PayPlug/PayPal ou bascule Stripe (à arbitrer), génération de factures PDF (dompdf), relances automatiques en queues Redis.

## Phase 8 — Finition (3 j)

Tests Pest sur le métier (résolution de sous-domaine, formules, quotas d'images, messagerie), sitemap, robots, redirections 301 exhaustives, `spatie/laravel-backup`, mise au propre des secrets.

## Phase 9 — (différée) Alpine.js + Tailwind

Conformément à la demande : jQuery → Alpine, CSS → Tailwind, **après** la mise en service fonctionnelle.

## Améliorations proposées

**Front** — URLs canoniques uniques (aujourd'hui 5 alias par page), images WebP/AVIF responsive + lazy loading, suppression de phpThumb, Core Web Vitals, sitemap généré dynamiquement.

**Book** — plus de fichiers PHP générés sur disque, rendu 100 % dynamique avec cache HTTP ; thèmes en composants Blade ; HTTPS et certificat wildcard.

**Back-office** — remplacement des scripts isolés par des ressources CRUD avec Policies, journal d'audit, impersonation (existe déjà chez Tesli), exports en queue.

**Technique** — secrets hors du dépôt, mots de passe bcrypt, suppression des 6 arborescences historiques mortes, une seule base de code au lieu de trois, tests automatisés, CI de déploiement rsync.

## Decisions arretees (2026-09-15)

| Sujet | Decision |
|---|---|
| Sous-domaines | Wildcard Valet/dnsmasq sur `*.ubdf2026.ultra-book.name` + `Route::domain('{login}....')`. Aucun fichier PHP genere par book. |
| Images en dev | Copie des 100 books de test uniquement, pas de disk vers le projet 2019. |
| Pipeline images | Generation a la demande (Intervention Image) + cache. phpThumb et les 12 dossiers pre-generes sont supprimes. |
| Paiement | Arbitrage PayPal/PayPlug vs Stripe reporte a la phase 7. |
