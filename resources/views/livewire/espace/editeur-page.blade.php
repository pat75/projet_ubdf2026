<div>
    <div class="mb-6">
        <a href="{{ route('espace.pages') }}" class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte hover:underline">{{ __('Toutes les pages') }}</a>
    </div>

    <form wire:submit="enregistrer" class="carte-espace flex flex-col gap-5 p-5">
        <div>
            <input type="text" wire:model="titre" aria-label="{{ __('Titre') }}" placeholder="{{ __('Titre de la page') }}"
                   class="w-full border-0 p-0 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte outline-none placeholder:text-ub-texte3">
            @error('titre') <p class="mt-1 text-[13px] text-ub-danger">{{ $message }}</p> @enderror
        </div>

        {{-- Editeur Redactor : wire:ignore, le contenu de `corps` ne quitte
             le composant qu'au callback `changed` (voir resources/js/espace.js). --}}
        <div wire:ignore x-data
             x-init="window.espacePageEditor($refs.corps, (html) => $wire.corps = html, {
                 upload: @js(route('espace.pages.upload-image')),
                 liste: @js(route('espace.pages.images.index')),
                 action: @js(route('espace.pages.images.update', ['image' => '__ID__'])),
             })"
             class="border border-ub-bord bg-white">
            <div x-ref="corps">{!! $corps !!}</div>
        </div>
        @error('corps') <p class="text-[13px] text-ub-danger">{{ $message }}</p> @enderror

        <div class="flex items-center gap-4">
            <button type="submit" wire:loading.attr="disabled" wire:target="enregistrer" class="bouton-espace bouton-espace-grand px-6">{{ __('Enregistrer') }}</button>
            <button type="button" class="text-[14px] text-ub-danger hover:underline" wire:click="supprimer"
                    wire:confirm="{{ __('Supprimer cette page ?') }}">{{ __('Supprimer') }}</button>
        </div>
    </form>
</div>
