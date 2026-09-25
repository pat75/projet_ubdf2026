@props(['action', 'question', 'sens' => 'haut', 'bloque' => null])

{{-- Confirmation de suppression dans une bulle rouge pointant sur le
     declencheur (slot). sens="haut" : bulle centree au-dessus ;
     sens="bas" : sous le declencheur, alignee a droite (en haut d'une
     carte overflow-hidden, ou une bulle au-dessus serait coupee).
     bloque="…" : suppression impossible, la bulle explique pourquoi. --}}
<div class="relative" x-data="{ confirmer: false }" x-on:click.outside="confirmer = false"
     x-on:keydown.escape.window="confirmer = false">
    <div x-on:click="confirmer = ! confirmer">{{ $slot }}</div>

    <div x-show="confirmer" x-cloak x-transition.opacity
         @class([
             'absolute z-[1000] w-max max-w-72 bg-ub-danger px-4 py-3 text-left text-[13px] font-normal text-white shadow-lg',
             'bottom-full left-1/2 mb-3 -translate-x-1/2' => $sens === 'haut',
             'top-full right-0 mt-3' => $sens === 'bas',
         ])>
        @if ($bloque)
            <p>{{ $bloque }}</p>
            <div class="mt-2.5">
                <button type="button" class="border border-white bg-white px-3 py-1 font-semibold text-ub-danger hover:bg-white/90"
                        x-on:click="confirmer = false">{{ __('Compris') }}</button>
            </div>
        @else
            <p>{{ $question }}</p>
            <div class="mt-2.5 flex gap-2">
                <button type="button" class="border border-white bg-white px-3 py-1 font-semibold text-ub-danger hover:bg-white/90"
                        wire:click="{{ $action }}">{{ __('Oui, supprimer') }}</button>
                <button type="button" class="border border-white/70 px-3 py-1 text-white hover:bg-white/10"
                        x-on:click="confirmer = false">{{ __('Annuler') }}</button>
            </div>
        @endif
        <span @class([
            'absolute h-2.5 w-2.5 rotate-45 bg-ub-danger',
            'left-1/2 top-full -translate-x-1/2 -translate-y-1/2' => $sens === 'haut',
            'right-2.5 bottom-full translate-y-1/2' => $sens === 'bas',
        ])></span>
    </div>
</div>
