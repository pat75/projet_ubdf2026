{{-- Page d'attente : l'espace creatif est construit en phase 5. --}}
@extends('layouts.portail')

@section('title', __('Mon espace').' | '.$marque->nom)
@section('body_class', 'page_espace')

@section('content')
    <div class="ui container bloc_portfolios">
        @if (session('statut'))
            <div class="ui positive message">{{ session('statut') }}</div>
        @endif

        <h1>{{ __('Bonjour :prenom', ['prenom' => $creatif->firstname ?: $creatif->login]) }}</h1>

        <p>{{ __('Votre identifiant est :login.', ['login' => $creatif->login]) }}</p>

        <p>
            <a href="{{ $creatif->bookUrl() }}">{{ $creatif->bookUrl() }}</a>
        </p>

        <form method="post" action="{{ route('deconnexion') }}">
            @csrf
            <button type="submit" class="ui basic button">{{ __('Se déconnecter') }}</button>
        </form>
    </div>
@endsection
