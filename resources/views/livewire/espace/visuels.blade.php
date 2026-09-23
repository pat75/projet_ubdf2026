<div>
    <a href="{{ route('espace.galeries') }}" class="text-sm text-gray-600 underline dark:text-gray-300">{{ __('Toutes les galeries') }}</a>
    <x-espace.titre>{{ $galerie->name }}</x-espace.titre>

    <label class="mt-6 flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white px-4 py-8 text-sm text-gray-600 hover:border-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
           x-data="{ survol: false }" :class="survol && 'border-indigo-500'"
           x-on:dragover.prevent="survol = true" x-on:dragleave="survol = false"
           x-on:drop.prevent="survol = false; $refs.envoi.files = $event.dataTransfer.files; $refs.envoi.dispatchEvent(new Event('change'))">
        <span wire:loading.remove wire:target="fichiers">{{ __('Déposez vos images ici ou cliquez pour les choisir (JPG, PNG, GIF)') }}</span>
        <span wire:loading wire:target="fichiers">{{ __('Envoi en cours…') }}</span>
        <input type="file" multiple accept="image/jpeg,image/png,image/gif" class="sr-only" x-ref="envoi" wire:model="fichiers">
    </label>
    @error('fichiers') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    @error('fichiers.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

    <ul class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4" x-data x-espace-tri="ordonner">
        @forelse ($visuels as $visuel)
            <li wire:key="visuel-{{ $visuel->id }}" data-id="{{ $visuel->id }}"
                class="flex flex-col rounded-lg bg-white p-2 shadow-sm dark:bg-gray-800">
                <img src="{{ $visuel->url('adm_medium') }}" alt="" loading="lazy"
                     @class(['h-40 w-full cursor-move object-contain', 'opacity-40' => $visuel->status !== 'published'])>

                <input type="text" value="{{ $visuel->title }}" placeholder="{{ __('Titre') }}"
                       x-on:change="$wire.modifier({{ $visuel->id }}, 'title', $event.target.value)"
                       class="mt-2 w-full rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                <textarea rows="2" placeholder="{{ __('Description') }}"
                          x-on:change="$wire.modifier({{ $visuel->id }}, 'description', $event.target.value)"
                          class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">{{ $visuel->description }}</textarea>
                @error('title.'.$visuel->id) <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('description.'.$visuel->id) <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-2 flex items-center justify-between text-xs">
                    <button type="button" wire:click="basculerPublication({{ $visuel->id }})" class="underline">
                        {{ $visuel->status === 'published' ? __('Masquer') : __('Afficher') }}
                    </button>
                    <button type="button" class="text-red-600" wire:click="supprimer({{ $visuel->id }})"
                            wire:confirm="{{ __('Supprimer ce visuel ?') }}">{{ __('Supprimer') }}</button>
                </div>
            </li>
        @empty
            <li class="col-span-full text-sm text-gray-500 dark:text-gray-400">{{ __('Cette galerie est vide.') }}</li>
        @endforelse
    </ul>
</div>
