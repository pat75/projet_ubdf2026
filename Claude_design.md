# Claude_design.md — charte graphique de l'espace créatif

À appliquer à toute modification graphique d'une page de l'espace
(`/espace/*`). Page de référence : `/espace/messages`
(`resources/views/livewire/espace/messages.blade.php`).

Couleurs, rayons et ombres : jetons de `resources/css/espace.css`
(`ub-texte`, `ub-texte3`, `ub-filet`, `ub-fond`, `ub-accent`…). Pas de
couleur en dur quand un jeton existe.

## Logo

Ultra-book utilise toujours le même visuel, sur l'ensemble du site :
`/img_front/ultra-book_logo_nb.svg`. Jamais de logo recomposé en texte
ni d'autre fichier. Sur fond sombre : même fichier, avec `invert`.

## Titre de page

Le bloc d'en-tête de chaque page de l'espace (référence : « Mes
messages »), à décliner sur toutes les pages :

```blade
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Échanger, communiquer, deviser') }}</div>
        <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Mes messages') }}</h1>
    </div>

    <span class="inline-flex items-center gap-2 rounded-sm bg-black px-4.5 py-2.5 text-[14px] font-bold text-white">
        {{ __(':n non lus', ['n' => 26]) }}
    </span>
</div>
```

- **Sous-titre** (au-dessus du titre, ex. « Échanger, communiquer,
  deviser ») : 13px, semi-gras, majuscules, tracking `.08em`, couleur
  **bleue** `text-ub-accent-texte` — jamais grise. C'est le repère de
  rubrique, il donne le ton de couleur de toute la page.
- **Titre** (h1) : `font-titre`, 34px, `font-light`, `leading-tight`,
  `tracking-tight`, couleur `text-ub-texte`.
- **Label à droite** (ex. « 26 non lus ») : le badge noir arrondi 4px
  décrit plus bas — `items-end` aligne son bas sur celui du h1.
  Optionnel : seules les pages avec un compteur pertinent le portent.

Toutes les pages de l'espace reprennent ce bloc pour leur en-tête, à
l'exception de Tableau de bord, Mon compte et Ma formule, dont l'en-tête
n'est pas retouché pour l'instant.

## Boutons

- Fond noir, **aucun arrondi** : utilitaire `bouton-espace`
  (noir, angles droits, survol `#333`, désactivé à 60 %).
- Composant Blade : `<x-espace.bouton>`.
- Bouton secondaire (pagination, page non courante) : `border border-ub-bord bg-white`, angles droits aussi.

### Deux hauteurs, selon l'importance

Toujours poser `bouton-espace` avec l'un des deux modificateurs de
hauteur ci-dessous — jamais de `py-*`/`text-[…]` improvisés au cas par
cas, pour que toutes les hauteurs de l'espace restent alignées sur ces
deux seules valeurs :

- **`bouton-espace-grand`** (43px, référence : « Comparer les formules »
  sur `/espace/formule`) — action principale d'une page ou d'un bloc :
  Renouveler, Comparer les formules, Écrire à, Profiter de l'offre,
  Activer, Sélectionner une formule, Enregistrer, Envoyer une réponse,
  Voir mon book.
- **`bouton-espace-petit`** (33px, référence : « Copier » dans le bloc
  « Offre couplée » de `/espace/formule`) — action secondaire, répétée,
  ou en ligne à côté d'un champ : Copier, Ouvrir, Annuler, Modifier,
  Supprimer/Restaurer un message, pagination.

### Deux boutons côte à côte : toujours la même hauteur

Quand deux boutons se suivent sur une même ligne (ex. « Envoyer » et
« Correction IA », ou « Annuler » et « Enregistrer » de la barre
sticky), ils portent le **même** modificateur de hauteur — `-grand`
ensemble ou `-petit` ensemble, jamais l'un à 43px et l'autre à 33px.
C'est la ligne, pas l'action individuelle, qui fixe la hauteur : une
action secondaire posée à cote d'une action principale grand prend
elle aussi `bouton-espace-grand`.

Seul `px-*` (marge latérale) varie encore d'un bouton à l'autre ; la
hauteur et la taille de texte sont fixées par le modificateur, donc
stables quel que soit le texte du bouton.

## Édition sur place

Un texte modifiable (titre, description…) ne montre **pas de champ** :
il s'affiche comme du texte, suivi d'un **crayon placé juste après le
dernier caractère**, décalé de 10px (`ml-2.5`).

**Règle de l'espace** : tout champ texte qui s'édite seul suit ce
principe, sur toutes les pages. Références : bloc « Présentation » de
`/espace/habillage`, cartes « Profil » et « Localisation » de
`/espace/compte`.

```blade
<x-espace.champ-editable nom="titre" :valeur="$titre"
    :libelle="__('Titre du book')" :vide="__('Ajouter un titre…')" />
<x-espace.champ-editable nom="description" :valeur="$description" multiligne
    :libelle="__('Description')" :vide="__('Ajouter une description…')" />
```

- **Entrer en édition** : clic sur le texte ou sur le crayon. Le curseur
  se place en fin de texte, rien n'est sélectionné ni effacé.
- **Pendant l'édition** : un filet **vert** (`border-b` `ub-succes`)
  souligne **le texte seul** — saisi ou en cours de saisie —, jamais toute
  la largeur de la ligne ; la typo ne change pas. Le **crayon disparaît**.
  En multi-lignes (`multiligne`), la zone s'agrandit avec le contenu.
- **Enregistrer** : sortie du texte, ou Entrée (une ligne seulement ; en
  multi-lignes, Entrée va à la ligne). Rien n'est envoyé si la valeur n'a
  pas changé. **Échap** annule.
- **Pendant l'enregistrement** : le texte passe **en vert** (`ub-succes`)
  dans une seule vague douce, puis revient à sa couleur (1,2 s, `champ-editable-vibre`) — jamais de clignotement agressif.
- **Confirmation** : à la place du crayon, une **coche verte** pendant
  **4 s**, puis le crayon revient.
- **Refus** (validation) : message rouge `ub-danger` sous le texte.
- **Texte vide** : invite en italique `ub-texte4` (`vide`).
- **Côté Livewire** : trait `App\Livewire\Concerns\EnregistreChamps`,
  avec `champsAutoEnregistres()` (nom => règles, liste blanche) et
  `persisterChamp($nom, $valeur)`. Un nom absent de la liste est refusé.
- Côté navigateur : Alpine `champEditable` (`resources/js/espace.js`).
- **Donnée privée** : `prive` ajoute le point `ub-prive` (●) après le
  libellé (ex. Téléphone).
- **Listes** (Métier, Statut…) suivent le même principe qu'un texte : pas
  de champ visible, la valeur choisie suivie d'une **flèche bas**, à la
  même place que le crayon (`ml-2.5`). Composant
  `<x-espace.select-discret :options="…" :valeur="…" :libelle="…"
  wire:model.live="…" />` : un `<select>` natif invisible, posé par-dessus
  le texte et la flèche, reçoit le clic, le clavier et le défilement
  tactile — pas de dropdown refait à la main. Comme les textes,
  s'enregistre dès que la valeur change (`wire:model.live` +
  `updatedXxx()`), sans bouton.
- **Champ à saisie progressive** (ex. SIRET, qui interroge un annuaire dès
  que le numéro est complet) : au repos, texte + crayon comme un champ
  normal ; au clic, le **champ d'origine** réapparaît (`wire:model.live`,
  indicateur de recherche…), puisque la saisie doit rester suivie au fil
  de la frappe, pas seulement à la sortie. Revient seul à l'affichage
  texte une fois la valeur trouvée (évènement dédié, ex. `siret-trouve`).
- **Interrupteurs** (SMS…) restent des contrôles visibles, et
  s'enregistrent eux aussi dès qu'ils changent.
- **Champ sensible mais courant** (mot de passe, adresse mail — c'est
  l'identifiant de connexion) : même principe texte + crayon, mais avec
  un vrai `<input>` plutôt qu'un texte éditable sur place : le clic sur
  le crayon ouvre le champ, la sortie du champ appelle directement
  `$wire.enregistrerChamp(nom, valeur)`, exactement comme
  `champ-editable` (pas `wire:model` + hook `updatedXxx()`). Toujours
  **sans mot de passe actuel ni confirmation** : la session en cours
  prouve déjà l'identité. Pour le mot de passe : masqué au repos (des
  points, jamais la vraie valeur), et un **œil** dans le champ pour le
  relire pendant la frappe (`text`/`password`). **Exception propre au
  mot de passe et à l'adresse mail** (et à eux seuls sur le site) : pas d'enregistrement à la
  sortie du champ ; un bouton « Valider » noir, texte blanc, apparaît
  dans le champ, à droite de l’œil (pas d'œil pour le mail, toujours
  lisible ; le mot de passe s'affiche en clair à l'ouverture) dès l'ouverture du champ, Entrée valide aussi ; Échap ou un clic ailleurs dans la page annule
  sans enregistrer. Références : lignes
  « Mot de passe » et « Adresse mail » de `/espace/compte`.
- **Action protégée par le mot de passe actuel** : réservé aux gestes
  plus lourds qu'un champ de profil (ex. supprimer le portfolio) —
  formulaire classique, champs visibles, bouton, `current_password` en
  règle de validation.
- Pas de bouton « Enregistrer » ni de barre « Modifications non
  enregistrées » pour ces champs. Un bouton ne subsiste que :
  - pour un lot validé d'un coup (ex. « Enregistrer les réglages » du
    modèle) ;
  - pour une action qui exige le mot de passe actuel (suppression du
    portfolio) : ce formulaire garde ses champs visibles et son bouton.

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
`restaurer`, `angle-droite`, `angle-bas`, `deplacer` (croix à quatre flèches : poignée de tout élément déplaçable). Ajouter un picto = ajouter son
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
