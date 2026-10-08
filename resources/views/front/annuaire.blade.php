@extends('layouts.portail')

@section('title', 'Annuaire des créatifs | Ultra-book')
@section('description', __('Annuaire des créatifs freelances, classés par ordre alphabétique : retrouvez un illustrateur, un graphiste ou un photographe par son nom et ouvrez son book.'))
@section('body_class', 'page_annuaire')

@section('content')
    <div class="ui container bloc_portfolios">
        <h1>{{ __('Annuaire des créatifs') }}</h1>

        <div class="ui text menu annuaire_alpha">
            @foreach ($lettres as $l)
                <a class="item {{ $lettre === $l ? 'active' : '' }}"
                   href="{{ lien('annuaire.lettre', ['lettre' => $l]) }}">{{ strtoupper($l) }}</a>
            @endforeach
            <a class="item {{ $lettre === null ? 'active' : '' }}" href="{{ lien('annuaire') }}">{{ __('Tous') }}</a>
        </div>

        <div class="ui three column grid annuaire_liste">
            @foreach ($creatifs as $creatif)
                <div class="column">
                    <a href="{{ $creatif->bookUrl() }}" class="coul_{{ $creatif->category?->slug }}">
                        {{ $creatif->fullName() }}
                    </a>
                    <span class="meta">{{ __($creatif->category?->name ?? '') }}</span>
                </div>
            @endforeach
        </div>

        {{ $creatifs->links() }}
    </div>
@endsection
