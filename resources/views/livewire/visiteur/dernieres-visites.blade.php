<div>
    @if ($parMois->isEmpty())
        <p class="text-[15px] text-ub-texte3">{{ __('Les books que vous consultez, connecté, s’afficheront ici.') }}</p>
    @else
        @php($profil = app(\App\Services\Espace\AffichageProfil::class))

        <div class="space-y-6">
            @foreach ($parMois as $mois => $books)
                <section wire:key="mois-{{ $mois }}">
                    <h3 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">
                        {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $mois)->translatedFormat('F Y') }}
                    </h3>

                    <ul class="mt-1 grid grid-cols-1 gap-x-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($books as $book)
                            @php($photo = $profil->photoUrl($book))
                            <li class="border-b border-ub-filet" wire:key="visite-{{ $book->id }}">
                                <a href="{{ $book->bookUrl() }}" target="_blank" rel="noopener" class="group flex items-center gap-3 py-3">
                                    @if ($photo)
                                        <img src="{{ $photo }}" alt="" class="h-10 w-10 shrink-0 rounded-full object-cover">
                                    @else
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[13px] font-bold text-white"
                                              style="background: {{ $profil->couleur($book) }}" aria-hidden="true">{{ $profil->initiales($book) }}</span>
                                    @endif
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-[15px] font-semibold text-ub-texte group-hover:underline">{{ $book->fullName() }}</span>
                                        <span class="block truncate text-[13px] text-ub-texte3">{{ $book->category?->name }} · {{ \Illuminate\Support\Carbon::parse($book->pivot->visited_at)->translatedFormat('j F') }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        @if ($reste)
            <div class="mt-5">
                <button type="button" wire:click="chargerPlus" wire:loading.attr="disabled" wire:target="chargerPlus"
                        class="bouton-espace bouton-espace-petit px-4">
                    <span wire:loading.remove wire:target="chargerPlus">{{ __('Charger plus') }}</span>
                    <span wire:loading wire:target="chargerPlus">{{ __('Chargement…') }}</span>
                </button>
            </div>
        @endif
    @endif
</div>
