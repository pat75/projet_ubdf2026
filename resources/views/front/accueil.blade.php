@extends('layouts.portail')

@section('body_class', 'page_accueil')

@php
    $racine = rtrim($marque->canonique, '/');
    // /accueil et / servent la meme page : une seule adresse canonique, la racine.
    $urlAccueil = $racine.($marque->multilingue() ? '/'.app()->getLocale() : '/');

    $pageJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $urlAccueil.'#page',
        'url' => $urlAccueil,
        'name' => $marque->titre(),
        'description' => $marque->description(),
        'inLanguage' => app()->getLocale(),
        'isPartOf' => ['@id' => $racine.'/#site'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'name' => __('Portfolios de créatifs par métier'),
            'numberOfItems' => $blocs->count(),
            'itemListElement' => $blocs->values()->map(fn (array $bloc, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => App\Support\Metier::titreBloc($bloc['slug']),
                'description' => App\Support\Metier::sousTitre($bloc['slug']),
                'url' => $racine.parse_url(lien_metier($bloc['slug']), PHP_URL_PATH),
            ])->all(),
        ],
    ];
@endphp

@section('canonical', $urlAccueil)

@if ($marque->multilingue())
    @section('hreflang')
        @foreach ($marque->langues as $langue)
            <link rel="alternate" hreflang="{{ $langue }}" href="{{ $racine.'/'.$langue }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $racine.'/'.$marque->locale() }}">
    @endsection
@endif

{{-- Donnees structurees de la page : une collection de portfolios, par
     metier, avec l'adresse de chacun (lisible par les moteurs comme par
     les assistants IA). --}}
@push('jsonld')
    <script type="application/ld+json">{!! json_encode($pageJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <div x-data>
        @include('partials.accueil-hero')

        <div class="bloc_portfolios_">
            @foreach ($blocs as $bloc)
                <x-bloc-metier :slug="$bloc['slug']" :books="$bloc['books']" :total="$bloc['total']" :premier="$loop->first" />
            @endforeach
        </div>
    </div>
@endsection
