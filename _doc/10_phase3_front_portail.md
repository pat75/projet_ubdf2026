# Phase 3 — Front public du portail (en cours, 2026-09-16)

## Ce qui fonctionne

| URL | Reponse |
|---|---|
| `/`, `/accueil` | 200 — accueil, premier ecran de books rendu cote serveur |
| `/illustrateur`, `/graphiste`, … (13 categories) | 200 |
| `/meilleurs-illustrateurs`, … (15 landings SEO) | 200 |
| `/annuaire`, `/annuaire_<lettre>` | 200 |
| `/portfolio/<login>/<slug>` | 200, slug errone redirige en 301 |
| `/accueil__<page>__<sel\|ult\|lub>__<type>` | 200, JSON du defilement infini |
| `/portfolios`, `/recherche`, `/graphisme`, … | 301 vers l'URL canonique |
| `/books/<login>/<fichier>` | 200, image ou visuel par defaut |

## Architecture du front 2018 — ce qu'il fallait comprendre

Le portail n'est pas une page rendue entierement cote serveur. Il combine :

1. **Semantic UI 2.3.1** pour la mise en page (`ui card`, `ui five doubling cards`) ;
2. des **templates Handlebars** inclus dans la page (9 blocs), consommes par `js2019/js_core_cards.js` ;
3. un **premier ecran rendu cote serveur** (une dizaine de cartes), puis un **defilement infini** qui appelle `GET /accueil__<page>__<selection>__<type>` et recoit un tableau JSON.

Le contrat JSON a ete releve sur le site en fonctionnement, pas deduit du code : `us_id`, `us_key`, `us_dir`, `us_type`, `us_prenom`, `us_nom`, `us_pf_img_vignette`, `us_path`, `img`, `slider`… **Ces cles ne doivent pas etre renommees** tant que le JavaScript n'est pas reecrit (phase 9). Un test le verrouille.

Les trois modes de selection sont ceux du legacy : `sel` (selection du moment), `ult` (ultra-selection), `lub` (comptes abonnes).

## Methode de portage des vues

Les vues Blade sont derivees du **HTML reellement rendu** par le site de 2020, pas des templates PHP d'origine — plus fidele, et sans reprendre la logique enchevetree de `action.php`.

```
resources/views/
├── layouts/portail.blade.php
├── partials/head.blade.php        (<head>, titres et meta dynamises)
├── partials/header.blade.php      (menu, slider, recherche, bannieres)
├── partials/footer.blade.php
├── partials/modals.blade.php      (connexion, inscription, memo book, contact)
├── partials/handlebars.blade.php  (9 templates, isoles dans @verbatim)
├── components/book-card.blade.php
└── front/{accueil,annuaire,portfolio}.blade.php
```

Les `{{ }}` de Handlebars entrent en conflit avec Blade : le partial dedie est entoure de `@verbatim`, les autres sont echappes en `@{{`.

CSS et JavaScript sont repris **tels quels**, aux memes chemins (`/html_pages_v2018/_/…`), pour ne rien casser : 37 Mo dans `public/`, non versionnes.

## Incoherences du legacy rencontrees

**1. 248 746 visuels sans fichier.** `ub2_gal_img.img_fichier` est vide sur **23 %** de la table : des enregistrements fantomes, sans image associee. Ils ne sont plus repris, et le repository les exclut.

**2. Des visuels reference sans fichier sur le disque.** Meme avec un nom renseigne, le fichier peut avoir disparu (exemple : `hectordexet/02_def_citrus_jpg__737093.jpg`). Le legacy traitait le cas dans son `.htaccess`, en servant une trame grise. `BookMediaController` fait de meme : sans cela, les cartes affichent des icones cassees.

**3. Vignettes stockees a part.** Elles vivent dans `cms_pref/`, pas dans les dossiers d'images. `LegacyFiles` les copie desormais (1 024 fichiers, 24 Mo).

## Choix : route de service d'images plutot que lien symbolique

`/books/{login}/{file}` passe par `BookMediaController` au lieu du lien `public/storage`. Deux raisons : servir un visuel par defaut quand le fichier manque, et disposer du point d'entree ou se greffera la **generation des declinaisons a la demande** en phase 4 — celle qui remplacera les 12 dossiers pre-generes du legacy.

## Tests

42 tests verts. `tests/Feature/Front/PortailRoutesTest.php` couvre l'accueil, les categories, les 301, le contrat JSON, la fiche portfolio, le visuel par defaut et la tentative de remontee d'arborescence sur `/books/`.

## Reste a faire dans la phase 3

- Recherche par mots-cles (`/rechercher_submit`) et filtres du menu.
- Formulaire de contact et messagerie intermediee (tokens).
- Pages CMS (`/page__<slug>`, `/doc/<slug>`) et actualites.
- Multi-marque Dustfolio : middleware de resolution de domaine.
- Multi-langue `fr` / `en` / `ja`.
- Memo book, inscription, connexion.

---

# Phase 3b — Blocs metier, defilement et ouverture des books (2026-09-16)

## Accueil : un bloc par metier

L'accueil n'est pas une liste plate de books. Le front 2018 affiche **9 blocs metier**, chacun compose de :

- un titre et un sous-titre — « Illustration » / « Derniere selection illustrateur freelance » ;
- une rangee de cartes ;
- une derniere carte portant le compteur (« 13 164 illustrateurs ») ;
- un lien « Voir tous les illustrateurs ».

Chaque metier porte donc **trois libelles distincts**, qui ne se deduisent pas les uns des autres : le nom (`Illustrateur`), le titre du bloc (`Illustration`, mais `Art` pour les plasticiens et `Digital & développement` pour le digital) et la forme du sous-titre (`designer objet`). Ils sont declares dans `config/categories.php` et lus par `App\Support\Metier`.

Les 4 categories restantes (styliste, scenographe, modele, autre) restent accessibles par leur URL mais n'apparaissent pas sur l'accueil, comme dans le legacy. Un bloc sans book n'est pas affiche.

## Defilement infini avec fondu en cascade

Sur une page de metier, les cartes suivantes arrivent par `GET /cartes/{categorie}/{page}`.

**Ecart assume avec le legacy** : celui-ci renvoyait du JSON que le navigateur assemblait avec un template Handlebars, soit deux rendus a maintenir pour une meme carte. Ici le serveur rend le **meme composant Blade** que le premier ecran : une carte chargee au defilement est forcement identique a une carte rendue au chargement. L'ancien contrat JSON (`/accueil__…`) reste servi pour le JavaScript repris tel quel, et reste verrouille par un test.

Le fondu reprend le mecanisme d'origine : chaque carte arrive avec la classe `newitem_hide` (opacite 0, translation de -30px, transition 0,3 s — regle deja presente dans `core.css`), que `public/js/ubdf-infinite.js` retire une par une avec 40 ms d'ecart.

## Le clic sur une carte n'ouvrait aucun book

Symptome : cliquer sur une carte ne faisait rien.

Cause : `data-user_detail` et `data-slider` sortaient **vides** (`{}`). **Laravel expose les methodes publiques d'un composant a sa vue**, ou elles masquent une variable du meme nom : `$detail` dans le template resolvait vers la methode `detail()`, pas vers le tableau passe par `render()`. `@json()` d'une fonction rend `{}`.

Le JavaScript lisait donc un diaporama vide et echouait sur `slider_data.book_img.length`. Les deux methodes sont passees en `private`, et un test verifie desormais que les attributs sont remplis.

> Fausse piste ecartee en chemin : `jquery.swipebox.min.js` repond 404, mais ce fichier n'existe pas davantage dans le legacy — la lightbox est incluse dans le bundle `js_allplug2018.js`. L'URL testee etait inventee, pas referencee par la page.

## En-tete : video et bloc de recherche

- La video `/_video/crea3.mov` n'etait pas servie : le dossier `_video/` n'avait pas ete copie. Seul `crea3.mov` (2,9 Mo) est repris, sur les 24 Mo du dossier d'origine.
- Le bloc « Trouvez les meilleurs portfolios de creatifs » est **remonte dans le bloc video** : il se superpose desormais a la video au lieu de la suivre.
- Son fond blanc passe a **20 % d'opacite**, avec un flou d'arriere-plan et un titre en blanc ombre — sans quoi le texte devient illisible selon l'image. Le champ de saisie, lui, reste opaque pour rester utilisable.

## Vite

`npm run dev` sert `resources/css/ubdf.css`, charge **apres** les feuilles du front 2018 pour les surcharger sans les modifier.

Deux points de configuration :

- **Tailwind n'est pas importe.** Son preflight reinitialiserait Semantic UI. `app.css` et `app.js` restent en place pour la refonte de la phase 9.
- **Le serveur de developpement repond en HTTPS**, avec le certificat que Valet a genere pour le domaine. En HTTP, le navigateur bloquerait ses ressources pour contenu mixte et la feuille ne serait jamais appliquee.

## Tests

45 tests verts, dont la non-regression sur les attributs `data-*` des cartes, la presence des blocs metier et le contrat du defilement.

## Correction : le bloc de recherche etait deja positionne par le legacy

Le bloc « Trouvez les meilleurs portfolios de creatifs » avait ete deplace **dans** le `<header>` de la video. C'etait une erreur : le front 2018 le remonte deja par le CSS, sans toucher au DOM.

```css
@media only screen and (min-width: 981px) {
    #bloc_rechercher {
        margin-top: -260px !important;      /* remonte dans la video */
        margin-bottom: 100px !important;    /* compense pour la suite */
        background-color: rgba(255, 255, 255, 0.8) !important;
    }
}
```

Ce fond blanc a 80 % d'opacite est exactement la transparence de 20 % demandee : elle etait deja la. Le bloc paraissait mal place parce que **la video ne s'affichait pas** — le dossier `_video/` n'avait pas ete copie, l'en-tete etait donc vide.

Le deplacement dans le DOM sortait le bloc du flux ou ce calcul s'applique, d'ou le decalage. Il est revenu a sa place, et `resources/css/ubdf.css` ne surcharge plus rien sur ce point.

> A retenir : verifier ce que le CSS d'origine fait deja avant de deplacer un element. Ici le legacy avait raison, et la seule vraie anomalie etait un fichier manquant.

## Blocs d'accroche reserves a l'accueil

`partials/header.blade.php` contenait, en plus des conteneurs communs, les accroches de l'accueil. Elles s'affichaient donc aussi sur `/illustrateur` et `/annuaire`.

Le partial est scinde :

| Fichier | Contenu | Portee |
|---|---|---|
| `partials/header.blade.php` | ouverture des conteneurs (refermes dans le footer) | toutes les pages |
| `partials/accueil-hero.blade.php` | video et titre, bloc de recherche, derniers mots-cles, « Creer votre portfolio », « Une selection de qualite », « Installer mon site internet pro », banniere des disponibilites | accueil seul |

La recherche du menu haut (`bloc_rechercher_top2_mobile`, `bloc_rechercher_top_menu_modal`) reste commune : c'est une fonction de navigation, pas une accroche.

## Les cartes chargees au defilement ne s'ouvraient pas

Symptome : sur `/illustrateur`, les books du premier ecran s'ouvrent en pleine page, mais ceux qui arrivent au defilement restent muets au clic.

Cause : le front 2018 ne pose le gestionnaire de clic qu'**une seule fois**, au chargement du document, via `book.book_static_show('.ptf_index_static')`. Une carte inseree ensuite n'en herite pas. Le legacy le savait et rappelait, apres chaque insertion :

```js
ub_infinit.post_traitement_dom('#user_' + id_user);  // -> btn_slide()
```

`public/js/ubdf-infinite.js` retirait bien `newitem_hide` et initialisait le `dimmer`, mais n'appelait pas cette fonction. C'est corrige : chaque carte inseree est activee, **avant** l'animation, pour qu'un clic pendant le fondu fonctionne aussi.

`js_core_cards.js` etant charge de facon asynchrone par LABjs, l'activation reessaie brievement (20 tentatives, 150 ms) si l'objet n'est pas encore defini — sinon un defilement tres rapide laisserait des cartes inertes.

> **Non repris volontairement** : `ub_ill_plus_de_book.stats_book()`, que le legacy appelle au meme endroit. Il emet un `action=add` vers `https://www.extra-book.com/2012_stats/st_action.php`, soit le serveur de statistiques **de production** : l'appeler depuis le developpement gonflerait les compteurs reels. Le comptage des vues sera reimplemente cote Laravel, sur la table `visit_stats` deja prevue.

## Regression : les cartes du defilement etaient inserees mais invisibles

En ajoutant l'activation des cartes (ci-dessus), le defilement a cesse d'afficher quoi que ce soit.

Cause reproduite dans un DOM (`tests/js/defilement.test.mjs`) :

```js
if (window.ub_infinit && typeof ub_infinit.post_traitement_dom === 'function') {
//                              ^^^^^^^^^^ reference nue
```

`public/js/ubdf-infinite.js` est en mode strict. Y referencer `ub_infinit` **sans prefixe `window.`** leve une `ReferenceError` tant que `js_core_cards.js` n'est pas charge par LABjs. L'exception remontait dans la boucle `$.each()`, qui s'interrompait : les cartes etaient bien inserees, mais `newitem_hide` n'etait jamais retire — opacite 0, donc rien a l'ecran.

Deux corrections :

1. les objets du front 2018 sont lus **uniquement** sur `window` (`var infinit = window.ub_infinit;`) ;
2. l'apparition passe en premier et l'activation est isolee dans un `try`/`catch`. **Une carte doit s'afficher meme si le JavaScript repris du legacy est indisponible.**

### Test

`npm run test:js` rejoue trois etats de chargement dans jsdom, avec le jQuery 1.12.4 que le site sert reellement :

| Scenario | Etat simule | Attendu |
|---|---|---|
| `absent` | `js_core_cards.js` pas encore charge | cartes visibles |
| `partiel` | `ub_infinit` present, `ub_ill_plus_de_book` non | cartes visibles |
| `complet` | front 2018 entierement charge | cartes visibles **et** activees |

Le scenario `partiel` est exactement celui qui produisait la regression.

## Ressources appelees par le JavaScript du front

`js_core_pages.js` reclame deux fichiers au chargement de chaque page. Les deux repondaient 404.

### `/html_pages_v2018/tpl_conf_msg/motcles_data_front_fr_en.json`

Donnees statiques de l'autocompletion de recherche (11 Ko). Le dossier `tpl_conf_msg/` n'avait pas ete copie avec les autres assets. Repris tel quel.

### `/cache_js/data_stats.json`

Compteurs globaux du portail : nombre de books, de selections, de visuels, de galeries, et le detail par metier — ce sont les « 13 164 illustrateurs » affiches sur l'accueil. S'y ajoute une liste de books mis en avant dans le menu.

Le legacy servait un fichier regenere periodiquement par une tache. Il est desormais **calcule depuis la base** par `StatsController` et mis en cache une heure, servi au meme chemin. Deux raisons de ne pas copier le fichier : les chiffres de production seraient figes dans le depot, et ils ne correspondraient pas aux donnees affichees en developpement.

## Appels encore sans reponse

Quatre chemins restent en 404. Ils correspondent aux fonctions non encore developpees et ne se declenchent qu'a l'usage, pas au chargement d'une page :

| Chemin | Fonction | Phase |
|---|---|---|
| `/rechercher_submit` | soumission d'une recherche | 3 (reste a faire) |
| `/fm_ajax` | formulaires en edition directe | 5 |
| `/ubaction__user_open` | connexion | 5 |
| `/front/ajax_2010.php` | actions diverses du legacy | 5 |

`https://www.extra-book.com/2012_stats/st_action.php` est appele par `stats_book()` : c'est le serveur de statistiques **de production**, volontairement non sollicite depuis le developpement.

## Le defilement s'arretait apres une page

### Ce qui n'etait pas en cause

Deux pistes verifiees et ecartees avant de corriger quoi que ce soit :

- **Conflit avec le defilement du legacy.** `book.infinite()` attache bien un `onBottomVisible` de Semantic UI sur `.infinite`, classe que porte aussi notre page. Mais il commence par `if (!book.infinite_ready) return;`, et ce drapeau ne passe a `true` qu'apres un premier chargement via l'API du legacy, qui n'a jamais lieu ici. Le mecanisme d'origine reste donc inerte.
- **Pagination.** Verifiee page par page sur les donnees reelles : 10 + 10 + 7 = 27 books, aucun doublon, aucun oubli. (Le comptage brut des `data-user` en annonce 12 sur la premiere page : deux d'entre eux sont des templates Handlebars, pas des cartes.)

### La cause

Le chargement n'etait declenche que par l'evenement `scroll`. Quand les cartes ajoutees ne rallongent pas la page au-dela du seuil — categorie peu fournie, ou simplement grand ecran — **aucun nouvel evenement n'est emis** et le chargement s'arretait apres la premiere page.

Le seuil est desormais reevalue apres chaque insertion, ainsi qu'au redimensionnement et au chargement initial. Une page plus courte que la fenetre enchaine donc les pages jusqu'a epuisement.

### Test

`tests/js/enchainement.test.mjs` place la page dans ce cas precis — une hauteur inferieure a la fenetre, donc aucun defilement possible — et verifie que les trois pages sont demandees d'elles-memes, que les 27 cartes s'affichent, et qu'aucune quatrieme requete n'est emise apres la fin.

`npm run test:js` execute les quatre scenarios JavaScript.

## La vraie cause : jQuery n'existait pas encore

Malgre les corrections precedentes, le defilement ne chargeait toujours rien : seuls les 10 books du premier ecran s'affichaient.

`public/js/ubdf-infinite.js` s'ouvrait par :

```js
(function ($) { … })(jQuery);
```

Or le front 2018 charge jQuery **par LABjs, de facon asynchrone** :

| Position dans la page | Script |
|---|---|
| 5 635 | `LAB.min.js` |
| 14 707 | `the_LAB = $LAB … .script("js_cdn/jquery-1.12.4.min.js")` |
| 139 440 | `ubdf-infinite.js` |

Notre balise est un `<script src>` classique : elle s'execute **des qu'elle est atteinte**, bien avant que LABjs ait fini. `jQuery` etait donc indefini, l'IIFE levait une `ReferenceError`, et le module entier ne s'executait jamais. Aucun gestionnaire de defilement n'etait pose.

Le script attend desormais la disponibilite de `window.jQuery` avant de demarrer, et renonce au bout de dix secondes en le signalant en console — un echec silencieux serait pire.

> **Pourquoi les tests ne l'avaient pas vu.** Ils injectaient jQuery *avant* d'evaluer le script, ce qui ne correspond a aucune situation reelle. Ils validaient donc du code qui ne s'executait jamais en production. `tests/js/enchainement.test.mjs` evalue maintenant le script **d'abord**, et ne fournit jQuery que 400 ms plus tard, comme LABjs.

## La cause des ajustements sans effet : `&#64;` dans `<style>` et `<script>`

Plusieurs reglages du bloc de recherche n'avaient aucun effet visible. La mesure dans Chrome l'a montre sans ambiguite :

```
marginTop : -160px        alors que la feuille declarait -200px !important
fond      : rgb(255,255,255)   alors qu'elle declarait rgba(255,255,255,0.8)
```

Les valeurs appliquees etaient celles de `core.css` (`.bloc_rechercher #bloc_rechercher { margin-top: -160px }`), pas les notres.

**Cause.** Le script qui a converti le HTML rendu en vues Blade (phase 3a) remplacait `@` par `&#64;` pour empecher Blade d'interpreter ses directives. Or **les entites HTML ne sont pas decodees a l'interieur de `<style>` et `<script>`** : `&#64;media only screen and (min-width: 981px)` restait litteral, la media query etait invalide, et **tout son contenu ignore**. Quatre media queries etaient mortes.

Le meme echappement cassait le JSON-LD des donnees structurees : `"&#64;context"` au lieu de `"@context"`, soit un balisage Schema.org invalide pour les moteurs.

**Correction.** Ces occurrences utilisent desormais `@@`, l'echappement de Blade, qui produit un `@` litteral en sortie. Les `&#64;` restants sont dans des attributs HTML (`mailto:`, `content="@ultra_book"`), ou ils sont correctement decodes.

Un test parcourt le contenu de chaque balise `<style>` et `<script>` des pages du portail et echoue si une entite HTML y subsiste.

## Calage final du bloc de recherche

Une fois la media query valide, la valeur du front 2018 s'est revelee juste. Mesures dans Chrome a 1487px de large :

| | |
|---|---|
| bloc video | 85 -> 565 (hauteur 480) |
| sous-titre finit a | 269 |
| bloc de recherche | 305 -> 534 |
| ecart sous le sous-titre | **36 px** |
| marge avant la fin du fond bleu | **31 px** |

Ce qui correspond a la maquette de reference. `margin-top: -260px`, `margin-bottom: 100px`, fond `rgba(255, 255, 255, 0.8)`.

> Les tentatives precedentes (-300px, puis -200px) ajustaient une regle qui n'etait jamais appliquee. **Mesurer le rendu avant de corriger** aurait evite trois allers-retours : `getComputedStyle` dit ce qui s'applique vraiment, la feuille de style dit seulement ce qu'on a demande.

---

# Phase 3c — Recherche par mots-cles (2026-09-16)

## `us_pf_css` ne contient pas de CSS

La colonne `inc_user_pref.us_pf_css` avait ete reprise telle quelle, sous le
nom `book_settings.custom_css`. C'etait une erreur de lecture du legacy : la
requete de recherche du front 2018 porte sur cette colonne.

```sql
WHERE user_pref.us_pf_css LIKE '%illustration%'
```

Le contenu le confirme — « Brochures,Affiches,Flyers,Logos »,
« #fashion, #chanel, #nyc ». Elle stocke les **mots-cles** du book. Renommee
en `keywords` (migration `2026_01_02_000100`). 18 225 comptes en portent dans
ub2020.

## Quinze ans de formats dans une meme colonne

Un echantillon de 4 000 comptes donne cinq formats coexistants :

| Forme | Origine probable |
|---|---|
| `illustration, aquarelle, presse` | liste simple |
| `[&#34; architecture&#34;,&#34; interieur&#34;]` | tableau JSON echappe |
| `#fashion, #chanel, #nyc` | saisie en hashtags |
| `&lt;meta name=&quot;keywords&quot; content=&quot;flyer,3d&quot;/&gt;` | balise collee dans le champ |
| `webdesigner，平面设计师` | virgule ideographique |

`App\Support\MotsCles` les ramene a une liste comparable : double decodage
des entites, extraction de l'attribut `content`, suppression des crochets,
guillemets et hashtags, decoupage sur `, ; | \n 、 ，`, dedoublonnage
insensible a la casse et aux accents, bornage a 40 mots de 60 caracteres.
L'apostrophe est conservee (« vue d'ensemble »). Les ideogrammes aussi :
`Str::ascii` les rend vides, un repli sur `mb_strtolower` les preserve.

Sur les 100 comptes migres, 74 portent des mots-cles ; 74 valeurs sur 74 ont
ete modifiees par la normalisation.

## Le contrat de la recherche, releve dans le JavaScript

`js_core_pages.js` construit une requete **GET** vers `/rechercher_submit`,
parametres a plat, et attend un **tableau JSON** :

```
q=illustration;drawing&anu_type=tous&recherche=mcles&flt_sel=false&flt_pro=false&suite=0
```

Le `;` n'est pas un separateur de mots-cles : il precede la **traduction**
du terme, que le JavaScript ajoute depuis `motcles_data_front_fr_en.json`.
Un book redige en anglais doit donc ressortir sur une recherche en francais.
`MotsCles` decoupe sur ce caractere comme sur la virgule, ce qui produit
exactement ce comportement.

Trois routes servent la recherche :

| URL | Reponse | Pour qui |
|---|---|---|
| `/recherche?q=…` | page HTML | navigateur, moteurs de recherche |
| `/recherche/cartes/{page}` | fragment HTML | defilement infini |
| `/rechercher_submit` | tableau JSON | `js_core_pages.js`, inchange |

`/recherche` etait jusqu'ici une redirection 301 vers l'accueil ; c'est
desormais une page a part entiere. Le legacy n'avait pas d'equivalent : la
recherche n'existait que dans le navigateur, sur une page d'accueil dont il
remplacait le contenu. Une URL propre est partageable, indexable, et
fonctionne sans JavaScript — le formulaire pointe dessus en GET.

## Ecart assume : OR plutot que AND

Le legacy exigeait les **deux premiers** termes et ignorait les suivants :

```php
$where = " us_pf_css LIKE '%$en%' OR ( us_pf_css LIKE '%$fr1%' AND us_pf_css LIKE '%$fr2%' ) ";
```

Une recherche de trois mots rendait donc presque toujours une page vide.
Ici un book ressort des qu'il porte **un** des termes, et le nombre de
termes trouves sert de score de tri, calcule par la base :

```sql
((keywords LIKE ?) + (keywords LIKE ?)) AS pertinence
```

Les books qui correspondent le mieux remontent, les autres suivent au lieu
de disparaitre.

## Pas d'index FULLTEXT

La recherche reste un `LIKE '%terme%'`. Les mots-cles sont choisis dans une
liste fermee, mais la saisie libre doit continuer a trouver un prefixe
(« illustr »), ce qu'un index en texte integral ne fait pas. A reconsiderer
en phase 8 si la volumetrie de production le demande.

Les jokers de `LIKE` saisis par l'utilisateur sont echappes : sans cela,
une recherche sur `%` ramenait la table entiere. Un test le verrouille.

## Defilement generalise

`public/js/ubdf-infinite.js` interrogeait `/cartes/<categorie>/<page>`, une
URL qu'il construisait lui-meme. La page declare desormais sa source
(`cartes_url`, `cartes_params`), ce qui permet a la recherche de reutiliser
le meme script sans le modifier.

## Tests

75 tests PHP (10 unitaires sur la normalisation, 12 fonctionnels sur la
recherche) et 4 scenarios JS.

---

# Phase 3d — Messagerie intermediee (2026-09-16)

## Ce que le dispositif protege

Un visiteur ecrit a un creatif sans jamais obtenir son adresse, et
reciproquement. C'est la raison d'etre de l'intermediation : sans elle, les
18 000 adresses des books seraient moissonnees en une nuit. Chaque partie
recoit un lien qui ouvre le fil **de son cote**.

## Le controle d'acces de 2019 ne controlait rien

Les liens emis par le legacy avaient cette forme :

```
/intermediate_msg_/cust<verif>/<token>/<selector>
/intermediate_msg_/user<verif>/<token>/<selector>
```

Le `token` et le `selector` sont **les memes pour les deux parties**. Seul le
segment `verif` change — et il est derive du jeton :

```php
verif_from_cust($token) = substr(substr($token,0,4).substr($token,2,4), 0, 6);
verif_from_user($token) = substr(substr($token,1,4).substr($token,3,4), 0, 6);
```

Quiconque detient son propre lien peut donc calculer celui de l'autre, lire
le fil de son point de vue et y repondre en son nom. Le `SELECT` ne portait
d'ailleurs que sur `mf_token` et `mf_selector` : le role venait du seul
parametre `from` de l'URL.

Le remplacement : **deux jetons independants**, de 256 bits, stockes haches
en SHA-256. Le `selector` reste en clair — il designe la ligne, sans quoi il
faudrait comparer le hachage de toutes les conversations. Une base volee ne
rend aucun lien utilisable.

Trois consequences assumees :

- **les liens de ub2020 ne sont plus honores.** Ils ont ete emis sous ce
  schema et resteraient exploitables. La valeur d'origine est conservee en
  `legacy_token`, pour la trace ;
- **un lien expire** au bout de 180 jours sans nouveau message. Le legacy
  n'en posait aucune : un lien de 2019 ouvrait encore le fil en 2026 ;
- **chaque notification emet un jeton neuf** pour son destinataire. Le lien
  qui a circule par courriel depuis l'ouverture du fil cesse alors
  d'ouvrir quoi que ce soit.

Un lien invalide et un lien expire rendent la meme reponse : rien ne permet
de distinguer les deux cas.

## Le spam : marque, jamais rejete

Parti pris repris du legacy, et il est juste — un faux positif qui
supprimerait une vraie demande de commande coute beaucoup plus cher a un
creatif qu'un message indesirable de plus. La demande est **toujours**
enregistree ; seule la notification par courriel est retenue.

Ce qui change : le marqueur est une colonne (`conversations.is_spam`). Le
legacy inserait une banniere HTML rouge en tete du message et prefixait le
nom de `[ ALERTE ]` — un indicateur de traitement ecrit dans la donnee
elle-meme, impossible a retirer ensuite. Le migrateur reconnait ces deux
marques, remonte l'information en colonne et rend la donnee a son etat
d'origine.

Les listes (67 adresses, 13 IP, 26 expressions) sont reprises dans
`config/messagerie.php`. Leur interrogation est corrigee : le legacy faisait

```php
preg_match('/'.$mail.'/', $conf['contact']['spam_list'])
```

soit la valeur saisie comme **motif** et la liste comme sujet. Une adresse
d'une lettre correspondait donc a tout, et un « / » dans la saisie rompait
l'expression. La liste d'IP comparait `$mail` au lieu de `$ip` — le fichier
d'origine porte d'ailleurs la mention « no active ». Ce sont maintenant des
listes, comparees par egalite.

## Captcha

Meme principe qu'en 2020 (cinq caracteres sans ambiguite visuelle, gardes en
session), avec deux corrections :

- le code est **toujours** retire de la session apres verification. Le
  legacy ne l'effacait qu'en cas de succes, ce qui laissait reessayer
  indefiniment sur la meme image ;
- la comparaison passe par `hash_equals`.

Le rendu passe de GD a **SVG** : plus de dependance a l'extension GD ni aux
fichiers de police du projet, et une image nette a toute definition.

## Contrat conserve avec le JavaScript de 2018

`js_core_cards.js` poste sur `/intermediate_send` et ne branche que son
gestionnaire de succes : sur un code 4xx il reste sur son indicateur de
chargement, sans rien afficher. La reponse garde donc la forme qu'il sait
lire — code 200, `error`, `error_list`, `action`, `savedb_result` — y compris
quand la validation echoue (`failedValidation` est surchargee).

Ce meme JavaScript ne connait pas le jeton CSRF de Laravel.
`/intermediate_send` en est exemptee, ce qui est sans consequence : la route
n'agit sur aucune session, elle enregistre la demande d'un visiteur anonyme.
Il n'y a rien qu'un tiers puisse y declencher au nom de quelqu'un d'autre.
Elle reste protegee par le captcha et par une limite de cinq demandes par
heure et par adresse IP. Le fil de discussion, lui, est rendu par Blade et
reste sous CSRF.

`us_key` est conserve : cette cle publique, exposee par la seule page du
book, empeche de poster une demande a un login devine. La comparaison se
fait en temps constant.

## Tests

91 tests PHP au total. 16 portent sur cette messagerie, dont la non-regression sur chacun des points
ci-dessus : jetons distincts, jeton jamais stocke en clair, refus du role
croise, expiration, renouvellement, captcha a usage unique, spam enregistre
mais non relaye, limite de debit, et absence de l'adresse du visiteur dans
la page du creatif.

---

# Phase 3e — Pages editoriales et actualites (2026-09-16)

## Un WordPress entier demarrait a chaque requete

Le portail de 2019 ne stockait pas ses pages editoriales. Il chargeait
WordPress dans son propre processus et l'interrogeait :

```php
require('../magazine/wp-load.php');
$tpl->wp_cont = new WP_Query(['pagename' => '/'.$page_wp]);
$tpl->cont_wp  = apply_filters('the_content', get_the_content());
```

Soit, pour **27 pages et 71 actualites**, un second framework a demarrer a
chaque affichage, ses tables, son cycle de mise a jour et sa surface
d'attaque — le tout dans une installation figee depuis 2018.

Les contenus sont repris dans `cms_pages` et `cms_posts`. La commande
`ubdf:import-cms` rejoue l'import a la demande, ce qui laisse la porte
ouverte si la redaction continue dans WordPress en attendant le
back-office (phase 6).

## La base WordPress se lit en UTF-8, pas en latin1

Point a ne pas confondre avec ub2020. `wp_posts` est declaree `utf8` et
contient reellement de l'UTF-8 (`C389` pour « É »). La connexion
`legacy_wp` est donc en **utf8mb4** : la lire en latin1, comme la connexion
`legacy`, y introduirait precisement le double encodage que cette derniere
sert a eviter.

Les deux connexions sont desormais protegees par le meme garde-fou en
lecture seule, et deux tests le verrouillent.

## Codes courts : trois utilises, un sans gestionnaire

Le contenu WordPress est deja du HTML — pas de `wpautop` a reproduire. Seuls
trois codes courts apparaissent, tous dans les pages :

| Code | Occurrences | Traitement |
|---|---|---|
| `[perso]` | 7 | developpe a l'import (bloc nom + role + photo) |
| `[clear]` | 2 | developpe a l'import |
| `[ub_formule]` | 2 | **aucun gestionnaire dans le legacy** |

`[ub_formule]` n'est declare nulle part : ni dans le theme, ni dans
`front/`, ni dans `inc/`. Les deux pages de tarifs (« Les formules
Ultra-book » et « Packages », dont le corps ne contient que ce code) **ont
donc toujours affiche le code court en toutes lettres** sur le site en
production. Il est retire a l'import et la page signalee : le bloc de tarifs
sera produit en phase 7, avec le paiement.

Les deux autres sont resolus **une fois, a l'import**, plutot que de
reconduire un moteur de codes courts a l'affichage.

## Medias : 86 fichiers sur 531

Le dossier `wp-content/uploads` pese 80 Mo pour 531 fichiers, essentiellement
des declinaisons produites par WordPress et jamais referencees. L'import ne
copie que les fichiers **reellement cites** par les contenus repris : 86, et
signale les 4 references dont le fichier a disparu du disque — meme
phenomene que pour les visuels des books.

## Langues

WPML donne la langue et le groupe de traduction de chaque contenu
(`wp_icl_translations`) : 17 pages FR, 11 EN, 74 actualites FR. Le
`translation_group` est conserve, il reliera les versions entre elles quand
le multi-langue sera en place.

Le sommaire lateral d'une page ne montre que ses soeurs **de la meme
langue** ; une page anglaise reste servie par son slug meme quand le portail
est en francais, ces URL etant indexees separement.

## URL conservees

| URL | Origine |
|---|---|
| `/doc/<slug>` | pages de l'arbre de documentation |
| `/page__<slug>` | meme page, forme de premier niveau |
| `/ultra-book__<slug>`, `/dustfolio__<slug>` | formes par marque |
| `/actus`, `/actus/<slug>` | actualites |
| `/blog` | 301 vers `/actus` (renvoyait vers un site externe) |

## Tests

111 tests PHP au total. 18 pour ce lot : 12 fonctionnels sur les routes, la publication differee et le
cloisonnement des langues, 6 unitaires sur la resolution des codes courts.

---

# Phase 3f — Multi-marque Ultra-book / Dustfolio (2026-09-16)

## Deux sites, un seul code

Dustfolio est la marque soeur d'Ultra-book : meme plateforme, meme base,
domaines distincts. Chaque compte porte la sienne (`users.brand`, reprise de
`inc_user.us_view`), chaque hote determine celle du visiteur.

Proportions reelles dans ub2020, sur les comptes non supprimes :

| `us_view` | comptes |
|---|---|
| *(vide)* | 51 197 |
| `ub` | 9 275 |
| `df` | 388 |

La colonne n'a ete remplie qu'a partir d'un certain moment : **84 % des
comptes n'ont pas de marque**. Le migrateur les rattache a `ub`, ce qui est
le bon defaut — Dustfolio n'existait pas au debut.

## La table des domaines servait de table de motifs

Le legacy tenait la meme correspondance dans `conf/conf_domaine_2018.php`
et la parcourait ainsi :

```php
foreach ($domaine_table as $key => $dom) {
    if ( preg_match('/'.$key.'/i', $_SERVER['HTTP_HOST']) ) { … break; }
}
```

La cle servait donc de **motif**, pas de nom d'hote :

- `ultra-book` reconnaissait n'importe quel hote contenant ces lettres,
  y compris `faux-ultra-book.com.attaquant.net` ;
- le point de `extra-book.net` valait n'importe quel caractere ;
- le premier motif qui mordait l'emportait, ce qui faisait de **l'ordre du
  tableau une partie du comportement**, sans que rien ne le signale.

Ici la comparaison se fait par **egalite**, sur l'hote prive de son port et
de son prefixe `www.`. Un hote inconnu rend la marque par defaut plutot
qu'une page blanche.

## Un piege de regex : `$` dans une alternative

Le portail repond desormais sur tous les hotes, le middleware
`ResoudreMarque` deduisant la marque de `HTTP_HOST` — c'est le modele du
legacy, un seul point d'entree, sans sa table de motifs.

Il fallait donc empecher `df.<book_domain>` d'etre pris pour le book d'un
creatif nomme « df ». Premier essai :

```php
$loginPattern = '(?!'.implode('$|', $reserves).'$)[-a-zA-Z0-9]+';
```

Sans effet. Le `$` d'une alternative comme `(?!df$|www$)` s'ancre a la fin
du **sujet entier** — ici l'hote complet, `df.ubdf2026.ultra-book.name` — et
non a la fin de l'etiquette capturee. La negation ne mordait jamais.

Le controle porte en fait sur la limite d'etiquette :

```php
$loginPattern = '(?!(?:'.$reserves.')(?![-a-zA-Z0-9]))[-a-zA-Z0-9]+';
```

`df.` va au portail, `dfx.` reste un login valide. Les deux cas sont testes.

Au passage, cette reserve corrige un manque du legacy : **rien n'empechait
un creatif de prendre le login « www »** et de capter le sous-domaine
correspondant.

## Dustfolio n'a pas de contenu propre

Le legacy servait les pages d'Ultra-book en y remplacant le nom au vol,
juste avant l'affichage :

```php
$cont_wp  = preg_replace('/ultra-book/i', $inc_action->site_name, $cont_wp);
$cont_wp  = preg_replace('/POLYGUN/i', 'DustWare SAS', $cont_wp);
```

Le comportement est conserve — c'est la seule facon de ne pas dupliquer 27
pages pour 388 comptes — mais la table des substitutions est declaree dans
`config/marques.php` au lieu d'etre dispersee dans `action.php`.

## Ressources : un dossier, pas un suffixe de fichier

`image_dir` du legacy (`''` / `'_df'`) s'ajoute au **nom du dossier** :
`img_front` et `img_front_df`. Les 10 fichiers de `img_front_df` (68 Ko) sont
repris. `Marque::asset()` applique la regle.

## Domaines

| Marque | Developpement | Production |
|---|---|---|
| Ultra-book | `ubdf2026.ultra-book.name` | `ultra-book.com`, `ultrabook.pro`, `extra-book.net`… |
| Dustfolio | `df.ubdf2026.ultra-book.name` | `dustfolio.com`, `extra-book.biz` |

L'hote de developpement de Dustfolio est un sous-domaine du domaine
principal : le server block nginx couvre deja `*.ubdf2026.ultra-book.name`
(cf. `_doc/03`), aucune modification de Valet n'est donc necessaire.
**L'URL de test principale reste `https://ubdf2026.ultra-book.name/`.**

## Reste a faire

Le pied de page et les fenetres modales reprises du front 2018 contiennent
encore des libelles « Ultra-book » en clair. Le legacy avait exactement le
meme defaut : il ne substituait que dans le contenu WordPress. Ces gabarits
seront repris a la reecriture Alpine/Tailwind (phase 9).

## Tests

119 tests PHP. 10 pour la marque : reconnaissance par egalite, prefixe
`www.` et port, repli par defaut, cloisonnement des deux portails,
sous-domaines reserves, substitution editoriale et repartition des
ressources.

---

# Phase 3g — Multi-langue fr / en / ja (2026-09-16)

## Les catalogues gettext se reprennent tels quels

Le site de 2019 traduisait avec gettext, catalogues `.po` dans
`languages/<posix>/LC_MESSAGES/`. Les deux systemes partagent la convention
qui compte : **la cle est la chaine francaise**. `msgid` devient la cle JSON,
`msgstr` la traduction, et rien n'est a reecrire dans les vues.

`ubdf:import-langues` fait la conversion :

| Catalogue | Entrees | Reprises |
|---|---|---|
| `fr_FR` | 862 | 0 — 848 `msgstr` vides, c'est la langue des cles |
| `en_US` | 1 474 | 1 473 |
| `ja_JP` | 621 | 630 |

Les entrees marquees `fuzzy` sont ecartees : ce sont des propositions non
relues, que gettext lui-meme n'utilise pas.

> A savoir : le catalogue anglais contient des traductions douteuses
> heritees — « Enregistrer » y est rendu par « Record » la ou « Save »
> s'impose. Elles sont reprises **telles quelles** ; les corriger releve
> d'une relecture editoriale, pas du portage.

## Ordre de resolution

1. le parametre `lang` de la requete — la bascule du selecteur ;
2. le cookie, ou le visiteur a laisse son choix precedent ;
3. la session, pour la duree de la visite ;
4. **l'en-tete `Accept-Language`** ;
5. la langue par defaut de la marque.

Le legacy s'arretait au point 3 puis retombait sur le defaut : un visiteur
japonais arrivait en francais tant qu'il n'avait pas trouve le selecteur,
alors que son navigateur annoncait sa langue.

La forme POSIX du legacy reste comprise (`lang=fr_FR`) : les visiteurs de
l'ancien site en portent une dans leur cookie.

## Le cookie de langue reste en clair

Laravel chiffre les cookies par defaut. Celui-ci en est exempte : ce n'est
pas un secret, le JavaScript repris du front 2018 le lit (`lang == 'fr'`
decide quel catalogue de mots-cles charger), et il est partage avec les
books servis sur les sous-domaines. Chiffre, il serait illisible pour eux —
et indechiffrable pour Laravel lui-meme s'il venait de l'ancien site.

## Bascule sans perdre la page

`.htaccess` renvoyait `^en$` sur `action.php?lang=en_US`, qui affichait
l'accueil : changer de langue depuis une fiche de book faisait perdre la
page consultee. Les URL `/fr`, `/en`, `/ja` sont conservees, mais elles
enregistrent le choix et renvoient d'ou vient le visiteur — apres avoir
verifie que le `Referer` appartient bien au site, sans quoi ce serait une
redirection ouverte.

## Un repli sur les actualites

Les 74 actualites reprises de WordPress sont **toutes en francais**. Filtrer
strictement par langue donnait une page vide a un visiteur anglophone.
`/actus` sert donc les actualites de la langue par defaut quand la langue
courante n'en a aucune.

## Limite connue : les vues Blade ne portent pas les appels de traduction

Les templates PHP du legacy appelaient `_('…')` a chaque libelle. Les vues
Blade de la phase 3a ont ete derivees du **HTML deja rendu** — plus fidele
au resultat, mais ces appels ont disparu au passage. Le mecanisme est en
place et les catalogues sont importes ; seules les chaines explicitement
marquees `__()` sont traduites a ce jour.

Le reste des libelles sera marque au fil de la reecriture des gabarits
(phase 9). Les 2 103 chaines importees attendent leurs cles.

## Tests

135 tests PHP. 13 pour la langue : forme POSIX, cookie, session,
`Accept-Language`, priorites entre les sources, bascule avec retour,
refus d'un `Referer` exterieur, cookie non chiffre, exposition au
JavaScript et forme POSIX de `og:locale`.
