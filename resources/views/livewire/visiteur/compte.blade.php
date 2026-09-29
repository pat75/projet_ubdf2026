<div class="flex flex-col gap-5">

    <div class="mb-1 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Mon compte') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Mes informations') }}</h1>
        </div>
    </div>

    {{-- Identifiants de connexion : edition sur place, comme la page
         « Mon compte » des createurs (voir Claude_design.md). --}}
    <x-espace.carte :titre="__('Votre compte')"
                    :sous-titre="__('Identifiants de connexion. Les champs marqués <span class=\'text-ub-prive\'>●</span> restent privés.')">
        <x-espace.ligne :libelle="__('Mot de passe')" :prive="true">
            <div x-data="{ edition: false, visible: false, valide: false, valeur: '', erreur: null, minuteur: null }" class="flex w-full flex-col gap-1">
                <div class="flex max-w-60 items-center">
                    <span x-show="! edition" class="flex items-center">
                        <span class="text-[18px] tracking-[3px] text-ub-texte">••••••••••</span>
                        <button type="button" @click="edition = true; erreur = null; $nextTick(() => $refs.champMotDePasse.focus())"
                                :title="valide ? @js(__('Enregistré')) : @js(__('Modifier'))"
                                class="ml-2.5 shrink-0 text-ub-texte3 hover:text-ub-texte">
                            <svg x-show="! valide" class="h-4 w-4" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true">
                                <path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"/>
                            </svg>
                            <svg x-show="valide" x-cloak class="h-4 w-4 text-ub-succes" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </span>

                    <span x-show="edition" x-cloak class="relative w-full">
                        <input x-ref="champMotDePasse" :type="visible ? 'text' : 'password'" x-model="valeur" @input="erreur = null"
                               @keydown.escape="edition = false; valeur = ''"
                               @blur="if (valeur === '') { edition = false; return; }
                                      $wire.enregistrerChamp('motDePasse', valeur).then((r) => {
                                          if (r?.erreur) { erreur = r.erreur; return; }
                                          erreur = null; valeur = ''; visible = false; edition = false; valide = true;
                                          clearTimeout(minuteur); minuteur = setTimeout(() => valide = false, 4000);
                                      })"
                               autocomplete="new-password" placeholder="••••••••••" class="champ-espace pr-10">
                        <button type="button" @click="visible = ! visible"
                                :title="visible ? @js(__('Masquer')) : @js(__('Afficher'))"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-ub-texte3 hover:text-ub-texte">
                            <svg x-show="! visible" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="visible" x-cloak class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58a3 3 0 0 0 4.24 4.24M6.53 6.53C4.2 8.06 2.25 12 2.25 12s3.75 7.5 9.75 7.5c1.9 0 3.53-.5 4.88-1.24M9.88 4.7A10.4 10.4 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a15.8 15.8 0 0 1-2.13 3.05"/>
                            </svg>
                        </button>
                    </span>
                </div>

                <span x-show="erreur" x-cloak x-text="erreur" class="text-[13px] text-ub-danger"></span>
            </div>
        </x-espace.ligne>

        <x-espace.ligne :libelle="__('Adresse mail')" :prive="true" :dernier="true">
            <div x-data="{ edition: false, valide: false, envoye: false, valeur: @js($visiteur->email), erreur: null, minuteur: null }" class="flex w-full flex-col gap-1">
                <div class="flex items-center">
                    <span x-show="! edition" class="flex items-center">
                        <span x-text="valeur"></span>
                        <button type="button" @click="edition = true; erreur = null; $nextTick(() => $refs.champEmail.focus())"
                                :title="valide ? @js(__('Enregistré')) : @js(__('Modifier'))"
                                class="ml-2.5 shrink-0 text-ub-texte3 hover:text-ub-texte">
                            <svg x-show="! valide" class="h-4 w-4" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true">
                                <path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"/>
                            </svg>
                            <svg x-show="valide" x-cloak class="h-4 w-4 text-ub-succes" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </span>

                    <span x-show="edition" x-cloak class="w-full max-w-xs">
                        <input x-ref="champEmail" type="email" x-model="valeur" @input="erreur = null"
                               @keydown.escape="edition = false; valeur = @js($visiteur->email)"
                               @blur="if (valeur === @js($visiteur->email)) { edition = false; return; }
                                      $wire.enregistrerChamp('email', valeur).then((r) => {
                                          if (r?.erreur) { erreur = r.erreur; return; }
                                          erreur = null; edition = false; valide = true; envoye = true;
                                          clearTimeout(minuteur); minuteur = setTimeout(() => valide = false, 4000);
                                      })"
                               autocomplete="email" class="champ-espace">
                    </span>
                </div>

                <span x-show="envoye" x-cloak class="text-[13px] text-ub-texte3">{{ __('Un lien de confirmation vient d’être envoyé à cette adresse.') }}</span>
                <span x-show="erreur" x-cloak x-text="erreur" class="text-[13px] text-ub-danger"></span>
            </div>
        </x-espace.ligne>
    </x-espace.carte>

    <x-espace.carte :titre="__('Profil')" :sous-titre="__('Votre nom accompagne vos messages aux créatifs et votre mémoBook partagé.')">
        <div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-x-5 gap-y-5">
            <x-espace.champ-editable nom="firstname" :valeur="(string) $visiteur->firstname"
                :libelle="__('Prénom')" :vide="__('Ajouter votre prénom…')" />

            <x-espace.champ-editable nom="lastname" :valeur="(string) $visiteur->lastname"
                :libelle="__('Nom')" :vide="__('Ajouter votre nom…')" />
        </div>
    </x-espace.carte>

    {{-- Newsletter : desinscription possible a tout moment. --}}
    <x-espace.carte :titre="__('Newsletter')">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="text-[15px] font-semibold">{{ __('Recevoir la newsletter') }}</div>
                <div class="text-[14px] text-ub-texte3">{{ __('Actualités, sélections et conseils de :marque. Vous pouvez vous désinscrire à tout moment.', ['marque' => \App\Support\Marque::depuisCode($visiteur->brand ?: 'ub')->nom]) }}</div>
            </div>

            <x-espace.interrupteur wire:click="$toggle('newsletter')" :actif="$newsletter" :libelle="__('Recevoir la newsletter')" />
        </div>
    </x-espace.carte>

    {{-- Zone dangereuse. --}}
    <section x-data="{ ouvert: false }"
             class="rounded-ub-carte border border-ub-danger-bord bg-ub-danger-fond px-7 py-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="text-[17px] font-semibold text-ub-danger">{{ __('Supprimer mon compte') }}</div>
                <div class="mt-0.5 text-[14px] text-[#8a5a5a]">{{ __('Votre mémoBook, vos visites et votre partage public sont effacés.') }}</div>
            </div>

            <button type="button" @click="ouvert = ! ouvert" class="bouton-espace bouton-espace-petit px-4">
                {{ __('Supprimer…') }}
            </button>
        </div>

        <form wire:submit="supprimerCompte" x-show="ouvert" x-collapse x-cloak class="mt-5 border-t border-ub-danger-bord pt-5">
            <p class="mb-4 text-[14px] text-[#8a5a5a]">
                {{ __('La suppression est définitive. Les messages déjà envoyés aux créatifs restent chez eux.') }}
            </p>

            <label class="flex flex-col gap-1.5 text-[14px] text-ub-texte2 sm:max-w-xs">{{ __('Mot de passe actuel') }}
                <input type="password" wire:model="motDePasseActuel" autocomplete="current-password" class="champ-espace">
                @error('motDePasseActuel') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
            </label>

            <button type="submit" wire:target="supprimerCompte" wire:loading.attr="disabled"
                    class="bouton-espace bouton-espace-grand mt-4 px-4">
                {{ __('Supprimer définitivement mon compte') }}
            </button>
        </form>
    </section>

</div>
