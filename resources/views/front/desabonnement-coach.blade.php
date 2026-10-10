@extends('layouts.portail')

@section('title', __('Désabonnement').' | '.$marque->nom)

@section('content')
    <div class="ui container bloc_portfolios" style="padding: 80px 0;">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 1em;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="56" height="56" aria-hidden="true" style="flex-shrink: 0;">
            <circle cx="12" cy="12" r="10"/>
            <path d="M8 12.5l3 3 5-6"/>
        </svg>
        <h1 style="margin: 0;">{{ __('C’est fait') }}</h1>
        </div>

        <p>{{ __('Vous ne recevrez plus nos conseils pour la réalisation de votre book.') }}</p>

        <p><a href="{{ lien('espace.diffusion') }}">{{ __('Revenir sur ce choix depuis votre espace') }}</a></p>
    </div>
@endsection
