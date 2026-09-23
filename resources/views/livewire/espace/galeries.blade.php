<div>
    <x-espace.titre>{{ __('Les portfolios') }}</x-espace.titre>

    <form wire:submit="creer" class="mt-6 flex flex-col gap-2 sm:flex-row">
        <input type="text" wire:model="nom" placeholder="{{ __('Nom de la nouvelle galerie') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 sm:max-w-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
        <x-espace.bouton wire:loading.attr="disabled" wire:target="creer">{{ __('Créer') }}</x-espace.bouton>
    </form>
    @error('nom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

    <ul class="mt-8 divide-y divide-gray-200 rounded-lg bg-white shadow-sm dark:divide-gray-600 dark:bg-gray-800"
        x-data x-espace-tri="ordonner">
        @forelse ($galeries as $galerie)
            <li wire:key="galerie-{{ $galerie->id }}" data-id="{{ $galerie->id }}" class="flex items-center gap-3 px-4 py-3">
                <span class="cursor-move select-none text-gray-400" data-poignee title="{{ __('Déplacer') }}">&#8942;&#8942;</span>

                <div class="min-w-0 flex-1" x-data="{ nom: @js($galerie->name), avant: @js($galerie->name), ok: false }">
                    <span contenteditable="true" x-ref="nom" x-text="nom"
                          class="cursor-text border-b border-transparent pb-1 transition-colors duration-500 focus:border-gray-400 focus:outline-none"
                          :class="ok && 'text-teal-600'"
                          x-on:keydown.enter.prevent="$el.blur()"
                          x-on:keydown.escape.prevent="$el.innerText = avant; $el.blur()"
                          x-on:blur="let v = $el.innerText.trim(); if (v !== avant) { $wire.renommer({{ $galerie->id }}, v).then(() => { avant = v; ok = true; setTimeout(() => ok = false, 900) }) }"></span>
                    @error('renommer.'.$galerie->id) <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ trans_choice(':n visuel|:n visuels', $galerie->media_count, ['n' => $galerie->media_count]) }}</p>
                </div>

                <button type="button" wire:click="basculerPublication({{ $galerie->id }})"
                        @class(['rounded-full px-3 py-1 text-xs', 'bg-teal-50 text-teal-800 dark:bg-teal-900 dark:text-teal-100' => $galerie->status === 'published', 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => $galerie->status !== 'published'])>
                    {{ $galerie->status === 'published' ? __('En ligne') : __('Masquée') }}
                </button>
                <a href="{{ route('espace.galeries.show', $galerie) }}" class="text-sm underline">{{ __('Visuels') }}</a>
                <button type="button" class="text-sm text-red-600"
                        wire:click="supprimer({{ $galerie->id }})"
                        wire:confirm="{{ __('Supprimer la galerie « :nom » et ses visuels ?', ['nom' => $galerie->name]) }}">{{ __('Supprimer') }}</button>
            </li>
        @empty
            <li class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Aucune galerie pour le moment.') }}</li>
        @endforelse
    </ul>
</div>
