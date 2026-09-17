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
