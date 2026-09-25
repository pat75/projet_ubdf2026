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
<div class="fixed left-1/2 top-3 z-[80] flex -translate-x-1/2 items-center gap-1 rounded-full bg-black/90 p-1 font-sans text-[13px] text-white shadow-lg">
    <span class="px-3 max-sm:hidden" x-show="$store.edition.actif">{{ __('Mode édition') }}</span>
    <button type="button" x-show="$store.edition.actif" @click="$store.edition.panneau = ! $store.edition.panneau"
            class="rounded-full px-3 py-1.5 hover:bg-white/15" :class="$store.edition.panneau && 'bg-white/20'">{{ __('Réglages') }}</button>
    <button type="button" @click="$store.edition.actif = ! $store.edition.actif; $store.edition.panneau = false"
            class="rounded-full px-3 py-1.5 hover:bg-white/15"
            x-text="$store.edition.actif ? @js(__('Aperçu visiteur')) : @js(__('Modifier'))"></button>
    <a href="{{ $vue->urlEspace() }}" class="rounded-full px-3 py-1.5 hover:bg-white/15">{{ __('Mon espace') }}</a>
</div>

{{-- Message d'enregistrement --}}
<div x-show="$store.edition.message" x-cloak x-transition.opacity
     class="fixed bottom-5 left-1/2 z-[80] -translate-x-1/2 rounded-full px-4 py-2 font-sans text-[13px] text-white shadow-lg"
     :class="$store.edition.erreur ? 'bg-red-600' : 'bg-emerald-600'" x-text="$store.edition.message"></div>

{{-- Panneau des reglages --}}
<aside x-data="reglagesBook" x-show="$store.edition.panneau" x-cloak
       x-transition:enter="transition duration-300" x-transition:enter-start="translate-x-full" x-transition:leave="transition duration-200" x-transition:leave-end="translate-x-full"
       @keydown.escape.window="$store.edition.panneau = false"
       class="fixed inset-y-0 right-0 z-[75] flex w-full max-w-sm flex-col overflow-y-auto bg-white font-sans text-[13px] text-gray-800 shadow-2xl">
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
        <h2 class="text-[15px] font-semibold text-gray-900">{{ __('Réglages du book') }}</h2>
        <button type="button" @click="$store.edition.panneau = false" class="p-1 text-gray-500 hover:text-black" aria-label="{{ __('Fermer') }}">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <div class="flex flex-col gap-6 px-5 py-5">
        <section>
            <h3 class="mb-2 font-semibold text-gray-900">{{ __('Fond') }}</h3>
            <div class="grid grid-cols-3 gap-2">
                @foreach (['theme_white' => __('Blanc'), 'theme_gris' => __('Gris'), 'theme_black' => __('Noir')] as $valeur => $nom)
                    <button type="button" @click="regler('theme', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->couleur()) }}">{{ $nom }}</button>
                @endforeach
            </div>
        </section>

        <section>
            <h3 class="mb-2 font-semibold text-gray-900">{{ __('Marge autour des visuels') }}</h3>
            <div class="grid grid-cols-3 gap-2">
                @foreach (['small' => __('Aucune'), 'normal' => __('Normale'), 'large' => __('Large')] as $valeur => $nom)
                    <button type="button" @click="regler('visuel_size', @js($valeur))" class="border px-2 py-2 {{ $choix($valeur, $vue->tailleVisuels()) }}">{{ $nom }}</button>
                @endforeach
            </div>
        </section>

        <section>
            <h3 class="mb-2 font-semibold text-gray-900">{{ __('En-tête') }}</h3>
            <div class="grid grid-cols-5 gap-2">
                <button type="button" @click="regler('header', 0)" title="{{ __('Aucun') }}"
                        class="flex aspect-square items-center justify-center border {{ $choix('0', (string) $entete) }}">—</button>
                <button type="button" @click="regler('header', 1)" title="{{ __('Photo') }}"
                        class="flex aspect-square items-center justify-center overflow-hidden border p-1 {{ $choix('1', (string) $entete) }}">
                    <img src="{{ $vue->photo() }}" alt="" class="size-full rounded-full object-cover">
                </button>
                @foreach (\App\Services\Book\VueUltra2020::ICONES as $i => $trace)
                    <button type="button" @click="regler('header', {{ $i + 2 }})"
                            class="flex aspect-square items-center justify-center border p-2 {{ $choix((string) ($i + 2), (string) $entete) }}">
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
        </section>

        <section class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">{{ __('Curseur animé') }}</h3>
            <button type="button" role="switch" aria-checked="{{ $vue->curseur() ? 'true' : 'false' }}"
                    @click="regler('cursor', @js($vue->curseur() ? 'false' : 'true'))"
                    class="flex h-6 w-11 rounded-full p-0.5 transition-colors {{ $vue->curseur() ? 'justify-end bg-black' : 'justify-start bg-gray-300' }}">
                <span class="size-5 rounded-full bg-white shadow"></span>
            </button>
        </section>

        <section>
            <h3 class="mb-2 font-semibold text-gray-900">{{ __('Intitulés du menu') }}</h3>
            <div class="flex flex-col gap-2">
                @foreach (['name_portfolio' => 'Portfolio', 'name_page' => 'Bio', 'name_contact' => 'Contact'] as $cle => $defaut)
                    <input type="text" value="{{ $vue->lien($cle, $defaut) }}" maxlength="40"
                           @change="regler('nav_link.{{ $cle }}', $el.value)" class="{{ $champ }}">
                @endforeach
            </div>
        </section>

        <section>
            <h3 class="mb-2 font-semibold text-gray-900">{{ __('Réseaux sociaux') }}</h3>
            <div class="flex flex-col gap-2">
                @foreach (\App\Services\Book\VueUltra2020::RESEAUX as $reseau)
                    <label class="flex items-center gap-2">
                        <span class="w-20 shrink-0 capitalize text-gray-600">{{ $reseau }}</span>
                        <input type="text" value="{{ $pref->social_link->{'link_'.$reseau} ?? '' }}" placeholder="https://…"
                               @change="regler('social_link.link_{{ $reseau }}', $el.value)" class="{{ $champ }}">
                    </label>
                @endforeach
            </div>
        </section>

        <section>
            <h3 class="mb-2 font-semibold text-gray-900">{{ __('Pied de page') }}</h3>
            <textarea rows="2" @change="regler('footer', $el.value)" class="{{ $champ }} font-mono">{{ $pref->footer ?? '' }}</textarea>
        </section>

        <section>
            <h3 class="mb-2 font-semibold text-gray-900">{{ __('Texte sous le formulaire de contact') }}</h3>
            <textarea rows="3" @change="regler('contact_footer', $el.value)" class="{{ $champ }}">{{ $pref->contact_footer ?? '' }}</textarea>
        </section>

        <section>
            <h3 class="mb-2 font-semibold text-gray-900">{{ __('CSS expert') }}</h3>
            <textarea rows="5" @change="regler('expert_css', $el.value)" spellcheck="false" class="{{ $champ }} font-mono text-[12px]">{{ $pref->expert_css ?? '' }}</textarea>
        </section>

        <p class="text-gray-500">{{ __('Titre, description et titre du contact se modifient directement sur la page : cliquez dessus.') }}</p>
    </div>
</aside>
