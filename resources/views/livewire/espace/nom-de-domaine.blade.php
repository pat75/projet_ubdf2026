{{--
 | Option nom de domaine, sous les formules : fond noir, arrondi des cartes
 | de formules (rounded-3xl). Developpement seulement : filet rouge dev_only.
 |
 | Sur ce fond, les boutons de l'espace (noirs) s'inversent : blanc, texte
 | noir. Champs translucides, textes secondaires en blanc attenue.
--}}
@php
    $champ = 'w-full rounded-sm border border-white/40 bg-white/[.14] px-3 py-2 text-[15px] text-white placeholder-white/60 focus:border-white focus:bg-white/[.18] focus:outline-none';
    $bouton = 'bouton-espace bg-white! text-black! hover:bg-white/85!';
    $erreur = 'text-[13px] text-red-300';
@endphp
<div id="nom-de-domaine" class="mt-10">
    <section class="dev_only rounded-3xl! bg-black px-7 py-11 text-white ring-1 ring-white/10 sm:px-8 sm:py-12">
        <div class="text-[12px] font-semibold uppercase tracking-[.08em] text-white/60">{{ __('Option') }}</div>
        <h2 class="mt-1 text-[31px] font-semibold">{{ __('Réserver mon nom de domaine') }}</h2>
        <p class="mt-1 text-[14px] text-white/70">{{ __('Votre book à votre adresse, en .fr ou en .com.') }}</p>

        <div class="mt-6">
        @if ($domaine && ! in_array($domaine->statut, ['a_payer', 'echec_commande', 'rembourse'], true))
            {{-- Domaine paye : le detail est sur sa page. --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <span class="text-[15px] text-pretty">
                    <span class="block text-[18px] font-semibold text-ub-accent">{{ $domaine->nom }}</span>
                    <span class="block text-[14px] text-white/70">{{ $domaine->source === 'externe'
                        ? __('Votre domaine est déclaré. Il pointe vers votre book dès que sa configuration DNS est vérifiée.')
                        : __('Réservé pour vous. Il pointe vers votre book dès que sa configuration est terminée.') }}</span>
                </span>
                <a href="{{ route('espace.domaine') }}" class="{{ $bouton }} bouton-espace-grand px-5">{{ __('Gérer mon domaine') }}</a>
            </div>
        @else
            @if ($domaine?->statut === 'a_payer')
                <p class="mb-4 text-[14px] text-white/80 text-pretty">
                    <span class="block">{{ __('Paiement de :nom en attente de confirmation.', ['nom' => $domaine->nom]) }}</span>
                    <span class="block">{{ __('Rechargez la page dans un instant.') }}</span>
                </p>
            @elseif ($domaine?->statut === 'rembourse' || $domaine?->statut === 'echec_commande')
                <p class="mb-4 text-[14px] text-red-300 text-pretty">
                    <span class="block">{{ __('La réservation de :nom n’a pas abouti.', ['nom' => $domaine->nom]) }}</span>
                    <span class="block">{{ __('Vous êtes intégralement remboursé.') }}</span>
                </p>
            @endif

            @if (! $choix)
                {{-- 1. Recherche : meme pastille que le champ newsletter du pied de page
                     (fond #f2f0ed, bouton noir arrondi), globe au lieu de l'enveloppe,
                     extension choisie juste avant le bouton. --}}
                <form wire:submit="rechercher" class="flex items-center rounded-full bg-[#f2f0ed] py-1 pr-1 pl-4 sm:max-w-xl">
                    <svg class="h-[18px] w-[18px] flex-none text-[#6e6b68]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>
                    </svg>
                    <label for="recherche-domaine" class="sr-only">{{ __('Nom de domaine') }}</label>
                    <input id="recherche-domaine" type="text" wire:model="recherche" autocomplete="off" maxlength="70"
                           placeholder="{{ __('mon-domaine') }}"
                           class="min-w-0 flex-1 border-none bg-transparent px-3 py-2.5 text-[15px] text-[#141414] placeholder-[#8a8580] outline-none focus:ring-0">
                    <fieldset class="mr-2 flex flex-none gap-1.5">
                        <legend class="sr-only">{{ __('Extension') }}</legend>
                        @foreach (array_keys(config('domaines.extensions')) as $ext)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model="extension" value="{{ $ext }}" class="peer sr-only">
                                <span class="block w-14 rounded-full border border-[#b5b0aa] px-3 py-1.5 text-center text-[14px] font-semibold text-[#6e6b68] peer-checked:border-[#141414] peer-checked:bg-[#141414] peer-checked:text-[#f2f0ed] peer-focus-visible:ring-2 peer-focus-visible:ring-[#141414]/40">.{{ $ext }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                    <button type="submit" wire:loading.attr="disabled" wire:target="rechercher"
                            class="self-stretch rounded-full bg-[#141414] px-[18px] text-[14px] font-semibold text-[#f2f0ed] transition-colors hover:bg-[oklch(0.62_0.2_25)] disabled:opacity-60">{{ __('Vérifier') }}</button>
                </form>
                @error('recherche') <span class="mt-2 block {{ $erreur }}">{{ $message }}</span> @enderror

                {{-- Domaine deja possede ailleurs (BrancherDomaine). --}}
                <div x-data="{ ouvert: {{ $errors->has('existant') ? 'true' : 'false' }} }" class="mt-4">
                    <button type="button" x-show="! ouvert" @click="ouvert = true; $nextTick(() => $refs.existant.focus())"
                            class="text-[14px] text-white/80 underline hover:text-white">{{ __('J’ai déjà un nom de domaine') }}</button>
                    <form x-show="ouvert" x-cloak wire:submit="brancher" class="sm:max-w-xl">
                        <label for="domaine-existant" class="block text-[14px] text-white/80">{{ __('Votre nom de domaine, acheté chez un autre registrar') }}</label>
                        <div class="mt-1.5 flex flex-wrap gap-3">
                            <input id="domaine-existant" x-ref="existant" type="text" wire:model="existant" autocomplete="off" maxlength="253"
                                   placeholder="{{ __('mon-domaine.fr') }}" class="{{ $champ }} min-w-0 flex-1">
                            <button type="submit" wire:loading.attr="disabled" wire:target="brancher"
                                    class="{{ $bouton }} bouton-espace-grand px-5">{{ __('Brancher') }}</button>
                        </div>
                        @error('existant') <span class="mt-2 block {{ $erreur }}">{{ $message }}</span> @enderror
                        <span class="mt-2 block text-[13px] text-white/60 text-pretty">
                            <span class="block">{{ __('Gratuit : vous gardez votre domaine chez votre registrar.') }}</span>
                            <span class="block">{{ __('Nous vous indiquons ensuite les deux réglages DNS à faire.') }}</span>
                        </span>
                    </form>
                </div>

                @if ($resultats)
                    <div class="mt-5">
                        @foreach ($resultats as $extension => $r)
                            <div wire:key="ext-{{ $extension }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-white/15 py-3 last:border-b-0">
                                <span class="text-[19px] font-semibold text-ub-accent">{{ $r['nom'] }}</span>
                                <span class="flex items-center gap-4">
                                    @if ($r['disponible'])
                                        <span class="text-[18px]">{{ __(':prix € TTC / an', ['prix' => number_format($r['prix'], 2, ',', ' ')]) }}</span>
                                        <button type="button" wire:click="choisir('{{ $extension }}')"
                                                class="{{ $bouton }} bouton-espace-petit px-4">{{ __('Choisir') }}</button>
                                    @else
                                        <span class="text-[14px] text-white/50">{{ __('Déjà pris') }}</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                {{-- 2. Titulaire et consentements --}}
                @php($prix = number_format((float) \App\Models\Domaine::prix(substr(strrchr($choix, '.'), 1)), 2, ',', ' '))
                <form wire:submit="payer">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/15 pb-4">
                        <span class="text-[20px] font-semibold text-ub-accent">{{ $choix }}</span>
                        <span class="flex items-center gap-4">
                            <span class="text-[18px]">{{ __(':prix € TTC / an', ['prix' => $prix]) }}</span>
                            <button type="button" wire:click="changer" class="text-[14px] font-semibold text-white underline hover:text-white/80">{{ __('Changer') }}</button>
                        </span>
                    </div>

                    <h3 class="mt-5 text-[15px] font-semibold">{{ __('Titulaire du domaine') }}</h3>
                    <span class="mt-1 block text-[13px] text-white/70 text-pretty">
                        <span class="block">{{ __('Vous êtes propriétaire du domaine : ces informations sont transmises au registre.') }}</span>
                        <span class="block">{{ __('Elles sont reprises de Mon compte, vérifiez-les.') }}</span>
                    </span>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            'prenom' => __('Prénom'), 'nom' => __('Nom'), 'societe' => __('Société (facultatif)'),
                            'email' => __('Adresse mail'), 'telephone' => __('Téléphone'), 'adresse' => __('Adresse'),
                            'code_postal' => __('Code postal'), 'ville' => __('Ville'),
                        ] as $nomChamp => $libelle)
                            <label class="flex flex-col gap-1.5 text-[14px] text-white/80">{{ $libelle }}
                                <input type="{{ $nomChamp === 'email' ? 'email' : 'text' }}" wire:model="titulaire.{{ $nomChamp }}" class="{{ $champ }}"
                                       @if ($nomChamp === 'telephone') placeholder="+33.612345678" @endif>
                                @error('titulaire.'.$nomChamp) <span class="{{ $erreur }}">{{ $message }}</span> @enderror
                            </label>
                        @endforeach

                        <label class="flex flex-col gap-1.5 text-[14px] text-white/80">{{ __('Pays') }}
                            <select wire:model="titulaire.pays" class="{{ $champ }}">
                                @foreach ($pays as $code => $libelle)
                                    <option value="{{ $code }}" class="text-black">{{ __($libelle) }}</option>
                                @endforeach
                            </select>
                            @error('titulaire.pays') <span class="{{ $erreur }}">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="mt-6 space-y-3 border-t border-white/15 pt-5 text-[14px]">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="cgu" class="mt-1 accent-white">
                            <span>{{ __('J’accepte les') }}
                                <a href="{{ lien('cms.doc', app()->getLocale() === 'en' ? 'conditions-of-use' : 'conditions-dutilisations') }}"
                                   target="_blank" rel="noopener" class="underline hover:text-white/80">{{ __('conditions générales d’utilisation') }}</a>.</span>
                        </label>
                        @error('cgu') <span class="block {{ $erreur }}">{{ $message }}</span> @enderror

                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="renonciation" class="mt-1 accent-white">
                            <span class="text-pretty">
                                <span class="block">{{ __('Je demande l’enregistrement immédiat du domaine et renonce expressément à mon droit de rétractation.') }}</span>
                                <span class="block text-[13px] text-white/60">{{ __('Un nom de domaine est une prestation personnalisée, exécutée dès la commande (article L.221-28 du Code de la consommation).') }}</span>
                            </span>
                        </label>
                        @error('renonciation') <span class="block {{ $erreur }}">{{ $message }}</span> @enderror
                    </div>

                    @error('choix') <span class="mt-4 block {{ $erreur }}">{{ $message }}</span> @enderror

                    <button type="submit" wire:loading.attr="disabled" wire:target="payer"
                            class="{{ $bouton }} bouton-espace-grand mt-6 px-6">{{ __('Payer :prix € TTC', ['prix' => $prix]) }}</button>
                    <span class="mt-2 block text-[12px] text-white/60">{{ __('Paiement sécurisé par Payplug. Le domaine est commandé une fois le paiement confirmé.') }}</span>
                </form>
            @endif
        @endif
        </div>
    </section>
</div>
