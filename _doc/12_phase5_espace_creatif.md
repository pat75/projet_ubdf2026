# Phase 5 — Espace créatif

Choix (22/09/2026) : l'espace est construit directement en Livewire 4 +
Alpine + Tailwind 4, et non porté depuis le legacy (jQuery/Semantic UI).
Il n'est vu que par les créatifs connectés, n'a aucune URL indexée à
préserver, et un portage aurait été refait en phase 9.

La feuille `resources/css/espace.css` n'est chargée que par
`layouts/espace` : le preflight de Tailwind n'atteint pas le portail, qui
reste sur Semantic UI.

## Inventaire du legacy

| Legacy | Rôle | Lot |
|---|---|---|
| `ubaction__user_bookaccueil` | tableau de bord | 5a |
| `ajax_2011_usadmin.php` rub_* / img_* | galeries, visuels, envoi, ordre | 5b |
| `ajax_2011_usadmin.php` pag_add, `ajax_2014_usadmin_editxt.php` | pages de texte, textes du thème | 5c |
| `ubaction__user_pref_form`, conf_modif_* | habillage et réglages des 11 thèmes | 5d |
| `ubaction__user_diffusion` | diffusion (portail, web, newsletter, dispo) | 5d |
| `ubaction__user_modif_form` | informations du compte, mot de passe | 5e |
| `ubaction__user_message`, `intermediate_msg_` | messages reçus | 5f |
| `ubaction__user_pref_formule`, factures | formule et factures | 5f |
| `ubaction__user_pref_nano` | export du book (nanobook) | 5f |
| PDF du book (propriétaire seul) | export PDF | 5f |

Les rubriques non livrées n'apparaissent pas dans le menu
(`x-espace.nav-lien` teste `Route::has`).

## 5a — Socle

Livewire installé, `layouts/espace`, menu, tableau de bord (`/espace`).

## 5b — Galeries et visuels

- `/espace/galeries` : créer, renommer (édition en place), masquer,
  supprimer (la galerie et ses visuels), réordonner par glisser-déposer.
- `/espace/galeries/{id}` : envoi multiple par dépôt ou sélection
  (JPG/PNG/GIF, 20 Mo, 50 fichiers), titre et description, masquer,
  supprimer, réordonner.
- `DepotVisuel` borne la source à 1980x3600 (déclinaison `source`) et la
  réencode : EXIF et contenu greffé disparaissent. Nom de fichier aléatoire.
- L'ordre des visuels reste `galleries.media_order`, en identifiants legacy
  pour les visuels repris : c'est ce que lit `ContexteBook::ordonner()`.
- Accès : `GalleryPolicy` (le propriétaire seul), vérifiée par la route et
  par chaque action Livewire.

## 5c — Pages de texte

- `/espace/pages` : rubriques (accueil, pages, actualités du classique 2010)
  et leurs pages ; créer, renommer, masquer, supprimer, réordonner
  (`page_order`, en identifiants legacy comme `media_order`).
- `/espace/pages/{id}` : édition avec Trix. Pas de fichier joint dans
  l'éditeur : les images passent par les galeries.
- `NettoyeurHtml` (symfony/html-sanitizer) filtre le corps à
  l'enregistrement : les gabarits l'affichent sans échappement.
- Limite connue : Trix normalise le HTML qu'il charge. Une page reprise du
  legacy (CKEditor : tableaux, polices, styles en ligne) perd cette mise en
  forme au premier enregistrement depuis l'espace.

## 5d — Habillage et diffusion

- `/espace/habillage` : choix parmi les modèles de `config/book_themes.php`,
  titre, description, pied de page, réglages du modèle.
- Le formulaire des réglages est déduit de la configuration JSON du thème
  (`data`) : couleur, case, nombre ou texte selon la valeur. Le format
  enregistré reste celui du legacy, lu tel quel par `ContexteBook`.
  Les valeurs sont contrôlées par type (couleur `#hex`, nombre, texte sans
  balises) : elles finissent dans du CSS en ligne.
- Chaque modèle garde ses réglages : ceux des modèles inactifs restent dans
  `legacy_payload` sous la colonne legacy du thème (`ReglagesTheme`).
- Libellés : dérivés des clés techniques (« Link bio », « Couleur fond »…),
  à remplacer par des libellés rédigés si besoin.
- `/espace/diffusion` : book en ligne, portail, newsletter, disponibilité.
- Pas repris : la barre de réglage visuelle intégrée au book
  (`ubdf_barrereglage`), et le choix des polices Google Fonts dans une liste
  (champ texte libre pour l'instant).

## 5e — Compte

- `/espace/compte` : métier, coordonnées, liens (http/https seulement).
- Adresse e-mail et mot de passe : le mot de passe actuel est exigé ;
  adresse unique ; session régénérée après changement de mot de passe.
- Non fait : déconnexion des autres appareils (demanderait le middleware
  `AuthenticateSession`), confirmation de la nouvelle adresse par e-mail.
