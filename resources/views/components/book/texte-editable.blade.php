@props(['cle', 'tag' => 'div', 'edition' => false])

{{-- Texte du book modifiable sur place en mode edition : le pendant de
     x-espace.champ-editable (crayon juste apres le texte, filet sous le
     texte seul pendant la saisie, vague verte puis coche 4 s). Alpine
     texteBook (resources/js/book/edition.js). Couleurs en currentColor :
     le texte garde celles du theme du book. Hors edition : la balise seule. --}}
@if ($edition)
    <span x-data="texteBook(@js($cle))" class="inline-flex max-w-full items-baseline">
        <{{ $tag }} x-ref="texte" data-editable @click="clic($event)" @keydown="touche($event)" @blur="valider()"
            :contenteditable="edition ? 'plaintext-only' : 'false'"
            :class="{ 'border-current/40': edition, 'border-transparent': ! edition, 'texte-book-enregistre': enregistrement }"
            {{ $attributes->merge(['class' => 'break-words border-b pb-0.5 outline-none']) }}>{{ $slot }}</{{ $tag }}><button type="button"
            x-show="$store.edition.actif && ! edition" @mousedown.prevent @click.prevent.stop="editer()"
            :title="valide ? @js(__('Enregistré')) : @js(__('Modifier'))"
            class="ml-2.5 inline-flex shrink-0 self-center text-book-texte2 opacity-50 hover:opacity-100">
            <svg x-show="! valide" class="h-4 w-4" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true">
                <path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"/>
            </svg>
            <svg x-show="valide" x-cloak class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="sr-only" x-text="valide ? @js(__('Enregistré')) : @js(__('Modifier'))"></span>
        </button>
    </span>
@else
    <{{ $tag }} {{ $attributes }}>{{ $slot }}</{{ $tag }}>
@endif
