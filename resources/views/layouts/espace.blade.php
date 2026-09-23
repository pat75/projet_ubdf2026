<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Mon espace')) | {{ $marque->nom }}</title>

    {{-- Les deux fontes de l'espace d'origine : Lato pour les titres,
         Source Sans Pro pour le texte courant. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Source+Sans+3:wght@300;400;600;700&display=swap">

    {{-- La fonte d'icones de l'espace d'origine (font_icon) : c'est elle
         qui porte les pictogrammes exacts du tableau de bord et des
         reseaux sociaux. Elle ne met en forme que les classes
         `fonticon-*` et ne touche a rien d'autre. --}}
    <link rel="stylesheet" href="{{ asset('html_pages_v2018/_/font_icon/style.css') }}">

    @vite(['resources/css/espace.css', 'resources/js/espace.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-ub-fond font-courant text-[15px] text-ub-texte">

@if (session()->has('prise_identite'))
    {{-- Un administrateur regarde l'espace par-dessus l'epaule du createur.
         Bandeau rouge, en haut de chaque page : on ne doit jamais oublier
         qu'on agit sous une autre identite. --}}
    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 bg-red-700 px-4 py-2 text-center text-sm text-white">
        <span>{{ __('Vous êtes connecté en tant que :login.', ['login' => auth()->user()->login]) }}</span>
        <form method="post" action="{{ route('admin.prise-identite.rendre') }}">
            @csrf
            <button type="submit" class="font-semibold underline">{{ __('Revenir au back-office') }}</button>
        </form>
    </div>
@endif

@include('partials.espace.entete')

{{-- La grille d'origine : douze douziemes de contenu, quatre de menu.
     En dessous de 1024 px le menu repasse au-dessus du contenu — l'espace
     d'origine, lui, refusait purement et simplement la tablette. --}}
<div class="mx-auto max-w-[1127px] px-4 pb-16 pt-6">
    <div class="lg:flex lg:items-start lg:gap-8">

        <main class="min-w-0 lg:w-3/4">
            <div class="panneau-espace mb-10 min-h-[730px] px-[4%] pb-[4%] pt-4">
                @if (session('statut'))
                    <div class="mb-6 rounded-ub bg-teal-50 px-4 py-3 text-sm text-teal-800">{{ session('statut') }}</div>
                @endif

                @yield('content')
            </div>
        </main>

        {{-- `sticky` remplace le `position: fixed` de l'original : le menu
             suit le defilement sans sortir de la grille. --}}
        <div class="order-first mb-6 lg:order-last lg:mb-0 lg:w-1/4 lg:sticky lg:top-6">
            @include('partials.espace.menu')
        </div>
    </div>
</div>

@include('partials.espace.pied')

@livewireScripts
</body>
</html>
