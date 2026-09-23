@php
    $champ = 'rounded border border-ub-gris-clair bg-white px-3 py-2 text-[15px] focus:border-ub-gris-moyen focus:outline-none';
    $crayon = 'shrink-0 text-ub-gris-fonce hover:text-ub-texte';
@endphp

<div>
    <x-espace.titre>{{ __('Mon compte') }}</x-espace.titre>

    <x-espace.hero
        :illustration="asset('img_admin/int-compte.svg')"
        :alt="__('Mes informations')"
        :titre="__('Mes informations')"
        :suite="__('publiques et privées')"
        :suite-turquoise="true"
        class="mb-10" />

    {{-- Rappel du mode de connexion : le legacy distinguait le compte
         ouvert par identifiant de ceux ouverts par Facebook ou LinkedIn. --}}
    <div class="mb-8 flex items-center gap-4 rounded bg-[#f4f4f4] px-5 py-4 text-[15px]">
        <svg class="h-10 w-10 shrink-0 text-[#555]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-8 2.6-8 5.8V22h16v-2.2c0-3.2-3.6-5.8-8-5.8Z"/>
        </svg>
        {{ __('Votre connexion s’est faite via votre identifiant et votre mot de passe') }}
    </div>

    <x-espace.section-pliante :titre="__('Votre compte')" :ouvert="true">

        <form wire:submit="enregistrerProfil">
            <x-espace.ligne-champ :libelle="__('Url de votre book')" :aide="__('C’est l’adresse publique de votre book. Elle suit votre identifiant et ne se change pas.')">
                <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener" class="text-ub-texte hover:text-ub-rouge">{{ $creatif->bookUrl() }}</a>
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Identifiant / Login')" :aide="__('Il sert à vous connecter et donne son adresse à votre book.')">
                <span>{{ $creatif->login }}</span>
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Métier')">
                <select wire:model="categorie" class="{{ $champ }}">
                    <option value="">—</option>
                    @foreach ($categories as $id => $nom)
                        <option value="{{ $id }}">{{ $nom }}</option>
                    @endforeach
                </select>
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Votre statut')">
                <select wire:model="statut" class="{{ $champ }}">
                    <option value="">—</option>
                    @foreach ($statuts as $valeur)
                        <option value="{{ $valeur }}">{{ $valeur }}</option>
                    @endforeach
                </select>
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Nom')" :obligatoire="true">
                <input type="text" wire:model="profil.lastname" class="{{ $champ }}">
                @error('profil.lastname') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Prénom')" :obligatoire="true">
                <input type="text" wire:model="profil.firstname" class="{{ $champ }}">
                @error('profil.firstname') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Société')">
                <input type="text" wire:model="profil.company" class="{{ $champ }}">
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Site web')" :aide="__('Votre site personnel, s’il en existe un. Il est affiché sur votre book.')">
                <input type="url" wire:model="profil.website" placeholder="https://www.monsite.com" class="{{ $champ }}">
                @error('profil.website') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Téléphone')" :prive="true" :aide="__('Il ne paraît pas sur votre book : il sert aux contacts qui passent par la plateforme.')">
                <input type="tel" wire:model="profil.phone" placeholder="+33601020304" class="{{ $champ }}">
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Contacts par SMS (Bêta)')" :aide="__('Vous recevez un SMS quand un client vous écrit.')">
                {{-- Interrupteur : la case est masquee, c'est le rail qui
                     la represente, et le clavier la reste atteint. --}}
                <label class="inline-flex cursor-pointer items-center gap-3">
                    <input type="checkbox" wire:model="sms" class="peer sr-only">
                    <span class="relative h-6 w-11 rounded-full bg-ub-gris-clair transition peer-checked:bg-[#17b7bf] peer-focus-visible:ring-2 peer-focus-visible:ring-ub-gris-moyen">
                        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-[13px] text-ub-gris-fonce">{{ __('Les SMS sont envoyés entre 9h et 21h') }}</span>
                </label>
            </x-espace.ligne-champ>

            <div class="mt-6">
                <x-espace.bouton wire:target="enregistrerProfil" wire:loading.attr="disabled">{{ __('Enregistrer') }}</x-espace.bouton>
            </div>
        </form>
    </x-espace.section-pliante>

    {{-- Adresse et mot de passe : separes du reste, parce qu'ils exigent
         le mot de passe actuel. --}}
    <x-espace.section-pliante :titre="__('Adresse e-mail et mot de passe')">
        <form wire:submit="enregistrerAcces" class="max-w-xl">
            <x-espace.ligne-champ :libelle="__('Adresse mail')" :prive="true" :obligatoire="true">
                <input type="email" wire:model="email" autocomplete="email" class="{{ $champ }} w-full">
                @error('email') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Nouveau mot de passe')" :prive="true">
                <input type="password" wire:model="nouveauMotDePasse" autocomplete="new-password" class="{{ $champ }} w-full">
                @error('nouveauMotDePasse') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Confirmation')">
                <input type="password" wire:model="nouveauMotDePasse_confirmation" autocomplete="new-password" class="{{ $champ }} w-full">
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Mot de passe actuel')" :obligatoire="true">
                <input type="password" wire:model="motDePasseActuel" autocomplete="current-password" class="{{ $champ }} w-full">
                @error('motDePasseActuel') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
            </x-espace.ligne-champ>

            <div class="mt-6">
                <x-espace.bouton wire:target="enregistrerAcces" wire:loading.attr="disabled">{{ __('Enregistrer') }}</x-espace.bouton>
            </div>
        </form>
    </x-espace.section-pliante>

    <x-espace.section-pliante :titre="__('Localisation')">
        <form wire:submit="enregistrerProfil" class="max-w-xl">
            <x-espace.ligne-champ :libelle="__('Adresse')">
                <input type="text" wire:model="profil.address" class="{{ $champ }} w-full">
            </x-espace.ligne-champ>
            <x-espace.ligne-champ :libelle="__('Code postal')">
                <input type="text" wire:model="profil.zipcode" class="{{ $champ }}">
            </x-espace.ligne-champ>
            <x-espace.ligne-champ :libelle="__('Ville')">
                <input type="text" wire:model="profil.city" class="{{ $champ }} w-full">
            </x-espace.ligne-champ>
            <x-espace.ligne-champ :libelle="__('Pays')">
                <input type="text" wire:model="profil.country" class="{{ $champ }} w-full">
            </x-espace.ligne-champ>

            <div class="mt-6">
                <x-espace.bouton wire:target="enregistrerProfil" wire:loading.attr="disabled">{{ __('Enregistrer') }}</x-espace.bouton>
            </div>
        </form>
    </x-espace.section-pliante>

    <x-espace.section-pliante :titre="__('Réseaux sociaux')">
        <form wire:submit="enregistrerProfil" class="max-w-xl">
            @foreach (['website' => __('Site web'), 'facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'twitter_url' => 'X / Twitter'] as $cle => $libelle)
                <x-espace.ligne-champ :libelle="$libelle">
                    <input type="url" wire:model="profil.{{ $cle }}" class="{{ $champ }} w-full">
                    @error('profil.'.$cle) <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
                </x-espace.ligne-champ>
            @endforeach

            <div class="mt-6">
                <x-espace.bouton wire:target="enregistrerProfil" wire:loading.attr="disabled">{{ __('Enregistrer') }}</x-espace.bouton>
            </div>
        </form>
    </x-espace.section-pliante>
</div>
