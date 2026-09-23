@props([
    'illustration',
    'titre',
    'suite' => '',
    'alt' => '',
    'lien' => null,
])

{{-- L'en-tete d'ecran de l'espace : une illustration a gauche, un titre en
     deux temps a droite — le premier mot en gras, la suite en maigre, les
     deux dans le meme turquoise (#17b7bf, releve sur l'original). --}}
@php($classes = 'grid items-center gap-6 text-ub-turquoise sm:grid-cols-[188px_1fr]')

@if ($lien)
    <a href="{{ $lien }}" {{ $attributes->merge(['class' => $classes]) }}>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
@endif

    <img src="{{ $illustration }}" alt="{{ $alt }}" class="mx-auto max-h-[260px] w-[188px]">

    <div>
        <h2 class="font-titre text-[35px] font-light leading-tight">
            <strong class="font-bold">{{ $titre }}</strong>
            @if ($suite)
                <span>{{ $suite }}</span>
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
