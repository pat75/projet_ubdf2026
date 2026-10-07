@extends('layouts.portail')

@php
    $nom = $creatif->fullName();
    $metier = __(\App\Support\Metier::find($creatif->category?->slug ?? 'autre')['name'] ?? '');
    $lieu = collect([$creatif->city, $creatif->country])->filter()->implode(', ');
    $bio = texte_seo($creatif->bookSetting?->description, 400);
    $reseaux = array_values(array_filter([$creatif->website, $creatif->instagram_url, $creatif->facebook_url, $creatif->twitter_url]));

    // Indexee seulement si le texte est assez riche : sinon contenu mince.
    $indexable = mb_strlen($media->ai_description.$bio) >= 120 && $media->tags->count() >= 5;

    $racine = rtrim($marque->canonique, '/');
    $urlPage = $racine.request()->getPathInfo();
    $personne = array_filter([
        '@type' => 'Person',
        'name' => $nom,
        'url' => $creatif->bookUrl(),
        'jobTitle' => $metier ?: null,
        'description' => $bio ?: null,
        'image' => $creatif->thumbnailUrl('carre_183') ?: null,
        'address' => $lieu ? array_filter(['@type' => 'PostalAddress', 'addressLocality' => $creatif->city, 'addressCountry' => $creatif->country]) : null,
        'sameAs' => $reseaux ?: null,
    ]);
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'ImageObject',
        '@id' => $urlPage.'#image',
        'url' => $urlPage,
        'contentUrl' => $media->url(),
        'thumbnailUrl' => $media->url('ptf_medium'),
        'name' => $media->ai_title,
        'description' => $media->ai_description,
        'keywords' => $media->tags->pluck('label')->implode(', '),
        'width' => $media->width,
        'height' => $media->height,
        'inLanguage' => app()->getLocale(),
        'isPartOf' => ['@id' => $racine.'/#site'],
        'creator' => $personne,
        'creditText' => $nom,
        'copyrightNotice' => '© '.$nom,
        'copyrightHolder' => ['@type' => 'Person', 'name' => $nom],
        'acquireLicensePage' => $creatif->bookUrl(),
    ];
@endphp

@section('title', $media->ai_title.' — '.$nom.' | '.$marque->nom)
@section('description', texte_seo($media->ai_description, 155))
@section('og_image', $media->url('ptf_medium'))
@unless ($indexable)
    {{-- Description courte ou peu de mots-cles : contenu mince, pas pour les moteurs. --}}
    @section('robots', 'noindex, follow')
@endunless
@section('body_class', 'page_image')

@push('jsonld')
    <script type="application/ld+json">{!! json_encode(array_filter($jsonLd), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <style>
        .fiche_image { background: #ebebeb; }
        .fiche_image .fi_page { max-width: 1160px; margin: 0 auto; padding: 48px 40px 64px; box-sizing: border-box; }
        .fiche_image .fi_grille { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: start; }
        .fiche_image .fi_visuel img { display: block; width: 100%; height: auto; }
        .fiche_image .fi_infos { display: flex; flex-direction: column; padding-top: 8px; }
        .fiche_image h1 { margin: 0; font-size: 37.4px; line-height: 1.2; font-weight: 300; color: #1a1716; text-wrap: pretty; }
        .fiche_image .fi_desc { margin: 16px 0 0; font-size: 17.85px; line-height: 1.6; color: #4a4542; max-width: 52ch; text-wrap: pretty; }
        .fiche_image .fi_tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 28px; }
        .fiche_image .fi_tags a { padding: 6px 14px; border-radius: 999px; background: #4a4d50; border: 1px solid #4a4d50; font-size: 13px; font-weight: 600; letter-spacing: .3px; color: #fff; }
        .fiche_image .fi_tags a:hover { background: #2f3133; color: #fff; }
        .fiche_image .fi_filet { height: 1px; background: #d6d3cf; margin: 36px 0 28px; }
        .fiche_image .fi_createur { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
        .fiche_image .fi_createur > a:first-child { gap: 14px; color: #1a1716; }
        .fiche_image .fi_createur > a:first-child > img,
        .fiche_image .fi_createur > a:first-child > span:first-child { width: 52px; height: 52px; }
        .fiche_image .fi_createur .font-bold { font-size: 18px; color: #1a1716; }
        .fiche_image .fi_createur .font-light { font-size: 14px; font-weight: 400; color: #6e6862; }
        .fiche_image .fi_boutons { display: flex; flex-direction: column; gap: 10px; }
        .fiche_image .fi_bouton { justify-content: space-between;  display: flex; align-items: center; gap: 10px; padding: 11px 20px; border: 1px solid #1a1716; border-radius: 4px; font-size: 15px; font-weight: 600; color: #1a1716; text-decoration: none; }
        .fiche_image .fi_bouton:hover { background: #1a1716; color: #fff; }
        .fiche_image .fi_apropos { margin-top: 24px; font-size: 15px; line-height: 1.6; color: #4a4542; text-wrap: pretty; }
        .fiche_image .fi_apropos p { margin: 0 0 8px; }
        .fiche_image .fi_apropos .fi_meta { color: #6e6862; }
        .fiche_image h2 { margin: 72px 0 20px; font-size: 24px; font-weight: 300; color: #1a1716; }
        .fiche_image .fi_autres { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; }
        .fiche_image .fi_autres a { display: block; aspect-ratio: 1 / 1; background: #fff; border-radius: 4px; overflow: hidden; }
        .fiche_image .fi_autres img { width: 100%; height: 100%; object-fit: cover; display: block; }
    </style>
    <div class="fiche_image">
        <div class="fi_page">
            <div class="fi_grille">
                <a class="fi_visuel" href="{{ $media->url() }}">
                    <img src="{{ $media->url('ptf_medium') }}" alt="{{ $media->ai_title }}"
                         @if ($media->width) width="{{ $media->width }}" height="{{ $media->height }}" @endif>
                </a>

                <div class="fi_infos">
                    <h1>{{ $media->ai_title }}</h1>
                    @if ($media->ai_description)
                        <p class="fi_desc">{{ $media->ai_description }}</p>
                    @endif

                    @if ($media->tags->isNotEmpty())
                        <div class="fi_tags">
                            @foreach ($media->tags as $tag)
                                <a href="{{ $tag->url() }}">{{ $tag->label }}</a>
                            @endforeach
                        </div>
                    @endif

                    <div class="fi_filet"></div>

                    <div class="fi_createur">
                        <x-portail.createur :creatif="$creatif" />
                        <div class="fi_boutons">
                            <a class="fi_bouton" href="{{ $creatif->bookUrl() }}" target="_blank">{{ __('Voir le book complet') }} <span aria-hidden="true">→</span></a>
                            <a class="fi_bouton" href="{{ $creatif->bookUrl() }}" data-contacter="{{ $creatif->login }}">{{ __('Contacter') }} <span aria-hidden="true">→</span></a>
                        </div>
                    </div>

                    <div class="fi_apropos">
                        <h2 class="sr-only">{{ __('À propos de :nom', ['nom' => $nom]) }}</h2>
                        @if ($metier || $lieu)
                            <p class="fi_meta">{{ collect([$metier, $lieu])->filter()->implode(' · ') }}</p>
                        @endif
                        @if ($creatif->bookSetting?->title)
                            <p><strong>{{ texte_seo($creatif->bookSetting->title) }}</strong></p>
                        @endif
                        @if ($bio)
                            <p>{{ $bio }}</p>
                        @endif
                        @if ($reseaux)
                            <p class="fi_meta">
                                @foreach ($reseaux as $lien)
                                    <a href="{{ $lien }}" target="_blank" rel="noopener">{{ rtrim(preg_replace('#^https?://(www\.)?|[?\#].*$#', '', $lien), '/') }}</a>@unless ($loop->last) · @endunless
                                @endforeach
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            @if ($autres->isNotEmpty())
                <h2>{{ __('Autres images de :nom', ['nom' => $nom]) }}</h2>
                <div class="fi_autres">
                    @foreach ($autres as $autre)
                        <a href="{{ $autre->pageUrl() }}"><img src="{{ $autre->url('carre_183') }}" alt="{{ $autre->ai_title }}" width="183" height="183" loading="lazy"></a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
