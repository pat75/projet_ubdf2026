@props(['titre', 'ouvert' => false, 'icone' => null])

{{-- Les sections depliables de la fiche du compte. Alpine tient l'etat :
     rien ne part au serveur pour ouvrir ou fermer un volet. --}}
<section x-data="{ ouvert: @js($ouvert) }" class="border-b border-ub-gris-clair">
    <button type="button" @click="ouvert = ! ouvert" :aria-expanded="ouvert"
            class="flex w-full items-center justify-between py-4 text-left">
        <span class="flex items-center gap-2 font-titre text-[20px] font-light text-[#070707]">
            @if ($icone)
                <x-espace.icone :nom="$icone" class="h-5 w-5 text-ub-rouge" />
            @endif
            {{ $titre }}
        </span>

        <svg class="h-5 w-5 shrink-0 text-ub-gris-fonce transition-transform" ::class="ouvert && 'rotate-90'"
             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
        </svg>
    </button>

    <div x-show="ouvert" x-collapse x-cloak class="pb-6">
        {{ $slot }}
    </div>
</section>
