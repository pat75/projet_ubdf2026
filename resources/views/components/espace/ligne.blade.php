@props(['libelle', 'aide' => null, 'prive' => false, 'dernier' => false])

{{-- Une ligne de fiche : libelle a gauche, valeur a droite. La pastille
     rose marque une donnee qui ne parait jamais sur le book ; le rond
     gris porte l'explication en infobulle. --}}
<div @class([
    'grid grid-cols-[minmax(140px,200px)_minmax(0,1fr)] items-center gap-4 py-3.5',
    'border-b border-ub-filet-ligne' => ! $dernier,
])>
    <div class="flex items-center gap-1.5 text-[15px] text-ub-texte2">
        <span>{{ $libelle }}</span>

        @if ($prive)
            <span class="text-[10px] text-ub-prive" title="{{ __('Donnée privée : elle n’apparaît jamais sur votre book.') }}">●<span class="sr-only">{{ __('Donnée privée') }}</span></span>
        @endif

        @if ($aide)
            <span class="grid h-4 w-4 cursor-help place-items-center rounded-full bg-[#ddd] text-[11px] font-bold text-ub-texte2" title="{{ $aide }}">?<span class="sr-only">{{ $aide }}</span></span>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3 text-[16px]">{{ $slot }}</div>
</div>
