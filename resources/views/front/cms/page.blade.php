@extends('layouts.portail')

@section('title', texte_seo($page->title).' | '.$marque->nom)
@section('description', texte_seo($page->excerpt ?: $page->body, 155))
@section('body_class', 'page_cms')

@php
    /*
     | Mise en page du mini-guide Tesli (info/infodomainekeyword) : sommaire
     | numerote a gauche, article, « Sur cette page » a droite, encart
     | d'inscription et guides precedent / suivant.
     */
    $rang = $navigation->search(fn ($p) => $p->is($page));
    $total = $navigation->count();
    $precedente = $rang !== false && $rang > 0 ? $navigation[$rang - 1] : null;
    $suivante = $rang !== false && $rang < $total - 1 ? $navigation[$rang + 1] : null;
    $minutes = max(1, (int) ceil(str_word_count(strip_tags($page->body ?? '')) / 200));

    // Ancres des <h2> du contenu, pour le sommaire « Sur cette page ».
    $sommaire = [];
    $corps = preg_replace_callback('/<h2([^>]*)>(.*?)<\/h2>/is', function ($m) use (&$sommaire) {
        $libelle = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $id = Str::slug($libelle) ?: 'section-'.(count($sommaire) + 1);
        $base = $id;
        for ($n = 2; in_array($id, array_column($sommaire, 'id'), true); $n++) {
            $id = $base.'-'.$n;
        }
        $sommaire[] = ['id' => $id, 'libelle' => $libelle];

        return '<h2'.preg_replace('/\sid="[^"]*"/i', '', $m[1]).' id="'.$id.'">'.$m[2].'</h2>';
    }, $page->body ?? '');

    /*
     | Questions frequentes : un <h3> qui finit par « ? » et le contenu qui le
     | suit (jusqu'au prochain titre) deviennent un <details> depliable. La
     | reponse reste dans le HTML — lisible par les moteurs — et alimente le
     | JSON-LD FAQPage. Fleches : pictos angle-droite / angle-bas de la charte.
     */
    $faq = [];
    $fleches = Blade::render('<x-espace.picto nom="angle-droite" class="ubg_faq_fleche ferme" /><x-espace.picto nom="angle-bas" class="ubg_faq_fleche ouvert" />');
    $corps = preg_replace_callback('/<h3([^>]*)>(.*?)<\/h3>(.*?)(?=<h[23][\s>]|$)/is', function ($m) use (&$faq, $fleches) {
        $question = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (! str_ends_with($question, '?')) {
            return $m[0];
        }
        $faq[] = ['question' => $question, 'reponse' => texte_seo($m[3], 1000)];

        return '<details class="ubg_faq"><summary><h3'.$m[1].'>'.$m[2].'</h3>'.$fleches.'</summary>'
            .'<div class="ubg_faq_reponse">'.$m[3].'</div></details>';
    }, $corps);

    $connecte = auth('web')->check();

    // Visuel d'en-tete, repris des photos du guide Tesli : le meme pour une
    // page donnee, d'une visite a l'autre. Sert aussi d'image de partage.
    $visuels = glob(public_path('img_doc/guide/*.avif')) ?: [];
    $visuel = $visuels ? '/img_doc/guide/'.basename($visuels[crc32($page->slug) % count($visuels)]) : null;
@endphp

@if ($visuel)
    @section('og_image', rtrim($marque->canonique, '/').$visuel)
@endif

{{-- Page quasi vide (fiches ecoles reprises du WordPress) : servie, mais
     pas indexee — du contenu mince affaiblit toute la documentation. --}}
@if (str_word_count(strip_tags($page->body ?? '')) < 80)
    @section('robots', 'noindex, follow')
@endif

@section('content')
    <div class="ubg">
        <div class="ubg_grille">

            @if ($total > 1)
                <aside class="ubg_nav" x-data="{ ouvert: false }" :class="{ ouvert }">
                    <div class="ubg_nav_entete">
                        <span class="ubg_mono">{{ __('Documentation') }}</span>
                        <span class="ubg_mono">{{ $total }}</span>
                    </div>
                    <div class="ubg_nav_titre">
                        <span>{{ __('Guide :marque', ['marque' => $marque->nom]) }}</span>
                        {{-- Mobile : le sommaire se replie derriere ce bouton. --}}
                        <button type="button" class="ubg_burger" @click="ouvert = ! ouvert"
                                :aria-expanded="ouvert" aria-controls="ubg_sommaire_guides"
                                :aria-label="ouvert ? @js(__('Fermer le menu')) : @js(__('Ouvrir le menu'))">
                            <svg x-show="! ouvert" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                            <svg x-show="ouvert" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                    </div>

                    <nav id="ubg_sommaire_guides">
                        @foreach ($navigation as $soeur)
                            <a href="{{ lien('cms.doc', $soeur->slug) }}"
                               class="ubg_nav_lien @if ($soeur->is($page)) actif @endif"
                               @if ($soeur->is($page)) aria-current="page" @endif>
                                <span class="ubg_mono">{{ sprintf('%02d', $loop->iteration) }}</span>
                                <span>{{ $soeur->title }}</span>
                            </a>
                        @endforeach
                    </nav>

                    @unless ($connecte)
                        <div class="ubg_nav_pied">
                            <span>{{ __('Votre portfolio en ligne, gratuit et sans publicité.') }}</span>
                            <a href="{{ lien('inscription.page') }}">{{ __('Créer') }}</a>
                        </div>
                    @endunless
                </aside>
            @endif

            <article class="ubg_article">
                <div class="ubg_meta ubg_mono">
                    @if ($rang !== false && $total > 1)
                        <span>{{ __('Guide :n sur :total', ['n' => sprintf('%02d', $rang + 1), 'total' => $total]) }}</span>
                        <span class="ubg_trait"></span>
                    @endif
                    <span>{{ __(':n min de lecture', ['n' => $minutes]) }}</span>
                    @if ($page->updated_at)
                        <span class="ubg_trait"></span>
                        <span>{{ __('Mis à jour le :date', ['date' => $page->updated_at->translatedFormat('j F Y')]) }}</span>
                    @endif
                </div>

                <h1>{{ $page->title }}</h1>

                @if ($page->excerpt)
                    <p class="ubg_chapo">{{ texte_seo($page->excerpt) }}</p>
                @endif

                @if ($visuel)
                    <figure class="ubg_visuel">
                        <img src="{{ $visuel }}" alt="{{ $page->title }} — {{ $marque->nom }}" width="1200" height="360" @unless ($rang === 0) loading="lazy" @endunless>
                    </figure>
                @endif

                <div class="ubg_contenu @if (count($sommaire) > 1) avec_sommaire @endif">
                    {{-- Contenu redige en interne dans l'ancien WordPress, importe
                         une fois. Il n'est pas alimente par des visiteurs. --}}
                    <div class="contenu_cms">
                        {!! $corps !!}
                    </div>

                    @if (count($sommaire) > 1)
                        <div class="ubg_sommaire">
                            <div class="ubg_mono">{{ __('Sur cette page') }}</div>
                            @foreach ($sommaire as $entree)
                                <a href="#{{ $entree['id'] }}">{{ $entree['libelle'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($tarifs)
                    @include('front.cms.tarifs')
                @endif

                @unless ($connecte)
                    <div class="ubg_cta">
                        <div>
                            <div class="ubg_cta_titre">{{ __('Votre book en ligne en quelques minutes.') }}</div>
                            <div class="ubg_cta_texte">{{ __('Choisissez votre adresse, ajoutez vos visuels, partagez votre portfolio. Gratuit, sans limite de durée.') }}</div>
                        </div>
                        <a href="{{ lien('inscription.page') }}">{{ __('Créer mon book') }}</a>
                    </div>
                @endunless

                @if ($total > 1)
                    <div class="ubg_suite">
                        @foreach ([[$precedente, __('← Guide précédent'), __('Vous êtes au premier guide')], [$suivante, __('Guide suivant →'), __('Vous êtes au dernier guide')]] as [$cible, $sens, $vide])
                            @if ($cible)
                                <a href="{{ lien('cms.doc', $cible->slug) }}" class="ubg_carte">
                                    <span class="ubg_mono">{{ $sens }}</span>
                                    <span class="ubg_carte_titre">{{ $cible->title }}</span>
                                </a>
                            @else
                                <div class="ubg_carte vide">
                                    <span class="ubg_mono">{{ $sens }}</span>
                                    <span class="ubg_carte_titre">{{ $vide }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </article>
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $url = lien('cms.doc', $page->slug);
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => $marque->nom, 'item' => rtrim($marque->canonique, '/').'/'],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => __('Documentation'), 'item' => lien('cms.doc', 'doc')],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $page->title, 'item' => $url],
                    ],
                ],
                [
                    '@type' => 'Article',
                    'headline' => $page->title,
                    'description' => texte_seo($page->excerpt ?: $page->body, 300),
                    'inLanguage' => $page->locale,
                    'datePublished' => $page->published_at?->toIso8601String(),
                    'dateModified' => ($page->updated_at ?? $page->published_at)?->toIso8601String(),
                    'mainEntityOfPage' => $url,
                    'publisher' => ['@type' => 'Organization', 'name' => $marque->nom, 'url' => rtrim($marque->canonique, '/').'/'],
                ],
            ],
        ];
        if (count($faq) > 1) {
            $jsonLd['@graph'][] = [
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn ($q) => [
                    '@type' => 'Question', 'name' => $q['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q['reponse']],
                ], $faq),
            ];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
