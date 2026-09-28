{{--
    Accueil Responsive 2014 (ex-ultrabook_2012_accueil) : une tuile par
    rubrique du portfolio, un grand carre et six petits, le nom de la
    rubrique au survol ; pages d'accueil au-dessus (c) et au-dessous (b).
--}}
@extends('book.responsive.layout')

@section('contenu')
    @if ($bloc = $vue->blocAccueil('c'))
        <div class="contenu-page mb-8 motion-safe:animate-apparition">{!! $bloc !!}</div>
    @endif

    @if ($vue->actif('ptf_vignette_aff'))
        <div class="flex flex-col gap-px">
            @foreach ($vue->rubriquesPortfolio() as $i => $rubrique)
                @continue(! $rubrique['visuels'])
                <a href="/{{ $rubrique['url'] }}" class="group relative grid grid-cols-1 gap-px overflow-hidden motion-safe:animate-apparition sm:grid-cols-[2fr_3fr]"
                   style="animation-delay: {{ min($i, 5) * 90 }}ms">
                    <img src="{{ $vue->carre($rubrique['visuels'][0], 368) }}" alt="{{ $rubrique['nom'] }}" width="368" height="368"
                         loading="{{ $i < 2 ? 'eager' : 'lazy' }}" @if ($i === 0) fetchpriority="high" @endif decoding="async"
                         class="aspect-square h-full w-full bg-book-texte/5 object-cover">
                    <div class="hidden grid-cols-3 gap-px sm:grid">
                        @foreach (array_slice($rubrique['visuels'], 1, 6) as $fichier)
                            <img src="{{ $vue->carre($fichier, 183) }}" alt="" width="183" height="183" loading="lazy" decoding="async"
                                 class="aspect-square h-full w-full bg-book-texte/5 object-cover">
                        @endforeach
                    </div>

                    {{-- Nom de la rubrique : au survol sur ordinateur, toujours visible sur mobile. --}}
                    <span class="absolute inset-x-0 bottom-0 bg-black/55 px-4 py-3 text-[18px] uppercase tracking-[.08em] text-white transition duration-300 sm:inset-0 sm:flex sm:items-center sm:justify-center sm:py-0 sm:text-[28px] sm:opacity-0 sm:group-hover:opacity-100">
                        <span class="transition duration-300 sm:translate-y-3 sm:group-hover:translate-y-0">{{ $rubrique['nom'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif

    @if ($bloc = $vue->blocAccueil('b'))
        <div class="contenu-page mt-8">{!! $bloc !!}</div>
    @endif
@endsection
