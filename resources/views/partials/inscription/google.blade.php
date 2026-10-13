{{-- Fin d'une creation de book par Google (GoogleController::inscrire).
     Formulaire classique : les erreurs reviennent en session. --}}
<form class="ui form creer_book_form creer_book_google_form" method="POST" action="{{ route('google.inscription') }}" @submit.prevent="envoyerGoogle($el)">
    @csrf
    <div class="field @error('us_login') error @enderror">
        <label for="google_us_login" class="adresse_page_libelle">{{ __('Adresse de mon book') }}</label>
        <div class="adresse_page">
            <span class="adresse_page_domaine">https://</span>
            <input id="google_us_login" name="us_login" type="text" autocomplete="username" required
                   placeholder="{{ __('identifiant') }}" value="{{ old('us_login') }}">
            <span class="adresse_page_domaine">.{{ $marque->domaineBooks }}</span>
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
    <button class="ui black button creer_book_valider" type="submit">{{ __('Créer mon book') }}</button>
</form>
