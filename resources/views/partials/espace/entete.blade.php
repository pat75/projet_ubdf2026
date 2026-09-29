@php($creatif = auth()->user())

{{--
 | Barre de tete de l'espace : la copie de celle du portail
 | (#menu-top-fixed dans partials/modals), mesuree sur la page d'accueil
 | — 85 px de haut, le conteneur Semantic (1127 / 933 / 723 px), les
 | pictogrammes de 24 px en #444, la meme ombre.
 |
 | Le balisage est en Tailwind : le portail est en Semantic UI, et les
 | deux feuilles ne cohabitent pas sur une meme page. Le bloc du createur
 | connecte, lui, est le meme composant des deux cotes.
--}}
<header class="relative z-20 h-[85px] bg-white shadow-[0_7px_20px_rgba(0,0,0,0.2)]">
    <div class="mx-4 flex h-full items-center pl-1 pr-2.5 min-[768px]:mx-auto min-[768px]:w-[723px] min-[992px]:w-[933px] min-[1200px]:w-[1127px]">

        {{-- Burger : le menu plein ecran du portail, sur fond noir, comme
             pour un visiteur (<x-portail.menu-plein-ecran>, layouts/espace). --}}
        <div x-data>
            <button type="button" @click="$store.menu.basculer()" :aria-expanded="$store.menu.ouvert"
                    class="w-[21px] cursor-pointer text-[24px] leading-none text-black" aria-label="{{ __('Menu') }}">☰</button>
        </div>

        <a href="{{ lien('home') }}" class="ml-[11px] shrink-0" aria-label="{{ $marque->nom }}">
            <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" class="w-[84px] min-[900px]:w-[120px]">
        </a>

        {{-- Bascule UB / DF (developpement seulement), a droite du logo. --}}
        <div class="ml-4">
            <x-dev.switch-marque />
        </div>

        <div class="ml-auto flex items-center gap-2.5">

            {{-- Filtre par metiers : l'entonnoir evide du portail. --}}
            <a href="{{ lien('annuaire') }}" class="mr-[5px] text-[#444] hover:opacity-70" title="{{ __('Filtrer par métiers') }}">
                <svg class="h-6 w-6" viewBox="-4 0 393 393.99003" fill="currentColor" aria-hidden="true">
                    <path d="m368.3125 0h-351.261719c-6.195312-.0117188-11.875 3.449219-14.707031 8.960938-2.871094 5.585937-2.3671875 12.3125 1.300781 17.414062l128.6875 181.28125c.042969.0625.089844.121094.132813.183594 4.675781 6.3125 7.203125 13.957031 7.21875 21.816406v147.796875c-.027344 4.378906 1.691406 8.582031 4.777344 11.6875 3.085937 3.105469 7.28125 4.847656 11.65625 4.847656 2.226562 0 4.425781-.445312 6.480468-1.296875l72.3125-27.574218c6.480469-1.976563 10.78125-8.089844 10.78125-15.453126v-120.007812c.011719-7.855469 2.542969-15.503906 7.214844-21.816406.042969-.0625.089844-.121094.132812-.183594l128.683594-181.289062c3.667969-5.097657 4.171875-11.820313 1.300782-17.40625-2.832032-5.511719-8.511719-8.9726568-14.710938-8.960938zm-131.53125 195.992188c-7.1875 9.753906-11.074219 21.546874-11.097656 33.664062v117.578125l-66 25.164063v-142.742188c-.023438-12.117188-3.910156-23.910156-11.101563-33.664062l-124.933593-175.992188h338.070312zm0 0"/>
                </svg>
                <span class="sr-only">{{ __('Filtrer par métiers') }}</span>
            </a>

            <a href="{{ lien('recherche') }}" class="text-[#444] hover:opacity-70" title="{{ __('Rechercher') }}">
                <svg class="h-6 w-6" viewBox="0 0 451 451" fill="currentColor" aria-hidden="true">
                    <path d="M447.05,428l-109.6-109.6c29.4-33.8,47.2-77.9,47.2-126.1C384.65,86.2,298.35,0,192.35,0C86.25,0,0.05,86.3,0.05,192.3s86.3,192.3,192.3,192.3c48.2,0,92.3-17.8,126.1-47.2L428.05,447c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4C452.25,441.8,452.25,433.2,447.05,428z M26.95,192.3c0-91.2,74.2-165.3,165.3-165.3c91.2,0,165.3,74.2,165.3,165.3s-74.1,165.4-165.3,165.4C101.15,357.7,26.95,283.5,26.95,192.3z"/>
                </svg>
                <span class="sr-only">{{ __('Rechercher') }}</span>
            </a>

            <x-barre.createur :creatif="$creatif" />
        </div>
    </div>
</header>
