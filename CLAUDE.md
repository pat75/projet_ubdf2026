# Ultra-book 2026 — instructions projet

Refonte de la plateforme Ultra-book en Laravel 12 / PHP 8.3.
Documentation : `_doc/01_etat_des_lieux.md`, `_doc/02_plan_de_codage.md`, `_doc/03_valet_sous_domaines.md`.

## Regles absolues

- Tout le developpement se fait dans `/Users/pat/Sites_2026/projet_ubdf2026`. Rien ailleurs.
- `/Users/pat/Sites_2019/_projet_ubdf_2020` et la base `ub2020` sont des **sources en lecture seule**. Aucune modification, jamais.
- `/Users/pat/Sites_2026/projet_tesli2026/tesli_www` sert de reference d'architecture. Ne jamais le modifier.
- Base de travail : `2026_ubdf` uniquement.

## Piege : binaire PHP

Le `php` du PATH est **MAMP 8.1.13**, pas 8.3. Toujours utiliser :

```bash
/usr/local/opt/php@8.3/bin/php artisan ...
/usr/local/opt/php@8.3/bin/php /usr/local/bin/composer.phar ...
```

## Environnement

| | |
|---|---|
| Portail | https://ubdf2026.ultra-book.name |
| Book creatif | https://\<login\>.ubdf2026.ultra-book.name |
| Serveur | Valet (nginx, tld `.name`), server block patche — voir `_doc/03_valet_sous_domaines.md` |
| MySQL | MAMP, **socket uniquement** (`/Applications/MAMP/tmp/mysql/mysql.sock`), pas de TCP sur 8889 |
| Connexion `legacy` | base `ub2020`, protegee en ecriture par `App\Providers\LegacyDatabaseServiceProvider` |
