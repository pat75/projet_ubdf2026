{{-- Fin d'une creation de book par Google (GoogleController::inscrire).
     Formulaire classique : les erreurs reviennent en session. --}}
<form class="ui form creer_book_form creer_book_google_form" method="POST" action="{{ route('google.inscription') }}">
    @csrf
    <div class="field @error('us_login') error @enderror">
        <div class="ui right labeled small input us_login_">
            <div class="ui label us_login_affhttp"> https://</div>
            <input name="us_login" type="text" autocomplete="username" required
                   placeholder="{{ __('Identifiant') }}" value="{{ old('us_login') }}">
            <div class="ui label us_login_affub">.{{ $marque->domaineBooks }}</div>
        </div>
        @error('us_login') <div class="ui basic red pointing prompt label show">{{ $message }}</div> @enderror
    </div>
    <div class="field @error('us_licence') error @enderror">
        <label class="creer_book_licence">
            <input type="checkbox" name="us_licence" value="1" @checked(old('us_licence'))>
            <span>{{ __('J’accepte les') }}
            <a href="{{ route('cms.doc', app()->getLocale() === 'fr' ? 'conditions-dutilisations' : 'conditions-of-use') }}" target="_blank">{{ __('conditions d’utilisation') }} {{ __('de la plateforme :marque', ['marque' => $marque->nom]) }}</a></span>
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
