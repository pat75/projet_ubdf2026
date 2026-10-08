@extends('layouts.portail')

@php
    $racine = rtrim($marque->canonique, '/');
    $urlPage = $racine.request()->getPathInfo();
    $titre = __(':motcle : illustrations, photos et créations', ['motcle' => mb_convert_case($motCle, MB_CASE_TITLE)]);
    $description = texte_seo(trans_choice(':n image|:n images', $images->count(), ['n' => $images->count()]).' « '.$motCle.' » '
        .trans_choice('de :n créatif indépendant|de :n créatifs indépendants', $creatifs->count(), ['n' => $creatifs->count()]).'. '
        .__('Découvrez leurs portfolios et contactez-les directement sur :marque.', ['marque' => $marque->nom]), 155);

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $urlPage.'#page',
        'url' => $urlPage,
        'name' => $titre,
        'description' => $description,
        'inLanguage' => app()->getLocale(),
        'isPartOf' => ['@id' => $racine.'/#site'],
        'about' => ['@type' => 'Thing', 'name' => $motCle],
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => $images->count(),
            'itemListElement' => $images->values()->map(fn ($image, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'item' => array_filter([
                    '@type' => 'ImageObject',
                    'contentUrl' => $image->url(),
                    'thumbnailUrl' => $image->url('carre_183'),
                    'name' => $image->ai_title,
                    'description' => $image->ai_description,
                    'keywords' => $motCle,
                    'creator' => ['@type' => 'Person', 'name' => $image->user->fullName(), 'url' => $image->user->bookUrl()],
                    'creditText' => $image->user->fullName(),
                    'copyrightNotice' => '© '.$image->user->fullName(),
                    'acquireLicensePage' => $image->user->bookUrl(),
                ]),
            ])->all(),
        ],
    ];
@endphp

@section('title', $titre.' | '.$marque->nom)
@section('description', $description)
@section('og_image', $images->first()->url('ptf_medium'))
@unless ($indexable)
    {{-- Trop peu d'images ou de createurs : page utile, pas encore assez riche pour un moteur. --}}
    @section('robots', 'noindex, follow')
@endunless
@section('body_class', 'page_images_motcle')

@push('jsonld')
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <div class="ui container bloc_portfolios" style="padding: 80px 0">
        <div class="bloc_titre">
            <h1>{{ $titre }}</h1>
            <div class="sub_title">
                {{ trans_choice(':n image|:n images', $images->count(), ['n' => $images->count()]) }},
                {{ trans_choice(':n créatif|:n créatifs', $creatifs->count(), ['n' => $creatifs->count()]) }}
            </div>
        </div>

        <div class="ui six doubling cards">
            @foreach ($images as $image)
                <a class="ui card" href="{{ $image->pageUrl() }}">
                    <div class="image">
                        <img src="{{ $image->url('carre_183') }}" alt="{{ $image->ai_title }}" width="183" height="183" loading="lazy">
                    </div>
                    <x-portail.legende-createur :creatif="$image->user" />
                </a>
            @endforeach
        </div>

        <h2>{{ __('Les créatifs') }}</h2>
        <div class="mb-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($creatifs as $creatif)
                <x-portail.createur :creatif="$creatif" />
            @endforeach
        </div>
    </div>
@endsection
