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
