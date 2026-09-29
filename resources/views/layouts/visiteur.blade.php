<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('title', __('Mon compte')) | {{ $marque->nom }}</title>

    <link rel="icon" type="image/png" sizes="32x32" href="/img_front/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img_front/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img_front/favicon/apple-icon-180x180.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('html_pages_v2018/_/font_icon/style.css') }}">

    @vite(['resources/css/espace.css', 'resources/js/espace.js'])
    @livewireStyles
</head>
{{-- Compte visiteur (guard `visitor`) : la charte de l'espace, sans ses
     rubriques de creatif. Le menu passe au-dessus du contenu sous 900 px. --}}
<body class="min-h-screen bg-ub-fond font-courant text-ub-texte antialiased">

@include('partials.visiteur.entete')

<main class="mx-auto flex max-w-[1140px] flex-wrap items-start gap-8 px-4 pb-18 pt-8 min-[900px]:px-5 min-[900px]:pt-11">

    <div class="order-2 flex min-w-0 flex-[1_1_560px] flex-col gap-5 min-[900px]:order-1">
        @if (session('statut'))
            <div class="self-start rounded-ub bg-[#e7f5ec] px-3.5 py-2 text-[14px] text-[#1f7a4a]">✓ {{ session('statut') }}</div>
        @endif

        @yield('content')
    </div>

    <aside class="order-1 flex w-full flex-col gap-4 min-[900px]:sticky min-[900px]:top-6 min-[900px]:order-2 min-[900px]:w-auto min-[900px]:min-w-60 min-[900px]:flex-[0_1_280px]">
        @include('partials.visiteur.menu')
    </aside>
</main>

@include('partials.espace.pied')

<x-portail.menu-plein-ecran />

@livewireScripts
</body>
</html>
