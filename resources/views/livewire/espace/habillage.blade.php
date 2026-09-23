@php($champ = 'w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200')
<div>
    <x-espace.titre>{{ __('Habillage') }}</x-espace.titre>

    <section class="mt-6">
        <h2 class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Modèle du book') }}</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Chaque modèle garde ses propres réglages : revenir à un ancien modèle les retrouve.') }}</p>
        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($themes as $cle => $nom)
                <button type="button" wire:click="choisirTheme('{{ $cle }}')" wire:key="theme-{{ $cle }}"
                        @class(['rounded-lg border px-3 py-3 text-left text-sm',
                            'border-black bg-white font-medium dark:border-white dark:bg-gray-800' => $cle === $theme,
                            'border-gray-200 bg-white hover:border-gray-400 dark:border-gray-600 dark:bg-gray-800' => $cle !== $theme])>
                    {{ $nom }}
                </button>
            @endforeach
        </div>
    </section>

    <form wire:submit="enregistrer" class="mt-10 space-y-8">
        <section class="space-y-3">
            <h2 class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Présentation') }}</h2>
            <input type="text" wire:model="titre" placeholder="{{ __('Titre du book') }}" class="{{ $champ }}">
            <textarea wire:model="description" rows="3" placeholder="{{ __('Description') }}" class="{{ $champ }}"></textarea>
            <textarea wire:model="piedDePage" rows="2" placeholder="{{ __('Pied de page') }}" class="{{ $champ }}"></textarea>
        </section>

        @if ($champs)
            <section>
                <h2 class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Réglages du modèle') }}</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    @foreach ($champs as $i => $c)
                        <label wire:key="champ-{{ $theme }}-{{ $i }}" class="flex items-center justify-between gap-3 rounded-md bg-white px-3 py-2 text-sm dark:bg-gray-800">
                            <span class="text-gray-700 dark:text-gray-300">{{ $c['libelle'] }}</span>
                            @switch($c['type'])
                                @case('booleen')
                                    <input type="checkbox" wire:model="valeurs.{{ $i }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    @break
                                @case('couleur')
                                    <input type="color" wire:model="valeurs.{{ $i }}" class="h-8 w-12 rounded border border-gray-300 dark:border-gray-600">
                                    @break
                                @case('nombre')
                                    <input type="number" wire:model="valeurs.{{ $i }}" class="w-24 rounded-md border border-gray-300 px-2 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    @break
                                @default
                                    <input type="text" wire:model="valeurs.{{ $i }}" class="w-1/2 rounded-md border border-gray-300 px-2 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            @endswitch
                        </label>
                    @endforeach
                </div>
            </section>
        @endif

        <x-espace.bouton wire:loading.attr="disabled" wire:target="enregistrer">{{ __('Enregistrer') }}</x-espace.bouton>
    </form>
</div>
