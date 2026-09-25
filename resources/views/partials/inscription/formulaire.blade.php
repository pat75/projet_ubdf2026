{{-- Formulaire « Creer un book » : fenetre du portail et page /creer-un-book.
     A placer dans un x-data="inscription" (resources/js/portail/inscription.js). --}}
<!-- envoi en cours -->
<div class="ui inverted dimmer" id="inscription_segment_loader" :class="{ active: chargement }">
    <div class="ui loader"></div>
</div>

<!-- erreurs renvoyees par le serveur -->
<div id="display_error_segment" x-show="vue === 'erreur'" x-cloak x-transition.opacity>
    <div class="ui error message" style="display: block;">
        <div class="header"><i class="cogs icon"></i> {{ __('Erreurs lors de l’enregistrement.') }}</div>
        <template x-for="message in messagesErreur"><p x-text="message"></p></template>
    </div>
    <button type="button" class="ui button icon return_first" @click="recommencer()"><i class="angle left icon"></i></button>
</div>

<!-- formulaire -->
<div id="inscription_segment" x-show="vue === 'formulaire'">
    <div id="inscription_segment_social_connect" x-show="volet === 1">
        <h4 style="margin-top:50px;text-align:left;color:#5f5f5f;margin-bottom: 8px;margin-left: 6px;">
            {{ __('Inscrivez-vous gratuitement') }}
        </h4>
    </div>

    <div class="ui left aligned basic small segment">
        <form class="ui form" id="inscription_classic" novalidate
              @submit.prevent="volet === 1 ? suivant($el) : envoyer($el)">
            @csrf
            <input type="hidden" name="action" value="form">
            <input type="hidden" name="form_id" value="form_adduser">
            <input type="hidden" name="form_action" value="form_valide">

            <!-- volet 1 : identifiant et metier -->
            <div class="volet_1" x-show="volet === 1" x-transition.opacity.duration.400ms>
                <div class="grouped fields">
                    <div class="ui field" :class="{ error: erreurs.us_login }">
                        <div class="ui right labeled small input us_login_">
                            <div class="ui label us_login_affhttp"> https://</div>
                            <input name="us_login" type="text" autocomplete="username"
                                   placeholder="{{ __('Identifiant') }}" id="us_login"
                                   x-model="login" @input.debounce.400ms="erreurs.us_login = ''; verifierLogin()">
                            <div class="ui label us_login_affub">.{{ config('ubdf.book_domain') }}</div>
                        </div>
                    </div>
                    <div class="ui basic red pointing prompt label" :class="{ show: erreurs.us_login }" x-text="erreurs.us_login"></div>
                </div>
                <div class="grouped fields">
                    <div class="field" :class="{ error: erreurs.us_type }">
                        <div class="ui selection dropdown dropdown_nav_metiers_"
                             x-data="listeDeroulante" x-bind="racine" @choix="erreurs.us_type = ''">
                            <input type="hidden" name="us_type">
                            <i class="dropdown icon"></i>
                            <div class="default text">{{ __('Métier ou domaine') }}</div>
                            <div class="menu dropdown_nav_metiers">
                                <div class="item" data-value="illustrateur">
                                    <div class="nuancier coul_illustrateur"></div>
                                    {{ __('Illustration') }}
                                </div>
                                <div class="item" data-value="illustrateur-jeunesse">
                                    <div class="nuancier coul_illustrateur_jeunesse"></div>
                                    {{ __('Illustration jeunesse') }}
                                </div>
                                <div class="item" data-value="graphiste">
                                    <div class="nuancier coul_graphiste"></div>
                                    {{ __('Graphisme') }}
                                </div>
                                <div class="item" data-value="directeur-artistique">
                                    <div class="nuancier coul_directeur_artistique"></div>
                                    {{ __('Direction artistique') }}
                                </div>
                                <div class="item" data-value="digital">
                                    <div class="nuancier coul_digital"></div>
                                    {{ __('Digital & développement') }}
                                </div>
                                <div class="item" data-value="plasticien">
                                    <div class="nuancier coul_plasticien"></div>
                                    {{ __('Art') }}
                                </div>
                                <div class="item" data-value="photographe">
                                    <div class="nuancier coul_photographe"></div>
                                    {{ __('Photographie') }}
                                </div>
                                <div class="item" data-value="design">
                                    <div class="nuancier coul_design"></div>
                                    {{ __('Design objet') }}
                                </div>
                                <div class="item" data-value="architecte">
                                    <div class="nuancier coul_architecte"></div>
                                    {{ __('Architecture') }}
                                </div>
                            </div>
                        </div>
                        <div class="ui basic red pointing prompt label" :class="{ show: erreurs.us_type }" x-text="erreurs.us_type"></div>
                    </div>
                </div>
                <button class="ui icon teal button valider_volet_1" type="submit"><i class="angle right icon"></i></button>
            </div>

            <!-- volet 2 : compte -->
            <div class="volet_2" x-show="volet === 2" x-cloak x-transition.opacity.duration.400ms>
                <div class="ui Large label id_connection" title="{{ __('Utilisez cet identifiant pour vous connecter et administrer votre book') }}">
                    <i class="user icon"></i>
                    <span id="show_id_connection" x-text="login.trim().toLowerCase()"></span>
                </div>
                <div class="grouped fields">
                    <div class="ui small input field" :class="{ error: erreurs.us_pass }">
                        <input id="mdp-desac" type="password" name="us_pass" value="" autocomplete="new-password"
                               placeholder="{{ __('Mot de passe') }}" @input="erreurs.us_pass = ''">
                    </div>
                    <div class="ui basic red pointing prompt label" :class="{ show: erreurs.us_pass }" x-text="erreurs.us_pass"></div>
                </div>
                <div class="grouped fields">
                    <div class="ui small input field" :class="{ error: erreurs.us_nom }">
                        <input type="text" name="us_nom" value="" autocomplete="name"
                               placeholder="{{ __('Nom / Prénom') }}" @input="erreurs.us_nom = ''">
                    </div>
                    <div class="ui basic red pointing prompt label" :class="{ show: erreurs.us_nom }" x-text="erreurs.us_nom"></div>
                </div>
                <div class="grouped fields">
                    <div class="ui small input field" :class="{ error: erreurs.us_mail }">
                        <input id="mail" type="email" name="us_mail" value="" autocomplete="email"
                               placeholder="{{ __('Mail') }}" @input="erreurs.us_mail = ''">
                    </div>
                    <div class="ui basic red pointing prompt label" :class="{ show: erreurs.us_mail }" x-text="erreurs.us_mail"></div>
                </div>
                {{-- Captcha local (App\Services\Captcha\Captcha), a la place du reCAPTCHA. --}}
                <div class="grouped fields">
                    <div class="ui small input field" :class="{ error: erreurs.captcha }" style="display:flex;align-items:center;gap:8px;flex-wrap:nowrap;">
                        <img :src="captcha || null" width="158" height="53" alt="{{ __('Code à recopier') }}" title="{{ __('Cliquez pour changer') }}"
                             style="flex-shrink:0;cursor:pointer;background:#fff" @click="rechargerCaptcha()">
                        <input type="text" name="captcha" maxlength="4" autocomplete="off" placeholder="{{ __('Recopiez le code') }}"
                               style="width:140px;" @input="erreurs.captcha = ''">
                        <i class="sync alternate icon" title="{{ __('Nouvelle image') }}" style="cursor:pointer;color:#888" @click="rechargerCaptcha()"></i>
                    </div>
                    <div class="ui basic red pointing prompt label" :class="{ show: erreurs.captcha }" x-text="erreurs.captcha"></div>
                </div>
                <div class="grouped fields">
                    <div class="field" :class="{ error: erreurs.us_licence }">
                        <div class="ui toggle checkbox us_licence" x-data="caseACocher" x-bind="racine" @change="erreurs.us_licence = ''">
                            <input name="us_licence" type="checkbox" tabindex="0" class="hidden">
                            <label>
                                {{ __('J’accepte les') }} <a href="/doc/conditions-dutilisations" target="_blank">{{ __('conditions d’utilisation') }}</a>
                                {{ __('de la plateforme :marque', ['marque' => $marque->nom]) }}
                            </label>
                        </div>
                        <div class="ui basic red pointing prompt label" :class="{ show: erreurs.us_licence }" x-text="erreurs.us_licence"></div>
                    </div>
                </div>
                <button type="button" class="ui button icon valider_volet_2" @click="volet = 1"><i class="angle left icon"></i></button>
                <button class="ui teal button valider_submit" type="submit">{{ __('Valider') }}</button>
            </div>
        </form>
    </div>
</div>
