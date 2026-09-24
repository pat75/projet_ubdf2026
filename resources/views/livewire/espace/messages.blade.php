<div>
    {{-- En-tete : le nombre de demandes non lues, tous dossiers confondus
         sauf la poubelle. --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-texte3">{{ __('Échanger, communiquer, deviser') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Mes messages') }}</h1>
        </div>

        <span class="inline-flex items-center gap-2 rounded-ub bg-ub-texte px-4.5 py-2.5 text-[14px] font-bold text-white">
            {{ trans_choice(':n non lu|:n non lus', $totalNonLus, ['n' => $totalNonLus]) }}
        </span>
    </div>

    {{-- Note de securite, statique : les tentatives d'escroquerie par
         trop-percu reviennent regulierement, elle rappelle la vigilance
         de base. --}}
    <section class="carte-espace mb-6 flex flex-wrap items-center gap-5 p-6">
        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#fff1d6] text-[#8a5a00]">
            <span class="fonticon-alert-triangle text-[28px]" aria-hidden="true"></span>
        </span>

        <div class="min-w-0 flex-1 basis-80">
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

    <div class="flex flex-wrap items-start gap-5">

        {{-- Les quatre dossiers : Contacts et Similaire/Ventes suivent le
             sujet pose au formulaire, Poubelle la suppression douce. --}}
        <nav class="carte-espace flex flex-row flex-wrap gap-1 p-2 sm:w-58 sm:shrink-0 sm:flex-col sm:gap-0.5">
            @foreach ($dossiers as $d)
                <button type="button" wire:click="choisir('{{ $d['cle'] }}')"
                        @class(['flex items-center gap-2.5 rounded-ub px-3.5 py-2.5 text-left text-[15px] transition',
                            'bg-ub-accent-fond font-bold text-ub-accent-texte' => $dossier === $d['cle'],
                            'font-normal text-ub-texte hover:bg-ub-fond' => $dossier !== $d['cle'],
                        ])>
                    <span class="flex-1 whitespace-nowrap">{{ $d['libelle'] }}</span>

                    @if ($d['non_lus'] > 0 && $d['cle'] !== 'poubelle')
                        <span class="min-w-6 shrink-0 rounded-full bg-ub-accent px-2 py-0.5 text-center text-[12px] font-bold text-white">{{ $d['non_lus'] }}</span>
                    @endif
                </button>
            @endforeach
        </nav>

        <div class="min-w-0 flex-[999] basis-120 space-y-4">

            {{-- La liste des demandes du dossier choisi. --}}
            <div class="carte-espace overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-ub-filet px-5 py-4">
                    <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ $dossierLibelle }}</h2>

                    @if ($conversations->total())
                        <span class="text-[13px] text-ub-texte3">
                            {{ __(':de–:a sur :total', ['de' => $conversations->firstItem(), 'a' => $conversations->lastItem(), 'total' => $conversations->total()]) }}
                        </span>
                    @endif
                </div>

                @forelse ($conversations as $c)
                    <div wire:key="conv-{{ $c->id }}" wire:click="ouvrir({{ $c->id }})"
                         @class(['flex cursor-pointer items-center gap-3.5 border-b border-ub-filet px-5 py-3.5 text-left transition last:border-b-0 hover:bg-ub-accent-fond/40',
                             'bg-white' => $c->non_lus, 'bg-[#fcfdfd]' => ! $c->non_lus,
                             'ring-2 ring-inset ring-ub-accent/30' => $ouvert === $c->id,
                         ])>
                        <span @class(['h-2 w-2 shrink-0 rounded-full', 'bg-ub-accent' => $c->non_lus, 'bg-transparent' => ! $c->non_lus])></span>

                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ub-accent-fond text-[14px] font-bold text-ub-accent-texte">
                            {{ mb_strtoupper(mb_substr($c->sender_name ?: $c->sender_email ?: '?', 0, 1)) }}
                        </span>

                        <span class="min-w-0 flex-1">
                            <span @class(['block truncate text-[15px]', 'font-bold' => $c->non_lus])>{{ $c->sender_name ?: $c->sender_email ?: __('Expéditeur inconnu') }}</span>
                            <span class="block text-[13px] text-ub-texte3">{{ $c->last_message_at?->translatedFormat('l j F Y à H:i') }}</span>
                        </span>

                        <button type="button" wire:click.stop="supprimer({{ $c->id }})"
                                class="shrink-0 rounded-md px-2.5 py-1.5 text-[13px] text-ub-texte4 hover:bg-[#fdf5f5] hover:text-ub-danger">
                            {{ $dossier === 'poubelle' ? __('Restaurer') : __('Supprimer') }}
                        </button>

                        <span class="shrink-0 text-[18px] text-ub-texte4" aria-hidden="true">›</span>
                    </div>
                @empty
                    <p class="px-5 py-12 text-center text-[15px] text-ub-texte3">{{ __('Aucun message dans ce dossier.') }}</p>
                @endforelse
            </div>

            {{-- Pagination : un bouton par page, la page courante en
                 turquoise. --}}
            @if ($conversations->lastPage() > 1)
                <div class="flex flex-wrap gap-1.5">
                    @for ($i = 1; $i <= $conversations->lastPage(); $i++)
                        <button type="button" wire:click="gotoPage({{ $i }})"
                                @class(['h-10 min-w-10 rounded-ub border px-1 text-[14px] font-bold transition',
                                    'border-ub-accent bg-ub-accent text-white' => $conversations->currentPage() === $i,
                                    'border-ub-bord bg-white text-ub-texte hover:border-ub-accent' => $conversations->currentPage() !== $i,
                                ])>{{ $i }}</button>
                    @endfor
                </div>
            @endif

            {{-- Le fil ouvert : messages, puis la reponse. --}}
            @if ($fil)
                <div class="carte-espace p-6" wire:key="fil-{{ $fil->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-[19px] font-light">{{ $fil->objet() }}</h2>
                            <p class="mt-1 text-[14px] text-ub-texte2">
                                {{ $fil->sender_name }}@if ($fil->sender_company), {{ $fil->sender_company }}@endif
                                — <a href="mailto:{{ $fil->sender_email }}" class="text-ub-accent-texte underline">{{ $fil->sender_email }}</a>
                                @if ($fil->sender_phone) — {{ $fil->sender_phone }} @endif
                            </p>
                        </div>

                        <button type="button" wire:click="fermer" class="shrink-0 text-[13px] font-bold text-ub-texte3 hover:text-ub-texte">{{ __('Fermer ✕') }}</button>
                    </div>

                    <ol class="mt-5 space-y-3">
                        @foreach ($fil->messages as $m)
                            <li @class(['max-w-lg rounded-ub-bandeau px-4 py-3 text-[14px] whitespace-pre-line',
                                    'ml-auto bg-ub-texte text-white' => $m->from_owner,
                                    'bg-ub-fond text-ub-texte' => ! $m->from_owner])>
                                {{ $m->body }}
                                <span class="mt-1.5 block text-[12px] opacity-60">{{ $m->created_at?->format('d/m/Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ol>

                    <form wire:submit="repondre" class="mt-5 space-y-2">
                        <textarea wire:model="reponse" rows="4" placeholder="{{ __('Votre réponse') }}"
                                  class="w-full rounded-ub border border-ub-bord px-3.5 py-2.5 text-[15px] outline-none focus:border-ub-accent focus:ring-2 focus:ring-ub-accent/20"></textarea>
                        @error('reponse') <p class="text-[13px] text-ub-danger">{{ $message }}</p> @enderror

                        <button type="submit" wire:target="repondre" wire:loading.attr="disabled"
                                class="rounded-ub bg-ub-accent px-5 py-2.5 text-[14px] font-bold text-white hover:bg-ub-accent-fonce disabled:opacity-60">
                            {{ __('Envoyer') }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
