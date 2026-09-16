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
