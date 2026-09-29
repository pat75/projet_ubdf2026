@extends('layouts.portail')

@section('title', __('Page introuvable').' | '.$marque->nom)

@section('content')
    <div class="ui container bloc_portfolios">
        <div class="ui basic segment center aligned recherche_vide" style="text-align:center">
            <img src="/img_front/recherche-vide.png" alt="" width="343" height="400" style="display:block;margin:0 auto 1em;max-width:60%;height:auto">
            <h1 style="text-align:center">{{ __('Page introuvable') }}</h1>
            <p style="text-align:center">{{ __('La page demandée n’existe pas ou a été déplacée.') }}</p>
            <p style="text-align:center;margin-top:1.5em">
                <a href="{{ url('/') }}" class="ui black button" style="border-radius:0">{{ __('Revenir à l’accueil') }}</a>
            </p>
        </div>
    </div>
@endsection
