@props(['nom', 'valeur' => '', 'libelle' => '', 'vide' => '', 'multiligne' => false, 'prive' => false, 'typo' => 'text-[15px] leading-relaxed'])

{{-- Texte editable sur place, sans champ visible (voir Claude_design.md,
     « Édition sur place »). Enregistre par enregistrerChamp() du composant
     Livewire parent (trait App\Livewire\Concerns\EnregistreChamps).
     wire:ignore : le texte appartient a Alpine pendant l'edition, un rendu
     Livewire ne doit pas l'ecraser.
     Texte et crayon sont en ligne : le crayon suit le dernier caractere, et
     le filet d'edition ne souligne que le texte, pas toute la largeur. --}}
<div x-data="champEditable(@js($nom), @js((string) $valeur), @js((bool) $multiligne))" wire:ignore data-erreur="{{ __('Enregistrement impossible, réessayez.') }}"
     {{ $attributes->merge(['class' => 'relative']) }}>
    @if ($libelle)
        <div class="mb-1 text-[12px] font-semibold uppercase tracking-[.06em] text-ub-texte3">{{ $libelle }}@if ($prive) <span class="text-[10px] text-ub-prive" title="{{ __('Donnée privée') }}">●</span>@endif</div>
    @endif

    <div class="{{ $typo }} text-ub-texte">
        <span x-ref="texte" role="textbox" aria-label="{{ $libelle ?: $vide }}" @if ($multiligne) aria-multiline="true" @endif
              :contenteditable="edition ? 'plaintext-only' : 'false'"
              @click="editer()" @keydown="touche($event)" @blur="valider()" @input="nettoyer()"
              :class="{ 'border-ub-succes': edition, 'border-transparent': ! edition, 'champ-editable-enregistre': enregistrement }"
              data-vide="{{ $vide }}"
              @class([
                  'champ-editable cursor-text border-b pb-0.5 break-words outline-none',
                  'whitespace-pre-wrap' => $multiligne,
              ])>{{ $valeur }}</span><button type="button" x-show="! edition" @mousedown.prevent @click="editer()"
                :title="valide ? @js(__('Enregistré')) : @js(__('Modifier'))"
                class="ml-2.5 inline-flex translate-y-0.5 align-baseline text-ub-texte3 hover:text-ub-texte">
            <svg x-show="! valide" class="h-4 w-4" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true">
                <path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"/>
            </svg>
            <svg x-show="valide" x-cloak class="h-4 w-4 text-ub-succes" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="sr-only" x-text="valide ? @js(__('Enregistré')) : @js(__('Modifier'))"></span>
        </button>
    </div>

    <p x-show="erreur" x-cloak x-text="erreur" class="mt-1 text-[13px] text-ub-danger"></p>
</div>
