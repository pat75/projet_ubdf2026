<div>
    @php($actifs = collect(array_keys($canaux))->filter(fn ($c) => $this->{$c})->count())

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Comment découvrir mon portfolio') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Diffusion et promotion') }}</h1>
        </div>

        <span class="inline-flex items-center gap-2 rounded-sm bg-black px-4.5 py-2.5 text-[14px] font-bold text-white">
            {{ __(':n / :total canaux actifs', ['n' => $actifs, 'total' => count($canaux)]) }}
        </span>
    </div>

    <section class="flex flex-col gap-3.5">
        <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Diffusion') }}</h2>

        <div class="carte-espace overflow-hidden">
            @foreach ($canaux as $champ => [$libelle, $aide, $url])
                <div wire:key="canal-{{ $champ }}" class="flex flex-wrap items-center gap-x-5 gap-y-3 border-b border-ub-filet px-5 py-4.5 last:border-b-0">
                    <div class="flex min-w-0 flex-[1_1_260px] flex-col gap-1">
                        <span class="text-[16px] font-bold text-ub-texte">{{ $libelle }}</span>
                        <span class="text-[13px] text-ub-texte2 text-pretty">
                            @foreach ((array) $aide as $phrase)
                                <span class="block">{{ $phrase }}</span>
                            @endforeach
                        </span>
                    </div>

                    @if ($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="min-w-0 rounded-md bg-ub-fond px-2.5 py-1.5 font-mono text-[12px] wrap-anywhere {{ $this->{$champ} ? 'text-ub-texte' : 'text-ub-texte4' }}">{{ $url }} ↗</a>
                    @endif

                    <div class="flex shrink-0 items-center gap-2.5">
                        <span class="w-18 text-right text-[13px] font-bold {{ $this->{$champ} ? 'text-ub-accent-texte' : 'text-ub-texte3' }}">
                            {{ $this->{$champ} ? __('Activé') : __('Désactivé') }}
                        </span>
                        <x-espace.interrupteur wire:click="basculer('{{ $champ }}')" :actif="$this->{$champ}" :libelle="$libelle" />
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-7 flex flex-col gap-3.5">
        <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Sélection') }}</h2>

        <div class="carte-espace flex flex-wrap items-center gap-x-5 gap-y-3 px-5 py-4.5">
            <div class="flex min-w-0 flex-[1_1_260px] flex-col gap-1">
                <span class="text-[16px] font-bold text-ub-texte">{{ __('Figurer dans la sélection') }}</span>
                <span class="text-[13px] text-ub-texte2 text-pretty">
                    <span class="block">{{ __('Les books sélectionnés sont mis en avant sur la page d’accueil.') }}</span>
                    <span class="block">{{ __('Nous examinons chaque demande.') }}</span>
                </span>
            </div>

            @if ($enSelection)
                {{-- Meme pilule que « ★ Sélection » sous l'avatar du menu de droite, agrandie de 20 %. --}}
                <span class="rounded-full bg-ub-formule-fond px-[28px] py-[10px] text-[14.4px] font-bold text-ub-formule">★ {{ __('Votre book fait partie de la sélection') }}</span>
            @elseif ($demandeLe)
                {{-- Date d'envoi a gauche, alignee sur le label (couleur du lien « Voir mon book »). --}}
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-[12px] text-ub-texte3">{{ __('Envoyée le :date', ['date' => $demandeLe->translatedFormat('j F Y')]) }}</span>
                    <span class="inline-flex items-center gap-2 rounded-sm bg-ub-accent-fonce px-3.5 py-2 text-[13px] font-bold text-white">{{ __('Demande en cours d’examen') }}</span>
                </div>
            @else
                <x-espace.bouton type="button" wire:click="demanderSelection">
                    {{ __('Demander à être sélectionné') }}
                </x-espace.bouton>
            @endif
        </div>
    </section>

    <section class="mt-7 grid gap-5 md:grid-cols-2">
        <div class="flex flex-col gap-3.5">
            <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Version PDF') }}</h2>

            <a href="{{ route('espace.exporter') }}" class="carte-espace group flex flex-1 items-center gap-4 p-5 text-ub-texte">
                <svg class="h-10 w-10 shrink-0 text-ub-texte" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2.5H6.5a1 1 0 0 0-1 1v17a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V7z"/>
                    <path d="M14 2.5V7h4.5"/>
                    <text x="12" y="16.5" text-anchor="middle" font-size="5" font-weight="600" fill="currentColor" stroke="none" font-family="inherit">PDF</text>
                </svg>
                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    <span class="text-[16px] font-bold group-hover:underline">{{ __('Générer mon book en PDF') }}</span>
                    <span class="text-[13px] text-ub-texte2 text-pretty">{{ __('Une version à imprimer ou à joindre à vos candidatures.') }}</span>
                </div>
                <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
            </a>
        </div>
    </section>
</div>
