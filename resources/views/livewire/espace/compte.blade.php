<div class="flex flex-col gap-5" x-data="{ locOpen: false }">

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

        {{-- Mot de passe et adresse mail : edition sur place, exactement
             comme les autres champs (voir Claude_design.md) — au repos,
             texte (ou points, pour le mot de passe) et un crayon ; au
             clic, le champ d'origine ; a la sortie, enregistrerChamp() du
             trait EnregistreChamps, appele directement en JS comme le fait
             x-espace.champ-editable. Un vrai <input>, et non un texte
             editable sur place : un mot de passe se saisit et se masque,
             il ne s'affiche jamais en clair au repos — d'ou l'oeil pour le
             relire pendant la frappe. Aucun des deux ne demande plus le
             mot de passe actuel : la session en cours prouve deja
             l'identite. --}}
        <x-espace.ligne :libelle="__('Mot de passe')" :prive="true">
            <div x-data="{ edition: false, visible: false, valide: false, valeur: '', erreur: null, minuteur: null, envoi: false,
                           annuler() { this.edition = false; this.valeur = ''; this.visible = false; this.erreur = null; },
                           valider() {
                               if (this.valeur === '' || this.envoi) return;
                               this.envoi = true;
                               $wire.enregistrerChamp('motDePasse', this.valeur).then((r) => {
                                   this.envoi = false;
                                   if (r?.erreur) { this.erreur = r.erreur; return; }
                                   this.annuler(); this.valide = true;
                                   clearTimeout(this.minuteur); this.minuteur = setTimeout(() => this.valide = false, 4000);
                               });
                           } }" class="flex w-full flex-col gap-1">
                <div class="flex items-center gap-2.5">
                    <span x-show="! edition" class="flex items-center">
                        <span class="text-[18px] tracking-[3px] text-ub-texte">••••••••••</span>
                        <button type="button" @click="edition = true; visible = true; erreur = null; $nextTick(() => $refs.champMotDePasse.focus())"
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

                    <span x-show="edition" x-cloak class="relative w-full max-w-80">
                        <input x-ref="champMotDePasse" :type="visible ? 'text' : 'password'" x-model="valeur" @input="erreur = null"
                               @keydown.enter.prevent="valider()"
                               @keydown.escape="annuler()"
                               @blur="annuler()"
                               autocomplete="new-password" placeholder="••••••••••" class="champ-espace pr-28">
                        <button type="button" @mousedown.prevent @click="visible = ! visible"
                                :title="visible ? @js(__('Masquer')) : @js(__('Afficher'))"
                                class="absolute right-20 top-1/2 -translate-y-1/2 text-ub-texte3 hover:text-ub-texte">
                            <svg x-show="! visible" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="visible" x-cloak class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58a3 3 0 0 0 4.24 4.24M6.53 6.53C4.2 8.06 2.25 12 2.25 12s3.75 7.5 9.75 7.5c1.9 0 3.53-.5 4.88-1.24M9.88 4.7A10.4 10.4 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a15.8 15.8 0 0 1-2.13 3.05"/>
                            </svg>
                        </button>
                        {{-- Mot de passe seulement : validation explicite par un
                             bouton, jamais a la sortie du champ (une faute de
                             frappe ne doit pas partir seule). mousedown.prevent
                             garde le focus dans le champ au clic. --}}
                        <button type="button" @mousedown.prevent @click="valider()"
                                :disabled="valeur === '' || envoi"
                                class="absolute inset-y-1 right-1 bg-black px-3 text-[13px] font-bold text-white hover:bg-[#333] disabled:opacity-60">
                            {{ __('Valider') }}
                        </button>
                    </span>

                </div>

                <span x-show="erreur" x-cloak x-text="erreur" class="text-[13px] text-ub-danger"></span>
            </div>
        </x-espace.ligne>

        <x-espace.ligne :libelle="__('Adresse mail')" :prive="true" :dernier="true">
            <div x-data="{ edition: false, valide: false, enregistre: @js($creatif->email), valeur: @js($creatif->email), erreur: null, minuteur: null, envoi: false, attente: null,
                           annuler() { this.edition = false; this.valeur = this.enregistre; this.erreur = null; },
                           valider() {
                               if (this.valeur === this.enregistre) { this.annuler(); return; }
                               if (this.valeur === '' || this.envoi) return;
                               this.envoi = true;
                               $wire.enregistrerChamp('email', this.valeur).then((r) => {
                                   this.envoi = false;
                                   if (r?.erreur) { this.erreur = r.erreur; return; }
                                   // L'adresse ne change qu'apres le lien de confirmation.
                                   this.attente = this.valeur; this.annuler(); this.valide = true;
                                   clearTimeout(this.minuteur); this.minuteur = setTimeout(() => this.valide = false, 4000);
                               });
                           } }" class="flex w-full flex-col gap-1">
                <div class="flex items-center">
                    <span x-show="! edition" class="flex items-center">
                        <span x-text="enregistre"></span>
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

                    {{-- Meme principe que le mot de passe : validation par le
                         bouton ou Entree, jamais a la sortie du champ ; pas
                         d'oeil, l'adresse est toujours lisible. --}}
                    <span x-show="edition" x-cloak class="relative w-full max-w-80">
                        <input x-ref="champEmail" type="email" x-model="valeur" @input="erreur = null"
                               @keydown.enter.prevent="valider()"
                               @keydown.escape="annuler()"
                               @blur="annuler()"
                               autocomplete="email" class="champ-espace pr-20">
                        <button type="button" @mousedown.prevent @click="valider()"
                                :disabled="valeur === '' || envoi"
                                class="absolute inset-y-1 right-1 bg-black px-3 text-[13px] font-bold text-white hover:bg-[#333] disabled:opacity-60">
                            {{ __('Valider') }}
                        </button>
                    </span>
                </div>

                <span x-show="erreur" x-cloak x-text="erreur" class="text-[13px] text-ub-danger"></span>
                <span x-show="attente" x-cloak class="text-[13px] text-ub-texte3"
                      x-text="@js(__('Lien de confirmation envoyé à :adresse. L’adresse changera une fois le lien suivi.')).replace(':adresse', attente)"></span>
            </div>
        </x-espace.ligne>
    </x-espace.carte>

    {{-- Profil public. --}}
    <x-espace.carte :titre="__('Profil')" :sous-titre="__('Ces informations apparaissent sur votre book.')">
        {{-- Textes : edition sur place, chacun s'enregistre seul. Listes :
             enregistrees des qu'elles changent. --}}
        <div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-x-5 gap-y-5">
            <x-espace.champ-editable nom="firstname" :valeur="$profil['firstname']"
                :libelle="__('Prénom').' *'" :vide="__('Ajouter votre prénom…')" />

            <x-espace.champ-editable nom="lastname" :valeur="$profil['lastname']"
                :libelle="__('Nom').' *'" :vide="__('Ajouter votre nom…')" />

            <x-espace.select-discret wire:model.live="categorie" :options="$categories" :valeur="$categorie"
                :libelle="__('Métier')" />

            <div>
                <x-espace.select-discret wire:model.live="statut" :options="array_combine($statuts, $statuts)" :valeur="$statut"
                    :libelle="__('Votre statut')" />
                @error('statut') <span class="text-[13px] text-ub-danger">{{ $message }}</span> @enderror
            </div>

            <x-espace.champ-editable nom="company" :valeur="$profil['company']"
                :libelle="__('Société')" :vide="__('Ajouter une société…')" />

            <x-espace.champ-editable nom="website" :valeur="$profil['website']"
                :libelle="__('Site web')" :vide="__('https://www.monsite.com')" />

            <x-espace.champ-editable nom="phone" :valeur="$profil['phone']" prive
                :libelle="__('Téléphone')" :vide="__('+33601020304')" />
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
            <div class="mt-5 border-t border-ub-filet pt-5"
                 x-data="{ edition: @js(! $facturation) }" x-on:siret-trouve.window="edition = false">
                <label class="flex flex-col gap-1 text-[12px] font-semibold uppercase tracking-[.06em] text-ub-texte3 sm:max-w-md">
                    {{ __('Numéro SIRET') }}

                    {{-- Au repos : texte + crayon, comme les autres champs
                         (voir Claude_design.md, « Édition sur place »). En
                         edition : le champ d'origine, avec sa recherche a
                         l'annuaire des que le numero est complet et juste. --}}
                    <span x-show="! edition" @click="edition = true"
                          class="flex w-fit cursor-text items-center text-[15px] font-normal normal-case tracking-normal text-ub-texte">
                        <span>{{ $facturation?->siretLisible() ?? __('Ajouter un SIRET…') }}</span>
                        <button type="button" @click.stop="edition = true" title="{{ __('Modifier') }}"
                                class="ml-2.5 shrink-0 text-ub-texte3 hover:text-ub-texte">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true">
                                <path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"/>
                            </svg>
                        </button>
                    </span>

                    <span x-show="edition" x-cloak class="relative block sm:max-w-md">
                        <input type="text" wire:model.live.debounce.500ms="siret"
                               inputmode="numeric" placeholder="552 081 317 66522" class="champ-espace pr-28 text-[15px] font-normal normal-case tracking-normal">
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

    {{-- Newsletter : desinscription possible a tout moment. --}}
    <x-espace.carte :titre="__('Newsletter')">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="text-[15px] font-semibold">{{ __('Recevoir la newsletter') }}</div>
                <div class="text-[14px] text-ub-texte3">{{ __('Actualités, sélections et conseils de :marque. Vous pouvez vous désinscrire à tout moment.', ['marque' => \App\Support\Marque::depuisCode(auth()->user()->brand ?: 'ub')->nom]) }}</div>
            </div>

            <x-espace.interrupteur wire:click="$toggle('newsletter')" :actif="$newsletter" :libelle="__('Recevoir la newsletter')" />
        </div>
    </x-espace.carte>

    {{-- Localisation, repliee par defaut. --}}
    <section class="carte-espace overflow-hidden">
        <button type="button" @click="locOpen = ! locOpen" :aria-expanded="locOpen"
                class="flex w-full items-center justify-between px-7 py-5.5 text-left hover:bg-[#fafaf8]">
            <span class="text-[20px] font-semibold">{{ __('Localisation') }}</span>
            <x-espace.picto x-show="! locOpen" nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
            <x-espace.picto x-show="locOpen" x-cloak nom="angle-bas" class="h-5 w-5 shrink-0 text-ub-texte" />
        </button>

        <div x-show="locOpen" x-collapse x-cloak class="px-7 pb-6">
            <div class="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-x-5 gap-y-5">
                <x-espace.champ-editable nom="address" :valeur="$profil['address']"
                    :libelle="__('Adresse')" :vide="__('Ajouter une adresse…')" />
                <x-espace.champ-editable nom="zipcode" :valeur="$profil['zipcode']"
                    :libelle="__('Code postal')" :vide="__('Ajouter…')" />
                <x-espace.champ-editable nom="city" :valeur="$profil['city']"
                    :libelle="__('Ville')" :vide="__('Ajouter une ville…')" />
                <x-espace.champ-editable nom="country" :valeur="$profil['country']"
                    :libelle="__('Pays')" :vide="__('Ajouter un pays…')" />
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

</div>
