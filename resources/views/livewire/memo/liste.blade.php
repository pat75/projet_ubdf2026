<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Repérer, garder, comparer') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('mémoBook') }}</h1>
        </div>

    </div>

    {{-- Recherche (loupe devant ; la liste suit la frappe) et, a droite,
         l'export PDF : deux colonnes sur une meme ligne. --}}
    <div class="flex flex-wrap items-center gap-3">
        <form wire:submit="$refresh" class="relative w-full min-w-0 sm:w-1/2" role="search">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-ub-texte3" viewBox="0 0 451 451" fill="currentColor" aria-hidden="true">
                <path d="M447.05,428l-109.6-109.6c29.4-33.8,47.2-77.9,47.2-126.1C384.65,86.2,298.35,0,192.35,0C86.25,0,0.05,86.3,0.05,192.3s86.3,192.3,192.3,192.3c48.2,0,92.3-17.8,126.1-47.2L428.05,447c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4C452.25,441.8,452.25,433.2,447.05,428z M26.95,192.3c0-91.2,74.2-165.3,165.3-165.3c91.2,0,165.3,74.2,165.3,165.3s-74.1,165.4-165.3,165.4C101.15,357.7,26.95,283.5,26.95,192.3z"/>
            </svg>
            <input type="search" wire:model.live.debounce.300ms="recherche"
                   placeholder="{{ __('Rechercher par nom…') }}" aria-label="{{ __('Rechercher par nom') }}"
                   class="champ-espace h-[43px] pl-10 pr-4 text-[15px] font-normal normal-case tracking-normal">
        </form>
        @if ($total > 0)
        {{-- Export PDF et ses options (engrenage) : le code QR de chaque
             book, sous sa fiche. Le choix reste en memoire du navigateur. --}}
        <div class="flex items-center gap-2 sm:ml-auto" wire:ignore
             x-data="{
                 options: false,
                 qr: (() => { try { return localStorage.getItem('memo_pdf_qr') === '1' } catch { return false } })(),
                 basculerQr() { this.qr = ! this.qr; try { localStorage.setItem('memo_pdf_qr', this.qr ? '1' : '0') } catch {} },
             }"
             @click.outside="options = false" @keydown.escape.window="options = false">
            <a :href="@js(lien('memobook.pdf')) + (qr ? '?qr=1' : '')" href="{{ lien('memobook.pdf') }}" target="_blank" rel="noopener"
               class="bouton-espace bouton-espace-grand px-5">
                {{ __('Exporter en PDF') }}
            </a>

            <div class="relative">
                <button type="button" @click="options = ! options" :aria-expanded="options"
                        class="bouton-espace-grand inline-flex w-[43px] items-center justify-center border border-ub-bord bg-white px-0 text-ub-texte hover:bg-ub-fond"
                        title="{{ __('Options du PDF') }}" aria-label="{{ __('Options du PDF') }}">
                    <x-espace.icone nom="reglage" class="h-4 w-4" />
                </button>

                <div x-show="options" x-cloak x-transition.opacity
                     class="absolute right-0 top-full z-20 mt-2 w-72 border border-ub-bord bg-white p-4 shadow-ub">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-[14px] text-ub-texte">{{ __('Code QR de chaque book') }}</span>
                        <button type="button" role="switch" :aria-checked="qr" @click="basculerQr()"
                                class="flex h-6.5 w-[46px] shrink-0 cursor-pointer rounded-[13px] p-[3px] transition-colors duration-200"
                                :class="qr ? 'justify-end bg-ub-accent' : 'justify-start bg-[#d6d6d3]'">
                            <span class="h-5 w-5 rounded-full bg-white shadow-[0_1px_3px_rgba(0,0,0,.2)]"></span>
                            <span class="sr-only">{{ __('Code QR de chaque book') }}</span>
                        </button>
                    </div>
                    <p class="mt-2 text-[12px] text-ub-texte3">{{ __('Ajouté sous chaque book, il ouvre son portfolio depuis un téléphone.') }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Partage public : une page en lecture seule (liste des books, sans
         messages ni retrait), a l'adresse affichee une fois active. --}}
    <div class="mt-5">
        <div class="flex items-center gap-3">
            <button type="button" role="switch" aria-checked="{{ $partage ? 'true' : 'false' }}" wire:click="basculerPartage"
                    wire:loading.attr="disabled" wire:target="basculerPartage"
                    @class(['flex h-6.5 w-[46px] shrink-0 cursor-pointer rounded-[13px] p-[3px] transition-colors duration-200',
                        'justify-end bg-ub-accent' => $partage, 'justify-start bg-[#d6d6d3]' => ! $partage])>
                <span class="h-5 w-5 rounded-full bg-white shadow-[0_1px_3px_rgba(0,0,0,.2)]"></span>
                <span class="sr-only">{{ __('Partager publiquement mon mémoBook') }}</span>
            </button>
            <span class="text-[15px] text-ub-texte">{{ __('Partager publiquement mon mémoBook') }}</span>
        </div>

        @if ($partage)
            <div class="mt-3" x-data="{ qr: false, copie: false }">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $partage->url() }}" target="_blank" rel="noopener"
                       class="min-w-0 truncate text-[14px] text-ub-accent-texte hover:underline">{{ $partage->url() }}</a>
                    <button type="button" class="bouton-espace bouton-espace-petit px-4"
                            @click="navigator.clipboard.writeText(@js($partage->url())); copie = true; setTimeout(() => copie = false, 2000)">
                        <span x-show="! copie">{{ __('Copier') }}</span>
                        <span x-show="copie" x-cloak>{{ __('Copié') }}</span>
                    </button>
                    <button type="button" @click="qr = ! qr" :aria-expanded="qr"
                            class="bouton-espace-petit inline-flex items-center border border-ub-bord bg-white px-4 font-semibold text-ub-texte hover:bg-ub-fond">
                        <span x-text="qr ? @js(__('Masquer le QR code')) : @js(__('Afficher le QR code'))"></span>
                    </button>
                </div>
                <div x-show="qr" x-cloak class="mt-3 inline-block bg-white p-2">{!! $qrPartage !!}</div>
            </div>
        @endif
    </div>

    @if ($books->isEmpty())
        <p class="mt-8 text-[15px] text-ub-texte3">
            @if ($recherche !== '')
                {{ __('Aucun book de votre mémoBook ne correspond à « :q ».', ['q' => $recherche]) }}
            @else
                {{ __('Votre mémoBook est vide. Cliquez sur le cœur d’un book, sur le portail, pour le garder ici.') }}
            @endif
        </p>
    @else
        @include('memo.partials.grille', ['interactif' => true])
    @endif

    {{-- Messages echanges avec un createur du memo : tous ses fils, en
         bulles (charte : gris a gauche pour le createur, accent a droite
         pour soi). Repondre passe par la messagerie par lien. --}}
    @if ($bookMessages)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center sm:p-6"
             wire:click.self="fermerMessages" x-data @keydown.escape.window="$wire.fermerMessages()"
             role="dialog" aria-modal="true" aria-labelledby="memo-messages-titre">
            <div class="flex max-h-[88vh] w-full max-w-[640px] flex-col overflow-hidden bg-white shadow-ub sm:rounded-sm"
                 style="border: 4px solid rgba(107,114,128,0.4)">
                <div class="flex items-center justify-between gap-4 border-b border-ub-filet px-6 py-4">
                    <div class="min-w-0">
                        <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Messages échangés avec') }}</div>
                        <h2 id="memo-messages-titre" class="truncate font-titre text-[24px] font-light text-ub-texte">{{ $bookMessages->fullName() }}</h2>
                    </div>
                    <button type="button" wire:click="fermerMessages" class="shrink-0 text-ub-texte3 hover:text-ub-texte" aria-label="{{ __('Fermer') }}">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto px-6 pb-6">
                    @foreach ($fils as $fil)
                        <section wire:key="fil-{{ $fil->id }}" class="border-b border-ub-filet py-5 last:border-b-0">
                            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                                <p class="text-[13px] text-ub-texte3">
                                    <span class="font-semibold text-ub-texte2">{{ $fil->objet() }}</span>
                                    — {{ $fil->created_at?->translatedFormat('j F Y') }}
                                </p>
                                <a href="{{ lien('memobook.message', ['conversation' => $fil->id]) }}" class="bouton-espace bouton-espace-petit px-4">{{ __('Répondre') }}</a>
                            </div>

                            <ol class="space-y-7">
                                @foreach ($fil->messages as $m)
                                    @php($moi = ! $m->from_owner)
                                    <li @class(['flex flex-col', 'items-end' => $moi, 'items-start' => ! $moi])>
                                        <div @class(['relative max-w-[85%] px-4.5 py-3.5 text-[15px] leading-relaxed whitespace-pre-line text-ub-texte',
                                                'bg-ub-accent-fond' => $moi, 'bg-[#efefed]' => ! $moi,
                                            ])>{{ $m->body }}<span @class([
                                                'absolute -bottom-2.5 h-0 w-0 border-x-[10px] border-t-[11px] border-x-transparent',
                                                'border-t-ub-accent-fond right-6' => $moi, 'border-t-[#efefed] left-6' => ! $moi,
                                            ]) aria-hidden="true"></span></div>

                                        <span @class(['mt-3.5 text-[12px] text-ub-texte3', 'pr-3' => $moi, 'pl-3' => ! $moi])>
                                            <span class="font-semibold text-ub-texte2">{{ $moi ? __('Vous') : $bookMessages->fullName() }}</span>
                                            — {{ $m->created_at?->translatedFormat('j F Y à H:i') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    {{-- Ecrire a un createur du memo : meme depot que le formulaire de
         contact du portail. L'adresse est celle du compte, non modifiable. --}}
    @if ($bookEcriture)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center sm:p-6"
             wire:click.self="fermerEcriture" x-data @keydown.escape.window="$wire.fermerEcriture()"
             role="dialog" aria-modal="true" aria-labelledby="memo-ecrire-titre">
            <div class="flex max-h-[88vh] w-full max-w-[560px] flex-col overflow-hidden bg-white shadow-ub sm:rounded-sm"
                 style="border: 4px solid rgba(107,114,128,0.4)">
                <div class="flex items-center justify-between gap-4 border-b border-ub-filet px-6 py-4">
                    <div class="min-w-0">
                        <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Envoyer un message à') }}</div>
                        <h2 id="memo-ecrire-titre" class="truncate font-titre text-[24px] font-light text-ub-texte">{{ $bookEcriture->fullName() }}</h2>
                    </div>
                    <button type="button" wire:click="fermerEcriture" class="shrink-0 text-ub-texte3 hover:text-ub-texte" aria-label="{{ __('Fermer') }}">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto px-6 py-5">
                    @if ($messageEnvoye)
                        <p class="rounded-ub bg-[#e7f5ec] px-3.5 py-2.5 text-[15px] text-[#1f7a4a]">
                            ✓ {{ __('Message envoyé. :nom le reçoit par mail ; sa réponse vous arrivera à :email.', ['nom' => $bookEcriture->fullName(), 'email' => $adresseExpediteur]) }}
                        </p>
                        <div class="mt-5 flex justify-end">
                            <button type="button" wire:click="fermerEcriture" class="bouton-espace bouton-espace-grand px-6">{{ __('Fermer') }}</button>
                        </div>
                    @else
                        <form wire:submit="envoyerMessage" class="space-y-4">
                            <div>
                                <label for="memo-nom" class="mb-1 block text-[13px] font-semibold text-ub-texte2">{{ __('Votre nom') }}</label>
                                <input id="memo-nom" type="text" wire:model="nomExpediteur" autocomplete="name"
                                       class="champ-espace h-[43px] text-[15px] font-normal normal-case tracking-normal">
                                @error('nomExpediteur') <p class="mt-1 text-[13px] text-ub-danger">{{ $message }}</p> @enderror
                            </div>

                            <p class="text-[13px] text-ub-texte3">{{ __('Réponse envoyée à :email', ['email' => $adresseExpediteur]) }}</p>

                            <div>
                                <label for="memo-message" class="mb-1 block text-[13px] font-semibold text-ub-texte2">{{ __('Votre message') }}</label>
                                <textarea id="memo-message" wire:model="messageTexte" rows="6"
                                          class="w-full border border-ub-bord px-3.5 py-2.5 text-[15px] outline-none focus:border-ub-accent focus:ring-2 focus:ring-ub-accent/20"></textarea>
                                @error('messageTexte') <p class="mt-1 text-[13px] text-ub-danger">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="fermerEcriture"
                                        class="bouton-espace-grand inline-flex items-center border border-ub-bord bg-white px-5 font-semibold text-ub-texte hover:bg-ub-fond">{{ __('Annuler') }}</button>
                                <button type="submit" class="bouton-espace bouton-espace-grand px-6" wire:loading.attr="disabled" wire:target="envoyerMessage">
                                    <span wire:loading.remove wire:target="envoyerMessage">{{ __('Envoyer') }}</span>
                                    <span wire:loading wire:target="envoyerMessage">{{ __('Envoi…') }}</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
