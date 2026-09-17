# Phase 4 — books sur sous-domaines

## Lot 4a — service d'images a la demande

Remplace les neuf declinaisons pre-generees par book et `phpThumb`.

### Ce que faisait le legacy

`conf/conf_img.php` declare neuf formats. A chaque enregistrement d'un
visuel, les neuf etaient fabriques et poses dans neuf dossiers du book :

```
users_2/p/i/pierre-chl/
  img_            1980x3600  contain   (l'original, deja borne)
  img_adm_small     24x35    contain
  img_adm_medium   180x180   contain
  img_ptf_small     48x48    contain
  img_ptf_medium   550x3600  contain
  img_iph_small     75x75    cover
  img_iph_medium   320x480   contain
  img_front_desk   250x136   cover
  img_front_mob    140x76    cover
```

5 276 books x 9 dossiers, dont la plupart ne servaient jamais : un book qui
n'apparait pas sur le portail n'a pas besoin de `front_desk`, un book jamais
consulte sur mobile n'a besoin d'aucun des deux `iph_`.

### Ce qui les remplace

Une seule copie stockee, les declinaisons produites a la demande et mises en
cache dans `storage/app/public/cache_images/`. Le cache est valide tant
qu'il est plus recent que sa source : remplacer un visuel le perime, sans
purge a faire. Il se vide sans dommage.

| | Legacy | Maintenant |
|---|---|---|
| Moment de la fabrication | A l'enregistrement, les neuf | A la premiere demande, celle-la seule |
| Dimensions | Dans l'URL (phpThumb) | Seulement les noms de `config/images.php` |
| Source demesuree | Ouverte | Refusee avant decompression |

### Le mode de cadrage, retrouve a la mesure

`conf_img.php` a un champ `recadr` valant `''`, `1` ou `2`, sans commentaire.
La distinction se lit sur les fichiers reels : une source 1240x1754 donne
**34x48** en `img_ptf_small` (`recadr` vide) mais **75x75** en
`img_iph_small` (`recadr` 1). Donc `''` = contain, `1` et `2` = cover.

`scaleDown` et non `scale` : une source de 500x500 ressort inchangee en
`ptf_medium`, dont la boite fait 550 de large. Le legacy n'agrandissait pas,
et agrandir n'ajouterait que du flou.

### Verification contre le legacy

Comparaison des dimensions produites avec les fichiers pre-generes de
`users_2/`, sur 60 sources : **430 comparaisons identiques sur 431**.

L'unique ecart : une source 1936x1296 donne 250x136 chez nous et **249x136**
dans le legacy, pour un cadrage qui doit faire exactement 250 de large.
C'est un arrondi rate de son cote, pas du notre.

### Le piege du jour : les parametres de route sont positionnels

`/books/{login}/{declinaison}/{file}` repondait 404. Pourtant la route
s'appariait correctement — verifie via `Route::getRoutes()->match()` — et le
service produisait bien l'image, verifie en tinker.

La permutation avait lieu entre les deux. La methode etait declaree
`show(string $login, string $file, ?string $declinaison = null)`, avec
l'espoir que Laravel apparie les parametres par leur nom. **Il ne le fait
pas** : les parametres de route scalaires sont passes dans l'ordre de l'URI.
La declinaison arrivait donc dans `$file`, et le nom du fichier dans
`$declinaison`.

Une sonde dans le controleur l'a montre en une ligne :

```
SONDE {"login":"adamgrant","file":"front_desk","declinaison":"…gif"}
```

Deux methodes distinctes plutot qu'un argument optionnel : `show()` pour la
declinaison par defaut, `showDeclinaison()` dont la signature suit l'ordre de
l'URI.

> A retenir : une signature de controleur dont l'ordre ne suit pas l'URI ne
> se voit ni au `route:list`, ni en test unitaire du service. Il faut une
> requete reelle.

### Intervention Image 4.3

`ImageManager::read()` n'existe pas dans cette version : elle a ete renommee
`decodePath()`. Le pilote est choisi a l'execution — Imagick s'il est
charge, GD sinon — plutot que fixe en configuration : le MAMP de
developpement n'a pas Imagick, le serveur de production peut l'avoir.

### Tests

12 scenarios sur le generateur (`tests/Feature/Images/DeclinaisonsTest.php`),
8 sur le point d'entree HTTP (`tests/Feature/Front/BookMediaTest.php`).

## Lot 4b — le book public

Remplace le placeholder texte (`BOOK · login = {$login}`) par un rendu reel :
accueil, galeries, rubriques.

### Perimetre reduit, assume

Le legacy proposait onze habillages graphiques distincts
(`config/categories.php:legacy_theme_map` — `mdl_2016_zoom`,
`mdl_2015_grid`, `mdl_2012_slide`…). Les reprendre pixel pres, un par un,
deborde largement ce lot. `BookController` rend **un gabarit unique**,
neutre et lisible, commun a tous les books quel que soit
`book_settings.theme`. Cette valeur reste importee et posee en classe CSS
(`book_theme_<slug>`) sur le `<body>`, prete pour une reprise par theme si
elle est demandee plus tard — mais rien n'y est accroche pour l'instant.

C'est un ecart deliberement plus large que les precedents (qui portaient sur
un comportement) : ici c'est l'apparence de centaines de books qui change.
A signaler a Pat.

### Structure

Deux arbres independants, comme dans les tables d'origine :

- **galeries** (`galleries`) portent les visuels — le « portfolio » —, avec
  sous-galeries ;
- **rubriques** (`book_sections`) portent les pages de texte (a propos,
  contact…), avec sous-rubriques et articles.

Une rubrique `is_private` reprend le mode « brouillon » du legacy : visible
au seul proprietaire connecte, 403 pour tout autre visiteur.

### Points d'entree

| Route | Contenu |
|---|---|
| `GET /` | Accueil : presentation, galeries et rubriques de premier niveau |
| `GET /portfolio/{slug}` | Une galerie : sous-galeries et visuels publies |
| `GET /rubrique/{slug}` | Une rubrique : sous-rubriques et articles publies |

Un compte absent ou supprime (`SoftDeletes`) rend 404 sans code
supplementaire : la portee par defaut d'Eloquent les exclut deja.

### Declinaisons d'images utilisees

Les vignettes de rubrique et les visuels de galerie utilisent `ptf_medium`
(le service ecrit en phase 4a) ; la photo de bio de l'en-tete utilise
`adm_medium`.

### Ecart connu sur la langue

Les books ne portent pas de prefixe de langue — c'est un mecanisme du
portail (`ResoudreLangue`/`ForcerLangue`), jamais applique au groupe de
routes du sous-domaine. `ResoudreMarque`, lui, s'applique partout : mais il
ne reconnait le book d'un createur que par son hote litteral, absent de
`config/marques.php`, donc un `<login>.ubdf2026.…` retombe systematiquement
sur la marque par defaut (Ultra-book, francais). Les quelques chaines
`__()` du gabarit de book s'affichent donc toujours en francais, meme pour
un createur Dustfolio, jusqu'a ce qu'un mecanisme de langue propre au book
soit defini — c'est plus naturellement un reglage du createur (phase 5)
qu'une resolution par hote.

### Tests

9 scenarios dans `tests/Feature/Front/BookControllerTest.php`, plus la
correction de deux tests de `MarqueTest.php` qui s'appuyaient sur le texte
du placeholder.

Verification en HTTP reel sur `amelancholygraphiste.ubdf2026.ultra-book.name`
(compte de l'echantillon de developpement, avec galeries) : accueil et une
galerie rendent correctement.

> A faire au prochain demarrage de `npm run dev` : la feuille
> `resources/css/book.css` est un nouveau point d'entree Vite
> (`vite.config.js`), non pris en compte par le serveur de dev deja lance.
