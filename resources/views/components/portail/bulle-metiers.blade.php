{{-- Bulle des metiers de l'entete, en Tailwind, pour l'espace creatif et le
     compte visiteur (Dustfolio) : meme rendu et meme comportement que celle
     du portail (partials/modals, .popup_ptf) — fond #1b1c1d, titre leger,
     un pastille de couleur par metier, ouverte au survol de l'icone avec
     400 ms de grace pour traverser l'ecart.

     Le declencheur (l'entonnoir) est le slot. --}}
@php
    $couleurs = [
        'illustrateur' => '#8a8b8a', 'illustrateur-jeunesse' => '#979689', 'graphiste' => '#757c98',
        'directeur-artistique' => '#9999ba', 'digital' => '#6d95ae', 'plasticien' => '#a27365',
        'photographe' => '#bb8372', 'design' => '#af8897', 'architecte' => '#a79fbc',
    ];
@endphp
<div class="relative flex items-center" x-data="{ ouvert: false, minuterie: null }"
     @mouseenter="clearTimeout(minuterie); ouvert = true"
     @mouseleave="minuterie = setTimeout(() => ouvert = false, 400)"
     @keydown.escape.window="ouvert = false">
    {{ $slot }}

    <div x-show="ouvert" x-cloak x-transition.opacity.duration.150ms
         class="absolute left-1/2 z-50 w-[270px] -translate-x-1/2 rounded-[4px] bg-[#1b1c1d] p-6 font-['Source_Sans_3','Source_Sans_Pro',sans-serif] text-white"
         style="top: calc(100% + 36px)">
        <span class="absolute -top-[5px] left-1/2 -ml-[5px] h-2.5 w-2.5 rotate-45 bg-[#1b1c1d]" aria-hidden="true"></span>
        <p class="-mt-[3px] mb-3.5 text-[24px] font-extralight leading-tight">{{ __('Filtres par métiers') }}</p>
        <ul class="m-0 flex list-none flex-col gap-[6px] p-0">
            @foreach (App\Support\Metier::blocsAccueil() as $metier)
                <li>
                    <a href="{{ lien_metier($metier['slug']) }}"
                       class="flex items-center gap-3 bg-white/10 text-[15px] font-extralight leading-[30px] text-white no-underline hover:bg-white/20">
                        <span class="h-[30px] w-[30px] shrink-0" style="background: {{ $couleurs[$metier['slug']] ?? '#8a8b8a' }}"></span>
                        {{ __($metier['titre_bloc']) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
