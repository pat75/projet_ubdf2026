# Scripts de mise en production

Tous en commandes Artisan (lecture seule sur `ub2020` et `users_2`).

## `ubdf:prod:comparer-structures`

Compare `information_schema` de l'ancienne base (connexion `legacy`) et de
la nouvelle, écrit dans `sql/` :

- `comparaison_structure.sql` — rapport commenté : correspondances
  ancienne table -> nouvelle(s), colonnes des deux côtés, tables
  abandonnées, tables nouvelles ;
- `conversion_structure.sql` — ALTER (tables de même nom), CREATE (tables
  nouvelles), DROP commentés (tables abandonnées). À n'exécuter que sur une
  **copie** de l'ancienne base.

Le schéma de référence reste celui des migrations Laravel. Les **données**
ne se convertissent pas en SQL : mots de passe chiffrés à rehasher
(`LegacyPassword`), identifiants hétérogènes, textes latin1 à nettoyer. Elles
passent par :

```bash
php artisan ubdf:migrate-legacy --tous --skip-files
```

`--tous` : tous les comptes vivants, sans l'échantillon de développement,
**par paquets successifs** de comptes (us_id croissants) :

- chaque paquet est repris en entier (compte, book, visuels, pages,
  factures, fichiers) avant le suivant ; la mémoire est libérée entre deux ;
- aucune requête ne charge une table entière (1,08 M visuels, 694 000
  corps d'articles) : lectures par lots de 1 000 identifiants ;
- messagerie, parrainages et codes promo passent à la fin, une fois tous
  les comptes créés ;
- chaque paquet affiche sa ligne de relance. Après une coupure :
  `--depuis=<us_id affiché>` repart au paquet suivant. Rejouer un paquet ne
  crée pas de doublon ;
- une valeur plus longue que sa colonne est coupée et comptée
  (« valeurs tronquées ») au lieu d'arrêter la reprise.

```bash
php artisan ubdf:migrate-legacy --tous --skip-files [--paquet=2000] [--depuis=0]
php artisan ubdf:migrate-legacy --tous --paquet=100 --max-paquets=3 --skip-files   # essai
```

Mesure en local : ~35 s pour 100 comptes (hors fichiers), soit 5 à 6 h pour 60 000.
Lancer dans `screen` ou avec `nohup … > reprise.log &`.

## `ubdf:prod:dossiers-books`

Recrée l'arborescence des books sur trois lettres :

```
users_2/a/d/adolie/img_…   ->   storage/app/public/books/a/d/o/adolie/…
```

- parcourt tout `users_2`, pas seulement les comptes en base ;
- copie les originaux (`img_`, replis `img_adm_medium`, `img_ptf_medium`),
  `cms_pref` et `img_cms` ; les déclinaisons se régénèrent à la demande ;
- la source n'est jamais modifiée ; un fichier déjà copié est ignoré,
  la commande se relance sans risque ;
- login de moins de trois caractères complété par `_` (`ab` -> `a/b/_/ab`) ;
- login à `_` ou `.` rangé sous son login converti, même table que la
  reprise en base : `users_2/a/_/a_menguy` -> `books/a/-/m/a-menguy` ;
- dossier d'un compte supprimé : ignoré.

```bash
php artisan ubdf:prod:dossiers-books --dry-run
php artisan ubdf:prod:dossiers-books [--source=/chemin/users_2] [--depuis=m]
```

`--depuis=m` reprend à la lettre `m` après une coupure SSH (lancer de
préférence dans `screen` ou `nohup`).
