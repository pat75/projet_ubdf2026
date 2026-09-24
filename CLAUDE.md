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

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- Ne PAS lancer `graphify update .` a la main apres chaque modification : un
  passage complet prend ~3 minutes, meme a cache chaud. Le hook git
  `post-commit` (installe par `graphify hook install`) rebatit le graphe en
  tache de fond, de facon incrementale, apres chaque commit. Le graphe est
  donc a jour du dernier commit, pas du dernier fichier edite.
- `graphify-out/` n'est pas versionne : c'est un artefact derive (5 Mo). Sur
  un depot fraichement clone, lancer une fois `graphify update .`.

## Charte graphique

Toute modification graphique d'une page de l'espace suit la charte :

@Claude_design.md
