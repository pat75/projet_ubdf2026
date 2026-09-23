# Phase 9 — L'espace créatif au design d'origine

Plan de codage. L'espace créatif de 2026 a été construit en Livewire et
Tailwind, avec une mise en page inventée (barre latérale à gauche, dix
entrées). L'espace de production est tout autre : colonne de contenu à
gauche, menu à droite, habillage Semantic UI et feuille maison. Il faut
reprendre cette partie pour que le créatif retrouve exactement ce qu'il
connaît.

Décisions prises avec Pat le 23 septembre 2026 :

- on **reprend le CSS d'origine** plutôt que de l'imiter en Tailwind ;
- je travaille **sur les sources seules** : pas de connexion au site
  d'origine (voir « Pourquoi pas de captures » plus bas) ;
- rendu identique sur écran large, mais **l'affichage mobile de 2026 est
  conservé** au lieu du refus sur tablette ; pas de mode sombre ;
- les trois rubriques propres à 2026 — Statistiques, Exporter, Parrainage —
  **restent au menu**, habillées comme le reste.

---

## 1. Ce que j'ai vérifié avant d'écrire ce plan

**Tout le CSS de l'espace d'origine est déjà dans le dépôt.** La feuille
`public/html_pages_v2018/_/css2019/core.css` (336 Ko, 11 882 lignes) est la
compilation des `less2019/`, dont `core_user_admin.less` qui pèse à lui
seul 97 Ko et ne sert qu'à l'espace créatif. Les sélecteurs y sont :
`bloc_cms_user` y apparaît 133 fois, `ub_user_menu` 47 fois, `ub_nav_user`
13 fois. Semantic UI 2.3.1, `responsive-semantic-ui.min.css`, la fonte
d'icônes et les bibliothèques d'appoint (minicolors, iCheck, jGrowl,
intro.js) sont également présentes sous `public/html_pages_v2018/_/lib/`.

Autrement dit : **il n'y a pas une ligne de CSS à écrire.** Le travail
consiste à produire, dans les vues Blade, le balisage que cette feuille
attend.

**Le chrome est déjà reproduit.** `layouts/portail.blade.php` inclut
`partials/head.blade.php` (20 Ko, l'en-tête 2018 avec ses feuilles) et
`partials/footer.blade.php`. L'espace d'origine s'insère entre les mêmes
deux morceaux (`ubdf___content.tlp.php` appelle successivement le contenu,
`ubdf_menu_droite.tlp.php` et `ubdf_footer.tlp.php`). L'espace 2026, lui,
a son propre squelette minimal — c'est ce squelette qu'il faut remplacer.

**La structure d'origine**, lue dans `html_pages_v2018/tpl/` :

```
ubdf___head.tlp.php          en-tête commun au portail
ubdf___content.tlp.php       aiguillage
  ubdf_adminuser.tlp.php     l'espace lui-même (33 Ko)
    <div class="ui vertical masthead bloc_base_contant">
      <div class="ui container bloc_user" id="type_cmsuser_{accueil|portfolio|news}">
        <div class="ui grid">
          <div class="twelve wide column">   ← contenu
            <div id="bloc_cms_user"> …
          <div class="four wide column">     ← menu
            ubdf_menu_droite.tlp.php
  ubdf_footer.tlp.php
```

Le menu de droite (`ub_user_menu`) contient, dans cet ordre : le titre
« Mon compte » avec l'icône de déconnexion et les trois pastilles d'état
(`gal_msg_waite`, `gal_msg_info`, `gal_msg_error`), le bandeau de formule
(`user_formule`, classe `formule_ub` ou `formule_gratuite`, plus le fanion
`us_affhome` quand le book est en sélection), l'encart promotionnel
repliable, la liste `ub_nav_user` (Tableau de bord, Mon compte, Ma formule,
Mes messages, Aide) puis la liste des rubriques `ub_nav_dossier`.

**Deux variantes d'espace cohabitent en production**, selon
`inc_user_pref.us_pf_v2` : l'ancienne liste de rubriques libres, et la
version 2 où les rubriques sont ramenées à trois catégories — accueil,
portfolio, actualités — pilotées par `gal_id_categorie`. J'ai compté dans
`ub2020` : **56 433 comptes en version 2 contre 18 746 en version 1**, et
les quatre books témoins (`adolie`, `anneletuffe`, `audreystylistephoto`,
`arcenterre`) sont tous en version 2. C'est donc la version 2 — celle que
voit un compte en modèle ultra-frais — que l'on reproduit. La version 1 ne
sera pas rebâtie : le modèle de données de 2026 a déjà unifié les deux.

### Pourquoi pas de captures du site d'origine

Se connecter à `https://ubdf2020ssl.localhost:4433/` **écrit dans
`ub2020`** : à l'ouverture de session, `user2010_open_action()` appelle
`user2020_token_book_deleted()`, qui exécute un `DELETE FROM
inc_user_token`. La règle du projet est que cette base ne doit jamais être
modifiée ; je m'en tiens donc aux gabarits et au CSS, qui décrivent le
rendu sans ambiguïté. La conséquence est indiquée au lot F : ce que le
JavaScript compose à l'exécution devra être validé par Pat à l'écran.

---

## 2. Principe

On garde **toute la logique de 2026** — les neuf composants Livewire, les
services, les validations, les tests — et on ne change que les vues et la
feuille de style chargée.

| | Aujourd'hui | Après |
|---|---|---|
| Squelette | `layouts/espace.blade.php`, Tailwind | `layouts/espace.blade.php` refait sur `partials/head` + grille Semantic |
| Feuille | `resources/css/espace.css` (Tailwind isolé) | `core.css` déjà servi, plus un `espace.css` réduit aux seuls correctifs |
| Navigation | barre latérale gauche, 10 entrées | `ub_user_menu` à droite, structure d'origine + 3 entrées |
| Vues Livewire | balisage Tailwind (376 lignes) | balisage à classes `bloc_cms_user`, `ui grid`, `ui button`… |
| Composants PHP | inchangés | inchangés |

Le risque principal est le **Preflight de Tailwind**, qui réinitialise ce
que Semantic UI met en forme. C'est déjà la raison pour laquelle
`resources/css/ubdf.css` n'importe pas Tailwind. L'espace doit suivre la
même règle : sortir de Tailwind complètement, plutôt que tenter de faire
cohabiter les deux dans la même page.

---

## 3. Découpage

### Lot A — Le squelette et le menu

1. Réécrire `resources/views/layouts/espace.blade.php` : `partials/head`,
   `body class="marque_… page_user"`, `partials/header`, la grille
   `ui container bloc_user` → `twelve wide column` + `four wide column`,
   `partials/footer`, `@livewireScripts`.
2. Créer `resources/views/partials/espace/menu.blade.php`, transcription
   de `ubdf_menu_droite.tlp.php` : titre, déconnexion, pastilles d'état,
   bandeau de formule (avec le fanion « Sélection » quand
   `in_home_selection`), `ub_nav_user`, `ub_nav_dossier`.
3. Adapter `x-espace.nav-lien` pour produire un `<li>` avec la classe
   `selected` et l'icône `fonticon-uniF006` de la page courante.
4. Vider `resources/css/espace.css` de Tailwind ; n'y garder que les
   correctifs (affichage mobile conservé, éditeur Trix).
5. Retirer l'amorce de mode sombre du squelette et la bascule associée.

Le bandeau rouge de prise d'identité, ajouté aujourd'hui, est conservé —
il vit au-dessus de la grille.

### Lot B — Tableau de bord

`ubdf_admin_home.tlp.php` : blocs de quotas (visuels, poids, pages), le
compteur de visites, les cercles de statistiques, l'encart « ajoutez vos
premières images » quand le book est vide. Le div `quota_init` en tête de
page porte les quotas en attributs `data-` : à reproduire tel quel, le
JavaScript d'origine s'en sert.

### Lot C — Portfolio : rubriques, galeries, visuels

Le gros morceau, et le cœur de l'outil : `ubdf_adminuser.tlp.php`, blocs
« gal », « ligne img », « detail img - page ». Grille de vignettes,
glisser-déposer de l'ordre, envoi de visuels, panneau de détail d'une
image. La mécanique reste celle de 2026 (`Galeries`, `Visuels`,
`DepotVisuel`, `x-espace-tri`) ; seul le balisage change.

### Lot D — Pages, habillage, diffusion

Pages et éditeur de texte, choix du modèle et de ses réglages
(`ReglagesTheme`), options de diffusion. Les sélecteurs de couleur
(minicolors) et les cases à cocher (iCheck) ont leur feuille dans le
dépôt : on reprend leur balisage, le comportement reste en Alpine.

### Lot E — Mon compte, Ma formule, Mes messages

`ubdf_user.tlp.php`, `ubdf_formule.tlp.php` (58 Ko, la page la plus
lourde : grille des offres, promotions, codes), `ubdf_message.tlp.php`
(liste, fil de discussion, formulaire de réponse). Les trois écrans propres
à 2026 — Statistiques, Exporter, Parrainage — reçoivent au passage le même
habillage.

### Lot F — Vérification à l'écran

Les tests actuels portent sur le comportement et survivront au changement
de balisage ; ils ne diront rien du rendu. Il faudra donc une passe devant
l'écran, book par book, avec les comptes de `liens_books_acces.TXT` —
`adolie` en premier, puisque c'est le modèle ultra-frais qui sert de
référence. Je préparerai une liste d'écrans à parcourir.

---

## 4. Ce qui ne pourra pas être identique

Trois points, à dire d'emblée plutôt qu'à découvrir en route :

1. **L'éditeur de texte.** L'original utilise Redactor, un composant
   commercial qui n'est pas redistribuable. 2026 utilise Trix. La barre
   d'outils sera habillée pour s'en rapprocher, mais elle ne sera pas la
   même.
2. **Les fenêtres et notifications.** jGrowl, tipsy, les popups Semantic :
   leurs feuilles sont là, mais le comportement est réécrit en Alpine et
   Livewire. Le rendu sera conforme, les animations pas toujours.
3. **La visite guidée** (intro.js, les attributs `data-intro` du menu) : à
   reprendre seulement si Pat y tient, c'est un chantier à part.

---

## 5. Ordre proposé

A, puis B, puis C, D, E, et F en continu. Le lot A donne tout de suite
l'aspect d'ensemble — c'est lui qui permet de juger sur pièce et de
corriger le tir avant d'avoir refait dix écrans. Un commit par lot, les
tests à chaque fois.
