# Phase 0 — Socle : termine (2026-09-15)

## Livre

1. **Laravel 12.69.2** installe dans `/Users/pat/Sites_2026/projet_ubdf2026` (PHP 8.3.33).
2. **`.env`** : `APP_URL=https://ubdf2026.ultra-book.name`, locale `fr`, base `2026_ubdf`, `BOOK_DOMAIN`, `LEGACY_BOOKS_PATH`.
3. **Connexion `legacy`** (`config/database.php`) vers `ub2020`, **en lecture seule** : `App\Providers\LegacyDatabaseServiceProvider` intercepte via `beforeExecuting()` toute requete dont le verbe n'est pas `select|show|describe|desc|explain|set` et leve une `RuntimeException`.
4. **`config/ubdf.php`** : `book_domain`, `legacy_books_path`, `dev_users_sample`.
5. **Arborescence type Tesli** : `app/{Actions,Services,Repository,Rules,Policies,Support,Helpers,Listeners,Notifications,Mail,Console/Commands,View/Components}` et `app/Http/Controllers/{Front,Book,Creatif,Admin}`.
6. **Routage par sous-domaine** operationnel dans `routes/web.php` (`Route::domain('{login}.'.config('ubdf.book_domain'))`), declare avant les routes du portail.

## Verifications passees

```
legacy inc_user : 77859
cible           : 2026_ubdf
garde-fou       : Ecriture interdite sur la connexion legacy (ub2020) : [update].

https://ubdf2026.ultra-book.name/              -> PORTAIL
https://pat10.ubdf2026.ultra-book.name/        -> BOOK · login = pat10
https://jean-dupont.ubdf2026.ultra-book.name/  -> BOOK · login = jean-dupont
```

Migrations Laravel de base (`users`, `cache`, `jobs`) appliquees sur `2026_ubdf`.

## Points d'attention

- MySQL MAMP n'ecoute **pas en TCP** : `DB_SOCKET` / `DB_LEGACY_SOCKET` obligatoires.
- Le `php` du PATH est MAMP 8.1 — utiliser `/usr/local/opt/php@8.3/bin/php`.
- Les routes de `routes/web.php` sont des placeholders de validation, a remplacer en phases 3 et 4.
- `.env` contient des mots de passe : a exclure du depot des l'initialisation de git.

## Suite : phase 1 — modele de donnees
