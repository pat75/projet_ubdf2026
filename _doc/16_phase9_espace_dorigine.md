# Phase 9 — L'espace créatif au design d'origine

Plan de codage. L'espace créatif de 2026 a été construit en Livewire et
Tailwind, avec une mise en page inventée (barre latérale à gauche, dix
entrées). L'espace de production est tout autre : colonne de contenu à
gauche, menu à droite, habillage Semantic UI et feuille maison. Il faut
reprendre cette partie pour que le créatif retrouve exactement ce qu'il
connaît.

Décisions prises avec Pat le 23 septembre 2026 :

- **tout le projet reste sur Tailwind** : on ne recharge pas la feuille de
  2018 dans l'espace, on reproduit son rendu avec les jetons relevés
  dedans (décision du 23 septembre, qui remplace l'option « reprendre le
  CSS d'origine » retenue une heure plus tôt) ;
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

Cette feuille n'est pas chargée par l'espace — le projet reste sur
Tailwind — mais elle sert de **relevé de côtes** : couleurs, corps de
texte, marges et ombres en sont extraits et réinscrits en jetons Tailwind
dans `resources/css/espace.css`, chacun avec le nom qu'il portait dans le
LESS d'origine. C'est ce qui permet de retrouver l'aspect sans reprendre
la dépendance à Semantic UI.

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
services, les validations, les tests — et on ne change que les vues et
l'habillage.

| | Aujourd'hui | Après |
|---|---|---|
| Squelette | barre latérale gauche, pleine largeur | grille 12/4 de l'original, largeur 1127 px, panneau blanc |
| Feuille | Tailwind, palette par défaut | Tailwind, jetons relevés dans `core_user_admin.less` |
| Navigation | 10 entrées à gauche | menu de droite : formule, liens de service, liste du compte, liste du book |
| Fontes | pile système | Lato pour les titres, Source Sans Pro pour le texte |
| Mode sombre | présent | retiré, l'original n'en a pas |
| Composants PHP | inchangés | inchangés |

Le piège à connaître : le **Preflight de Tailwind** réinitialise ce que
Semantic UI met en forme. C'est la raison pour laquelle
`resources/css/ubdf.css`, côté portail, n'importe pas Tailwind. Les deux
feuilles ne doivent donc jamais se retrouver sur la même page tant que le
portail n'a pas basculé à son tour : l'espace charge `espace.css` et lui
seul, et ne réutilise aucun gabarit du portail.

---

## 3. Découpage

### Lot A — Le squelette et le menu — **fait**

1. `resources/css/espace.css` : les jetons relevés dans
   `core_user_admin.less` — rouge `#DF014C`, les trois gris, fond de page
   `#f8f8f8`, rayon 5 px, ombre `0 0 3px #959595`, Lato et Source Sans
   Pro — plus l'utilitaire `panneau-espace`.
2. `layouts/espace.blade.php` refait : grille 12/4 dans une largeur de
   1127 px, panneau blanc de 730 px de haut minimum, padding de 4 %.
3. `partials/espace/menu.blade.php` : bandeau de formule (gras cramoisi
   pour une formule payante, gris pour la gratuite, fanion « Sélection »
   quand `in_home_selection`), liens de service (book en bleu, sortie en
   chocolat), liste du compte, liste du book.
4. `x-espace.nav-lien` rendu sous forme de `<li>` séparé d'un filet, la
   rubrique ouverte en rouge avec sa puce.
5. `x-espace.titre` : le `.h1_page_titre` d'origine, 28 px maigre, adopté
   par les onze écrans.
6. Mode sombre retiré du squelette.

Le bandeau rouge de prise d'identité est conservé : il vit au-dessus de la
grille.

Deux écarts assumés, à reprendre plus tard :

- **L'en-tête** reste réduit à la marque. L'espace d'origine affiche
  l'en-tête complet du portail, qui est encore en Semantic UI ; il
  viendra quand le portail basculera.
- **Le menu est `sticky`** là où l'original est `fixed` : même effet au
  défilement, sans chevaucher le pied de page.

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
