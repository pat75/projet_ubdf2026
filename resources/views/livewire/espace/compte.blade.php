@php($champ = 'w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200')
<div>
    <x-espace.titre>{{ __('Mon compte') }}</x-espace.titre>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Identifiant : :login', ['login' => auth()->user()->login]) }}</p>

    <form wire:submit="enregistrerProfil" class="mt-6 max-w-3xl">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm sm:col-span-2">
                <span class="text-gray-700 dark:text-gray-300">{{ __('Métier') }}</span>
                <select wire:model="categorie" class="mt-1 {{ $champ }}">
                    <option value="">—</option>
                    @foreach ($categories as $id => $nom)
                        <option value="{{ $id }}">{{ $nom }}</option>
                    @endforeach
                </select>
            </label>
            @foreach ($libelles as $cle => $libelle)
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-300">{{ $libelle }}</span>
                    <input type="text" wire:model="profil.{{ $cle }}" class="mt-1 {{ $champ }}">
                    @error('profil.'.$cle) <span class="text-red-600">{{ $message }}</span> @enderror
                </label>
            @endforeach
        </div>
        <div class="mt-6"><x-espace.bouton wire:target="enregistrerProfil" wire:loading.attr="disabled">{{ __('Enregistrer') }}</x-espace.bouton></div>
    </form>

    <form wire:submit="enregistrerAcces" class="mt-12 max-w-xl space-y-4">
        <h2 class="text-lg font-light">{{ __('Adresse e-mail et mot de passe') }}</h2>
        <label class="block text-sm">
            <span class="text-gray-700 dark:text-gray-300">{{ __('Adresse e-mail') }}</span>
            <input type="email" wire:model="email" autocomplete="email" class="mt-1 {{ $champ }}">
            @error('email') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>
        <label class="block text-sm">
            <span class="text-gray-700 dark:text-gray-300">{{ __('Nouveau mot de passe (laisser vide pour le garder)') }}</span>
            <input type="password" wire:model="nouveauMotDePasse" autocomplete="new-password" class="mt-1 {{ $champ }}">
            @error('nouveauMotDePasse') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>
        <label class="block text-sm">
            <span class="text-gray-700 dark:text-gray-300">{{ __('Confirmation') }}</span>
            <input type="password" wire:model="nouveauMotDePasse_confirmation" autocomplete="new-password" class="mt-1 {{ $champ }}">
        </label>
        <label class="block text-sm">
            <span class="text-gray-700 dark:text-gray-300">{{ __('Mot de passe actuel') }}</span>
            <input type="password" wire:model="motDePasseActuel" autocomplete="current-password" class="mt-1 {{ $champ }}">
            @error('motDePasseActuel') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>
        <x-espace.bouton wire:target="enregistrerAcces" wire:loading.attr="disabled">{{ __('Enregistrer') }}</x-espace.bouton>
    </form>
</div>
