{{--
    Accueil Grid 2015 (ex-ultrabook_accueil) : une grande tuile carree par
    rubrique du portfolio (carre_335, nom dans un bandeau), puis une petite
    tuile par page ou par rubrique de pages. Apparition en cascade.
--}}
@extends('book.grid2015.layout')

@section('contenu')
    @php $rubriques = array_values(array_filter($vue->rubriquesPortfolio(), fn ($r) => $r['visuels'])); @endphp

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:gap-4 xl:grid-cols-4">
        @if ($vue->actif('accueil_ptf_vignette_aff'))
            @foreach ($rubriques as $i => $rubrique)
                <a href="/{{ $rubrique['url'] }}" class="group relative block aspect-square overflow-hidden bg-book-texte/5 motion-safe:animate-apparition"
                   style="animation-delay: {{ min($i, 8) * 70 }}ms">
                    <img src="{{ $vue->carre($rubrique['visuels'][0], 335) }}" alt="{{ $rubrique['nom'] }}" width="335" height="335"
                         loading="{{ $i < 4 ? 'eager' : 'lazy' }}" @if ($i === 0) fetchpriority="high" @endif decoding="async"
                         class="size-full object-cover transition duration-700 ease-out group-hover:scale-[1.04]">
                    <span class="absolute inset-x-0 bottom-0 flex min-h-[12%] items-center justify-center bg-[#d9d9d9]/90 px-3 py-2 text-center text-[13px] font-semibold uppercase leading-tight text-black transition-colors duration-300 group-hover:bg-white md:text-[15px]">
                        {{ $rubrique['nom'] }}
                    </span>
                </a>
            @endforeach
        @endif

        @foreach ($vue->tuilesPages() as $j => $tuile)
            <a href="/{{ $tuile['url'] }}" class="flex aspect-square items-center justify-center bg-[#d9d9d9] p-4 text-center text-[14px] font-semibold uppercase leading-tight text-black transition-colors duration-300 hover:bg-book-texte hover:text-book-fond motion-safe:animate-apparition sm:aspect-auto sm:min-h-32"
               style="animation-delay: {{ min(count($rubriques) + $j, 10) * 70 }}ms">{{ $tuile['titre'] }}</a>
        @endforeach
    </div>
@endsection
