{{-- Page servie sur tout le portail quand un administrateur a coche
     « Mettre le site en maintenance » (App\Http\Middleware\Maintenance).
     Meme visuel que la page introuvable, sans menu ni pied de page :
     rien du portail ne doit rester atteignable. --}}
@extends('layouts.portail')

@section('plein_ecran', true)

@section('title', __('Site en maintenance').' | '.$marque->nom)

@section('content')
    <div class="ui container bloc_portfolios">
        <div class="ui basic segment center aligned recherche_vide" style="text-align:center;padding-top:3em">
            <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" style="display:block;margin:0 auto 2.5em;width:180px;max-width:50%;height:auto">
            <img src="/img_front/recherche-vide.png" alt="" width="343" height="400" style="display:block;margin:0 auto 1em;max-width:60%;height:auto">
            <h1 style="text-align:center">{{ __('Site en maintenance') }}</h1>
            {{-- Jour de la semaine dans la langue de la page, sans date ni
                 heure : la maintenance dure de quelques minutes a quelques
                 heures, une heure de retour annoncee serait un engagement. --}}
            <p style="text-align:center">
                {!! __('Maintenance en cours ce <strong>:jour</strong>.', ['jour' => e(now()->translatedFormat('l'))]) !!}
            </p>
            <p style="text-align:center">
                {!! __('Elle peut durer de <strong>quelques minutes à quelques heures</strong>.') !!}
            </p>
            @if ($annonce = \App\Models\Reglage::texte(\App\Models\Reglage::MESSAGE_MAINTENANCE))
                <p class="ub-message-maintenance" style="text-align:center;margin-top:1.5em">{!! nl2br(e($annonce)) !!}</p>
            @endif
        </div>
    </div>
@endsection
