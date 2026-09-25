# Remplacement de jQuery par Alpine.js — inventaire, faisabilité, plan

Périmètre : le **front** (portail Ultra-book / Dustfolio et books créatifs).
L'espace créatif (`/espace/*`) est déjà en Livewire + Alpine et n'est pas concerné.

Relevé du 25/09/2026, fait sur le code (`resources/views`, `public/`) et sur la base locale `2026_ubdf`.

---

## 1. Inventaire

### 1.1 Portail (Ultra-book / Dustfolio)

Toutes les pages du portail passent par `layouts/portail.blade.php` → `partials/head.blade.php`,
qui charge en LABjs : **jQuery 1.12.4 + jquery-migrate 1.4.1**, Semantic UI 2.3.1 (JS), Swiper 4,
socket.io, puis le code maison `js2019/*`. Chaque page du portail dépend donc de jQuery :

| Page | Vue | Code jQuery concerné |
|---|---|---|
| Accueil (cartes, défilement infini, héro) | `front/accueil`, `partials/accueil-hero`, `partials/handlebars` | `js_core_pages.js`, `js_core_cards.js` |
| Recherche | `front/recherche` | `js_core_pages.js`, `js_core_cards.js` |
| Annuaire, catégorie | `front/annuaire`, `front/categorie` | `js_core_cards.js` |
| Portfolio (portail) | `front/portfolio` | `js_core_cards.js` |
| Microbook | `front/microbook` | `js_core_function.js` |
| Inscription, connexion, mot de passe | `partials/modals`, `front/mot-de-passe` | `js_core_inscription.js` |
| Captcha / contact | `front/captcha` | `js_core_function.js` |
| Pages CMS, désabonnement | `front/cms/*`, `front/desabonnement` | en-tête / menu / modales uniquement |
| En-tête, menu, pied, modales (toutes pages) | `partials/header`, `partials/footer`, `partials/modals` | Semantic UI (`.modal`, `.dropdown`, `.popup`, `.sidebar`), tipsy |

Volume du code maison (`public/html_pages_v2018/_/js2019/`) :

| Fichier | Lignes | Appels `$(` | Appels ajax |
|---|---:|---:|---:|
| `js_core_pages.js` | 2 644 | 187 | 4 |
| `js_core_cards.js` | 1 877 | 176 | 4 |
| `js_core_inscription.js` | 1 308 | 84 | 4 |
| `js_core_function.js` | 874 | 68 | 2 |
| `js_core_fm2.js` (admin du book, chargé si `user_admin_js`) | 863 | 71 | 6 |
| `ub_core_function_autres.js` | 276 | 33 | 1 |
| `js_core.js` | 228 | 7 | 0 |
| **Total** | **≈ 8 000** | **≈ 630** | **21** |

Plugins jQuery appelés sur le portail : Semantic UI `.popup` (51), `.modal` (42),
`.dropdown` (24), `.sidebar` (2), `tipsy` (23), `scrollTo` (7). Le template côté
client utilise Handlebars (sans lien avec jQuery).

À noter : `head.blade.php:67` contient `jQuery.noConflict(true)` exécuté avant tout chargement : c'est du code mort.

### 1.2 Books créatifs

Chaque thème charge **sa propre version** de jQuery (de 1.6.2 à 1.12.4), parfois avec
jQuery UI 1.8 ou jQuery Mobile 1.1, ainsi que ses plugins. La logique est dupliquée d'un thème à l'autre
(`ub_book_core_mdl2012.js` + `ub_book_core_pr_2012.js`, ~1 000 à 1 300 lignes chacun).

| Thème (`config/book_themes.php`) | Dossier vues | Books actifs (base locale) | JS maison (lignes / `$(`) | Plugins |
|---|---|---:|---|---|
| `mdl_2016_zoom` | `zoom2016` | 27 | 2 660 / 371 | fotorama, swipebox, tipsy, scrollTo |
| `mdl_2014_responsive` | `responsive` | 24 | 2 120 / 281 | fotorama, swipebox, nailthumb |
| `mdl_classique` | `_racine` | 16 | inline : 84 `$(` sur 20 vues | galleriffic, colorbox, facebox, jaipho (iPhone), jQuery Mobile |
| `mdl_2015_classique` | `classique2015` | 12 | 2 350 / 316 | fotorama, tipsy |
| `mdl_2013_pinter` | `pinter` | 7 | 1 400 / 190 | isotope, bbq |
| `mdl_2015_grid` | `grid2015` | 5 | 2 540 / 337 | isotope, nailthumb |
| `mdl_2012` | `base` | 4 | 1 560 / 200 | montage, vgrid, photoswipe 3 |
| `mdl_2020_ultra_zen` / `_frais` | `ultra2020` | 3 | 930 / 83 (+ admin 1 800 / 193) | Semantic UI, magnific-popup, lavalamp |
| `mdl_2012_slide` | `slide` | 2 | 1 640 / 216 | galleriffic, history |
| — | `non_diffuse` (page d'attente) | — | 2 `$(` | — |
| — | `book/contact.blade.php` | — | 12 `$(` | validate / captcha |

Toutes les pages d'un book (accueil, portfolio, galerie `-p`, galerie mobile `-pi`,
page `-r-c`, actualités, contact) dépendent de jQuery, quel que soit le thème.

Code d'administration intégré au book (chargé seulement si le créatif est connecté) :
ckeditor + adaptateur jQuery, dropzone, fineuploader, miniColors, googlefontpicker,
jquery-ui, colorbox, bootstrap-tooltip/popover (8 vues). L'espace créatif Livewire le remplace déjà.

Doublons ou code mort : `zoom2016_v2-old/`, les fichiers `*_min.js` / `*.min.js` vides
(0 ligne source, contenu minifié en double), la librairie Magnific-Popup avec ses tests
(`qunit`, `website/`), et les scripts commentés de `head.blade.php`.

---

## 2. Faisabilité

**Faisable, mais pas comme un remplacement mécanique.** Alpine ne remplace pas
jQuery ligne à ligne : il remplace les **interactions** (ouvrir/fermer, onglets,
menus, états), et `fetch()` remplace `$.ajax`. Le vrai coût est dans les **plugins** :

| Dépendance jQuery | Remplaçant sans jQuery | Difficulté |
|---|---|---|
| Semantic UI modal / dropdown / popup / sidebar | Alpine (`x-show`, `x-transition`, `@click.outside`, `x-trap`) + CSS de Semantic conservé | moyenne : 120 appels, mais des motifs répétitifs |
| tipsy, bootstrap-tooltip | infobulle CSS ou Alpine (motif de la charte) | faible |
| `$.ajax` / `$.post` / `getJSON` (21 sur le portail) | `fetch()` + jeton CSRF | faible |
| défilement infini + Handlebars (cartes) | Alpine + `fetch` + `IntersectionObserver` ; Handlebars peut rester | moyenne |
| jquery.validate (inscription, contact) | validation HTML5 + erreurs serveur (Form Request) | faible |
| fotorama, galleriffic, swipebox, photoswipe 3, colorbox, magnific | **Swiper** (déjà chargé sur le portail) + **PhotoSwipe 5** (sans jQuery) | élevée : c'est le cœur visuel des books |
| isotope | `isotope-layout` (fonctionne sans jQuery) | faible |
| nailthumb, montage, vgrid | CSS (`object-fit`, grid) | moyenne |
| jQuery Mobile, jaipho (anciennes vues iPhone/iPad) | supprimer : vues responsive | faible si abandon |

Risques :
- **Fidélité des thèmes** : les books sont servis « dans leur thème d'origine ». Tout
  remplacement de galerie change le rendu ; il faut une validation visuelle, thème par thème.
- **Couplage global** : `js_core_*.js` partagent des fonctions et variables globales ;
  on ne peut pas retirer un fichier sans vérifier ses appelants.
- **Pas de filet actuel sur le JS** : les tests Pest vérifient le HTML rendu, pas les erreurs JavaScript.

Recommandation : **convertir le portail entièrement, et pour les books ne convertir que les
thèmes qui en valent la peine** (ultra2020, zoom2016, responsive, classique2015 : 66 books sur 100).
Pour les thèmes peu utilisés (slide, base, pinter, grid2015, `_racine`), mieux vaut migrer
leurs books vers un thème converti proche que les porter.

---

## 3. Plan de codage progressif

Règle de chaque étape : **une page ou un thème par commit**. jQuery reste chargé tant qu'un
consommateur existe, et on vérifie par `grep` qu'une fonction n'a plus d'appelant avant de la retirer.

### Étape 0 — Filet de sécurité (avant toute conversion)
- Tests navigateur **Pest 4 (plugin browser, Playwright)** : pour chaque page du portail et
  chaque couple *thème × type de page de book*, visiter la page, `assertNoJavascriptErrors()`,
  vérifier l'interaction principale (ouvrir la modale de connexion, charger la page 2 des cartes,
  ouvrir une image en grand).
- Captures de référence par thème (comparaison visuelle manuelle à chaque étape).
- Book de test par thème dans la base de dev (voir `_doc/liens_books_acces.TXT`).

### Étape 1 — Nettoyage sans risque
- Supprimer le code mort : `jQuery.noConflict` ligne 67, scripts commentés de `head.blade.php`,
  `zoom2016_v2-old/`, doublons `*_min.js`, dossiers de démo et tests des librairies.
- Supprimer le JS d'administration intégré aux books (ckeditor, dropzone, fineuploader,
  miniColors, fontpicker…) une fois vérifié que l'espace créatif couvre tout, puis `js_core_fm2.js`.
- Critère : les tests de l'étape 0 restent verts.

### Étape 2 — Alpine en parallèle de jQuery
- Entrée Vite `resources/js/front.js` (Alpine + modules front), chargée par
  `layouts/portail` et par chaque layout de thème. Alpine et jQuery coexistent sans conflit.
- Interrupteur de configuration `config('front.alpine')` (liste des pages ou thèmes convertis) :
  retour arrière immédiat, sans redéploiement de code, si une conversion pose problème en production.

### Étape 3 — Portail, du plus simple au plus couplé
1. En-tête, menu, pied : dropdown / sidebar / popup → Alpine ; tipsy → infobulle CSS.
2. Modales (`partials/modals`) : `.modal` Semantic → composant Blade `<x-front.modale>` Alpine.
3. Inscription / connexion / mot de passe : `js_core_inscription.js` → Alpine + `fetch` ;
   validation par Form Requests (erreurs 422 affichées sous les champs).
4. Contact et captcha.
5. Cartes, défilement infini, recherche, annuaire, catégorie : `js_core_cards.js` et
   `js_core_pages.js` → composant Alpine `cartes()` (`fetch` + `IntersectionObserver`).
   Les routes JSON existantes (`/cartes/...`, `/accueil__...`) ne changent pas.
6. Microbook.
7. Retrait de Semantic UI JS (le CSS peut rester), puis de jQuery + migrate sur le portail.

### Étape 4 — Books, thème par thème, par ordre d'usage
Commencer par ultra2020 (code le plus récent, aucun `$(` inline), puis zoom2016, responsive,
classique2015. Pour chacun :
1. Extraire le noyau commun (`ub_book_core_*`) en un module Alpine partagé
   `resources/js/book/` (menu, navigation, formulaire de contact, statistiques).
2. Galerie → Swiper + PhotoSwipe 5 ; isotope → `isotope-layout`.
3. Validation visuelle contre les captures de l'étape 0, puis activation par l'interrupteur de l'étape 2.
4. Retrait de jQuery du layout de ce thème.

Thèmes peu utilisés : proposer (ou faire) la migration de leurs books vers le thème converti
le plus proche, puis retirer le thème. **Décision produit à prendre avant cette étape.**

### Étape 5 — Retrait final
- Supprimer `public/js_cdn/jquery*`, `public/js_jquery/`, les librairies jQuery devenues orphelines.
- Contrôle : `grep -rE "jQuery|\\$\\(" resources/views public/*/js` ne doit plus rien renvoyer
  hors code tiers conservé volontairement.

### Ordre de grandeur
| Étape | Charge estimée |
|---|---|
| 0 — filet | 2 à 3 j |
| 1 — nettoyage | 1 j |
| 2 — socle Alpine | 0,5 j |
| 3 — portail | 8 à 12 j |
| 4 — books (4 thèmes) | 3 à 5 j par thème |
| 5 — retrait | 0,5 j |

---

## 4. Avancement — portail terminé (25/09/2026, branche `jquery-alpine`)

Le portail (Ultra-book, Dustfolio) ne charge plus ni jQuery, ni Semantic UI JS,
ni LABjs, ni Handlebars, ni `js2019/`. Tout son JavaScript est
`resources/js/portail.js` (Alpine), un module par fonction :

| Module | Remplace |
|---|---|
| `modales.js` + `<x-portail.modale>` | Semantic modal |
| `connexion.js`, `inscription.js`, `recaptcha.js` | `js_core_inscription.js` |
| `controles.js` | Semantic dropdown / checkbox |
| `entete.js` (`x-infobulle`, menu, haut de page) | `ubdf_accueil`, `ub_menu`, Semantic popup / visibility |
| `recherche.js` + `motcles.json` | `ubdf_recherche` : aboutit a `/recherche` (rendu serveur) |
| `cartes.js` | `book_static_show`, `ub_infinit`, `public/js/ubdf-infinite.js` |
| `visionneuse.js` + `partials/visionneuse` | Swipebox, `tpl_book_open`, mémo book |
| `contact.js` | contact intermédiaire (`tpl_bloc_modal_content_ajax_*`) |
| `cookies.js`, `newsletter.js` | `cookie_rgpd`, `ub_newsletter` |

Les feuilles CSS du front 2018 (Semantic UI CSS, `core.css`, swipebox) restent :
les composants gardent les classes d'origine.

Filet : `npm run test:front` (Playwright sur le site Valet, 13 pages, parcours
connexion, inscription, recherche, visionneuse, contact, mémo book, défilement).

Défauts corrigés en chemin : erreur de connexion jamais affichée, suffixe de book
`.ubdf2020ssl.localhost:4433` en dur, mot de passe de 2 caractères accepté par le
navigateur, jeton reCAPTCHA périmé, consentement cookies limité à la session,
image de la visionneuse en vignette 250×136, erreurs `jQuery is not defined` et
`ga is not defined`, appels de statistiques vers le serveur de production.

Restent à traiter :
- **Thèmes des books** (`resources/views/book/themes/*`, `public/2012_web/*`) :
  jQuery par thème, reporté (voir §3, étape 4).
- **Newsletter** : `/front/action_ajax_2.php` n'existe pas dans le nouveau site ;
  le formulaire affiche une erreur. Il faut une route et un stockage des abonnés.
- `/img_admin/diffusion-b.svg` (fenêtre « Créer un book ») est absent.
