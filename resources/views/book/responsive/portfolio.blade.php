{{--
    Portfolio Responsive 2014 (ex-ultrabook_2012_portfolio + Fotorama) :
    diaporama de la rubrique, vignettes ou points en haut ou en bas,
    legendes, plein ecran. Presentation « image » : les visuels a la suite.
    Comportement : x-data="diaporama" (resources/js/book/diaporama.js).
--}}
@extends('book.responsive.layout')

@php
    $diapos = $vue->diapositives();
    $nav = $vue->navigation();
    $legendes = $vue->legendes();
@endphp

@if ($diapos && $vue->presentation() !== 'image')
    @push('entete')
        {{-- « 180px » sous 768 px : volontairement faux, pour que le mobile (densite 3)
             prenne la version 550 px et non la source 1980 px. --}}
        <link rel="preload" as="image" href="{{ $diapos[0]['grand'] }}" imagesrcset="{{ $diapos[0]['moyen'] }} 550w, {{ $diapos[0]['grand'] }} 1980w" imagesizes="(min-width: 1024px) 75vw, (max-width: 767px) 180px, 100vw" fetchpriority="high">
    @endpush
@endif

@section('contenu')
    <h2 class="ub_font_ptf_titre mb-4 text-[15px] uppercase tracking-[.08em] lg:sr-only">{{ collect($vue->rubriquesPortfolio())->firstWhere('active', true)['nom'] ?? '' }}</h2>

    @if (! $diapos)
        <p class="text-book-texte3">{{ __('Aucun visuel dans ce portfolio.') }}</p>
    @elseif ($vue->presentation() === 'image')
        {{-- Les visuels les uns sous les autres. --}}
        <div class="flex flex-col gap-10">
            @foreach ($diapos as $i => $diapo)
                <figure class="motion-safe:animate-apparition">
                    <img src="{{ $diapo['moyen'] }}" srcset="{{ $diapo['moyen'] }} 550w, {{ $diapo['grand'] }} 1980w" sizes="(min-width: 1024px) 75vw, (max-width: 767px) 180px, 100vw"
                         alt="{{ $diapo['titre'] ?: $diapo['nom_rubrique'] }}"
                         @if ($diapo['largeur'] && $diapo['hauteur']) width="{{ $diapo['largeur'] }}" height="{{ $diapo['hauteur'] }}" @endif
                         loading="{{ $i < 2 ? 'eager' : 'lazy' }}" decoding="async" class="mx-auto h-auto max-h-[90vh] w-auto max-w-full">
                    @if ($legendes && ($diapo['titre'] || $diapo['description']))
                        <figcaption class="mt-3 text-center">
                            <span class="ub_font_ptf_titre block">{{ $diapo['titre'] }}</span>
                            <span class="ub_font_ptf_legende block">{{ $diapo['description'] }}</span>
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    @else
        <div x-data="diaporama({{ count($diapos) }})" @keydown.window="clavier($event)"
             @class(['flex flex-col gap-3 bg-book-fond motion-safe:animate-apparition', 'flex-col-reverse' => $vue->navigationEnHaut()])
             role="region" aria-roledescription="{{ __('diaporama') }}" aria-label="{{ __('Portfolio') }}">

            <div class="group relative flex items-center justify-center overflow-hidden"
                 :class="pleinEcran ? 'h-[calc(100vh-110px)]' : '{{ $vue->presentation() === 'full' ? 'h-[80vh]' : 'h-[62vh] lg:h-[72vh]' }}'"
                 @touchstart.passive="debutGlisser($event)" @touchend="finGlisser($event)">
                @foreach ($diapos as $i => $diapo)
                    <figure class="absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-500"
                            :class="index === {{ $i }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                            @if ($i > 0) aria-hidden="true" :aria-hidden="index !== {{ $i }}" @endif>
                        {{-- La premiere est dans le HTML ; les autres chargent a l'approche. --}}
                        <img @if ($i === 0) src="{{ $diapo['grand'] }}" srcset="{{ $diapo['moyen'] }} 550w, {{ $diapo['grand'] }} 1980w" fetchpriority="high"
                             @else :src="proche({{ $i }}) ? @js($diapo['grand']) : null" :srcset="proche({{ $i }}) ? @js($diapo['moyen'].' 550w, '.$diapo['grand'].' 1980w') : null" @endif
                             sizes="(min-width: 1024px) 75vw, (max-width: 767px) 180px, 100vw"
                             alt="{{ $diapo['titre'] ?: $diapo['nom_rubrique'] }}"
                             @if ($diapo['largeur'] && $diapo['hauteur']) width="{{ $diapo['largeur'] }}" height="{{ $diapo['hauteur'] }}" @endif
                             decoding="async" class="min-h-0 max-h-full w-auto max-w-full flex-1 object-contain">
                        @if ($legendes && ($diapo['titre'] || $diapo['description']))
                            <figcaption class="shrink-0 pt-2 text-center">
                                <span class="ub_font_ptf_titre">{{ $diapo['titre'] }}</span>
                                @if ($diapo['description'])
                                    <span class="ub_font_ptf_legende block">{{ $diapo['description'] }}</span>
                                @endif
                            </figcaption>
                        @endif
                    </figure>
                @endforeach

                @if (count($diapos) > 1)
                    <button type="button" @click="aller(-1)" aria-label="{{ __('Précédente') }}"
                            class="absolute left-0 top-1/2 z-10 -translate-y-1/2 p-2 text-book-texte/70 transition hover:text-book-texte md:opacity-0 md:group-hover:opacity-100">
                        <svg class="size-10 drop-shadow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
                    </button>
                    <button type="button" @click="aller(1)" aria-label="{{ __('Suivante') }}"
                            class="absolute right-0 top-1/2 z-10 -translate-y-1/2 p-2 text-book-texte/70 transition hover:text-book-texte md:opacity-0 md:group-hover:opacity-100">
                        <svg class="size-10 drop-shadow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                    </button>
                @endif

                <button type="button" @click="basculerPleinEcran()" :aria-label="pleinEcran ? @js(__('Quitter le plein écran')) : @js(__('Plein écran'))"
                        class="absolute right-1 top-1 z-10 p-2 text-book-texte/60 transition hover:text-book-texte max-md:hidden">
                    <svg x-show="! pleinEcran" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
                    <svg x-show="pleinEcran" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5"/></svg>
                </button>
            </div>

            @if (count($diapos) > 1 && $nav === 'thumbs')
                <div x-ref="vignettes" class="flex gap-1 overflow-x-auto pb-1 [scrollbar-width:thin]">
                    @foreach ($diapos as $i => $diapo)
                        <button type="button" @click="voir({{ $i }})" :aria-current="index === {{ $i }}" aria-label="{{ __('Image :n', ['n' => $i + 1]) }}"
                                class="size-16 shrink-0 overflow-hidden border-2 transition"
                                :class="index === {{ $i }} ? 'border-book-texte' : 'border-transparent opacity-60 hover:opacity-100'">
                            <img src="{{ $vue->carre(basename($diapo['moyen']), 183) }}" alt="" width="64" height="64" loading="lazy" decoding="async" class="size-full object-cover">
                        </button>
                    @endforeach
                </div>
            @elseif (count($diapos) > 1 && $nav === 'dots')
                <div x-ref="vignettes" class="flex flex-wrap justify-center gap-2">
                    @foreach ($diapos as $i => $diapo)
                        <button type="button" @click="voir({{ $i }})" :aria-current="index === {{ $i }}" aria-label="{{ __('Image :n', ['n' => $i + 1]) }}"
                                class="size-2.5 rounded-full transition" :class="index === {{ $i }} ? 'bg-book-texte' : 'bg-book-texte/25 hover:bg-book-texte/60'"></button>
                    @endforeach
                </div>
            @endif

            <p class="sr-only" aria-live="polite" x-text="`${index + 1} / ${total}`"></p>
        </div>
    @endif
@endsection
