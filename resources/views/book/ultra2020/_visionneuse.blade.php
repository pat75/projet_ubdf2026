{{-- Visionneuse du portfolio ($store.visionneuse, resources/js/book/visionneuse.js), ex-Magnific Popup. --}}
<div x-data x-show="$store.visionneuse.ouverte" x-cloak x-transition.opacity.duration.300ms
     class="fixed inset-0 z-[70] flex flex-col bg-black/90 text-white"
     role="dialog" aria-modal="true" aria-label="{{ __('Visionneuse') }}"
     @click.self="$store.visionneuse.fermer()"
     @touchstart.passive="$el._x = $event.changedTouches[0].clientX"
     @touchend="(d => Math.abs(d) > 50 && $store.visionneuse.aller(d < 0 ? 1 : -1))($event.changedTouches[0].clientX - $el._x)">

    <button type="button" class="absolute right-3 top-3 z-10 p-2 text-white/80 hover:text-white" @click="$store.visionneuse.fermer()" aria-label="{{ __('Fermer') }}">
        <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>

    <div class="flex min-h-0 flex-1 items-center justify-center px-4 pt-14 md:px-20" @click.self="$store.visionneuse.fermer()">
        <template x-for="(image, i) in $store.visionneuse.images" :key="i">
            <img x-show="i === $store.visionneuse.index" x-transition.opacity.duration.300ms
                 :src="Math.abs(i - $store.visionneuse.index) <= 1 ? image.src : null"
                 :alt="image.titre" class="max-h-full max-w-full object-contain">
        </template>
    </div>

    <div class="flex items-start justify-between gap-4 px-4 pb-5 pt-3 text-[13px] md:px-20">
        <p class="min-w-0">
            <strong x-text="$store.visionneuse.image.rubrique"></strong>
            <span x-text="$store.visionneuse.image.titre"></span>
            <small class="mt-1 block text-white/60" x-text="$store.visionneuse.image.description"></small>
        </p>
        <span class="shrink-0 text-white/60" x-text="`${$store.visionneuse.index + 1} / ${$store.visionneuse.images.length}`"></span>
    </div>

    <button type="button" class="absolute left-1 top-1/2 -translate-y-1/2 p-3 text-white/70 hover:text-white max-md:hidden" @click="$store.visionneuse.aller(-1)" aria-label="{{ __('Précédente') }}">
        <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
    </button>
    <button type="button" class="absolute right-1 top-1/2 -translate-y-1/2 p-3 text-white/70 hover:text-white max-md:hidden" @click="$store.visionneuse.aller(1)" aria-label="{{ __('Suivante') }}">
        <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
    </button>
</div>
