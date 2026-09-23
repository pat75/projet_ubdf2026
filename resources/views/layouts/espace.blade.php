<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Mon espace')) | {{ $marque->nom }}</title>
    <script>
        try { if (localStorage.getItem('espace_dark') === '1') document.documentElement.classList.add('dark'); } catch (e) {}
    </script>
    @vite(['resources/css/espace.css', 'resources/js/espace.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
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
<div class="md:flex">
    <aside class="border-b border-gray-200 bg-white md:min-h-screen md:w-60 md:border-b-0 md:border-r dark:border-gray-600 dark:bg-gray-800">
        <div class="flex items-center justify-between px-4 py-4">
            <a href="{{ lien('home') }}" class="text-lg font-light">{{ $marque->nom }}</a>
            <x-dev.switch-marque />
        </div>
        <nav class="flex gap-1 overflow-x-auto px-2 pb-3 md:flex-col md:overflow-visible">
            <x-espace.nav-lien route="espace">{{ __('Tableau de bord') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.galeries">{{ __('Galeries') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.pages">{{ __('Pages') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.design">{{ __('Habillage') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.diffusion">{{ __('Diffusion') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.statistiques">{{ __('Statistiques') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.messages">{{ __('Messages') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.compte">{{ __('Mon compte') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.formule">{{ __('Formule') }}</x-espace.nav-lien>
            <x-espace.nav-lien route="espace.exporter">{{ __('Exporter') }}</x-espace.nav-lien>
        </nav>
        <div class="flex items-center gap-3 px-4 pb-4">
            <a href="{{ auth()->user()->bookUrl() }}" class="text-sm text-gray-600 underline dark:text-gray-300" target="_blank" rel="noopener">{{ __('Voir mon book') }}</a>
            <form method="post" action="{{ route('deconnexion') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 underline dark:text-gray-300">{{ __('Se déconnecter') }}</button>
            </form>
        </div>
    </aside>

    <main class="flex-1 px-4 py-6 md:px-10 md:py-10">
        @if (session('statut'))
            <div class="mb-6 rounded-md bg-teal-50 px-4 py-3 text-sm text-teal-800 dark:bg-teal-900 dark:text-teal-100">{{ session('statut') }}</div>
        @endif

        @yield('content')
    </main>
</div>
@livewireScripts
</body>
</html>
