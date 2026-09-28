@extends('layouts.portail')

@section('title', $recherche->q !== ''
    ? $recherche->q.' — portfolios de créatifs | Ultra-book'
    : 'Rechercher un portfolio | Ultra-book')
@section('body_class', 'page_recherche')
{{-- Resultats de recherche interne : les moteurs suivent les liens vers
     les books, mais n'indexent pas une page par requete (consigne Google). --}}
@if ($recherche->q !== '' || ! empty($ajax))
    @section('robots', 'noindex, follow')
@endif

@section('content')
    {{-- /search (ajax) : le formulaire remplit #resultats_recherche sans
         recharger ; vide tant qu'aucune recherche n'est lancee. --}}
    @include('partials.bloc-recherche', ['niveauTitre' => empty($ajax) ? 'h2' : 'h1', 'ajax' => $ajax ?? false])

    <div id="resultats_recherche">
        @if (empty($ajax) || $recherche->q !== '')
            @include('front.partials.resultats-recherche')
        @endif
    </div>
@endsection
