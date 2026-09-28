{{--
    Accueil Classique 2015 (ex-ultrabook_accueil) : grand visuel menant au
    portfolio, texte libre dessous ; pages d'accueil au-dessus (c) et au-dessous (b).
--}}
@extends('book.responsive.layout')

@section('contenu')
    @if ($bloc = $vue->blocAccueil('c'))
        <div class="contenu-page mb-8 motion-safe:animate-apparition">{!! $bloc !!}</div>
    @endif

    @if ($visuel = $vue->visuelAccueil())
        <a href="/portfolio" class="group block overflow-hidden motion-safe:animate-apparition">
            <img src="{{ $visuel }}" alt="{{ $vue->nomCreateur() }}" width="1180" height="600" fetchpriority="high" decoding="async"
                 class="mx-auto h-auto w-full max-w-[1180px] transition duration-700 ease-out group-hover:scale-[1.01]">
        </a>
    @endif

    @if ($vue->texteAccueil() !== '')
        <div class="texte-libre mx-auto mt-8 max-w-3xl text-center leading-snug motion-safe:animate-apparition motion-safe:[animation-delay:150ms]">{!! $vue->texteAccueil() !!}</div>
    @endif

    @if ($bloc = $vue->blocAccueil('b'))
        <div class="contenu-page mt-8">{!! $bloc !!}</div>
    @endif
@endsection
