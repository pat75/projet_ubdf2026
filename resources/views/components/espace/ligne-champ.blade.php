@props(['libelle', 'aide' => null, 'prive' => false, 'obligatoire' => false])

{{-- Une ligne du formulaire du compte : le libelle a gauche, le champ a
     droite. La cle signale une donnee qui ne parait jamais sur le book,
     le point d'interrogation porte l'explication. --}}
<div class="grid items-center gap-x-4 gap-y-1 border-b border-transparent py-2.5 sm:grid-cols-[200px_1fr]">
    <div class="flex items-center gap-1.5 text-[15px]">
        <span>{{ $libelle }}@if ($obligatoire)<span class="text-ub-texte">*</span>@endif</span>

        @if ($prive)
            <span title="{{ __('Donnée privée : elle n’apparaît jamais sur votre book.') }}" class="text-ub-gris-fonce">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="8" cy="15" r="4"/><path stroke-linecap="round" d="m11 12 8-8M16 7l2 2M19 4l2 2"/>
                </svg>
                <span class="sr-only">{{ __('Donnée privée') }}</span>
            </span>
        @endif

        @if ($aide)
            <span title="{{ $aide }}" class="cursor-help text-ub-gris-fonce">
                <x-espace.icone nom="aide" class="h-4 w-4" />
                <span class="sr-only">{{ $aide }}</span>
            </span>
        @endif
    </div>

    <div class="flex items-center gap-2">{{ $slot }}</div>
</div>
