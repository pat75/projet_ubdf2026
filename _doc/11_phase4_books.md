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

## Lot 4c — contact et 404 dediee

### 404 dediee

`bootstrap/app.php` intercepte desormais les `NotFoundHttpException` : si
l'hote est un sous-domaine de book (`*.<book_domain>`), la reponse 404
generique de Laravel est remplacee par une page qui nomme la situation
(« Ce book n'existe pas ou n'est plus disponible ») et renvoie vers le
portail. Le reste des 404 — portail, points d'entree techniques — garde le
rendu par defaut : le controle porte sur l'hote de la requete, pas sur la
route.

### Contact

Le formulaire de contact (`js_core_cards.js`, `/intermediate_send`) vit dans
la fenetre modale du portail, avec sa propre mecanique JS (captcha, envoi
AJAX). Le dupliquer sur le sous-domaine du book n'entrait pas dans ce lot ;
le lien « Contacter » du menu du book renvoie vers la fiche portail du
createur, ou le formulaire fonctionne deja.

## Lot 4d — les onze habillages d'origine

Decision de Pat : les books gardent leurs gabarits du legacy, tous. Le
gabarit unique du lot 4b est abandonne.

### Methode : portage mecanique, verifie contre la production

Les gabarits Savant (`2011_html_pages_v2/`, ~250 Ko sur dix dossiers) sont
convertis en vues Blade par `_outils/porter_gabarits.py`, reproductible. Le
HTML est conserve a l'octet ; seul le PHP est adapte :

| Legacy | Portage |
|---|---|
| `$this->…` | `$b->…` — `App\Services\Book\ContexteBook`, adaptateur qui expose les donnees Eloquent sous les noms et structures du legacy (`menu`, `gal_cont`, `rep_img550`…) |
| `include $this->loadTemplate(…)` | `include Gabarit::chemin(…)` : le gabarit Blade **compile**, inclus dans la meme portee — Savant partageait les variables locales, et des gabarits en dependent |
| fonctions definies dans les gabarits | extraites dans `<dossier>/_fonctions.php`, prefixees par dossier. Les envelopper dans `function_exists` supprimait le « hoisting » de PHP dont les gabarits dependent |
| `count(null)`, `in_array(…, null)`… | `App\Services\Book\Php7` : la valeur que rendait PHP 7, la ou PHP 8 leve une TypeError |
| avertissements (index absents…) | toleres pendant le rendu d'un theme seulement (`Gabarit::rendre`) |
| `$_COOKIE`, `$_GET`, `$_SERVER` | `request()` |
| phpThumb, dimensions dans l'URL | trois declinaisons nommees (`carre_368`, `carre_335`, `carre_183`) |
| `/users_2/…/img_cms/…` dans le HTML des pages | `/books/<login>/cms/…` (arborescence conservee) |
| mode edition sur simple cookie `us_pr` | neutralise : n'importe quel visiteur pouvait l'activer. L'edition releve de la phase 5 |

Deux outils de controle : `_outils/comparer_book.sh <login>` compare chaque
page d'un book a la production (squelette de balises, texte visible,
visuels).

### Ce que la comparaison a revele — et corrige en amont

- **Changement de theme depuis l'instantane.** `ub2020` date de 2022 : des
  createurs ont change de theme depuis. Les books de reference sont choisis
  parmi ceux dont la production sert encore le theme de l'instantane et
  dont les visuels existent tous chez nous.
- **L'ordre des visuels etait perdu a l'import** : `rub_ordre_img` est
  separe par des tirets bas, pas des virgules. Et l'algorithme d'ordre du
  legacy (`usbook2011_img_ordre`) est reproduit a l'identique, y compris
  son defaut : les visuels absents de la liste s'ecrasent, un seul survit.
- **Le titre de l'accueil** : la production n'ajoute plus le nom de la
  premiere rubrique ; son code a diverge de la copie locale. La production
  fait foi.

### Etat au commit

| Theme | Reference | Accueil | Portfolio | Pages | Contact |
|---|---|---|---|---|---|
| Zoom 2016 | ar-creation | 100 % | 100 % | 100 % | 100 % |
| Grid 2015 | altcrea | 100 % | 100 % | 97 % | (1) |
| Ultra-frais 2020 | arpsara | 100 % | 100 % | 100 % | 100 % |
| Portfolio 2012 | annlaurs | 100 % | 100 % | 100 % | (1) |
| Responsive 2014 | audenguyenhuu | 100 % | 100 % | 100 % | (1) |
| Classique 2010 | alainvilcocq | 95 % | 92 % | 96 % | 100 % |
| Classique 2015 | alexandrelagneau | 92 % | **22 %** | 91 % | (1) |
| Slide 2012 | gabrielleka | **50 %** | **50 %** | 62 % | (1) |
| Pinter 2013 | anneloreparot | **5 %** | **5 %** | 46 % | (1) |
| Ultra-zen 2020 | anneletuffe | **30 %** | **30 %** | 58 % | 99 % |

Pourcentages : similarite du squelette de balises avec la production.
En gras, ce qui reste a traiter.

(1) **Ecart voulu.** Ces six themes prevoient une page contact dans leur
aiguillage, mais le gabarit n'a jamais existe : en production, /contact y
affiche une page vide. Le formulaire du book y est presente comme une page
de rubrique, habillee par le gabarit « page » du theme.

### Formulaire de contact du book

Balisage identique au formulaire de production (PFBC : champs
`fm_contact_*`, identifiants `ajax-element-N`, rappel
`contactForm_callback`), deux ecarts : il poste au book lui-meme et non plus
a `www.ultra-book.com/contact_reponse_frombook__<id de session PHP>__<login>`
— l'identifiant de session etait expose dans l'URL —, et le jeton CSRF
remplace `fm_key` (md5 de l'identifiant de session). La demande suit le
chemin de celle du portail (`DepotDemande`, extrait de ContactController) :
fil intermedie, detection du spam, deux courriels. reCAPTCHA v2, celui des
gabarits d'origine (`services.recaptcha.v2`).

### Corrections d'import faites au passage

- rubriques 1/3 et leurs pages de texte (voir le commit « Import : les
  pages de texte des books etaient perdues ») ;
- `img_cms` (images des pages, 1 545 fichiers sur l'echantillon) copie avec
  son arborescence, sans aucun fichier executable ;
- `mdl_default` et theme inconnu rendus en Responsive 2014, comme le legacy.
