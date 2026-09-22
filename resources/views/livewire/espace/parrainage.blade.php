<div class="mt-12 max-w-xl">
    <h2 class="text-lg font-light">{{ __('Parrainage') }}</h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Votre code de parrainage :') }}
        <strong class="font-mono">{{ $monCode }}</strong>.
        {{ __('Quand un créatif avec une formule payante l’utilise, vous gagnez chacun 1 à 3 mois de formule.') }}
    </p>

    @if ($filleuls->isNotEmpty())
        <ul class="mt-3 text-sm">
            @foreach ($filleuls as $f)
                <li>{{ $f->referred?->fullName() }} — {{ $f->confirmed_at?->format('d/m/Y') }}</li>
            @endforeach
        </ul>
    @endif

    <form wire:submit="utiliser" class="mt-6 flex flex-col gap-2 sm:flex-row">
        <input type="text" wire:model="code" placeholder="{{ __('Code de parrainage ou code promo') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 font-mono uppercase focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 sm:max-w-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
        <x-espace.bouton wire:target="utiliser" wire:loading.attr="disabled">{{ __('Valider') }}</x-espace.bouton>
    </form>
    @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    @if ($resultat) <p class="mt-2 text-sm">{{ $resultat }}</p> @endif
</div>
