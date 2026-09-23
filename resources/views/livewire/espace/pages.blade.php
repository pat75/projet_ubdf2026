@php
    $types = ['accueil' => __('Accueil'), 'pages' => __('Pages'), 'news' => __('Actualités')];
    $champ = 'rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200';
@endphp
<div>
    <x-espace.titre>{{ __('Les pages de contenu') }}</x-espace.titre>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Biographie, actualités, textes d’accueil : les rubriques de texte de votre book.') }}</p>

    <form wire:submit="creerRubrique" class="mt-6 flex flex-col gap-2 sm:flex-row">
        <input type="text" wire:model="nom" placeholder="{{ __('Nom de la nouvelle rubrique') }}" class="w-full sm:max-w-sm {{ $champ }}">
        <x-espace.bouton>{{ __('Créer') }}</x-espace.bouton>
    </form>
    @error('nom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

    @forelse ($rubriques as $rubrique)
        <section wire:key="rubrique-{{ $rubrique->id }}" class="mt-8 rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <header class="flex flex-wrap items-center gap-3 border-b border-gray-200 bg-gray-100 px-4 py-3 dark:border-gray-600 dark:bg-gray-700">
                <div class="min-w-0 flex-1" x-data="{ avant: @js($rubrique->title), ok: false }">
                    <span contenteditable="true" x-text="avant"
                          class="cursor-text border-b border-transparent pb-1 font-medium transition-colors duration-500 focus:border-gray-400 focus:outline-none"
                          :class="ok && 'text-teal-600'"
                          x-on:keydown.enter.prevent="$el.blur()"
                          x-on:keydown.escape.prevent="$el.innerText = avant; $el.blur()"
                          x-on:blur="let v = $el.innerText.trim(); if (v !== avant) { $wire.renommer({{ $rubrique->id }}, v).then(() => { avant = v; ok = true; setTimeout(() => ok = false, 900) }) }"></span>
                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ $types[$rubrique->kind] ?? $rubrique->kind }}</span>
                    @error('renommer.'.$rubrique->id) <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="button" wire:click="basculerRubrique({{ $rubrique->id }})" class="text-sm underline">
                    {{ $rubrique->is_published ? __('Masquer') : __('Afficher') }}
                </button>
                <button type="button" class="text-sm text-red-600" wire:click="supprimerRubrique({{ $rubrique->id }})"
                        wire:confirm="{{ __('Supprimer la rubrique « :nom » et ses pages ?', ['nom' => $rubrique->title]) }}">{{ __('Supprimer') }}</button>
            </header>

            <ul class="divide-y divide-gray-200 dark:divide-gray-600" x-data
                x-init="window.espaceTri($el, ids => $wire.ordonnerPages({{ $rubrique->id }}, ids))">
                @foreach ($rubrique->articles as $page)
                    <li wire:key="page-{{ $page->id }}" data-id="{{ $page->id }}" class="flex items-center gap-3 px-4 py-2">
                        <span class="cursor-move select-none text-gray-400" data-poignee>&#8942;&#8942;</span>
                        <a href="{{ route('espace.pages.edit', $page) }}" class="flex-1 truncate hover:underline">{{ $page->title ?: __('(sans titre)') }}</a>
                        @if ($page->status !== 'published')
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ __('Brouillon') }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>

            <form wire:submit="creerPage({{ $rubrique->id }})" class="flex gap-2 px-4 py-3">
                <input type="text" wire:model="nouvellePage.{{ $rubrique->id }}" placeholder="{{ __('Titre de la nouvelle page') }}" class="w-full text-sm {{ $champ }}">
                <button type="submit" class="whitespace-nowrap text-sm underline">{{ __('Ajouter') }}</button>
            </form>
            @error('nouvellePage.'.$rubrique->id) <p class="px-4 pb-3 text-sm text-red-600">{{ $message }}</p> @enderror
        </section>
    @empty
        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400">{{ __('Aucune rubrique pour le moment.') }}</p>
    @endforelse
</div>
