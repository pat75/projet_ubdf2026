@php($creatif = auth()->user())

{{--
 | Barre de tete de l'espace, reprise de l'en-tete du portail : le menu
 | replie a gauche, le logo, puis les trois acces du portail et la
 | vignette du createur.
--}}
<header class="sticky top-0 z-20 border-b border-black/5 bg-white">
    <div class="mx-auto flex max-w-[1127px] items-center gap-6 px-4 py-3">

        {{-- Menu replie : il ouvre les rubriques du portail. Alpine tient
             l'etat, rien ne part au serveur. --}}
        <div x-data="{ ouvert: false }" class="relative">
            <button type="button" @click="ouvert = ! ouvert" :aria-expanded="ouvert"
                    class="p-1 text-gray-700 hover:text-black" aria-label="{{ __('Menu') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                </svg>
            </button>

            <div x-show="ouvert" x-cloak x-transition.opacity @click.outside="ouvert = false"
                 class="absolute left-0 top-full mt-2 w-56 rounded-ub bg-white py-2 shadow-ub">
                <a href="{{ lien('home') }}" class="block px-4 py-1.5 hover:text-ub-rouge">{{ __('Accueil') }}</a>
                <a href="{{ lien('annuaire') }}" class="block px-4 py-1.5 hover:text-ub-rouge">{{ __('Annuaire des books') }}</a>
                <a href="{{ lien('recherche') }}" class="block px-4 py-1.5 hover:text-ub-rouge">{{ __('Recherche') }}</a>
                <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener" class="block px-4 py-1.5 hover:text-ub-rouge">{{ __('Mon book') }}</a>
            </div>
        </div>

        <a href="{{ lien('home') }}" class="shrink-0" aria-label="{{ $marque->nom }}">
            <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" class="h-9 w-auto">
        </a>

        <div class="ml-auto flex items-center gap-5 text-gray-700">
            <a href="{{ lien('annuaire') }}" class="hover:text-black" title="{{ __('Filtrer les books') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18l-7 8.5v6l-4 2v-8z"/>
                </svg>
                <span class="sr-only">{{ __('Filtrer les books') }}</span>
            </a>
            <a href="{{ lien('recherche') }}" class="hover:text-black" title="{{ __('Rechercher') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="6.5"/><path stroke-linecap="round" d="m16 16 4.5 4.5"/>
                </svg>
                <span class="sr-only">{{ __('Rechercher') }}</span>
            </a>
            <a href="{{ lien('home') }}#memobook" class="hover:text-black" title="{{ __('Mon mémo-book') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.3-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.7-7 9-7 9z"/>
                </svg>
                <span class="sr-only">{{ __('Mon mémo-book') }}</span>
            </a>

            <a href="{{ route(nom_route('espace.compte')) }}" class="flex items-center gap-3">
                <x-espace.vignette :creatif="$creatif" class="h-11 w-11" />
                <span class="hidden text-right leading-tight sm:block">
                    <span class="block font-titre font-bold text-black">{{ $creatif->fullName() }}</span>
                    <span class="block text-[13px] text-gray-500">{{ $creatif->category?->name }}</span>
                </span>
            </a>
        </div>
    </div>
</header>
