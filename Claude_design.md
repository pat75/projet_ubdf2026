# Claude_design.md — charte graphique de l'espace créatif

À appliquer à toute modification graphique d'une page de l'espace
(`/espace/*`). Page de référence : `/espace/messages`
(`resources/views/livewire/espace/messages.blade.php`).

Couleurs, rayons et ombres : jetons de `resources/css/espace.css`
(`ub-texte`, `ub-texte3`, `ub-filet`, `ub-fond`, `ub-accent`…). Pas de
couleur en dur quand un jeton existe.

## Boutons

- Fond noir, **aucun arrondi** : utilitaire `bouton-espace`
  (noir, angles droits, survol `#333`, désactivé à 60 %).
- Seules la taille et la marge varient : `bouton-espace px-5 py-2.5 text-[14px]`.
- Composant Blade : `<x-espace.bouton>`.
- Bouton secondaire (pagination, page non courante) : `border border-ub-bord bg-white`, angles droits aussi.

## Badges en face des titres

Le compteur placé en face d'un titre de page (ex. « 28 non lus » en face
de « Mes messages ») :

```blade
<span class="inline-flex items-center gap-2 rounded-sm bg-black px-4.5 py-2.5 text-[14px] font-bold text-white">…</span>
```

- Fond noir, texte blanc gras, **arrondi 4px** (`rounded-sm` en Tailwind 4).
- Aligné à droite du titre (`flex justify-between items-end`).

Exception : la pastille de compteur **dans un onglet** (ex. « 3 » dans
Contacts) reste ronde et bleue : `rounded-full bg-ub-accent text-white text-[12px]`.

## Flèches d'ouverture

Toute flèche qui ouvre un menu ou déplie un bloc (liste de messages,
sections pliantes) se place **à droite** de sa ligne et reprend les
pictos de Mes messages. Même picto `angle-droite` pour les chevrons de
lien en bout de ligne (jauges du tableau de bord). Les flèches « → »
des cartes d'action restent des flèches de lien.

```blade
<x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />  {{-- fermé --}}
<x-espace.picto nom="angle-bas"    class="h-5 w-5 shrink-0 text-ub-texte" />  {{-- ouvert --}}
```

- Flèche pleine (glyphe Font Awesome d'origine), 20px, couleur `ub-texte` : bien visible.
- Pas de chevron fin en trait, pas de caractère `❯`, pas de rotation CSS : on permute les deux pictos.
- Avec Alpine : deux pictos, `x-show="ouvert"` / `x-show="! ouvert"`.

## Pictogrammes

Composant `<x-espace.picto nom="…">` (glyphes relevés dans la messagerie
d'origine) : `courrier` (non lu), `courrier-lu` (lu), `poubelle`,
`restaurer`, `angle-droite`, `angle-bas`. Ajouter un picto = ajouter son
tracé dans `resources/views/components/espace/picto.blade.php`.

## Onglets

Onglets et panneau dans une même carte (`carte-espace overflow-hidden`) :

- barre `flex bg-ub-fond border-b border-ub-filet`, onglets `flex-1` (largeur égale, toute la largeur) ;
- onglet actif : `bg-white font-bold text-ub-accent-texte`, il se fond dans le panneau ;
- onglet inactif : fond de la barre, `text-ub-texte3` ;
- **aucun soulignement** de couleur sous l'onglet actif.

## Listes dépliables

- Ligne fermée : séparée par `border-b border-ub-filet`.
- Élément ouvert : encadré d'un filet de 1px à 50 % du texte
  (`border border-ub-texte/50`) qui englobe la ligne de titre et tout son
  contenu ; filet `border-t border-ub-filet` entre les deux.
- Un clic sur la ligne déplie, un second replie.

## Bulles de conversation

Fond `#efefed`, angles droits, queue triangulaire en bas ; alternance
gauche (expéditeur) / droite (créatif). Légende sous la bulle : nom en
gras `text-ub-texte2`, date `text-ub-texte3`, 12px.

## Cartes

Bloc de contenu : utilitaire `carte-espace` (fond blanc, rayon et ombre
de la charte).
