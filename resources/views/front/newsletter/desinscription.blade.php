@extends('layouts.portail')

@section('title', __('Désinscription').' | '.$marque->nom)

@section('content')
    <div class="ui container bloc_portfolios">
        @if ($etat === 'desinscrit')
            <h1>{{ __('C’est fait') }}</h1>

            <p>{{ __('Vous ne recevrez plus la lettre d’information de :marque.', ['marque' => $marque->nom]) }}</p>

            <p>{{ __('Vous continuerez à recevoir les messages qui vous concernent directement : demandes de contact et échéance de formule.') }}</p>

            <p>
                <a href="{{ URL::signedRoute('newsletter.reabonnement', ['adresse' => $jeton]) }}">{{ __('C’était une erreur, me réabonner') }}</a>
            </p>
        @elseif ($etat === 'reabonne')
            <h1>{{ __('Vous êtes réabonné') }}</h1>

            <p>{{ __('Vous recevrez de nouveau la lettre d’information de :marque.', ['marque' => $marque->nom]) }}</p>
        @else
            <h1>{{ __('Adresse inconnue') }}</h1>

            <p>{{ __('Cette adresse ne figure dans aucune de nos listes : il n’y a rien à désinscrire.') }}</p>
        @endif

        <p><a href="{{ lien('home') }}">{{ __('Retour à l’accueil') }}</a></p>
    </div>
@endsection
