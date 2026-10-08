@extends('layouts.portail')

@section('title', __('Page introuvable').' | '.$marque->nom)

{{-- Page 404 : visuel, titre, phrase et bouton centres sur la page. Styles
     locaux et prefixes : les feuilles legacy (core.css, Semantic UI) posent
     des largeurs et alignements sur h1/p qui decentraient le texte. --}}
@section('content')
    <style>
        .page_404 { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 48px 20px 72px; }
        .page_404 img { display: block; width: 343px; max-width: 60%; height: auto; margin: 0 0 28px; }
        .page_404 h1 { margin: 0 0 12px; font-family: 'Lato', sans-serif; font-weight: 300; font-size: 34px; line-height: 1.2; }
        .page_404 p { margin: 0 auto; max-width: 520px; font-size: 17px; line-height: 1.5; color: #555; }
        .page_404 .page_404_retour { margin-top: 28px; border-radius: 0; }
        @@media (max-width: 767px) {
            .page_404 { padding: 32px 20px 56px; }
            .page_404 h1 { font-size: 28px; }
            .page_404 p { font-size: 15px; }
        }
    </style>

    <div class="ui container bloc_portfolios">
        <div class="page_404">
            <img src="/img_front/recherche-vide.webp" alt="" width="343" height="400">
            <h1>{{ __('Page introuvable') }}</h1>
            <p>{{ __('La page demandée n’existe pas ou a été déplacée.') }}</p>
            <a href="{{ url('/') }}" class="ui black button page_404_retour">{{ __('Revenir à l’accueil') }}</a>
        </div>
    </div>
@endsection
