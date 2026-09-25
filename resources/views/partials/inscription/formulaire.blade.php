{{-- Formulaire « Creer un book », en une seule etape comme /register sur
     Tesli. A placer dans un x-data="inscription" (resources/js/portail/inscription.js).
     Le metier n'est plus demande ici : il se choisit ensuite dans l'espace. --}}
<!-- envoi en cours -->
<div class="ui inverted dimmer" id="inscription_segment_loader" :class="{ active: chargement }">
    <div class="ui loader"></div>
</div>

<!-- erreurs renvoyees par le serveur -->
<div id="display_error_segment" x-show="vue === 'erreur'" x-cloak x-transition.opacity>
    <div class="ui error message" style="display: block;">
        <div class="header">{{ __('Erreurs lors de l’enregistrement.') }}</div>
        <template x-for="message in messagesErreur"><p x-text="message"></p></template>
    </div>
    <a href="#" class="creer_book_retour" @click.prevent="recommencer()">{{ __('Corriger') }}</a>
</div>

<!-- formulaire -->
<form class="ui form creer_book_form" id="inscription_classic" novalidate
      x-show="vue === 'formulaire'" @submit.prevent="envoyer($el)">
    @csrf
    <input type="hidden" name="action" value="form">
    <input type="hidden" name="form_id" value="form_adduser">
    <input type="hidden" name="form_action" value="form_valide">

    <div class="field" :class="{ error: erreurs.us_nom }">
        <input id="us_nom" type="text" name="us_nom" autocomplete="name" placeholder="{{ __('Nom Prénom') }}" aria-label="{{ __('Nom Prénom') }}" @input="erreurs.us_nom = ''">
        <div class="creer_book_erreur" x-show="erreurs.us_nom" x-text="erreurs.us_nom"></div>
    </div>

    <div class="field" :class="{ error: erreurs.us_mail }">
        <input id="mail" type="email" name="us_mail" autocomplete="email" placeholder="{{ __('Mail') }}" aria-label="{{ __('Mail') }}" @input="erreurs.us_mail = ''">
        <div class="creer_book_erreur" x-show="erreurs.us_mail" x-text="erreurs.us_mail"></div>
    </div>

    <div class="field" :class="{ error: erreurs.us_login }">
        <div class="creer_book_adresse">
            <span>https://</span>
            <input name="us_login" type="text" id="us_login" autocomplete="off" placeholder="{{ __('adresse-de-votre-book') }}" aria-label="{{ __('Adresse de votre book') }}"
                   x-model="login" @input.debounce.400ms="erreurs.us_login = ''; verifierLogin()">
            <span>.{{ config('ubdf.book_domain') }}</span>
        </div>
        <div class="creer_book_erreur" x-show="erreurs.us_login" x-text="erreurs.us_login"></div>
        <div class="creer_book_aide" x-show="! erreurs.us_login && loginLibre === true" x-cloak>{{ __('Adresse disponible') }}</div>
    </div>

    <div class="field" :class="{ error: erreurs.us_pass }" x-data="{ visible: false }">
        <div class="ui icon input">
            <input id="mdp-desac" :type="visible ? 'text' : 'password'" type="password" name="us_pass" autocomplete="new-password" placeholder="{{ __('Mot de passe') }}" aria-label="{{ __('Mot de passe') }}"
                   @input="erreurs.us_pass = ''">
            <i class="link icon" :class="visible ? 'eye slash' : 'eye'" @click="visible = ! visible"></i>
        </div>
        <div class="creer_book_erreur" x-show="erreurs.us_pass" x-text="erreurs.us_pass"></div>
    </div>

    {{-- Captcha local (App\Services\Captcha\Captcha). --}}
    <div class="field" :class="{ error: erreurs.captcha }">
        <div class="creer_book_captcha">
            <img :src="captcha || null" width="158" height="53" alt="{{ __('Code à recopier') }}" title="{{ __('Cliquez pour changer') }}"
                 @click="rechargerCaptcha()">
            <input id="captcha" type="text" name="captcha" maxlength="4" autocomplete="off" placeholder="{{ __('Recopiez le code') }}" aria-label="{{ __('Recopiez le code') }}" @input="erreurs.captcha = ''">
            <i class="sync alternate link icon" title="{{ __('Nouvelle image') }}" @click="rechargerCaptcha()"></i>
        </div>
        <div class="creer_book_erreur" x-show="erreurs.captcha" x-text="erreurs.captcha"></div>
    </div>

    <div class="field" :class="{ error: erreurs.us_licence }">
        <label class="creer_book_licence">
            <input name="us_licence" type="checkbox" @change="erreurs.us_licence = ''">
            <span>{{ __('J’accepte les') }}
            <a href="{{ route('cms.doc', app()->getLocale() === 'fr' ? 'conditions-dutilisations' : 'conditions-of-use') }}" target="_blank">{{ __('conditions d’utilisation') }} {{ __('de la plateforme :marque', ['marque' => $marque->nom]) }}</a></span>
        </label>
        <div class="creer_book_erreur" x-show="erreurs.us_licence" x-text="erreurs.us_licence"></div>
    </div>

    <button class="ui black button creer_book_valider valider_submit" type="submit">{{ __('Créer mon book') }}</button>
</form>
