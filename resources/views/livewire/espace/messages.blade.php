<div>
    {{-- En-tete : le nombre de demandes non lues, tous dossiers confondus
         sauf la poubelle. --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Échanger, communiquer, deviser') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Mes messages') }}</h1>
        </div>

        <span class="inline-flex items-center gap-2 rounded-sm bg-black px-4.5 py-2.5 text-[14px] font-bold text-white">
            {{ trans_choice(':n non lu|:n non lus', $totalNonLus, ['n' => $totalNonLus]) }}
        </span>
    </div>

    {{-- Note de securite : les tentatives d'escroquerie par trop-percu
         reviennent regulierement, elle rappelle la vigilance de base.
         L'utilisateur peut la fermer, un cookie retient son choix. --}}
    @unless ($alerteEscroquerieMasquee)
        <section class="carte-espace relative mb-6 flex flex-wrap items-center gap-5 p-6">
            <button type="button" wire:click="fermerAlerte" title="{{ __('Fermer') }}"
                    class="absolute right-3.5 top-3.5 p-1.5 text-ub-texte4 hover:text-ub-texte">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/>
                </svg>
                <span class="sr-only">{{ __('Fermer') }}</span>
            </button>

            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#fff1d6] text-[#8a5a00]">
                <span class="fonticon-alert-triangle text-[28px]" aria-hidden="true"></span>
            </span>

            <div class="min-w-0 flex-1 basis-80 pr-6">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="rounded-full bg-[#fff1d6] px-2.5 py-1 text-[11px] font-black uppercase tracking-[.06em] text-[#8a5a00]">{{ __('Attention') }}</span>
                    <span class="text-[17px] font-bold">{{ __('Tentatives d’escroquerie signalées') }}</span>
                </div>

                <p class="mt-2 text-[14px] leading-relaxed text-ub-texte2 text-pretty">
                    {{ __('Nous avons reçu des signalements concernant des tentatives d’escroquerie par trop-perçu d’acompte. Ne remboursez jamais un acompte versé en trop avant d’avoir vérifié l’encaissement.') }}
                </p>

                <a href="https://www.ultra-book.info" target="_blank" rel="noopener" class="mt-2 inline-block text-[14px] font-bold text-ub-texte hover:text-ub-accent-texte">
                    {{ __('Voir l’article complet sur ultra-book.info →') }}
                </a>
            </div>
        </section>
    @endunless

    <div class="space-y-4">

            {{-- Les deux dossiers, en onglets qui rejoignent directement le
                 panneau ci-dessous : celui qui est choisi se fond dans le
                 blanc du panneau (pas de filet entre les deux), l'autre
                 reste en retrait, sur le fond de la page. --}}
            <div class="carte-espace overflow-hidden">
                <nav class="flex border-b border-ub-filet bg-ub-fond">
                    @foreach ($dossiers as $d)
                        <button type="button" wire:click="choisir('{{ $d['cle'] }}')"
                                @class(['flex flex-1 items-center justify-center gap-2 px-3.5 py-3.5 text-[15px] transition',
                                    'bg-white font-bold text-ub-accent-texte' => $dossier === $d['cle'],
                                    'font-normal text-ub-texte3 hover:text-ub-texte' => $dossier !== $d['cle'],
                                ])>
                            @if ($d['cle'] === 'poubelle')
                                <x-espace.picto nom="poubelle" class="h-4 w-4 shrink-0" />
                            @endif

                            <span class="whitespace-nowrap">{{ $d['libelle'] }}</span>

                            @if ($d['non_lus'] > 0 && $d['cle'] !== 'poubelle')
                                <span class="min-w-6 shrink-0 rounded-full bg-ub-accent px-2 py-0.5 text-center text-[12px] font-bold text-white">{{ $d['non_lus'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </nav>

                <div class="flex items-center justify-between gap-3 border-b border-ub-filet px-5 py-4">
                    <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ $dossierLibelle }}</h2>

                    <div class="flex items-center gap-4">
                        @if ($conversations->total())
                            <span class="text-[13px] text-ub-texte3">
                                {{ __(':de–:a sur :total', ['de' => $conversations->firstItem(), 'a' => $conversations->lastItem(), 'total' => $conversations->total()]) }}
                            </span>
                        @endif

                        @if ($dossier === 'poubelle' && $conversations->total())
                            <button type="button" wire:click="viderCorbeille"
                                    wire:confirm="{{ __('Supprimer définitivement toutes les demandes de la corbeille ?') }}"
                                    class="bouton-espace bouton-espace-petit px-3.5">
                                <x-espace.picto nom="poubelle" class="h-4 w-4 shrink-0" />
                                {{ __('Vider la corbeille') }}
                            </button>
                        @endif
                    </div>
                </div>

                @forelse ($conversations as $c)
                    @php($deplie = $ouvert === $c->id && $fil)

                    {{-- Un message ouvert se detache du reste de la liste par
                         un filet fonce qui encadre a la fois sa ligne de
                         titre et tout son contenu deplie : l'ensemble se lit
                         d'un coup, sans se confondre avec les autres lignes. --}}
                    <div wire:key="conv-{{ $c->id }}"
                         @class(['relative', 'border border-ub-texte/50' => $deplie, 'border-b border-[#a8a8a7] last:border-b-0' => ! $deplie])>

                        {{-- La ligne : un clic la deplie, un second la replie. --}}
                        <div wire:click="basculer({{ $c->id }})"
                             @class(['flex cursor-pointer items-center gap-3.5 px-5 py-3.5 text-left transition hover:bg-ub-accent-fond/40',
                                 'bg-white' => $c->non_lus || $deplie, 'bg-[#fcfdfd]' => ! $c->non_lus && ! $deplie,
                             ])>

                            {{-- Enveloppe fermee pour un message non lu, ouverte
                                 pour un message lu : les deux pictogrammes de
                                 la messagerie d'origine. --}}
                            @if ($c->non_lus)
                                <x-espace.picto nom="courrier" class="h-4.5 w-4.5 shrink-0 text-ub-texte" />
                                <span class="sr-only">{{ __('Non lu') }}</span>
                            @else
                                <x-espace.picto nom="courrier-lu" class="h-4.5 w-4.5 shrink-0 text-ub-texte4" />
                            @endif

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ub-accent-fond text-[14px] font-bold text-ub-accent-texte">
                                {{ mb_strtoupper(mb_substr($c->sender_name ?: $c->sender_email ?: '?', 0, 1)) }}
                            </span>

                            <span class="min-w-0 flex-1">
                                <span @class(['block truncate text-[15px]', 'font-bold' => $c->non_lus])>{{ $c->sender_name ?: $c->sender_email ?: __('Expéditeur inconnu') }}</span>
                                <span class="block text-[13px] text-ub-texte3">{{ $c->last_message_at?->translatedFormat('l j F Y à H:i') }}</span>
                            </span>

                            @if ($dossier === 'poubelle')
                                <button type="button" wire:click.stop="supprimer({{ $c->id }})" title="{{ __('Restaurer') }}"
                                        class="shrink-0 p-2 text-ub-texte4 hover:text-ub-accent-texte">
                                    <x-espace.picto nom="restaurer" class="h-4.5 w-4.5" />
                                    <span class="sr-only">{{ __('Restaurer') }}</span>
                                </button>
                            @else
                                <button type="button" wire:click.stop="supprimer({{ $c->id }})" title="{{ __('Supprimer') }}"
                                        class="shrink-0 p-2 text-ub-texte4 hover:text-ub-danger">
                                    <x-espace.picto nom="poubelle" class="h-4.5 w-4.5" />
                                    <span class="sr-only">{{ __('Supprimer') }}</span>
                                </button>
                            @endif

                            {{-- La fleche de depliage, bien visible. --}}
                            <x-espace.picto :nom="$deplie ? 'angle-bas' : 'angle-droite'" class="h-5 w-5 shrink-0 text-ub-texte" />
                        </div>

                        {{-- Le fil, juste sous la ligne : des bulles de bande
                             dessinee, la queue en bas, qui alternent de cote
                             entre l'expediteur et le createur. --}}
                        @if ($deplie)
                            <div class="border-t border-ub-filet bg-white px-5 pb-6 pt-4" wire:key="fil-{{ $fil->id }}">
                                <p class="mb-5 text-[13px] text-ub-texte3">
                                    <span class="font-semibold text-ub-texte2">{{ $fil->objet() }}</span>
                                    @if ($fil->sender_company) — {{ $fil->sender_company }} @endif
                                    @if ($fil->sender_email) — <a href="mailto:{{ $fil->sender_email }}" class="text-ub-accent-texte underline">{{ $fil->sender_email }}</a> @endif
                                    @if ($fil->sender_phone) — {{ $fil->sender_phone }} @endif
                                </p>

                                <ol class="space-y-7">
                                    @foreach ($fil->messages as $m)
                                        <li @class(['flex flex-col', 'items-end' => $m->from_owner, 'items-start' => ! $m->from_owner])>
                                            <div @class(['relative max-w-[85%] px-4.5 py-3.5 text-[15px] leading-relaxed whitespace-pre-line',
                                                    'bg-ub-accent-fond text-ub-texte' => $m->from_owner,
                                                    'bg-[#efefed] text-ub-texte' => ! $m->from_owner,
                                                ])>{{ $m->body }}<span @class([
                                                    'absolute -bottom-2.5 h-0 w-0 border-x-[10px] border-t-[11px] border-x-transparent',
                                                    'border-t-ub-accent-fond' => $m->from_owner, 'border-t-[#efefed]' => ! $m->from_owner,
                                                    'right-6' => $m->from_owner, 'left-6' => ! $m->from_owner,
                                                ]) aria-hidden="true"></span></div>

                                            <span @class(['mt-3.5 text-[12px] text-ub-texte3', 'pr-3' => $m->from_owner, 'pl-3' => ! $m->from_owner])>
                                                <span class="font-semibold text-ub-texte2">{{ $m->from_owner ? __('Vous') : ($fil->sender_name ?: __('Expéditeur')) }}</span>
                                                — {{ $m->created_at?->translatedFormat('j F Y à H:i') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ol>

                                <form wire:submit="repondre" class="mt-7 space-y-2.5">
                                    <textarea wire:model="reponse" rows="4" placeholder="{{ __('Votre réponse') }}"
                                              class="w-full border border-ub-bord px-3.5 py-2.5 text-[15px] outline-none focus:border-ub-accent focus:ring-2 focus:ring-ub-accent/20"></textarea>
                                    @error('reponse') <p class="text-[13px] text-ub-danger">{{ $message }}</p> @enderror

                                    {{-- La proposition de l'IA : gardee a part, jamais ecrite
                                         dans le champ tant que le createur ne l'a pas acceptee. --}}
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
                    <p class="px-5 py-12 text-center text-[15px] text-ub-texte3">{{ __('Aucun message dans ce dossier.') }}</p>
                @endforelse
            </div>

            {{-- Pagination : un bouton par page, la page courante en noir. --}}
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
</div>
