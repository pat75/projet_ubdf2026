@props([
    'illustration',
    'titre',
    'suite' => '',
    'alt' => '',
    'lien' => null,
    'suiteTurquoise' => false,
])

{{-- L'en-tete d'ecran de l'espace : une illustration a gauche, un titre en
     deux temps a droite — le premier mot en gras turquoise, la suite en
     maigre. Repris de `.firstaddimg` : h2 de 35 px en graisse 300,
     turquoise #17b7bf. --}}
@php($classes = 'grid items-center gap-6 sm:grid-cols-[188px_1fr]')

@if ($lien)
    <a href="{{ $lien }}" {{ $attributes->merge(['class' => $classes]) }}>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
@endif

    <img src="{{ $illustration }}" alt="{{ $alt }}" class="mx-auto max-h-[260px] w-[188px]">

    <div>
        <h2 class="font-titre text-[35px] font-light leading-tight">
            <strong class="font-bold text-[#17b7bf]">{{ $titre }}</strong>
            @if ($suite)
                <span @class(['text-[#17b7bf]' => $suiteTurquoise, 'text-[#55636a]' => ! $suiteTurquoise])>{{ $suite }}</span>
            @endif
        </h2>

        @if (trim($slot) !== '')
            <div class="mt-4 flex items-center gap-3 text-[22px] font-light text-[#55636a]">
                {{ $slot }}
            </div>
        @endif
    </div>

@if ($lien)
    </a>
@else
    </div>
@endif
