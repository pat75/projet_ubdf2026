{{--
    Accueil du modele classique 2010 (ex-ultrabook_accueil) : les pages de
    la premiere rubrique d'accueil, l'une sous l'autre, sous le bandeau.
--}}
@extends('book.responsive.layout')

@section('contenu')
    @foreach ($vue->pagesAccueil() as $i => $page)
        <div class="contenu-page mb-10 motion-safe:animate-apparition" style="animation-delay: {{ min($i, 4) * 100 }}ms">{!! $page !!}</div>
    @endforeach
@endsection
