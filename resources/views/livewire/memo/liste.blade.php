<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Repérer, garder, comparer') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Mon mémo book') }}</h1>
        </div>

        <span class="inline-flex items-center gap-2 rounded-sm bg-black px-4.5 py-2.5 text-[14px] font-bold text-white">
            {{ trans_choice(':n book|:n books', $total, ['n' => $total]) }}
        </span>
    </div>

    <div class="carte-espace p-5 sm:p-7">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-0 flex-[1_1_240px]">
                <input type="search" wire:model.live.debounce.300ms="recherche"
                       placeholder="{{ __('Rechercher par nom…') }}" aria-label="{{ __('Rechercher par nom') }}"
                       class="champ-espace pr-28 text-[15px] font-normal normal-case tracking-normal">
                <span wire:loading wire:target="recherche"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-[13px] text-ub-texte3">{{ __('Recherche…') }}</span>
            </div>

            @if ($total > 0)
                <a href="{{ lien('memobook.pdf') }}" target="_blank" rel="noopener" class="bouton-espace bouton-espace-petit px-4">
                    {{ __('Exporter en PDF') }}
                </a>
            @endif
        </div>

        @if ($books->isEmpty())
            <p class="mt-8 text-[15px] text-ub-texte3">
                @if ($recherche !== '')
                    {{ __('Aucun book de votre mémo ne correspond à « :q ».', ['q' => $recherche]) }}
                @else
                    {{ __('Votre mémo book est vide. Cliquez sur le cœur d’un book, sur le portail, pour le garder ici.') }}
                @endif
            </p>
        @else
            <ul class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($books as $book)
                    @php($couverture = $book->media->first())
                    <li wire:key="memo-{{ $book->id }}" class="flex flex-col overflow-hidden rounded-ub border border-ub-bord-carte bg-white">
                        <a href="{{ $book->bookUrl() }}" target="_blank" rel="noopener" class="block h-36 bg-ub-fond">
                            @if ($couverture)
                                <img src="{{ $couverture->url('front_desk') }}" alt="{{ $book->fullName() }}" loading="lazy" class="h-full w-full object-cover">
                            @endif
                        </a>

                        <div class="flex flex-1 flex-col gap-1 px-4 pb-4 pt-3">
                            <a href="{{ $book->bookUrl() }}" target="_blank" rel="noopener" class="text-[17px] font-semibold text-ub-texte hover:underline">{{ $book->fullName() }}</a>
                            <div class="text-[12px] uppercase tracking-wide text-ub-texte3">{{ $book->category?->name }}</div>
                            <div class="text-[12px] text-ub-texte4">
                                {{ __('Ajouté le :date', ['date' => \Illuminate\Support\Carbon::parse($book->memorise_le)->translatedFormat('j F Y')]) }}
                            </div>

                            <div class="mt-auto flex gap-2 pt-3">
                                <a href="{{ $book->bookUrl() }}" target="_blank" rel="noopener" class="bouton-espace bouton-espace-petit px-4">{{ __('Ouvrir') }}</a>
                                <button type="button" wire:click="retirer(@js($book->login))" wire:loading.attr="disabled" wire:target="retirer(@js($book->login))"
                                        class="bouton-espace-petit inline-flex items-center border border-ub-bord bg-white px-4 font-semibold text-ub-texte hover:bg-ub-fond disabled:opacity-60">
                                    {{ __('Retirer') }}
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
