{{--
    Mode edition du book, pour son createur connecte sur le sous-domaine
    (EditionBookController) : barre, panneau des reglages, message.
    Comportement : resources/js/book/edition.js.
    Interface de l'outil, pas du book : couleurs fixes, independantes du theme.
    Commun aux modeles convertis : les reglages propres a chacun sont dans
    book/<dossier>/_reglages ($choix, $champ et $pref y sont disponibles).
--}}
@php
    $pref = $vue->pref;
    $choix = fn (string $valeur, string $courant) => $valeur === $courant
        ? 'border-black bg-black text-white'
        : 'border-gray-300 bg-white text-gray-800 hover:border-black';
    $champ = 'w-full border border-gray-300 bg-white px-2.5 py-1.5 text-[13px] text-gray-900 outline-none focus:border-black';
@endphp

{{-- Barre du mode edition --}}
{{-- Sur grand ecran, ces boutons sont dans le panneau ; la barre flottante
     ne sert qu'en mobile. --}}
<div x-show="! $store.edition.cadre"
     class="lg:hidden fixed inset-x-0 top-0 z-[80] flex h-12 items-center gap-1 bg-black pr-1 font-sans text-[13px] text-white shadow-lg">
    {{-- Retour a l'espace : carre colle au bord gauche, fleche seule (comme sur grand ecran). --}}
    <a href="{{ $vue->urlEspace() }}" title="{{ __('Mon espace') }}"
       class="flex h-full w-12 shrink-0 items-center justify-center border-r border-white/20 hover:bg-white/15">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>
        <span class="sr-only">{{ __('Mon espace') }}</span>
    </a>
    <span class="px-3 max-sm:hidden" x-show="$store.edition.actif">{{ __('Mode édition') }}</span>
    <button type="button" x-show="$store.edition.actif" @click="$store.edition.panneau = ! $store.edition.panneau"
            class="lg:hidden rounded-full px-3 py-1.5 hover:bg-white/15" :class="$store.edition.panneau && 'bg-white/20'">{{ __('Réglages') }}</button>
    <button type="button" @click="$store.edition.actif = ! $store.edition.actif; $store.edition.panneau = false"
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 hover:bg-white/15">
        <svg x-show="! $store.edition.actif" x-cloak class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
        <span x-text="$store.edition.actif ? @js(__('Aperçu')) : @js(__('Modifier'))"></span>
    </button>
</div>

{{-- Message d'enregistrement --}}
<div x-show="$store.edition.message" x-cloak x-transition.opacity
     class="fixed bottom-5 left-1/2 z-[80] -translate-x-1/2 rounded-full px-4 py-2 font-sans text-[13px] text-white shadow-lg"
     :class="$store.edition.erreur ? 'bg-red-600' : 'bg-emerald-600'" x-text="$store.edition.message"></div>

{{-- Rechargement apres un reglage : voile + loader sur le portfolio --}}
<div x-show="$store.edition.chargement" x-cloak x-transition.opacity
     class="fixed inset-0 z-[70] flex items-center justify-center bg-white/60 backdrop-blur-[1px] lg:left-[24rem]">
    <svg class="size-10 animate-spin text-black" fill="none" viewBox="0 0 24 24" aria-label="{{ __('Actualisation…') }}">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
</div>

{{-- Apercu mobile / tablette : le book dans un cadre a la largeur de
     l'appareil (ses propres points de rupture s'appliquent), dans la colonne
     de droite. Le cadre charge la page en apercu visiteur (edition.js). --}}
<template x-if="! $store.edition.cadre && $store.edition.format !== 'desktop'">
    <div x-data="apercuAppareil" x-ref="zone" @resize.window="mesurer()" x-effect="$store.edition.actif; $nextTick(() => mesurer())"
         :class="$store.edition.actif && 'lg:left-[24rem]'" class="fixed inset-0 z-[65] hidden items-center justify-center bg-gray-200 lg:flex">
        {{-- Contour d'appareil a sa taille reelle, reduit a l'echelle de la
             colonne : la page garde la largeur de l'appareil. --}}
        <div class="relative shrink-0" :style="`width:${total.l * echelle}px;height:${total.h * echelle}px`">
            <div :style="`width:${ecran.l}px;height:${ecran.h}px;border-width:${bord}px;transform:scale(${echelle})`"
                 :class="mobile ? 'rounded-[3rem]' : 'rounded-[2.25rem]'"
                 class="absolute left-0 top-0 box-content origin-top-left overflow-hidden border-solid border-gray-900 bg-gray-900 shadow-2xl ring-1 ring-gray-700">
                <span x-show="mobile" class="absolute left-1/2 top-0 z-10 h-6 w-28 -translate-x-1/2 rounded-b-2xl bg-gray-900"></span>
                <iframe src="{{ request()->fullUrl() }}" title="{{ __('Aperçu') }}"
                        class="size-full bg-white" :class="mobile ? 'rounded-[2.1rem]' : 'rounded-[.9rem]'"></iframe>
            </div>
        </div>
    </div>
</template>

{{-- Panneau des reglages --}}
{{-- Grand ecran : panneau permanent a gauche (rendu cote serveur, il ne
     clignote pas au rechargement). Mobile : ouvert par « Réglages ». --}}
<aside x-data="reglagesBook" x-ref="panneau"
       :class="{ 'max-lg:!flex': $store.edition.panneau, 'lg:!hidden': ! $store.edition.actif }"
       @keydown.escape.window="$store.edition.panneau = false"
       class="fixed inset-y-0 left-0 z-[75] flex max-lg:hidden w-full max-w-sm flex-col overflow-y-auto border-r border-gray-200 bg-white font-sans text-[13px] text-gray-800 shadow-2xl lg:shadow-none">
    <div class="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 px-5">
        <h2 class="flex items-center gap-2 text-[15px] font-semibold text-gray-900">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            {{ __('Réglages du book') }}
        </h2>
        <button type="button" @click="$store.edition.panneau = false" class="p-1 lg:hidden text-gray-500 hover:text-black" aria-label="{{ __('Fermer') }}">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>

    {{-- Place de la barre d'outils, posee par-dessus (voir plus bas). --}}
    <div class="h-[53px] shrink-0 max-lg:hidden"></div>

    <div x-show="$store.edition.actif" class="flex flex-col divide-y divide-gray-200 px-5 py-5 [&>*]:py-5 [&>*:first-child]:pt-0 [&>*:last-child]:pb-0">
        @include('book.'.$vue->dossier().'._reglages')
    </div>
</aside>

{{-- Grand ecran : apercu, espace, formats d'apercu. Hors du
     panneau, pour rester au meme endroit quand l'apercu visiteur l'efface. --}}
<div x-show="! $store.edition.cadre"
     :class="$store.edition.actif ? 'border-b border-r' : 'border shadow-lg'"
     class="fixed left-0 top-14 z-[76] flex h-[53px] w-full max-w-sm items-center gap-3 border-gray-200 bg-white pr-5 font-sans text-[13px] max-lg:hidden">
    {{-- Retour a l'espace : carre colle au bord gauche, fleche seule. --}}
    <a href="{{ $vue->urlEspace() }}" title="{{ __('Mon espace') }}"
       class="flex h-full w-[53px] shrink-0 items-center justify-center bg-black text-white hover:bg-gray-800">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>
        <span class="sr-only">{{ __('Mon espace') }}</span>
    </a>
    <div class="flex items-center gap-1 rounded-full bg-black p-1 text-[12px] text-white">
        <button type="button" @click="$store.edition.actif = ! $store.edition.actif"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 hover:bg-white/15">
            <svg x-show="! $store.edition.actif" x-cloak class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            <span x-text="$store.edition.actif ? @js(__('Aperçu')) : @js(__('Modifier'))"></span>
        </button>
    </div>
    <div class="ml-auto flex items-center gap-0.5" role="group" aria-label="{{ __('Format d’aperçu') }}">
        @foreach ([
            'mobile' => [__('Mobile'), '<rect x="7" y="2.5" width="10" height="19" rx="2"/><path d="M11 18.5h2"/>'],
            'tablette' => [__('Tablette'), '<rect x="4" y="2.5" width="16" height="19" rx="2"/><path d="M11 18.5h2"/>'],
            'desktop' => [__('Ordinateur'), '<rect x="2.5" y="4" width="19" height="12.5" rx="1.5"/><path d="M8 20.5h8M12 16.5v4"/>'],
        ] as $format => [$nom, $trace])
            <button type="button" @click="$store.edition.choisirFormat(@js($format))" title="{{ $nom }}"
                    :aria-pressed="$store.edition.format === @js($format)"
                    :class="$store.edition.format === @js($format) ? 'bg-black text-white' : 'text-gray-500 hover:text-black'"
                    class="p-1.5">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">{!! $trace !!}</svg>
                <span class="sr-only">{{ $nom }}</span>
            </button>
        @endforeach
        <button type="button" x-show="$store.edition.format === 'tablette'" x-cloak @click="$store.edition.pivoter()"
                :title="$store.edition.paysage ? @js(__('Tablette verticale')) : @js(__('Tablette horizontale'))"
                class="ml-1 border-l border-gray-200 p-1.5 pl-2 text-gray-500 hover:text-black">
            <svg class="size-5 transition-transform duration-300" :class="$store.edition.paysage && 'rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></svg>
            <span class="sr-only" x-text="$store.edition.paysage ? @js(__('Tablette verticale')) : @js(__('Tablette horizontale'))"></span>
        </button>
    </div>
</div>
