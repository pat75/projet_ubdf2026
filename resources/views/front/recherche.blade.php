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
        @elseif (! empty($populaires))
            {{-- Accueil du moteur : mots-cles les plus recherches sur 90 jours. --}}
            {{-- Meme ossature que le bloc de recherche : le titre s'aligne a
                 gauche sur « Trouvez les meilleurs portfolios… ». Etiquettes
                 aux couleurs de celles de l'accueil : la classe
                 bloc_last_recherche leur applique les memes fonds coul_<domaine>. --}}
            <div class="ui container bloc_last_recherche recherches_populaires">
                <div class="ui grid">
                    <div class="row one column">
                        <div class="column">
                            <div class="ui segment basic left aligned">
                                <h2>{{ __('Les recherches du moment') }}</h2>
                                @foreach ($populaires as $populaire)
                                    <a class="ui large basic label cursor_effect {{ $populaire['domaine'] ? 'coul_'.$populaire['domaine'] : '' }}"
                                       href="{{ lien('search', ['q' => $populaire['q'], 'type_recherche' => 'mcles']) }}">
                                        <i class="chevron right icon"></i><strong>{{ $populaire['mot'] }}</strong>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
