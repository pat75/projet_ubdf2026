{{--
    Portfolio Grid 2015 (ex-ultrabook_portfolio + Fotorama) : barre du haut
    (rubrique, compteur, index, plein ecran, fermer), diaporama d'une image a
    la fois, legende dessous ; l'index remplace le diaporama par une grille
    de vignettes. Comportement : x-data="diaporama" (resources/js/book/diaporama.js).
--}}
@extends('book.grid2015.layout')

@php $diapos = $vue->diapositives(); @endphp

@if ($diapos)
    @push('entete')
        {{-- « 180px » sous 768 px : volontairement faux, pour que le mobile (densite 3)
             prenne la version 550 px et non la source 1980 px. --}}
        <link rel="preload" as="image" href="{{ $diapos[0]['grand'] }}" imagesrcset="{{ $diapos[0]['moyen'] }} 550w, {{ $diapos[0]['grand'] }} 1980w" imagesizes="(min-width: 1024px) 75vw, (max-width: 767px) 180px, 100vw" fetchpriority="high">
    @endpush
@endif

@section('contenu')
    <div x-data="{ index_: false }">
    <div x-data="diaporama({{ count($diapos) }})" @keydown.window="clavier($event)"
         class="flex flex-col bg-book-fond motion-safe:animate-apparition" role="region" aria-roledescription="{{ __('diaporama') }}" aria-label="{{ $vue->nomRubrique() }}">

        <div class="mb-3 flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-baseline gap-4">
                <h2 class="ub_font_ptf_titre truncate text-[18px] font-bold uppercase">{{ $vue->nomRubrique() }}</h2>
                @if (count($diapos) > 1)
                    <span class="shrink-0 text-[13px] font-semibold tabular-nums" x-text="`${index + 1} / ${total}`">1 / {{ count($diapos) }}</span>
                @endif
            </div>
            <div class="flex shrink-0 items-center gap-1">
                @if (count($diapos) > 1)
                    <button type="button" @click="index_ = ! index_" :aria-pressed="index_" aria-label="{{ __('Index des images') }}" class="p-2 transition-opacity hover:opacity-60">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M4 4h4v4H4zM10 4h4v4h-4zM16 4h4v4h-4zM4 10h4v4H4zM10 10h4v4h-4zM16 10h4v4h-4zM4 16h4v4H4zM10 16h4v4h-4zM16 16h4v4h-4z"/></svg>
                    </button>
                @endif
                <button type="button" @click="basculerPleinEcran()" aria-label="{{ __('Plein écran') }}" class="p-2 transition-opacity hover:opacity-60 max-md:hidden">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
                </button>
                <a href="/" aria-label="{{ __('Fermer') }}" class="p-2 transition-opacity hover:opacity-60">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19"/></svg>
                </a>
            </div>
        </div>

        @if (! $diapos)
            <p class="text-book-texte3">{{ __('Aucun visuel dans ce portfolio.') }}</p>
        @else
            {{-- Index : toutes les vignettes. --}}
            <div x-show="index_" x-cloak x-transition.opacity class="grid grid-cols-3 gap-2 sm:grid-cols-5 lg:grid-cols-7">
                @foreach ($diapos as $i => $diapo)
                    <button type="button" @click="voir({{ $i }}); index_ = false" aria-label="{{ $diapo['titre'] ?: __('Image :n', ['n' => $i + 1]) }}"
                            class="aspect-square overflow-hidden transition-opacity hover:opacity-70">
                        <img src="{{ $vue->carre(basename($diapo['moyen']), 183) }}" alt="" width="183" height="183" loading="lazy" decoding="async" class="size-full object-cover">
                    </button>
                @endforeach
            </div>

            <div x-show="! index_" class="group relative flex h-[65vh] items-center justify-center overflow-hidden lg:h-[calc(100vh-190px)]"
                 :class="pleinEcran && 'h-screen! bg-book-fond'"
                 @touchstart.passive="debutGlisser($event)" @touchend="finGlisser($event)">
                @foreach ($diapos as $i => $diapo)
                    <figure class="absolute inset-0 flex flex-col items-start justify-center transition-[opacity,translate] duration-500"
                            :class="index === {{ $i }} ? 'opacity-100 translate-x-0' : 'pointer-events-none opacity-0 translate-x-3'"
                            @if ($i > 0) aria-hidden="true" :aria-hidden="index !== {{ $i }}" @endif>
                        <img @if ($i === 0) src="{{ $diapo['grand'] }}" srcset="{{ $diapo['moyen'] }} 550w, {{ $diapo['grand'] }} 1980w" fetchpriority="high"
                             @else :src="proche({{ $i }}) ? @js($diapo['grand']) : null" :srcset="proche({{ $i }}) ? @js($diapo['moyen'].' 550w, '.$diapo['grand'].' 1980w') : null" @endif
                             sizes="(min-width: 1024px) 75vw, (max-width: 767px) 180px, 100vw" alt="{{ $diapo['titre'] ?: $diapo['nom_rubrique'] }}"
                             @if ($diapo['largeur'] && $diapo['hauteur']) width="{{ $diapo['largeur'] }}" height="{{ $diapo['hauteur'] }}" @endif
                             decoding="async" class="min-h-0 max-h-full w-auto max-w-full flex-1 object-contain object-left max-md:object-center">
                        @if ($diapo['titre'] || $diapo['description'])
                            <figcaption class="shrink-0 pt-3 text-[13px] font-semibold">
                                <span class="ub_font_ptf_titre">{{ $diapo['titre'] }}</span>
                                @if ($diapo['description'])
                                    <span class="ub_font_ptf_legende font-normal">{{ $diapo['description'] }}</span>
                                @endif
                            </figcaption>
                        @endif
                    </figure>
                @endforeach

                @if (count($diapos) > 1)
                    <button type="button" @click="aller(-1)" aria-label="{{ __('Précédente') }}" class="absolute left-0 top-1/2 z-10 -translate-y-1/2 p-3 transition-opacity hover:opacity-60">
                        <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
                    </button>
                    <button type="button" @click="aller(1)" aria-label="{{ __('Suivante') }}" class="absolute right-0 top-1/2 z-10 -translate-y-1/2 p-3 transition-opacity hover:opacity-60">
                        <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                    </button>
                @endif
            </div>
        @endif
    </div>
    </div>
@endsection
