@props(['titre', 'ouvert' => false, 'icone' => null])

{{-- Les sections depliables de la fiche du compte. Alpine tient l'etat :
     rien ne part au serveur pour ouvrir ou fermer un volet. --}}
<section x-data="{ ouvert: @js($ouvert) }" class="border-b border-ub-gris-clair">
    <button type="button" @click="ouvert = ! ouvert" :aria-expanded="ouvert"
            class="flex w-full items-center justify-between py-4 text-left">
        <span class="flex items-center gap-2 font-titre text-[20px] font-light text-[#070707]">
            @if ($icone === 'alerte')
                <span class="fonticon-alert-circle text-[18px] text-ub-rouge" aria-hidden="true"></span>
            @elseif ($icone === 'factures')
                <span class="fonticon-layers text-[16px] text-ub-texte" aria-hidden="true"></span>
            @elseif ($icone)
                <x-espace.icone :nom="$icone" class="h-5 w-5 text-ub-rouge" />
            @endif
            {{ $titre }}
        </span>

        <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0 text-ub-texte" />
        <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0 text-ub-texte" />
    </button>

    <div x-show="ouvert" x-collapse x-cloak class="pb-6">
        {{ $slot }}
    </div>
</section>
