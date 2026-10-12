@extends('layouts.portail')

@section('title', 'Simulation | Créer un book')
@section('robots', 'noindex, nofollow')
@section('body_class', 'page_creer_book')
@section('plein_ecran', '1')

{{-- Simulation de l'ecran final de /creer-un-book (anneau de progression,
     machine a ecrire, « Bravo ») sans creer de compte. Local/dev seulement
     (route espace.test-crea). La redirection automatique est coupee : on
     reste sur la page, « Rejouer » relance l'animation. --}}
@section('content')
    <section class="creer_book modal_creerbook"
             x-data="inscription(@js([]))"
             x-init="vue = 'validation'; $nextTick(() => animerProgression())">

        <div class="dev_only" style="position: fixed; top: 12px; left: 12px; z-index: 50; padding: 8px 12px; background: #fff; font-size: 13px; display: flex; gap: 12px; align-items: center;">
            <span>{{ __('Simulation : aucun compte créé') }}</span>
            <button type="button" class="ui black button mini" style="border-radius: 0; margin: 0"
                    @click="progression = 0; animerProgression()">{{ __('Rejouer') }}</button>
        </div>

        <div class="creer_book_visuel actif">
            <div id="inscription_segment_gauche">
                @include('partials.inscription.validation')
            </div>
        </div>
    </section>
@endsection
