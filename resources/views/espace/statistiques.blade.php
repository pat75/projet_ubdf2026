@extends('layouts.espace')

@section('title', __('Statistiques'))

@section('content')
<div x-data="statistiques(@js($parJour), @js($parMois), @js($surfaces))">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Mesurer, comparer, progresser') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Mes statistiques') }}</h1>
        </div>

        <span class="inline-flex items-center gap-2 rounded-sm bg-black px-4.5 py-2.5 text-[14px] font-bold text-white">
            {{ __(':n vues depuis l’ouverture', ['n' => number_format($total, 0, ',', ' ')]) }}
        </span>
    </div>

    {{-- Periode : onglets et courbe dans la meme carte (charte, « Onglets »). --}}
    <div class="carte-espace overflow-hidden">
        <div class="flex border-b border-ub-filet bg-ub-fond">
            @foreach ([7, 30, 90] as $n)
                <button type="button" @click="jours = {{ $n }}"
                        class="flex-1 px-3 py-3 text-[14px]"
                        :class="jours === {{ $n }} ? 'bg-white font-bold text-ub-accent-texte' : 'text-ub-texte3'">
                    {{ __(':n derniers jours', ['n' => $n]) }}
                </button>
            @endforeach
        </div>

        <div class="p-4 md:p-6">
            <dl class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div>
                    <dt class="text-[12px] font-semibold uppercase tracking-[.06em] text-ub-texte3">{{ __('Visites') }}</dt>
                    <dd class="mt-1 font-titre text-[28px] font-light text-ub-texte" x-text="nombre(totalPeriode)"></dd>
                </div>
                <div>
                    <dt class="text-[12px] font-semibold uppercase tracking-[.06em] text-ub-texte3">{{ __('Moyenne par jour') }}</dt>
                    <dd class="mt-1 font-titre text-[28px] font-light text-ub-texte" x-text="nombre(moyenne)"></dd>
                </div>
                <div>
                    <dt class="text-[12px] font-semibold uppercase tracking-[.06em] text-ub-texte3">{{ __('Meilleur jour') }}</dt>
                    <dd class="mt-1 font-titre text-[28px] font-light text-ub-texte">
                        <span x-text="meilleurJour ? nombre(meilleurJour.n) : '—'"></span>
                        <span class="text-[13px] text-ub-texte3" x-text="meilleurJour?.date"></span>
                    </dd>
                </div>
                <div>
                    <dt class="text-[12px] font-semibold uppercase tracking-[.06em] text-ub-texte3">{{ __('Aujourd’hui') }}</dt>
                    <dd class="mt-1 font-titre text-[28px] font-light text-ub-texte" x-text="nombre(visites.at(-1))"></dd>
                </div>
            </dl>

            <h2 class="mt-8 text-[17px] font-semibold text-ub-texte">
                {{ __('Visites des') }} <span x-text="jours"></span> {{ __('derniers jours') }}
            </h2>
            <p class="mt-0.5 text-[13px] text-ub-texte3" x-text="periode"></p>
            <div class="mt-4 h-[205px] md:h-[272px]">
                <canvas x-ref="visites"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="carte-espace p-4 md:p-6">
            <h2 class="text-[17px] font-semibold text-ub-texte">{{ __('Par support') }}</h2>
            <p class="mt-0.5 text-[13px] text-ub-texte3">{{ __('Book et MiniBook, sur la période choisie') }}</p>
            <div class="mt-4 h-[205px]">
                <canvas x-ref="surfaces"></canvas>
            </div>
        </div>

        <div class="carte-espace p-4 md:p-6">
            <h2 class="text-[17px] font-semibold text-ub-texte">{{ __('12 derniers mois') }}</h2>
            <p class="mt-0.5 text-[13px] text-ub-texte3">{{ __('Visites cumulées par mois') }}</p>
            <div class="mt-4 h-[205px]">
                <canvas x-ref="mois"></canvas>
            </div>
        </div>
    </div>

    <p class="mt-6 text-[12px] text-ub-texte4">{{ __('Vos propres visites ne sont pas comptées.') }}</p>
</div>
@endsection
