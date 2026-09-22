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
