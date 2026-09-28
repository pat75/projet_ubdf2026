@extends('layouts.portail')

@section('title', $recherche->q !== ''
    ? $recherche->q.' — portfolios de créatifs | Ultra-book'
    : 'Rechercher un portfolio | Ultra-book')
@section('body_class', 'page_recherche')
{{-- Resultats de recherche interne : les moteurs suivent les liens vers
     les books, mais n'indexent pas une page par requete (consigne Google). --}}
@if ($recherche->q !== '')
    @section('robots', 'noindex, follow')
@endif

@section('content')
    @include('partials.bloc-recherche', ['niveauTitre' => 'h2'])

    <div class="ui container bloc_portfolios">

        <div class="bloc_titre">
            <h1>
                @if ($recherche->q === '')
                    {{ __('Rechercher un portfolio') }}
                @else
                    {{ $recherche->q }}
                @endif
            </h1>

            @if ($recherche->exploitable())
                <div class="sub_title">
                    <strong>{{ number_format($total, 0, ',', ' ') }}</strong>
                    {{ trans_choice('portfolio|portfolios', $total) }}
                    {{ $recherche->mode === 'pseudo' ? __('au nom recherché') : __('sur ces mots-clés') }}
                </div>
            @endif
        </div>

        @if (! $recherche->exploitable())
            {{-- Regle reprise du legacy : en deca de trois caracteres, la
                 requete balaierait la table sans rien discriminer. --}}
            <div class="ui basic segment center aligned recherche_mini">
                {{ __('Indiquez au moins trois caractères.') }}
            </div>
        @elseif ($books->isEmpty())
            <div class="ui basic segment center aligned recherche_vide">
                {{ __('Aucun portfolio ne correspond à cette recherche.') }}
            </div>
        @endif

        <div class="visibility infinite" x-data="defilementInfini">
            <div class="ui five doubling cards" id="accueil_portfolio">
                @foreach ($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
                <div id="position_card_last" x-ref="fin"></div>
            </div>

            <div class="ui basic segment">
                <div class="ui grid result_message"></div>
            </div>

            <div class="ui horizontal icon divider result_end" :class="{ show: termine && page > 0 }">
                <i class="circular large angle up icon"></i>
            </div>

            <div class="ui large centered inline text loader" :class="{ active: enCours }">{{ __('Chargement...') }}</div>
        </div>
    </div>
@endsection
