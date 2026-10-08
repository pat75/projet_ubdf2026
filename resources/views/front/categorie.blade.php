@extends('layouts.portail')

@php
    use App\Support\Metier;

    /*
     | Page metier, ou page d'accroche SEO ($landing) qui en reprend la
     | selection avec son propre titre, H1, description et introduction
     | (config/seo_contenus.php). Les questions frequentes ne figurent que
     | sur la page metier : les repeter sur chaque accroche dupliquerait le
     | contenu.
     */
    $racine = rtrim($marque->canonique, '/');
    $absolu = fn (string $url) => $racine.(parse_url($url, PHP_URL_PATH) ?: '/');
    $urlMetier = $absolu(lien_metier($categorie));
    $urlPage = $racine.request()->getPathInfo();

    $titre = $landing ? __($landing['titre']).' | '.$marque->nom : Metier::titreSeo($categorie, $marque->nom);
    $description = $landing ? __($landing['description']) : Metier::descriptionSeo($categorie, $marque->nom);
    // « Illustrateurs freelance » plutot que « Illustration » : ce que l'on cherche.
    $h1 = $landing ? __($landing['h1']) : __(':metiers freelance', ['metiers' => ucfirst(Metier::pluriel($categorie))]);
    $intro = $landing ? __($landing['intro']) : Metier::intro($categorie, $marque->nom);
    $faq = $landing ? [] : Metier::faq($categorie, $marque->nom);

    $fil = [['nom' => __('Accueil'), 'url' => $racine.'/'], ['nom' => Metier::titreBloc($categorie), 'url' => $urlMetier]];
    if ($landing) {
        $fil[] = ['nom' => $h1, 'url' => $urlPage];
    }

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => array_values(array_filter([
            [
                '@type' => 'CollectionPage',
                '@id' => $urlPage.'#page',
                'url' => $urlPage,
                'name' => $h1,
                'description' => $description,
                'inLanguage' => app()->getLocale(),
                'isPartOf' => ['@id' => $racine.'/#site'],
                'breadcrumb' => ['@id' => $urlPage.'#fil'],
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'name' => $h1,
                    'numberOfItems' => $books->count(),
                    'itemListElement' => $books->values()->map(fn ($book, $i) => [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'name' => $book->fullName(),
                        'url' => $book->bookUrl(),
                    ])->all(),
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => $urlPage.'#fil',
                'itemListElement' => collect($fil)->values()->map(fn ($etape, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $etape['nom'],
                    'item' => $etape['url'],
                ])->all(),
            ],
            $faq ? [
                '@type' => 'FAQPage',
                '@id' => $urlPage.'#faq',
                'mainEntity' => array_map(fn ($entree) => [
                    '@type' => 'Question',
                    'name' => $entree['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $entree['reponse']],
                ], $faq),
            ] : null,
        ])),
    ];
@endphp

@section('title', $titre)
@section('description', $description)
@section('body_class', 'page_metier')

@push('jsonld')
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <div class="ui container bloc_portfolios">

        <div class="bloc_titre">
            <h1 class="metier_group coultxt_{{ $categorie }}">{{ $h1 }}</h1>
            <div class="sub_title">
                <strong>{{ number_format($total, 0, ',', ' ') }}</strong>
                {{ Metier::pluriel($categorie) }}
            </div>
            @if ($intro)
                <p class="metier_intro">{{ $intro }}</p>
            @endif
        </div>

        {{-- Premier ecran rendu cote serveur ; la suite arrive par
             defilement infini, chaque carte apparaissant en fondu. --}}
        <div class="visibility infinite" x-data="defilementInfini">
            <div class="ui five doubling cards" id="accueil_portfolio">
                @foreach ($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
                <div id="position_card_last" x-ref="fin"></div>
            </div>

            <div class="ui basic segment">
                <div class="ui grid result_message"></div>
            </div>

            <div class="ui horizontal icon divider result_end" :class="{ show: termine && page > 0 }">
                <i class="circular large angle up icon"></i>
            </div>

            <div class="ui large centered inline text loader" :class="{ active: enCours }">{{ __('Chargement...') }}</div>
        </div>

        @if ($faq)
            {{-- Questions frequentes : texte visible, repris en FAQPage
                 dans les donnees structurees ci-dessus. --}}
            <section class="metier_faq">
                <h2>{{ __('Questions fréquentes') }}</h2>
                @foreach ($faq as $entree)
                    <details>
                        <summary>{{ $entree['question'] }}</summary>
                        <p>{{ $entree['reponse'] }}</p>
                    </details>
                @endforeach
            </section>
        @endif
    </div>
@endsection
