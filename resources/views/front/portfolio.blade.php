@extends('layouts.portail')

@php
    use App\Support\Metier;

    /*
     | Fiche d'un book sur le portail : titre « Nom, metier freelance », une
     | description redigee si celle du book n'est qu'une liste de mots-cles
     | (cas frequent : « femme, illustration, dessin »), et les donnees
     | structurees du createur et de ses images.
     */
    $nom = $book->fullName();
    $slugMetier = $book->category?->slug ?? 'autre';
    $metier = mb_strtolower(__(Metier::find($slugMetier)['name'] ?? ''));
    $racine = rtrim($marque->canonique, '/');
    $urlPage = $racine.request()->getPathInfo();

    $bio = texte_seo($book->bookSetting?->description);
    $bioRedigee = mb_strlen($bio) >= 60 && substr_count($bio, ',') < mb_strlen($bio) / 25;
    $description = $bioRedigee
        ? texte_seo($bio, 155)
        : texte_seo(__(':nom, :metier freelance:ville : découvrez son portfolio et contactez directement ce créatif sur :marque.', [
            'nom' => $nom,
            'metier' => $metier,
            'ville' => $book->city ? ' '.__('à :ville', ['ville' => $book->city]) : '',
            'marque' => $marque->nom,
        ]), 155);

    $personne = array_filter([
        '@type' => 'Person',
        '@id' => $urlPage.'#createur',
        'name' => $nom,
        'jobTitle' => $metier ?: null,
        'url' => $book->bookUrl(),
        'description' => $bioRedigee ? texte_seo($bio, 300) : null,
        'address' => $book->city ? ['@type' => 'PostalAddress', 'addressLocality' => $book->city] : null,
        'sameAs' => array_values(array_filter([$book->bookUrl(), $book->website])),
    ]);

    $images = $book->media->take(20)->values()->map(fn ($media) => array_filter([
        '@type' => 'ImageObject',
        'contentUrl' => $media->url(),
        'thumbnailUrl' => $media->url('ptf_medium'),
        'name' => $media->ai_title ?: ($media->title ? texte_seo(pathinfo($media->title, PATHINFO_FILENAME)) : null),
        'description' => $media->ai_description,
        'creator' => ['@id' => $urlPage.'#createur'],
        'creditText' => $nom,
        'copyrightNotice' => '© '.$nom,
    ]))->all();

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => array_values(array_filter([
            [
                '@type' => 'ProfilePage',
                '@id' => $urlPage.'#page',
                'url' => $urlPage,
                'name' => $nom,
                'description' => $description,
                'inLanguage' => app()->getLocale(),
                'isPartOf' => ['@id' => $racine.'/#site'],
                'mainEntity' => ['@id' => $urlPage.'#createur'],
                'breadcrumb' => ['@id' => $urlPage.'#fil'],
            ],
            $personne,
            $images ? ['@type' => 'ImageGallery', 'name' => __('Portfolio de :nom', ['nom' => $nom]), 'author' => ['@id' => $urlPage.'#createur'], 'image' => $images] : null,
            [
                '@type' => 'BreadcrumbList',
                '@id' => $urlPage.'#fil',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => __('Accueil'), 'item' => $racine.'/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => Metier::titreBloc($slugMetier), 'item' => $racine.parse_url(lien_metier($slugMetier), PHP_URL_PATH)],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $nom, 'item' => $urlPage],
                ],
            ],
        ])),
    ];
@endphp

@section('title', __(':nom, :metier freelance : portfolio', ['nom' => $nom, 'metier' => $metier]).' | '.$marque->nom)
@section('description', $description)
@section('body_class', 'page_book_single')

@push('jsonld')
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <div class="ui container bloc_portfolios book_single">
        <h1>{{ $book->fullName() }}</h1>
        <div class="meta">
            <a class="group coul_{{ $book->category?->slug }}">{{ __($book->category?->name ?? '') }}</a>
            @if ($book->city)<span class="ville">{{ $book->city }}</span>@endif
        </div>

        @if ($book->bookSetting?->description)
            <div class="bio">{!! nl2br(e($book->bookSetting->description)) !!}</div>
        @endif

        <div class="ui five doubling cards">
            @foreach ($book->media->take(20) as $media)
                <div class="ui card">
                    <a class="ui fluid image" href="{{ $media->url() }}" target="_blank">
                        <img src="{{ $media->url('ptf_medium') }}" alt="{{ $media->alt ?: ($media->ai_title ?: $media->title) }}">
                    </a>
                    @if ($media->title)
                        <div class="content center aligned">
                            <div class="header">{{ $media->title }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <a class="ui button" href="{{ $book->bookUrl() }}" target="_blank">
            {{ __('Voir le book complet') }}
        </a>
    </div>
@endsection
