@extends('layouts.portail')

@section('content')
    {{-- Portfolios : le premier ecran est rendu cote serveur, la suite est
         chargee par defilement infini via /accueil__<page>__<selection>__<type>. --}}
    <div class="ui container bloc_portfolios">
        <div class="visibility infinite">
            <div class="ui five doubling cards" id="accueil_portfolio">
                @foreach ($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
                <div id="position_card_last"></div>
            </div>

            <div class="ui basic segment">
                <div class="ui grid result_message"></div>
            </div>

            <div class="ui horizontal icon divider result_end">
                <i class="circular large angle up icon"></i>
            </div>

            <div class="ui large centered inline text loader">
                {{ __('Chargement...') }}
            </div>
        </div>
    </div>
@endsection
