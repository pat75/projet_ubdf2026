@extends('layouts.portail')

@section('title', __('Désabonnement').' | '.$marque->nom)

@section('content')
    <div class="ui container bloc_portfolios">
        <h1>{{ __('C’est fait') }}</h1>

        <p>{{ __('Vous ne recevrez plus nos conseils pour la réalisation de votre book.') }}</p>

        <p><a href="{{ lien('espace.diffusion') }}">{{ __('Revenir sur ce choix depuis votre espace') }}</a></p>
    </div>
@endsection
