{{--
    Mode edition du book, pour son createur connecte sur le sous-domaine
    (EditionBookController) : barre, panneau des reglages, message.
    Comportement : resources/js/book/edition.js.
    Interface de l'outil, pas du book : couleurs fixes, independantes du theme.
--}}
@php
    $pref = $vue->pref;
    $choix = fn (string $valeur, string $courant) => $valeur === $courant
        ? 'border-black bg-black text-white'
        : 'border-gray-300 bg-white text-gray-800 hover:border-black';
    $entete = (int) ($pref->header ?? 0);
    $champ = 'w-full border border-gray-300 bg-white px-2.5 py-1.5 text-[13px] text-gray-900 outline-none focus:border-black';
@endphp

{{-- Barre du mode edition --}}
{{-- Sur grand ecran, ces boutons sont dans le panneau ; la barre flottante
     ne sert qu'en mobile. --}}
<div x-show="! $store.edition.cadre"
     class="lg:hidden fixed left-1/2 top-3 z-[80] flex -translate-x-1/2 items-center gap-1 rounded-full bg-black/90 p-1 font-sans text-[13px] text-white shadow-lg">
    <span class="px-3 max-sm:hidden" x-show="$store.edition.actif">{{ __('Mode édition') }}</span>
    <button type="button" x-show="$store.edition.actif" @click="$store.edition.panneau = ! $store.edition.panneau"
            class="lg:hidden rounded-full px-3 py-1.5 hover:bg-white/15" :class="$store.edition.panneau && 'bg-white/20'">{{ __('Réglages') }}</button>
    <button type="button" @click="$store.edition.actif = ! $store.edition.actif; $store.edition.panneau = false"
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 hover:bg-white/15">
        <svg x-show="! $store.edition.actif" x-cloak class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
        <span x-text="$store.edition.actif ? @js(__('Aperçu')) : @js(__('Modifier'))"></span>
    </button>
    <span class="h-4 w-px bg-white/60" aria-hidden="true"></span>
    <a href="{{ $vue->urlEspace() }}" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 hover:bg-white/15"><svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>{{ __('Mon espace') }}</a>
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
        <section x-data="blocReglage('fond')">
            <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
                <span>{{ __('Fond') }}</span>
                <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
                <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
            </button>
            <div x-show="ouvert" x-cloak class="mt-2">
            <div class="grid grid-cols-3 gap-2">
                @foreach (['theme_white' => __('Blanc'), 'theme_gris' => __('Gris'), 'theme_black' => __('Noir')] as $valeur => $nom)
                    <button type="button" @click="regler('theme', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->couleur()) }}">{{ $nom }}</button>
                @endforeach
            </div>
            </div>
        </section>

        <section x-data="blocReglage('marge')">
            <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
                <span>{{ __('Marge autour des visuels') }}</span>
                <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
                <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
            </button>
            <div x-show="ouvert" x-cloak class="mt-2">
            <div class="grid grid-cols-3 gap-2">
                @foreach (['small' => __('Aucune'), 'normal' => __('Normale'), 'large' => __('Large')] as $valeur => $nom)
                    <button type="button" @click="regler('visuel_size', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->tailleVisuels()) }}">{{ $nom }}</button>
                @endforeach
            </div>
            </div>
        </section>

        <section x-data="blocReglage('entete')">
            <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
                <span>{{ __('Visuel de profil') }}</span>
                <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
                <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
            </button>
            <div x-show="ouvert" x-cloak class="mt-2">
            <div class="grid grid-cols-6 gap-1.5">
                <button type="button" @click="regler('header', 0)" title="{{ __('Aucun') }}"
                        class="flex aspect-square items-center justify-center border {{ $choix('0', (string) $entete) }}">—</button>
                <button type="button" @click="regler('header', 1)" title="{{ __('Photo') }}"
                        class="flex aspect-square items-center justify-center overflow-hidden border p-1 {{ $choix('1', (string) $entete) }}">
                    <img src="{{ $vue->photo() }}" alt="" class="size-full rounded-full object-cover">
                </button>
                @foreach (\App\Services\Book\VueUltra2020::ICONES as $i => $trace)
                    <button type="button" @click="regler('header', {{ $i + 2 }})"
                            class="flex aspect-square items-center justify-center border p-1 {{ $choix((string) ($i + 2), (string) $entete) }}">
                        <svg viewBox="0 0 24 24" class="size-full fill-current" aria-hidden="true">{!! $trace !!}</svg>
                    </button>
                @endforeach
            </div>
            <div class="mt-2 grid grid-cols-3 gap-2">
                @foreach (['S', 'M', 'L'] as $valeur)
                    <button type="button" @click="regler('header_size', @js($valeur))" class="border px-2 py-1.5 {{ $choix($valeur, $vue->tailleEntete()) }}">{{ $valeur }}</button>
                @endforeach
            </div>
            <p class="mt-2 text-gray-500">{{ __('La photo se change depuis l’habillage de votre espace.') }}</p>
            </div>
        </section>

        <section class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">{{ __('Curseur animé') }}</h3>
            <button type="button" role="switch" aria-checked="{{ $vue->curseur() ? 'true' : 'false' }}"
                    @click="regler('cursor', @js($vue->curseur() ? 'false' : 'true'))"
                    class="flex h-6 w-11 rounded-full p-0.5 transition-colors {{ $vue->curseur() ? 'justify-end bg-black' : 'justify-start bg-gray-300' }}">
                <span class="size-5 rounded-full bg-white shadow"></span>
            </button>
        </section>

        <section x-data="blocReglage('menu')">
            <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
                <span>{{ __('Intitulés du menu') }}</span>
                <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
                <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
            </button>
            <div x-show="ouvert" x-cloak class="mt-2">
            <div class="flex flex-col gap-2">
                @foreach (['name_portfolio' => 'Portfolio', 'name_page' => 'Bio', 'name_contact' => 'Contact'] as $cle => $defaut)
                    <input type="text" value="{{ $vue->lien($cle, $defaut) }}" maxlength="40"
                           @change="regler('nav_link.{{ $cle }}', $el.value)" class="{{ $champ }}">
                @endforeach
            </div>
            </div>
        </section>

        <section x-data="blocReglage('reseaux')">
            <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
                <span>{{ __('Réseaux sociaux') }}</span>
                <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
                <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
            </button>
            <div x-show="ouvert" x-cloak class="mt-2">
            <div class="flex flex-col gap-2">
                @foreach (\App\Services\Book\VueUltra2020::RESEAUX as $reseau)
                    <label class="flex items-center gap-2">
                        <span class="w-20 shrink-0 capitalize text-gray-600">{{ $reseau }}</span>
                        <input type="text" value="{{ $pref->social_link->{'link_'.$reseau} ?? '' }}" placeholder="https://…"
                               @change="regler('social_link.link_{{ $reseau }}', $el.value)" class="{{ $champ }}">
                    </label>
                @endforeach
            </div>
            </div>
        </section>

        <section x-data="blocReglage('pied')">
            <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
                <span>{{ __('Pied de page') }}</span>
                <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
                <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
            </button>
            <div x-show="ouvert" x-cloak class="mt-2">
            <textarea rows="2" @change="regler('footer', $el.value)" class="{{ $champ }} font-mono">{{ $pref->footer ?? '' }}</textarea>
            </div>
        </section>

        <section x-data="blocReglage('contact')">
            <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
                <span>{{ __('Texte sous le formulaire de contact') }}</span>
                <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
                <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
            </button>
            <div x-show="ouvert" x-cloak class="mt-2">
            <textarea rows="3" @change="regler('contact_footer', $el.value)" class="{{ $champ }}">{{ $pref->contact_footer ?? '' }}</textarea>
            </div>
        </section>

        <section x-data="blocReglage('css')">
            <button type="button" @click="basculer()" :aria-expanded="ouvert" class="flex w-full items-center justify-between text-left font-semibold text-gray-900">
                <span>{{ __('CSS expert') }}</span>
                <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0" />
                <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0" />
            </button>
            <div x-show="ouvert" x-cloak class="mt-2">
            <textarea rows="5" @change="regler('expert_css', $el.value)" spellcheck="false" class="{{ $champ }} font-mono text-[12px]">{{ $pref->expert_css ?? '' }}</textarea>
            </div>
        </section>

        <p class="text-gray-500">{{ __('Titre, description, intitulés du menu, « les projets » et titre du contact se modifient directement sur la page : cliquez dessus.') }}</p>
    </div>
</aside>

{{-- Grand ecran : apercu, espace, formats d'apercu. Hors du
     panneau, pour rester au meme endroit quand l'apercu visiteur l'efface. --}}
<div x-show="! $store.edition.cadre"
     :class="$store.edition.actif ? 'border-b border-r' : 'border shadow-lg'"
     class="fixed left-0 top-14 z-[76] flex h-[53px] w-full max-w-sm items-center gap-2 border-gray-200 bg-white px-5 font-sans text-[13px] max-lg:hidden">
    <div class="flex items-center gap-1 rounded-full bg-black p-1 text-[12px] text-white">
        <button type="button" @click="$store.edition.actif = ! $store.edition.actif"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 hover:bg-white/15">
            <svg x-show="! $store.edition.actif" x-cloak class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            <span x-text="$store.edition.actif ? @js(__('Aperçu')) : @js(__('Modifier'))"></span>
        </button>
        <span class="h-4 w-px bg-white/60" aria-hidden="true"></span>
        <a href="{{ $vue->urlEspace() }}" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 hover:bg-white/15"><svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>{{ __('Mon espace') }}</a>
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
