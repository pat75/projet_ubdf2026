{{--
 | Navigation mobile de l'espace, facon WebApp (sous 900 px seulement ; le
 | desktop garde le menu de droite) :
 | - barre d'onglets fixee en bas, a portee de pouce ;
 | - « Plus » ouvre un volet par le bas avec le menu complet
 |   (partials/espace/menu). Fermeture : glisser vers le bas, fond, Echap.
--}}
<div x-data="{
        plus: false,
        depart: null,
        decalage: 0,
        fermer() { this.plus = false; this.decalage = 0; },
     }"
     x-effect="document.documentElement.classList.toggle('overflow-hidden', plus)"
     @keydown.escape.window="fermer()"
     class="min-[900px]:hidden">

    {{-- Hauteur de la barre, reservee sous le pied de page. --}}
    <div class="h-[calc(62px+env(safe-area-inset-bottom))]" aria-hidden="true"></div>

    <nav class="fixed inset-x-0 bottom-0 z-30 flex border-t border-ub-filet bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_16px_rgba(0,0,0,0.06)] backdrop-blur"
         aria-label="{{ __('Navigation de l’espace') }}">
        <x-espace.onglet-mobile route="espace" icone="maison">{{ __('Tableau de bord') }}</x-espace.onglet-mobile>
        <x-espace.onglet-mobile route="espace.messages" icone="enveloppe" :pastille="$messagesNonLus">{{ __('Messages') }}</x-espace.onglet-mobile>
        <x-espace.onglet-mobile route="espace.galeries" icone="image">{{ __('Images') }}</x-espace.onglet-mobile>
        <x-espace.onglet-mobile route="espace.diffusion" icone="diffusion">{{ __('Diffuser') }}</x-espace.onglet-mobile>

        <button type="button" @click="plus = true" :aria-expanded="plus"
                class="flex flex-1 flex-col items-center gap-1 pt-2 pb-1.5 text-[11px]"
                :class="plus ? 'font-semibold text-ub-accent-texte' : 'text-ub-texte3'">
            <x-espace.icone nom="plus" class="h-6 w-6" />
            <span>{{ __('Plus') }}</span>
        </button>
    </nav>

    {{-- Fond --}}
    <div x-show="plus" x-cloak x-transition.opacity @click="fermer()"
         class="fixed inset-0 z-40 bg-black/40"></div>

    {{-- Volet --}}
    <div x-show="plus" x-cloak
         x-transition:enter="transition duration-300 ease-out"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition duration-200 ease-in"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         :style="decalage ? `transform: translateY(${decalage}px)` : ''"
         @touchstart="depart = $event.touches[0].clientY"
         @touchmove="if (depart !== null && $refs.defile.scrollTop <= 0) decalage = Math.max(0, $event.touches[0].clientY - depart)"
         @touchend="decalage > 90 ? fermer() : (decalage = 0); depart = null"
         role="dialog" aria-modal="true" aria-label="{{ __('Menu de l’espace') }}"
         class="fixed inset-x-0 bottom-0 z-50 flex max-h-[88vh] flex-col rounded-t-2xl bg-ub-fond pb-[env(safe-area-inset-bottom)] shadow-ub">

        <div class="flex shrink-0 justify-center py-2.5">
            <span class="h-1.5 w-10 rounded-full bg-ub-texte/25"></span>
        </div>

        <div x-ref="defile" class="flex flex-col gap-4 overflow-y-auto overscroll-contain px-4 pb-5">
            @include('partials.espace.menu')
        </div>
    </div>
</div>
