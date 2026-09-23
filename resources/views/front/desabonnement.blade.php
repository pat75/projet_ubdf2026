@extends('layouts.portail')

@section('title', __('Désabonnement').' | '.$marque->nom)

@section('content')
    <div class="ui container bloc_portfolios">
        <h1>{{ __('C’est fait') }}</h1>

        <p>{{ __('Vous ne recevrez plus les messages de :marque.', ['marque' => $marque->nom]) }}</p>

        <p>{{ __('Vous continuerez à recevoir les messages qui vous concernent directement : demandes de contact et échéance de formule.') }}</p>

        <p><a href="{{ lien('espace.diffusion') }}">{{ __('Revenir sur ce choix depuis votre espace') }}</a></p>
    </div>
@endsection
