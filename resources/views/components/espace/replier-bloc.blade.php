@props(['clair' => false])

{{-- Fleche de repli d'un bloc, en haut a droite. Le parent porte l'etat
     Alpine `repliee` et la methode `basculer()`. Bulle d'aide selon l'etat
     (meme rendu que x-espace.info-bulle). `clair` : fleche blanche, pour
     un fond sombre. --}}
<div class="absolute top-4 right-4">
    <span class="group/bulle relative inline-flex">
        <button type="button" @click="basculer()" :aria-expanded="! repliee"
                class="flex h-8 w-8 items-center justify-center rounded-full transition {{ $clair ? 'hover:bg-white/15' : 'hover:bg-white/40' }}">
            <x-espace.picto nom="angle-bas" x-show="! repliee" class="h-5 w-5 {{ $clair ? 'text-white' : 'text-ub-texte' }}" />
            <x-espace.picto nom="angle-droite" x-show="repliee" x-cloak class="h-5 w-5 {{ $clair ? 'text-white' : 'text-ub-texte' }}" />
        </button>
        <span role="tooltip" x-text="repliee ? @js(__('Déplier')) : @js(__('Replier'))"
              class="pointer-events-none invisible absolute top-full left-1/2 z-20 mt-3 -translate-x-1/2 whitespace-nowrap bg-gray-900 px-3 py-2 text-[14px] text-white opacity-0 transition-opacity duration-150 group-hover/bulle:visible group-hover/bulle:opacity-100 group-focus-within/bulle:visible group-focus-within/bulle:opacity-100"></span>
    </span>
</div>
