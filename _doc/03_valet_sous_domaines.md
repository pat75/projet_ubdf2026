# Sous-domaines des books sous Valet — configuration validée

Objectif : `<login>.ubdf2026.ultra-book.name` doit servir le même projet Laravel que `ubdf2026.ultra-book.name`, pour que `Route::domain('{login}.…')` fonctionne.

## Constat

- Le DNS wildcard est déjà OK (dnsmasq Valet : tout `*.name` → 127.0.0.1).
- Le server block nginx du site déclare déjà `server_name … *.ubdf2026.ultra-book.name` et le certificat couvre le wildcard.
- **Mais** Valet fait passer tout le PHP par son routeur `~/.composer/vendor/laravel/valet/server.php`, qui résout le site par `HTTP_HOST` et renvoie « Valet - Not Found » pour un sous-domaine non lié.

## Correctif appliqué

Fichier `~/.config/valet/Nginx/ubdf2026.ultra-book.name` (sauvegarde : `~/.config/valet/Nginx/ubdf2026.ultra-book.name.bak.valet`, copie de référence : `_doc/valet/`) :

1. `root /;` → `root "/Users/pat/Sites_2026/projet_ubdf2026/public"; index index.php index.html;`
2. `location / { rewrite ^ ".../valet/server.php" last; }` → `location / { try_files $uri $uri/ /index.php?$query_string; }`
3. Suppression de `error_page 404 ".../valet/server.php";`
4. Dans `location ~ \.php` :
   - `fastcgi_param SCRIPT_FILENAME` → `$document_root$fastcgi_script_name`
   - `fastcgi_index` → `index.php`

Puis `valet restart`.

## Vérification

```
curl -sk https://ubdf2026.ultra-book.name/          # parent
curl -sk https://pat10.ubdf2026.ultra-book.name/    # book existant
curl -sk https://xyz-test.ubdf2026.ultra-book.name/ # book inexistant -> 404 applicative
```
Les trois atteignent bien `public/index.php` avec le bon `HTTP_HOST`. **Validé le 2026-09-15.**

## Books Dustfolio : `<login>.ubdf-dust-2026.ultra-book.name`

Chaque marque a son domaine de books (`domaine_books` dans
`config/marques.php`, surchargé par `BOOK_DOMAIN` et `DF_BOOK_DOMAIN`). Un
book n'est servi que sur le domaine de la marque de son compte
(`users.brand`) ; sur l'autre, `BookSurSonDomaine` redirige (301), chemin
et paramètres conservés.

| `.env` | Test | Production |
|---|---|---|
| `BOOK_DOMAIN` | `extra-book.com` | `ultra-book.com` |
| `DF_BOOK_DOMAIN` | `extra-book.biz` | `dustfolio.com` |

DNS et certificat : joker `*.<domaine>` pour chacun.

Même correctif appliqué au bloc `~/.config/valet/Nginx/ubdf-dust-2026.ultra-book.name`
(sauvegarde : `.bak.valet`, copie de référence :
`_doc/valet/nginx-ubdf-dust-2026.ultra-book.name.conf`). **Validé le 2026-09-29.**

## Attention

`valet link`, `valet secure` ou `valet unsecure` sur ce site **régénèrent** le fichier et écrasent le correctif. Le cas échéant, réappliquer depuis `_doc/valet/nginx-ubdf2026.ultra-book.name.conf`.

## robots.txt

robots.txt est propre à chaque marque : chaque domaine n'annonce que son
sitemap (`App\Support\Robots`, `RobotsController`). Il est donc rendu par
Laravel, et `public/robots.txt` ne doit **pas** exister.

Le bloc serveur de Valet (comme celui de Forge) contient
`location = /robots.txt { … }`, qui sert le fichier statique et renvoie 404
s'il manque. Il faut lui ajouter le passage à Laravel, dans chaque bloc
(Valet ubdf2026, Valet Dustfolio, **Forge en production**) :

```nginx
location = /robots.txt  { access_log off; log_not_found off; try_files $uri /index.php?$query_string; }
```

Correctif déjà reporté dans `_doc/valet/*.conf`.
