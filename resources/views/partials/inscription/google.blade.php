{{-- Fin d'une creation de book par Google (GoogleController::inscrire).
     Formulaire classique : les erreurs reviennent en session. --}}
@php
    $metiers = [
        'illustrateur' => __('Illustration'),
        'illustrateur-jeunesse' => __('Illustration jeunesse'),
        'graphiste' => __('Graphisme'),
        'directeur-artistique' => __('Direction artistique'),
        'digital' => __('Digital & développement'),
        'plasticien' => __('Art'),
        'photographe' => __('Photographie'),
        'design' => __('Design objet'),
        'architecte' => __('Architecture'),
    ];
@endphp
<form class="ui form creer_book_google_form" method="POST" action="{{ route('google.inscription') }}">
    @csrf
    <div class="field @error('us_login') error @enderror">
        <div class="ui right labeled small input us_login_">
            <div class="ui label us_login_affhttp"> https://</div>
            <input name="us_login" type="text" autocomplete="username" required
                   placeholder="{{ __('Identifiant') }}" value="{{ old('us_login') }}">
            <div class="ui label us_login_affub">.{{ config('ubdf.book_domain') }}</div>
        </div>
        @error('us_login') <div class="ui basic red pointing prompt label show">{{ $message }}</div> @enderror
    </div>
    <div class="field @error('us_type') error @enderror">
        <select name="us_type" class="ui dropdown">
            <option value="">{{ __('Métier ou domaine') }}</option>
            @foreach ($metiers as $valeur => $libelle)
                <option value="{{ $valeur }}" @selected(old('us_type') === $valeur)>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>
    <div class="field @error('us_licence') error @enderror">
        <label class="creer_book_licence">
            <input type="checkbox" name="us_licence" value="1" @checked(old('us_licence'))>
            {{ __('J’accepte les') }} <a href="/doc/conditions-dutilisations" target="_blank">{{ __('conditions d’utilisation') }}</a>
            {{ __('de la plateforme :marque', ['marque' => $marque->nom]) }}
        </label>
        @error('us_licence') <div class="ui basic red pointing prompt label show">{{ $message }}</div> @enderror
    </div>
    <p class="creer_book_note">{{ __('Un mot de passe est créé pour vous : vous pourrez en choisir un à tout moment avec « Mot de passe oublié », pour vous connecter sans Google.') }}</p>
    <button class="ui black button" type="submit">{{ __('Créer mon book') }}</button>
</form>
<form method="POST" action="{{ route('google.abandon') }}" class="creer_book_abandon">
    @csrf
    <button type="submit" class="lien">{{ __('Utiliser un autre moyen') }}</button>
</form>
