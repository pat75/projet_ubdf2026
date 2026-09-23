@php
    $champ = 'rounded border border-ub-gris-clair bg-white px-3 py-2 text-[15px] focus:border-ub-gris-moyen focus:outline-none';
@endphp

<div>
    <x-espace.titre>{{ __('Mon compte') }}</x-espace.titre>

    <x-espace.hero
        :illustration="asset('img_admin/int-compte.svg')"
        :alt="__('Mes informations')"
        :titre="__('Mes informations')"
        :suite="__('publiques et privées')"
        :suite-dessous="true"
        class="mb-10" />

    {{-- Rappel du mode de connexion : le legacy distinguait le compte
         ouvert par identifiant de ceux ouverts par Facebook ou LinkedIn. --}}
    <div class="mb-8 flex items-center gap-4 rounded bg-[#f4f4f4] px-5 py-4 text-[15px]">
        <span class="fonticon-user text-[34px] text-[#555]" aria-hidden="true"></span>
        {{ __('Votre connexion s’est faite via votre identifiant et votre mot de passe') }}
    </div>

    {{-- L'ordre des lignes suit celui de l'espace d'origine. --}}
    <x-espace.section-pliante :titre="__('Votre compte')" :ouvert="true">

        <x-espace.ligne-champ :libelle="__('Url de votre book')" :aide="__('C’est l’adresse publique de votre book. Elle suit votre identifiant et ne se change pas.')">
            <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener" class="text-ub-texte hover:text-ub-rouge">{{ $creatif->bookUrl() }}</a>
        </x-espace.ligne-champ>

        <x-espace.ligne-champ :libelle="__('Identifiant / Login')" :aide="__('Il sert à vous connecter et donne son adresse à votre book.')">
            <span>{{ $creatif->login }}</span>
        </x-espace.ligne-champ>

        {{-- Mot de passe et adresse : ils vivent dans la meme fiche que le
             reste, mais leur enregistrement passe par le formulaire
             d'acces, qui redemande le mot de passe actuel. --}}
        <div x-data="{ ouvert: @js($errors->hasAny(['email', 'nouveauMotDePasse', 'motDePasseActuel'])) }">

            <x-espace.ligne-champ :libelle="__('Mot de passe')" :prive="true" :obligatoire="true">
                <input type="password" value="motdepasse" disabled class="{{ $champ }} w-44 text-ub-gris-moyen">
                <button type="button" @click="ouvert = true" class="text-ub-gris-fonce hover:text-ub-texte" :aria-expanded="ouvert">
                    <span class="fonticon-edit text-[17px]" aria-hidden="true"></span>
                    <span class="sr-only">{{ __('Changer mon mot de passe') }}</span>
                </button>
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Adresse mail')" :prive="true" :obligatoire="true">
                <span x-show="! ouvert">{{ $creatif->email }}</span>
                <button type="button" x-show="! ouvert" @click="ouvert = true" class="text-ub-gris-fonce hover:text-ub-texte">
                    <span class="fonticon-edit text-[17px]" aria-hidden="true"></span>
                    <span class="sr-only">{{ __('Changer mon adresse') }}</span>
                </button>
            </x-espace.ligne-champ>

            <form wire:submit="enregistrerAcces" x-show="ouvert" x-collapse x-cloak
                  class="my-3 max-w-xl rounded bg-[#f8f8f8] px-5 py-3">
                <x-espace.ligne-champ :libelle="__('Adresse mail')">
                    <input type="email" wire:model="email" autocomplete="email" class="{{ $champ }} w-full">
                    @error('email') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
                </x-espace.ligne-champ>

                <x-espace.ligne-champ :libelle="__('Nouveau mot de passe')">
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

                <div class="mt-4 flex items-center gap-4">
                    <x-espace.bouton wire:target="enregistrerAcces" wire:loading.attr="disabled">{{ __('Enregistrer') }}</x-espace.bouton>
                    <button type="button" @click="ouvert = false" class="text-[13px] text-ub-gris-fonce hover:text-ub-texte">{{ __('Annuler') }}</button>
                </div>
            </form>
        </div>

        <form wire:submit="enregistrerProfil">
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
                    <span class="relative h-6 w-11 rounded-full bg-ub-gris-clair transition peer-checked:bg-ub-turquoise peer-focus-visible:ring-2 peer-focus-visible:ring-ub-gris-moyen">
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
            @foreach (['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'twitter_url' => 'X / Twitter'] as $cle => $libelle)
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

    <x-espace.section-pliante :titre="__('Supprimer mon portfolio')" icone="alerte">
        <form wire:submit="supprimerPortfolio" class="max-w-xl">
            <p class="mb-4">
                {{ __('Votre book cessera d’être en ligne et votre fiche quittera l’annuaire. Vos données sont conservées quelque temps : écrivez-nous si vous changez d’avis.') }}
            </p>

            <x-espace.ligne-champ :libelle="__('Recopiez votre identifiant')" :obligatoire="true">
                <input type="text" wire:model="confirmationSuppression" autocomplete="off" placeholder="{{ $creatif->login }}" class="{{ $champ }}">
                @error('confirmationSuppression') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
            </x-espace.ligne-champ>

            <x-espace.ligne-champ :libelle="__('Mot de passe actuel')" :obligatoire="true">
                <input type="password" wire:model="motDePasseActuel" autocomplete="current-password" class="{{ $champ }}">
                @error('motDePasseActuel') <span class="text-[13px] text-ub-rouge">{{ $message }}</span> @enderror
            </x-espace.ligne-champ>

            <button type="submit" wire:target="supprimerPortfolio" wire:loading.attr="disabled"
                    class="mt-6 rounded bg-ub-rouge px-5 py-2 text-white hover:bg-ub-rouge-fonce">
                {{ __('Supprimer définitivement mon portfolio') }}
            </button>
        </form>
    </x-espace.section-pliante>
</div>
