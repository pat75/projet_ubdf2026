<div>
    <a href="{{ route('espace.pages') }}" class="text-sm text-gray-600 underline dark:text-gray-300">{{ __('Toutes les pages') }}</a>

    <form wire:submit="enregistrer" class="mt-4 space-y-4">
        <input type="text" wire:model="titre" aria-label="{{ __('Titre') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 text-xl font-light focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
        @error('titre') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <div wire:ignore class="espace-trix rounded-md bg-white dark:bg-gray-700">
            <input id="corps-{{ $page->id }}" type="hidden" value="{{ $corps }}">
            <trix-editor input="corps-{{ $page->id }}" class="min-h-[300px] dark:text-gray-200"
                         x-data x-on:trix-change="$wire.corps = $event.target.value"></trix-editor>
        </div>
        @error('corps') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="enLigne" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            {{ __('Publiée sur le book') }}
        </label>

        <div class="flex items-center gap-4">
            <x-espace.bouton wire:loading.attr="disabled" wire:target="enregistrer">{{ __('Enregistrer') }}</x-espace.bouton>
            <button type="button" class="text-sm text-red-600" wire:click="supprimer"
                    wire:confirm="{{ __('Supprimer cette page ?') }}">{{ __('Supprimer') }}</button>
        </div>
    </form>
</div>
