@extends('layouts.portail')

@section('title', App\Support\Metier::titreBloc($categorie).' | Ultra-book')
@section('body_class', 'page_metier')

@section('content')
    <div class="ui container bloc_portfolios">

        <div class="bloc_titre">
            <h1 class="metier_group coultxt_{{ $categorie }}">{{ App\Support\Metier::titreBloc($categorie) }}</h1>
            <div class="sub_title">
                <strong>{{ number_format($total, 0, ',', ' ') }}</strong>
                {{ App\Support\Metier::pluriel($categorie) }}
            </div>
        </div>

        {{-- Premier ecran rendu cote serveur ; la suite arrive par
             defilement infini, chaque carte apparaissant en fondu. --}}
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
