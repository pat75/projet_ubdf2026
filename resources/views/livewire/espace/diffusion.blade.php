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
                        <span class="text-[13px] text-ub-texte2 text-pretty">{{ $aide }}</span>
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

    <section class="mt-7 grid gap-5 md:grid-cols-2">
        <div class="flex flex-col gap-3.5">
            <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Promouvoir') }}</h2>

            <div class="carte-espace flex flex-1 flex-wrap items-center gap-4 p-5">
                <div class="flex min-w-0 flex-[1_1_200px] flex-col gap-1">
                    <span class="text-[16px] font-bold text-ub-texte">{{ __('Disponibilité') }}</span>
                    <span class="text-[13px] text-ub-texte2 text-pretty">
                        {{ $disponible
                            ? __('Un badge indique aux visiteurs que vous êtes disponible pour de nouveaux projets.')
                            : __('Activez pour signaler que vous acceptez de nouveaux projets.') }}
                    </span>
                </div>
                <div class="flex shrink-0 items-center gap-2.5">
                    <span class="text-[13px] font-bold {{ $disponible ? 'text-ub-accent-texte' : 'text-ub-texte3' }}">
                        {{ $disponible ? __('Disponible') : __('Indisponible') }}
                    </span>
                    <x-espace.interrupteur wire:click="basculer('disponible')" :actif="$disponible" :libelle="__('Disponibilité')" />
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3.5">
            <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Version PDF') }}</h2>

            <a href="{{ route('espace.exporter') }}" class="carte-espace group flex flex-1 items-center gap-4 p-5 text-ub-texte">
                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    <span class="text-[16px] font-bold group-hover:underline">{{ __('Générer mon book en PDF') }}</span>
                    <span class="text-[13px] text-ub-texte2 text-pretty">{{ __('Une version à imprimer ou à joindre à vos candidatures.') }}</span>
                </div>
                <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
            </a>
        </div>
    </section>
</div>
