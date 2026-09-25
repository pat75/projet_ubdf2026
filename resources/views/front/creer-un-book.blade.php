@extends('layouts.portail')

@section('title', __('Créer un book gratuitement').' | '.$marque->nom)
@section('body_class', 'page_creer_book')
@section('plein_ecran', '1')

@section('content')
    {{-- Ecran partage, sur le modele de /register de Tesli : formulaire a
         gauche, illustration a droite (masquee sur mobile, sauf pour
         l'anneau de progression apres l'envoi). `modal_creerbook` reprend
         les regles du formulaire de l'ancienne fenetre (css2019/core.css). --}}
    <section class="creer_book modal_creerbook" x-data="inscription(@js($textes))">

        {{-- Retour a l'accueil : maison en haut a droite, croix sur mobile. --}}
        <a href="{{ lien('accueil') }}" class="creer_book_fermer" aria-label="{{ __('Retour à l’accueil') }}">
            <i class="home icon"></i>
        </a>

        <div class="creer_book_formulaire" x-show="vue !== 'validation'">
            <div class="creer_book_colonne">
                <img class="creer_book_logo" src="{{ $marque->logo }}" alt="{{ $marque->nom }}">
                <h1 class="creer_book_titre">{{ __('Créez un book') }}</h1>
                <p class="creer_book_accroche">
                    {{ __('Rejoignez les 50.000 créatifs.') }}<br>
                    {{ __('Créez, diffusez et proposez vos services') }}
                </p>

                @if ($google)
                    {{-- Retour de Google : nom et mail sont connus, il reste
                         l'adresse du book, le metier et les conditions. --}}
                    <p class="creer_book_google_compte">
                        {{ __('Compte Google :') }} <strong>{{ $google['email'] }}</strong>
                    </p>
                    @include('partials.inscription.google')
                @else
                    <x-portail.bouton-google :libelle="__('Créer mon book avec Google')" class="creer_book_google" />
                    <div class="creer_book_ou"><span>{{ __('ou avec un mot de passe') }}</span></div>

                    <div class="ui segment basic" id="inscription_segment_">
                        @include('partials.inscription.formulaire')
                    </div>
                @endif

                @guest
                    <div class="creer_book_connexion">
                        {{ __('Déjà un book ?') }}
                        <a href="#" @click.prevent="$store.modale.ouvrir('connexion')">{{ __('Se connecter') }}</a>
                    </div>
                @endguest
            </div>
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
