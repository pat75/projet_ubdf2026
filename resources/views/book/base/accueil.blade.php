{{--
    Accueil des modeles 2012 et 2012-slide (ex-ultrabook_2012_accueil) : une
    tuile par rubrique du portfolio, son nom au survol ; pages d'accueil
    au-dessus (c) et au-dessous (b).
--}}
@extends('book.responsive.layout')

@section('contenu')
    @php [$haut, $bas] = $vue->blocsAccueil(); @endphp

    @if ($bloc = $vue->blocAccueil($haut))
        <div class="contenu-page mb-8 motion-safe:animate-apparition">{!! $bloc !!}</div>
    @endif

    @if ($tuiles = $vue->tuilesAccueil())
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($tuiles as $i => $tuile)
                <a href="/{{ $tuile['url'] }}" class="group relative block aspect-[4/3] overflow-hidden bg-book-texte/5 motion-safe:animate-apparition"
                   style="animation-delay: {{ min($i, 6) * 80 }}ms">
                    <img src="{{ $tuile['visuel']['moyen'] }}" srcset="{{ $tuile['visuel']['petit'] }} 320w, {{ $tuile['visuel']['moyen'] }} 550w"
                         sizes="(min-width: 640px) 40vw, 100vw" alt="{{ $tuile['nom'] }}"
                         loading="{{ $i < 2 ? 'eager' : 'lazy' }}" @if ($i === 0) fetchpriority="high" @endif decoding="async"
                         class="size-full object-cover transition duration-700 ease-out group-hover:scale-[1.04]">
                    <span class="ub_font_ptf_titre absolute inset-x-0 bottom-0 bg-black/60 px-4 py-3 text-white! transition duration-300 sm:translate-y-full sm:group-hover:translate-y-0">{{ $tuile['nom'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    @if ($bloc = $vue->blocAccueil($bas))
        <div class="contenu-page mt-8">{!! $bloc !!}</div>
    @endif
@endsection
