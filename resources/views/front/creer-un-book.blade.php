@extends('layouts.portail')

@section('title', __('Créer un book gratuitement').' | '.$marque->nom)
@section('description', __('Créez gratuitement votre portfolio de créatif freelance sur :marque : en ligne en quelques minutes, diffusé auprès des agences, éditeurs et entreprises.', ['marque' => $marque->nom]))
@section('body_class', 'page_creer_book')
@section('plein_ecran', '1')

@section('content')
    {{-- Ecran partage, sur le modele de /register de Tesli : formulaire a
         gauche, illustration a droite (masquee sur mobile, sauf pour
         l'anneau de progression apres l'envoi). `modal_creerbook` reprend
         les regles du formulaire de l'ancienne fenetre (css2019/core.css). --}}
    <section class="creer_book modal_creerbook" x-data="inscription(@js($textes))">

        {{-- Fermer la page : retour a la page precedente du site, sinon a l'accueil. --}}
        <a href="{{ lien('home') }}" class="creer_book_fermer" aria-label="{{ __('Fermer') }}"
           @click.prevent="document.referrer.startsWith(location.origin) && history.length > 1 ? history.back() : (location.href = $el.href)">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </a>

        <div class="creer_book_formulaire" x-show="vue !== 'validation'">
            <div class="creer_book_colonne">
                {{-- Sur mobile : entete en haut, formulaire au centre de l'ecran (portail.css). --}}
                <div class="creer_book_entete">
                <img class="creer_book_logo" src="{{ $marque->logo }}" alt="{{ $marque->nom }}">
                <h1 class="creer_book_titre">{{ __('Créer mon book') }}</h1>
                <p class="creer_book_accroche">{{ __('Gratuit, prêt en quelques minutes.') }}</p>
                </div>

                <div class="creer_book_corps">

                @if ($google)
                    {{-- Retour de Google : nom et mail sont connus, il reste
                         l'adresse du book et les conditions. --}}
                    <p class="creer_book_google_compte">
                        {{ __('Compte Google :') }} <strong>{{ $google['email'] }}</strong>
                    </p>
                    @include('partials.inscription.google')
                @else
                    @if (\App\Models\Reglage::googleActif())
                        <x-portail.bouton-google :libelle="__('S’inscrire avec Google')" class="creer_book_google" />
                        <div class="creer_book_ou"><span>{{ __('ou avec votre e-mail') }}</span></div>
                    @endif

                    <div class="ui segment basic" id="inscription_segment_">
                        @include('partials.inscription.formulaire')
                    </div>
                @endif
                </div>

            </div>

            {{-- Tout en bas de la colonne, comme sur Tesli. --}}
            @guest
                <div class="creer_book_connexion">
                    {{ __('Déjà un compte ?') }}
                    <a href="#" @click.prevent="$store.modale.ouvrir('connexion')">{{ __('Se connecter') }}</a>
                </div>
            @endguest
        </div>

        <div class="creer_book_visuel" :class="{ actif: vue === 'validation' }">
            <div id="inscription_segment_gauche">
                <img class="creer_book_illustration" src="/img_admin/diffusion-b.svg" alt="{{ __('Créez un book') }}"
                     width="400" height="400" x-show="vue !== 'validation'">
                @include('partials.inscription.validation')
            </div>
        </div>

    </section>
@endsection
