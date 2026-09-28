{{-- <head> du portail 2018, repris tel quel. Les balises variables sont
     pilotees par la section @yield('meta'). --}}
<head id="ultra-book">

@php
    /*
     | Referencement : une seule adresse par page. L'URL canonique est bati
     | sur le domaine de production de la marque (et non sur l'hote servi),
     | sans parametre de requete ; une page peut la fixer (@section('canonical')).
     */
    $canonique = trim($__env->yieldContent('canonical')) ?: rtrim($marque->canonique, '/').request()->getPathInfo();
    $titrePage = trim($__env->yieldContent('title')) ?: $marque->titre();
    $descriptionPage = trim($__env->yieldContent('description')) ?: $marque->description();
    $imagePartage = trim($__env->yieldContent('og_image')) ?: rtrim($marque->canonique, '/').'/img_front/favicon/android-icon-192x192.png';
@endphp
<meta charset="UTF-8">
{{-- Zoom autorise : user-scalable=no penalise l'accessibilite (et Lighthouse). --}}
<meta name="viewport" content="width=device-width, initial-scale=1">


    <title>{{ $titrePage }}</title>
    <meta name="description" content="{{ $descriptionPage }}">
    <link rel="canonical" href="{{ $canonique }}">
    {{-- L'espace creatif est prive : jamais indexe, meme s'il est lie. --}}
    <meta name="robots" content="@yield('robots', request()->is('espace', 'espace/*', '*/espace', '*/espace/*') ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    @yield('hreflang')



{{-- Plus de meta keywords : ignoree par les moteurs, elle ne renseigne que la concurrence. --}}
<meta name="application-name" 	    content="{{ $marque->nom }}" />

{{-- og:locale attend la forme POSIX complete (fr_FR), pas le code court. --}}
<meta property='og:locale' 		    content='{{ App\Support\Langue::posix(app()->getLocale()) }}'/>
<meta property='og:type' 			content='website'/>
<meta property='og:title' 			content='{{ $titrePage }}'/>
<meta property='og:url' 			content='{{ $canonique }}'/>
<meta property='og:site_name'		content='{{ $marque->nom }}'/>
<meta property='og:description' 	content='{{ $descriptionPage }}'/>
<meta property='og:image' 			content='{{ $imagePartage }}'/>

<meta name="twitter:card" 			content="summary" />
<meta name="twitter:site" 			content="&#64;ultra_book" />
<meta name="twitter:title" 		    content="{{ $titrePage }}" />
<meta name="twitter:description"    content="{{ $descriptionPage }}"/>
<meta name="twitter:url" 			content="{{ $canonique }}" />
<meta name="twitter:image" 		    content="{{ $imagePartage }}" />
<meta name="twitter:creator" 		content="&#64;ultra_book" />


<meta name="p:domain_verify" 		content="3141b3250ec1ce665aa24814628e365b"/>



<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<link rel="apple-touch-icon" sizes="57x57" href="/img_front/favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/img_front/favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/img_front/favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/img_front/favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/img_front/favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/img_front/favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/img_front/favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/img_front/favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/img_front/favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="/img_front/favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/img_front/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/img_front/favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/img_front/favicon/favicon-16x16.png">
<link rel="manifest" href="/img_front/favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/img_front/favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">




<!-- SementicUI #2018-->


<link rel="stylesheet"  href="/html_pages_v2018/_/lib/Semantic-UI-CSS-master2.3.1/semantic.min.css">
<link rel="stylesheet"  href="/html_pages_v2018/_/lib/responsive-semantic-ui.min.css">
<style>
    body { background-color: #ebebeb!important;}
</style>


<!-- slides -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.15/jquery.bxslider.min.css"/>
<!-- slider -->
<link rel="stylesheet" href="/html_pages_v2018/_/js/swipebox-master/src/css/swipebox.min.css">

<!-- Icones ubdf #icomoon -->
<link rel="stylesheet" href="/html_pages_v2018/_/font_icon/style.min.css">

<!-- Fontes |Open+Sans|Raleway:300,700|Playfair+Display:400,700,900 -->
<link rel="stylesheet"	href="https://fonts.googleapis.com/css?family=Lato:300,400,700|Source+Sans+Pro:200,300,400,500,600,700">




<!-- commun-->





<!--

	CSS core
	All Less 2019

-->

<link rel="stylesheet" href="/html_pages_v2018/_/css2019/core.css?v=1783005157">









{{-- Le JavaScript du portail est resources/js/portail.js (Alpine), charge
     par layouts/portail. Le front jQuery de 2019 (LABjs, jQuery, Semantic
     UI JS, js2019, Handlebars) est retire : _doc/17_remplacement_jquery.md. --}}






<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-464814-3"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-464814-3');

</script>




