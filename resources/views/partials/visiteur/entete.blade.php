{{-- Barre de tete du compte visiteur : celle de l'espace
     (partials/espace/entete), avec l'adresse du visiteur a la place du
     bloc du createur. --}}
<header class="relative z-20 h-[85px] bg-white shadow-[0_7px_20px_rgba(0,0,0,0.2)]">
    <div class="mx-4 flex h-full items-center pl-1 pr-2.5 min-[768px]:mx-auto min-[768px]:w-[723px] min-[992px]:w-[933px] min-[1200px]:w-[1127px]">

        <div x-data>
            <button type="button" @click="$store.menu.basculer()" :aria-expanded="$store.menu.ouvert"
                    class="w-[21px] cursor-pointer text-[24px] leading-none text-black" aria-label="{{ __('Menu') }}">☰</button>
        </div>

        <a href="{{ lien('home') }}" class="ml-[11px] shrink-0" aria-label="{{ $marque->nom }}">
            <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" class="w-[84px] min-[900px]:w-[120px]">
        </a>

        <div class="ml-auto flex min-w-0 items-center gap-2.5">
            <a href="{{ lien('recherche') }}" class="text-[#444] hover:opacity-70" title="{{ __('Rechercher') }}">
                <svg class="h-6 w-6" viewBox="0 0 451 451" fill="currentColor" aria-hidden="true">
                    <path d="M447.05,428l-109.6-109.6c29.4-33.8,47.2-77.9,47.2-126.1C384.65,86.2,298.35,0,192.35,0C86.25,0,0.05,86.3,0.05,192.3s86.3,192.3,192.3,192.3c48.2,0,92.3-17.8,126.1-47.2L428.05,447c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4C452.25,441.8,452.25,433.2,447.05,428z M26.95,192.3c0-91.2,74.2-165.3,165.3-165.3c91.2,0,165.3,74.2,165.3,165.3s-74.1,165.4-165.3,165.4C101.15,357.7,26.95,283.5,26.95,192.3z"/>
                </svg>
                <span class="sr-only">{{ __('Rechercher') }}</span>
            </a>

            <a href="{{ lien('visiteur.tableau') }}" class="hidden min-w-0 truncate text-[15px] font-semibold text-ub-texte hover:underline sm:block">
                {{ auth('visitor')->user()->email }}
            </a>
        </div>
    </div>
</header>
