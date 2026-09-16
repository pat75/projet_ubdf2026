@extends('layouts.portail')

@section('title', __('Nouveau mot de passe').' | '.$marque->nom)
@section('body_class', 'page_motdepasse')

@section('content')
    <div class="ui container bloc_portfolios">
        <h1>{{ __('Choisir un nouveau mot de passe') }}</h1>

        <form method="post"
              action="{{ lien('mot-de-passe.enregistrer', ['demande' => $demande, 'jeton' => $jeton]) }}"
              class="ui form">
            @csrf

            <div class="field @error('password') error @enderror">
                <label for="password">{{ __('Nouveau mot de passe') }}</label>
                <input id="password" type="password" name="password"
                       autocomplete="new-password" required minlength="8">
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('Confirmer le mot de passe') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       autocomplete="new-password" required minlength="8">
            </div>

            @error('password')
                <div class="ui negative message">{{ $message }}</div>
            @enderror

            <button type="submit" class="ui teal button">{{ __('Enregistrer') }}</button>
        </form>
    </div>
@endsection
