# Mise en production sur O2switch — domaine de test extra-book.com

Objectif : faire tourner le nouveau site sur O2switch sous **extra-book.com**
le temps de la recette, sans toucher à la production actuelle
(ultra-book.com). La bascule définitive ne changera que les domaines du
`.env.prod`.

| | |
|---|---|
| Portail | https://www.extra-book.com |
| Books | https://\<login\>.extra-book.com |
| PHP | 8.3 |
| Base | `<compte>_ubdf` (nouvelle) + `<compte>_ub2020` (copie de l'ancienne, lecture seule) |

---

## 1. Domaine et DNS

1. cPanel > **Domaines** : ajouter `extra-book.com`, racine du document
   `~/extra-book/public` (et non `public_html`).
2. Zone DNS d'extra-book.com (chez le registrar, ou cPanel > Zone Editor si
   les NS pointent vers O2switch) :
   - `A  extra-book.com      -> IP du serveur O2switch`
   - `A  www.extra-book.com  -> même IP`
   - `A  *.extra-book.com    -> même IP` (**joker** : un sous-domaine par book)
3. cPanel > **Domaines** > créer le sous-domaine `*.extra-book.com`, même
   racine `~/extra-book/public`.
4. cPanel > **SSL/TLS Status** (AutoSSL / Let's Encrypt) : certificat pour
   `extra-book.com`, `www.` **et** `*.extra-book.com`. Le joker exige la
   validation DNS : si AutoSSL refuse, ouvrir un ticket O2switch, ils
   délivrent le certificat wildcard.

## 2. PHP

cPanel > **Sélecteur de version PHP** : 8.3, extensions `pdo_mysql`,
`mbstring`, `intl`, `gd` ou `imagick`, `zip`, `bcmath`, `fileinfo`,
`openssl`, `exif`. `memory_limit` 512M, `upload_max_filesize` et
`post_max_size` 64M, `max_execution_time` 300.

En SSH, le `php` par défaut n'est pas forcément 8.3 : utiliser
`/opt/alt/php83/usr/bin/php` (vérifier avec `ls /opt/alt/`). Dans la suite,
`php` désigne ce binaire :

```bash
alias php=/opt/alt/php83/usr/bin/php
```

## 3. Bases de données

cPanel > **Bases de données MySQL** :

1. Créer `<compte>_ubdf` et `<compte>_ub2020`, un utilisateur par base.
   L'utilisateur de `ub2020` n'a que le droit **SELECT**.
2. Exporter `ub2020` depuis la production actuelle **en conservant le
   latin1** (voir `_doc/06_charset.md`) :

```bash
mysqldump --default-character-set=latin1 --single-transaction ub2020 | gzip > ub2020.sql.gz
```

3. Importer sur O2switch :

```bash
gunzip -c ub2020.sql.gz | mysql --default-character-set=latin1 -u <user> -p <compte>_ub2020
```

## 4. Code

Accès SSH : cPanel > **Terminal** ou clé SSH (cPanel > Accès SSH).

```bash
cd ~
git clone <depot> extra-book
cd extra-book
php ~/composer.phar install --no-dev --optimize-autoloader
```

Les assets Vite se construisent sur le poste (`npm run build`) puis
s'envoient : `rsync -av public/build/ <compte>@<serveur>:extra-book/public/build/`.

## 5. `.env.prod`

Copier `.env.prod.example` en `.env.prod`, puis :

```ini
APP_URL=https://www.extra-book.com
APP_KEY=                              # php artisan key:generate

DB_DATABASE=<compte>_ubdf
DB_USERNAME=<user_ubdf>
DB_PASSWORD=…

DB_LEGACY_HOST=localhost
DB_LEGACY_PORT=3306
DB_LEGACY_DATABASE=<compte>_ub2020
DB_LEGACY_USERNAME=<user_ub2020>      # SELECT seul
DB_LEGACY_PASSWORD=…

BOOK_DOMAIN=extra-book.com
PORTAIL_DOMAIN=www.extra-book.com
UB_CANONIQUE=https://www.extra-book.com
SESSION_DOMAIN=.extra-book.com        # session partagée portail / books

LEGACY_BOOKS_PATH=/home/<compte>/users_2
```

`config/marques.php` reconnaît déjà extra-book.com comme hôte Ultra-book.
Tant que la recette dure, bloquer l'indexation (sinon Google indexe des
doublons d'ultra-book.com). `ubdf:robots` réécrit `robots.txt` chaque jour :
passer plutôt par un en-tête, en tête de `~/extra-book/public/.htaccess`
sur le serveur seulement, à retirer à la bascule :

```apache
Header set X-Robots-Tag "noindex, nofollow"
```

## 6. Fichiers des books

Envoyer l'ancien arbre `users_2` (≈ 30 Go) hors du web :

```bash
rsync -a --info=progress2 <ancien-serveur>:/chemin/users_2/ ~/users_2/
```

Puis recréer l'arborescence sur **trois lettres** (voir
`02_scripts.md`) :

```bash
php artisan ubdf:prod:dossiers-books --dry-run   # contrôle
php artisan ubdf:prod:dossiers-books             # copie
php artisan storage:link
```

## 7. Structure et données

```bash
php artisan migrate --force                        # schéma cible
php artisan db:seed --class=CategorySeeder --force # catégories métier
php artisan ubdf:prod:comparer-structures          # rapport SQL, relecture
php artisan ubdf:migrate-legacy --tous --skip-files   # par paquets de 2000, 5 à 6 h
php artisan ubdf:import-cms
php artisan ubdf:import-langues
```

`--skip-files` : les fichiers ont été traités à l'étape 6.

## 8. Tâches planifiées et files

cPanel > **Tâches Cron**, toutes les minutes :

```
* * * * * cd ~/extra-book && /opt/alt/php83/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

La file (`QUEUE_CONNECTION=database`) : O2switch n'autorise pas de
processus permanent, on la dépile par cron :

```
* * * * * cd ~/extra-book && /opt/alt/php83/usr/bin/php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

## 9. Optimisation

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

À relancer après chaque modification du `.env.prod` ou déploiement.

## 10. Recette

- Portail, connexion d'un compte repris avec son **ancien** mot de passe.
- Trois books au hasard : `https://<login>.extra-book.com`, visuels,
  pages, actualités.
- Un book Dustfolio (redirection vers son domaine de marque).
- Formulaire de contact d'un book, réception du courriel.
- Paiement Payplug en mode test.
- En-tête `X-Robots-Tag: noindex` présent (`curl -I https://www.extra-book.com`).

## Logins avec `_` ou `.`

Le `_` est interdit dans un nom d'hôte. Ces logins sont convertis, en base
comme sur le disque et dans les URL d'images des pages (`LegacyLogins`) :
`_` et `.` deviennent `-` (a_menguy -> a-menguy), ou `--` si le login en
`-` existe déjà (c_line -> c--line). 2 666 comptes sont concernés, dont
63 en `--`.

20 logins restent sans conversion possible (`_` ou `-` final dont la forme
nettoyée est déjà prise : `mimibelle_`, `g-`…) : ils sont écartés et
listés en fin de reprise. Les créatifs concernés sont à prévenir de leur
nouvelle adresse.
