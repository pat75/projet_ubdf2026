{{--
    Referencement commun aux books passes en Blade ($vue : App\Services\Book\VueBook) :
    titre, description, adresse canonique, Open Graph / X, donnees
    structurees schema.org (Person, ProfilePage, ImageGallery) pour les
    moteurs de recherche comme pour les moteurs generatifs.
--}}
@php
    $titre = $vue->titrePage();
    $description = $vue->descriptionPage();
    $canonique = $vue->urlCanonique();
    $image = $vue->imagePartage();
@endphp
<title>{{ $titre }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonique }}">
@if ($vue->edition() || $vue->sansIndex)
    <meta name="robots" content="noindex">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif
<meta name="author" content="{{ $vue->nomCreateur() }}">

<meta property="og:type" content="profile">
<meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale() === 'fr' ? 'fr_FR' : app()->getLocale()) }}">
<meta property="og:site_name" content="{{ $vue->nomCreateur() }}">
<meta property="og:title" content="{{ $titre }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonique }}">
@if ($image)
    <meta property="og:image" content="{{ $image['src'] }}">
    @if ($image['largeur'] && $image['hauteur'])
        <meta property="og:image:width" content="{{ $image['largeur'] }}">
        <meta property="og:image:height" content="{{ $image['hauteur'] }}">
    @endif
@endif
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $titre }}">
<meta name="twitter:description" content="{{ $description }}">
@if ($image)
    <meta name="twitter:image" content="{{ $image['src'] }}">
@endif

<link rel="icon" href="{{ $b->icone }}">
<link rel="apple-touch-icon" href="{{ $b->icone_iphone }}">

<script type="application/ld+json">{!! json_encode($vue->donneesStructurees(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
