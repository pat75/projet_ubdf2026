@props(['nom', 'ouverte' => false])

{{-- Fenetre modale du portail, pilotee par Alpine ($store.modale) a la
     place du module modal de Semantic UI. Garde les classes Semantic
     (page dimmer, modal, active, visible, scrolling) : les feuilles du
     front 2018 continuent de la mettre en forme.

     Ouvrir : $store.modale.ouvrir('{{ $nom }}'). Se ferme par Echap, un
     clic sur le fond ou sur un element .btn_close. Plus haute que l'ecran,
     elle defile (classe `scrolling`, comme le faisait Semantic).
     `ouverte` l'ouvre des le chargement (ex. apres un echec de connexion). --}}
<div class="ui page modals dimmer" data-modale-fond="{{ $nom }}"
     x-data="{ defile: false }"
     @if ($ouverte) x-init="$nextTick(() => $store.modale.ouvrir(@js($nom)))" @endif
     x-show="$store.modale.ouverte === @js($nom)"
     x-effect="$store.modale.ouverte === @js($nom) && $nextTick(() => defile = $refs.fenetre.offsetHeight > window.innerHeight - 40)"
     :class="{ 'active visible': $store.modale.ouverte === @js($nom), scrolling: defile }"
     x-transition.opacity.duration.250ms
     @click.self="$store.modale.fermer()"
     x-cloak>
    <div {{ $attributes->class(['ui modal active visible']) }} data-modale="{{ $nom }}" x-ref="fenetre"
         :class="{ scrolling: defile }"
         x-show="$store.modale.ouverte === @js($nom)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         @click="$event.target.closest('.btn_close') && $store.modale.fermer()">
        {{ $slot }}
    </div>
</div>
