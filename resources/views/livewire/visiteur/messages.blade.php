<div>
    {{-- Meme page que « Mes messages » des createurs (livewire/espace/messages),
         vue du visiteur : les bulles du visiteur a droite, celles du
         createur a gauche. --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Échanger, communiquer, deviser') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Mes messages') }}</h1>
        </div>

        <span class="inline-flex items-center gap-2 rounded-sm bg-black px-4.5 py-2.5 text-[14px] font-bold text-white">
            {{ trans_choice(':n non lu|:n non lus', $totalNonLus, ['n' => $totalNonLus]) }}
        </span>
    </div>

    @unless ($visiteur->email_verified_at)
        <div class="carte-espace mb-6 flex flex-wrap items-center justify-between gap-3 p-5">
            <p class="text-[15px] text-ub-texte2">
                {{ __('Confirmez votre adresse :email pour retrouver aussi les messages envoyés aux créatifs avant l’ouverture de votre compte.', ['email' => $visiteur->email]) }}
            </p>
            <form method="post" action="{{ lien('visiteur.confirmation') }}">
                @csrf
                <button type="submit" class="bouton-espace bouton-espace-petit px-4">{{ __('Renvoyer le mail') }}</button>
            </form>
        </div>
    @endunless

    <div class="space-y-4">
        <div class="carte-espace overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-ub-filet px-5 py-4">
                <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Mes échanges') }}</h2>

                @if ($conversations->total())
                    <span class="text-[13px] text-ub-texte3">
                        {{ __(':de–:a sur :total', ['de' => $conversations->firstItem(), 'a' => $conversations->lastItem(), 'total' => $conversations->total()]) }}
                    </span>
                @endif
            </div>

            @forelse ($conversations as $c)
                @php
                    $deplie = $ouvert === $c->id && $fil;
                    $createur = $c->user?->fullName() ?: $c->user?->login ?: __('Créatif');
                @endphp

                <div wire:key="conv-{{ $c->id }}"
                     @class(['relative', 'border border-ub-texte/50' => $deplie, 'border-b border-[#a8a8a7] last:border-b-0' => ! $deplie])>

                    <div wire:click="basculer({{ $c->id }})"
                         @class(['flex cursor-pointer items-center gap-3.5 px-5 py-3.5 text-left transition hover:bg-ub-accent-fond/40',
                             'bg-white' => $c->non_lus || $deplie, 'bg-[#fcfdfd]' => ! $c->non_lus && ! $deplie,
                         ])>

                        @if ($c->non_lus)
                            <x-espace.picto nom="courrier" class="h-4.5 w-4.5 shrink-0 text-ub-texte" />
                            <span class="sr-only">{{ __('Non lu') }}</span>
                        @else
                            <x-espace.picto nom="courrier-lu" class="h-4.5 w-4.5 shrink-0 text-ub-texte4" />
                        @endif

                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ub-accent-fond text-[14px] font-bold text-ub-accent-texte">
                            {{ mb_strtoupper(mb_substr($createur, 0, 1)) }}
                        </span>

                        <span class="min-w-0 flex-1">
                            <span @class(['block truncate text-[15px]', 'font-bold' => $c->non_lus])>{{ $createur }}</span>
                            <span class="block text-[13px] text-ub-texte3">{{ $c->last_message_at?->translatedFormat('l j F Y à H:i') }}</span>
                        </span>

                        <x-espace.picto :nom="$deplie ? 'angle-bas' : 'angle-droite'" class="h-5 w-5 shrink-0 text-ub-texte" />
                    </div>

                    @if ($deplie)
                        <div class="border-t border-ub-filet bg-white px-5 pb-6 pt-4" wire:key="fil-{{ $fil->id }}">
                            <p class="mb-5 text-[13px] text-ub-texte3">
                                <span class="font-semibold text-ub-texte2">{{ $fil->objet() }}</span>
                                @if ($fil->user)
                                    — <a href="{{ $fil->user->bookUrl() }}" target="_blank" rel="noopener" class="text-ub-accent-texte underline">{{ __('Voir son book') }}</a>
                                @endif
                            </p>

                            {{-- Le visiteur a droite (« Vous »), le createur a gauche. --}}
                            <ol class="space-y-7">
                                @foreach ($fil->messages as $m)
                                    @php($moi = ! $m->from_owner)
                                    <li @class(['flex flex-col', 'items-end' => $moi, 'items-start' => ! $moi])>
                                        <div @class(['relative max-w-[85%] px-4.5 py-3.5 text-[15px] leading-relaxed whitespace-pre-line',
                                                'bg-ub-accent-fond text-ub-texte' => $moi,
                                                'bg-[#efefed] text-ub-texte' => ! $moi,
                                            ])>{{ $m->body }}<span @class([
                                                'absolute -bottom-2.5 h-0 w-0 border-x-[10px] border-t-[11px] border-x-transparent',
                                                'border-t-ub-accent-fond' => $moi, 'border-t-[#efefed]' => ! $moi,
                                                'right-6' => $moi, 'left-6' => ! $moi,
                                            ]) aria-hidden="true"></span></div>

                                        <span @class(['mt-3.5 text-[12px] text-ub-texte3', 'pr-3' => $moi, 'pl-3' => ! $moi])>
                                            <span class="font-semibold text-ub-texte2">{{ $moi ? __('Vous') : $createur }}</span>
                                            — {{ $m->created_at?->translatedFormat('j F Y à H:i') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ol>

                            <form wire:submit="repondre" class="mt-7 space-y-2.5">
                                <textarea wire:model="reponse" rows="4" placeholder="{{ __('Votre réponse') }}"
                                          class="w-full border border-ub-bord px-3.5 py-2.5 text-[15px] outline-none focus:border-ub-accent focus:ring-2 focus:ring-ub-accent/20"></textarea>
                                @error('reponse') <p class="text-[13px] text-ub-danger">{{ $message }}</p> @enderror

                                @if ($suggestionIA)
                                    <div class="border border-ub-accent-texte/30 bg-ub-accent-fond px-3.5 py-3 text-[14px]">
                                        <p class="mb-1.5 text-[12px] font-bold uppercase tracking-[.06em] text-ub-accent-texte">{{ __('Suggestion de l’IA') }}</p>
                                        <p class="leading-relaxed text-ub-texte whitespace-pre-line">{{ $suggestionIA }}</p>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button type="button" wire:click="utiliserSuggestionIA" class="bouton-espace bouton-espace-petit px-3.5">
                                                {{ __('Utiliser ce texte') }}
                                            </button>
                                            <button type="button" wire:click="ignorerSuggestionIA"
                                                    class="bouton-espace-petit border border-ub-bord bg-white px-3.5 text-ub-texte hover:border-black">
                                                {{ __('Garder mon texte') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($erreurIA)
                                    <p class="text-[13px] text-ub-danger">{{ $erreurIA }}</p>
                                @endif

                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" wire:target="repondre" wire:loading.attr="disabled" class="bouton-espace bouton-espace-grand px-5">
                                        {{ __('Envoyer') }}
                                    </button>

                                    <button type="button" wire:click="corrigerReponse" wire:target="corrigerReponse" wire:loading.attr="disabled"
                                            class="bouton-espace bouton-espace-grand px-3.5">
                                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2 13.8 8.2 20 10 13.8 11.8 12 18 10.2 11.8 4 10 10.2 8.2 12 2ZM19 14.5 19.9 17.6 23 18.5 19.9 19.4 19 22.5 18.1 19.4 15 18.5 18.1 17.6 19 14.5ZM5.5 15 6.2 17.3 8.5 18 6.2 18.7 5.5 21 4.8 18.7 2.5 18 4.8 17.3 5.5 15Z"/>
                                        </svg>
                                        <span wire:loading.remove wire:target="corrigerReponse">{{ __('Correction IA') }}</span>
                                        <span wire:loading wire:target="corrigerReponse">{{ __('Correction…') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="px-5 py-12 text-center text-[15px] text-ub-texte3">{{ __('Aucun message pour le moment.') }}</p>
            @endforelse
        </div>

        @if ($conversations->lastPage() > 1)
            <div class="flex flex-wrap gap-1.5">
                @for ($i = 1; $i <= $conversations->lastPage(); $i++)
                    <button type="button" wire:click="gotoPage({{ $i }})"
                            @class(['h-10 min-w-10 cursor-pointer px-1 text-[14px] font-bold',
                                'bouton-espace' => $conversations->currentPage() === $i,
                                'border border-ub-bord bg-white text-ub-texte hover:border-black' => $conversations->currentPage() !== $i,
                            ])>{{ $i }}</button>
                @endfor
            </div>
        @endif
    </div>
</div>
