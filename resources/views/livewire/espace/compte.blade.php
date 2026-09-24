<div class="flex flex-col gap-5" x-data="{ locOpen: false, acces: @js($errors->hasAny(['email', 'nouveauMotDePasse', 'motDePasseActuel'])) }">

    <div class="flex flex-col gap-1.5 px-1 pb-2">
        <div class="text-[13px] font-semibold uppercase tracking-[1.4px] text-ub-accent-fonce">{{ __('Mon compte') }}</div>
        <h1 class="text-[36px] font-light leading-[1.15] tracking-[-.3px]">
            {{ __('Mes informations') }} <span class="font-semibold">{{ __('publiques et privées') }}</span>
        </h1>
    </div>

    <div class="flex items-center gap-3 rounded-ub-bandeau bg-ub-accent-fond px-4.5 py-3.5 text-[15px] text-[#1f5a5f]">
        <span class="h-2 w-2 shrink-0 rounded-full bg-ub-accent"></span>
        {{ __('Votre connexion s’est faite via votre identifiant et votre mot de passe') }}
    </div>

    {{-- Identifiants de connexion. --}}
    <x-espace.carte :titre="__('Votre compte')"
                    :sous-titre="__('Identifiants de connexion. Les champs marqués <span class=\'text-ub-prive\'>●</span> restent privés.')">

        <x-espace.ligne :libelle="__('Url de votre book')" :aide="__('Adresse publique de votre portfolio')">
            <span class="break-all">{{ $creatif->bookUrl() }}</span>
            <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"
               class="text-[14px] font-semibold text-ub-accent-fonce hover:underline">{{ __('Ouvrir') }} ↗</a>
        </x-espace.ligne>

        <x-espace.ligne :libelle="__('Identifiant / Login')" :aide="__('Utilisé pour vous connecter')">
            {{ $creatif->login }}
        </x-espace.ligne>

        <x-espace.ligne :libelle="__('Mot de passe')" :prive="true">
            <span class="text-[18px] tracking-[3px]" x-show="! acces">••••••••••</span>
            <button type="button" @click="acces = ! acces"
                    class="bouton-espace bouton-espace-petit px-3.5">
                <span x-show="! acces">{{ __('Modifier') }}</span>
                <span x-show="acces" x-cloak>{{ __('Annuler') }}</span>
            </button>
        </x-espace.ligne>

        <x-espace.ligne :libelle="__('Adresse mail')" :prive="true" :dernier="true">
            <span x-show="! acces">{{ $creatif->email }}</span>
            <span x-show="acces" x-cloak class="text-[14px] text-ub-texte3">{{ __('Modifiable ci-dessous.') }}</span>
        </x-espace.ligne>

        {{-- Adresse et mot de passe changent ensemble : l'un comme l'autre
             demande le mot de passe actuel. --}}
        <form wire:submit="enregistrerAcces" x-show="acces" x-collapse x-cloak class="mt-3 rounded-ub bg-[#fafaf8] p-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Adresse mail') }}
                    <input type="email" wire:model="email" autocomplete="email" class="champ-espace">
                    @error('email') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Mot de passe actuel') }}
                    <input type="password" wire:model="motDePasseActuel" autocomplete="current-password" class="champ-espace">
                    @error('motDePasseActuel') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Nouveau mot de passe') }}
                    <input type="password" wire:model="nouveauMotDePasse" autocomplete="new-password" class="champ-espace">
                    @error('nouveauMotDePasse') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Confirmation') }}
                    <input type="password" wire:model="nouveauMotDePasse_confirmation" autocomplete="new-password" class="champ-espace">
                </label>
            </div>

            <button type="submit" wire:target="enregistrerAcces" wire:loading.attr="disabled"
                    class="bouton-espace bouton-espace-grand mt-4 px-4">
                {{ __('Enregistrer') }}
            </button>
        </form>
    </x-espace.carte>

    {{-- Profil public. --}}
    <x-espace.carte :titre="__('Profil')" :sous-titre="__('Ces informations apparaissent sur votre book.')">
        <div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-x-5 gap-y-4.5">
            <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Prénom') }} *
                <input type="text" wire:model="profil.firstname" class="champ-espace">
                @error('profil.firstname') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
            </label>

            <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Nom') }} *
                <input type="text" wire:model="profil.lastname" class="champ-espace">
                @error('profil.lastname') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
            </label>

            <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Métier') }}
                <select wire:model="categorie" class="champ-espace">
                    <option value="">—</option>
                    @foreach ($categories as $id => $nom)
                        <option value="{{ $id }}">{{ $nom }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Votre statut') }}
                <select wire:model="statut" class="champ-espace">
                    <option value="">—</option>
                    @foreach ($statuts as $valeur)
                        <option value="{{ $valeur }}">{{ $valeur }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Société') }}
                <input type="text" wire:model="profil.company" class="champ-espace">
            </label>

            <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Site web') }}
                <input type="url" wire:model="profil.website" placeholder="www.monsite.com" class="champ-espace">
                @error('profil.website') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
            </label>

            <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">
                <span>{{ __('Téléphone') }} <span class="text-[10px] text-ub-prive" title="{{ __('Donnée privée') }}">●</span></span>
                <input type="tel" wire:model="profil.phone" placeholder="+33601020304" class="champ-espace">
            </label>
        </div>

        {{-- Interrupteur SMS, sous son filet. --}}
        <div class="mt-4.5 flex items-center justify-between gap-4 border-t border-ub-filet pt-4.5">
            <div>
                <div class="text-[15px] font-semibold">
                    {{ __('Contacts par SMS') }}
                    <span class="ml-1 rounded bg-[#f1eafa] px-1.5 py-px text-[12px] font-semibold text-[#7a4fb5]">{{ __('Bêta') }}</span>
                </div>
                <div class="text-[14px] text-ub-texte3">{{ __('Les SMS sont envoyés entre 9h et 21h') }}</div>
            </div>

            <x-espace.interrupteur wire:click="$toggle('sms')" :actif="$sms" :libelle="__('Contacts par SMS')" />
        </div>
    </x-espace.carte>

    {{-- Facturation electronique. --}}
    <x-espace.carte :titre="__('Facturation électronique')"
                    :sous-titre="__('Obligatoire entre professionnels à compter de 2026. Vos informations légales sont relevées auprès de l’annuaire des entreprises de l’État.')">

        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="text-[15px] font-semibold">{{ __('Je facture en tant que professionnel') }}</div>
                <div class="text-[14px] text-ub-texte3">{{ __('Votre SIRET et votre raison sociale figureront sur vos factures.') }}</div>
            </div>

            <x-espace.interrupteur wire:click="basculerProfessionnel" :actif="$professionnel" :libelle="__('Facturer en professionnel')" />
        </div>

        @if ($professionnel)
            <div class="mt-5 border-t border-ub-filet pt-5">
                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2 sm:max-w-md">
                    {{ __('Numéro SIRET') }}
                    {{-- Pas de bouton : le numero part des qu'il est complet
                         et que sa cle est bonne. La demi-seconde de repit
                         evite d'appeler l'annuaire a chaque frappe. --}}
                    <span class="relative block sm:max-w-md">
                        <input type="text" wire:model.live.debounce.500ms="siret"
                               inputmode="numeric" placeholder="552 081 317 66522" class="champ-espace pr-28">
                        <span wire:loading wire:target="siret,verifierSiret"
                              class="absolute right-3 top-1/2 -translate-y-1/2 text-[13px] text-ub-texte3">{{ __('Recherche…') }}</span>
                    </span>
                </label>

                @if ($erreurSiret)
                    <p class="mt-2 text-[14px] text-ub-danger">{{ $erreurSiret }}</p>
                @endif

                @if ($facturation)
                    {{-- Les informations relevees, sur trois colonnes pour
                         rester lisibles sous le numero. --}}
                    <dl class="mt-5 grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-x-5 gap-y-4 rounded-ub bg-[#fafaf8] p-5 text-[15px]">
                        <x-espace.donnee :libelle="__('Raison sociale')">{{ $facturation->company_name }}</x-espace.donnee>
                        <x-espace.donnee :libelle="__('SIRET')">{{ $facturation->siretLisible() }}</x-espace.donnee>
                        <x-espace.donnee :libelle="__('TVA intracommunautaire')">{{ $facturation->vat_number ?: '—' }}</x-espace.donnee>

                        <x-espace.donnee :libelle="__('Forme juridique')">{{ $facturation->formeJuridique() ?: '—' }}</x-espace.donnee>
                        <x-espace.donnee :libelle="__('Code APE / NAF')">{{ $facturation->naf_code ?: '—' }}</x-espace.donnee>
                        <x-espace.donnee :libelle="__('Immatriculation')">{{ $facturation->established_on?->format('d/m/Y') ?: '—' }}</x-espace.donnee>

                        <x-espace.donnee :libelle="__('Adresse')" class="sm:col-span-2">{{ $facturation->adresseComplete() ?: '—' }}</x-espace.donnee>
                        <x-espace.donnee :libelle="__('État')">
                            @if ($facturation->actif())
                                <span class="text-[#1f7a4a]">{{ __('En activité') }}</span>
                            @else
                                <span class="text-ub-danger">{{ __('Établissement fermé') }}</span>
                            @endif
                        </x-espace.donnee>
                    </dl>

                    <p class="mt-2 text-[13px] text-ub-texte3">
                        {{ __('Relevé le :date auprès de l’annuaire des entreprises.', ['date' => $facturation->checked_at?->format('d/m/Y')]) }}
                    </p>
                @endif
            </div>
        @endif
    </x-espace.carte>

    {{-- Localisation, repliee par defaut. --}}
    <section class="carte-espace overflow-hidden">
        <button type="button" @click="locOpen = ! locOpen" :aria-expanded="locOpen"
                class="flex w-full items-center justify-between px-7 py-5.5 text-left hover:bg-[#fafaf8]">
            <span class="text-[20px] font-semibold">{{ __('Localisation') }}</span>
            <span class="text-[18px] text-[#888] transition-transform duration-200" :class="locOpen && 'rotate-90'">›</span>
        </button>

        <div x-show="locOpen" x-collapse x-cloak class="px-7 pb-6">
            <div class="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-x-5 gap-y-4.5">
                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Adresse') }}
                    <input type="text" wire:model="profil.address" class="champ-espace">
                </label>
                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Code postal') }}
                    <input type="text" wire:model="profil.zipcode" class="champ-espace">
                </label>
                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Ville') }}
                    <input type="text" wire:model="profil.city" class="champ-espace">
                </label>
                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Pays') }}
                    <input type="text" wire:model="profil.country" class="champ-espace">
                </label>
            </div>
        </div>
    </section>

    {{-- Zone dangereuse. --}}
    <section x-data="{ ouvert: false }"
             class="rounded-ub-carte border border-ub-danger-bord bg-ub-danger-fond px-7 py-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="text-[17px] font-semibold text-ub-danger">{{ __('Supprimer mon portfolio') }}</div>
                <div class="mt-0.5 text-[14px] text-[#8a5a5a]">{{ __('Votre book quitte le site et vos images ne sont plus servies.') }}</div>
            </div>

            <button type="button" @click="ouvert = ! ouvert"
                    class="bouton-espace bouton-espace-petit px-4">
                {{ __('Supprimer…') }}
            </button>
        </div>

        <form wire:submit="supprimerPortfolio" x-show="ouvert" x-collapse x-cloak class="mt-5 border-t border-ub-danger-bord pt-5">
            <p class="mb-4 text-[14px] text-[#8a5a5a]">
                {{ __('Vos données sont conservées quelque temps : écrivez-nous si vous changez d’avis.') }}
            </p>

            <div class="grid gap-4 sm:max-w-lg sm:grid-cols-2">
                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Recopiez votre identifiant') }}
                    <input type="text" wire:model="confirmationSuppression" autocomplete="off" placeholder="{{ $creatif->login }}" class="champ-espace">
                    @error('confirmationSuppression') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2">{{ __('Mot de passe actuel') }}
                    <input type="password" wire:model="motDePasseActuel" autocomplete="current-password" class="champ-espace">
                    @error('motDePasseActuel') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
                </label>
            </div>

            <button type="submit" wire:target="supprimerPortfolio" wire:loading.attr="disabled"
                    class="bouton-espace bouton-espace-grand mt-4 px-4">
                {{ __('Supprimer définitivement mon portfolio') }}
            </button>
        </form>
    </section>

    {{-- Barre d'enregistrement : elle ne parait que si le formulaire a
         bouge, et suit le defilement en bas de fenetre. --}}
    @if ($this->modifie)
        <div class="sticky bottom-5 flex items-center justify-between gap-3 rounded-ub-barre bg-[#2f2f2f] py-3 pl-5 pr-3.5 text-white shadow-[0_10px_30px_rgba(0,0,0,.2)]">
            <span class="text-[15px]">{{ __('Modifications non enregistrées') }}</span>

            <div class="flex gap-2">
                <button type="button" wire:click="annuler"
                        class="bouton-espace bouton-espace-petit border border-white/25 px-3.5">{{ __('Annuler') }}</button>

                <button type="button" wire:click="enregistrerProfil" wire:target="enregistrerProfil" wire:loading.attr="disabled"
                        class="bouton-espace bouton-espace-grand px-4">{{ __('Enregistrer') }}</button>
            </div>
        </div>
    @endif
</div>
