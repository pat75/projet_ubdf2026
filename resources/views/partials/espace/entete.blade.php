@php($creatif = auth()->user())

{{--
 | Barre de tete de l'espace : la meme que celle du portail pour un
 | visiteur — menu replie, logo, filtre par metiers, recherche,
 | memo-book — avec la vignette du createur connecte tout a droite.
 |
 | Les deux boutons « Connexion » et « Creer un book » du portail n'y
 | figurent pas : ils ne veulent rien dire pour quelqu'un qui est deja
 | connecte, et c'est la vignette qui prend leur place.
 |
 | Le balisage est en Tailwind, pas repris du portail : celui-ci est
 | encore en Semantic UI, et les deux feuilles ne peuvent pas cohabiter
 | sur une meme page. Les pictogrammes, eux, sont les memes fichiers.
--}}
<header class="relative z-20 bg-white shadow-[0_2px_10px_rgba(0,0,0,.06)]">
    <div class="mx-auto flex h-21 max-w-[1140px] items-center gap-5 px-5">

        {{-- Menu replie : il ouvre les rubriques du portail. Alpine tient
             l'etat, rien ne part au serveur. --}}
        <div x-data="{ ouvert: false }" class="relative">
            <button type="button" @click="ouvert = ! ouvert" :aria-expanded="ouvert"
                    class="text-[22px] leading-none text-[#222] hover:opacity-70" aria-label="{{ __('Menu') }}">☰</button>

            <div x-show="ouvert" x-cloak x-transition.opacity @click.outside="ouvert = false"
                 class="absolute left-0 top-full mt-3 w-56 rounded-ub bg-white py-2 shadow-ub">
                <a href="{{ lien('home') }}" class="block px-4 py-1.5 hover:text-ub-accent-fonce">{{ __('Accueil') }}</a>
                <a href="{{ lien('annuaire') }}" class="block px-4 py-1.5 hover:text-ub-accent-fonce">{{ __('Annuaire des books') }}</a>
                <a href="{{ lien('recherche') }}" class="block px-4 py-1.5 hover:text-ub-accent-fonce">{{ __('Recherche') }}</a>
                <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener" class="block px-4 py-1.5 hover:text-ub-accent-fonce">{{ __('Mon book') }}</a>
            </div>
        </div>

        <a href="{{ lien('home') }}" class="shrink-0" aria-label="{{ $marque->nom }}">
            <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" class="w-[120px]">
        </a>

        <div class="ml-auto flex items-center gap-5">

            {{-- Filtre par metiers : l'entonnoir du portail, et son
                 libelle a cote sur grand ecran. --}}
            <a href="{{ lien('annuaire') }}" class="flex items-center gap-2 text-[#333] hover:opacity-70" title="{{ __('Filtrer par métiers') }}">
                <svg class="h-5.5 w-5.5" viewBox="-4 0 393 393.99" fill="currentColor" aria-hidden="true">
                    <path d="m368.3125 0h-351.261719c-6.195312-.0117188-11.875 3.449219-14.707031 8.960938-2.871094 5.585937-2.3671875 12.3125 1.300781 17.414062l128.6875 181.28125c.042969.0625.089844.121094.132813.183594 4.675781 6.3125 7.203125 13.957031 7.21875 21.816406v147.796875c-.027344 4.378906 1.691406 8.582031 4.777344 11.6875 3.085937 3.105469 7.28125 4.847656 11.65625 4.847656 2.226562 0 4.425781-.445312 6.480468-1.296875l72.3125-27.574218c6.480469-1.976563 10.78125-8.089844 10.78125-15.453126v-120.007812c.011719-7.855469 2.542969-15.503906 7.214844-21.816406.042969-.0625.089844-.121094.132812-.183594l128.683594-181.289062c3.667969-5.097657 4.171875-11.820313 1.300782-17.40625-2.832032-5.511719-8.511719-8.9726568-14.710938-8.960938z"/>
                </svg>
                <span class="hidden text-[13px] tracking-wide lg:inline">{{ __('MÉTIERS') }}</span>
            </a>

            <a href="{{ lien('recherche') }}" class="text-[#333] hover:opacity-70" title="{{ __('Rechercher') }}">
                <svg class="h-5.5 w-5.5" viewBox="0 0 451 451" fill="currentColor" aria-hidden="true">
                    <path d="M447.05,428l-109.6-109.6c29.4-33.8,47.2-77.9,47.2-126.1C384.65,86.2,298.35,0,192.35,0C86.25,0,0.05,86.3,0.05,192.3s86.3,192.3,192.3,192.3c48.2,0,92.3-17.8,126.1-47.2L428.05,447c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4C452.25,441.8,452.25,433.2,447.05,428z M26.95,192.3c0-91.2,74.2-165.3,165.3-165.3c91.2,0,165.3,74.2,165.3,165.3s-74.1,165.4-165.3,165.4C101.15,357.7,26.95,283.5,26.95,192.3z"/>
                </svg>
                <span class="sr-only">{{ __('Rechercher') }}</span>
            </a>

            <a href="{{ lien('home') }}#memobook" class="text-[22px] leading-none text-[#333] hover:opacity-70" title="{{ __('Mémo-book') }}">
                <span class="fonticon-heart_white" aria-hidden="true"></span>
                <span class="sr-only">{{ __('Mémo-book') }}</span>
            </a>

            <x-dev.switch-marque />

            {{-- La seule difference avec la barre du portail : le createur
                 connecte. --}}
            <a href="{{ route(nom_route('espace.compte')) }}" class="flex items-center gap-3">
                <x-espace.vignette :creatif="$creatif" class="h-11 w-11" />
                <span class="hidden leading-tight sm:block">
                    <span class="block text-[19px] font-semibold">{{ $creatif->fullName() }}</span>
                    <span class="block text-[14px] text-ub-texte3">{{ $creatif->category?->name }}</span>
                </span>
            </a>
        </div>
    </div>
</header>
