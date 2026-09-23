<div>
    <x-espace.titre>{{ __('Diffusion') }}</x-espace.titre>

    <form wire:submit="enregistrer" class="mt-6 max-w-xl space-y-3">
        @foreach ([
            'web' => [__('Book en ligne'), __('Hors ligne, les visiteurs voient une page d’attente ; vous seul voyez le book.')],
            'portail' => [__('Présent sur le portail'), __('Le book apparaît dans l’annuaire et les recherches.')],
            'newsletter' => [__('Dans les newsletters'), __('Vos visuels peuvent être sélectionnés pour la newsletter.')],
            'disponible' => [__('Disponible pour des projets'), __('Signale votre disponibilité sur le portail.')],
        ] as $champ => [$libelle, $aide])
            <label class="flex items-start gap-3 rounded-md bg-white px-4 py-3 dark:bg-gray-800">
                <input type="checkbox" wire:model="{{ $champ }}" class="mt-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                <span>
                    <span class="block text-sm">{{ $libelle }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $aide }}</span>
                </span>
            </label>
        @endforeach

        <x-espace.bouton>{{ __('Enregistrer') }}</x-espace.bouton>
    </form>
</div>
