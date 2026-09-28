{{--
    Portfolio Zoom 2016 (ex-ultrabook_portfolio + Isotope) : grille de 2 a
    5 colonnes, filtre par rubrique, titre au survol, visionneuse.
    Reglage « ptf_activer_iso_category » : un intertitre par rubrique.
    Comportement : x-data="mosaique" (resources/js/book/mosaique.js).
--}}
@extends('book.zoom2016.layout')

@section('contenu')
    @php
        $rubriques = $vue->rubriques();
        $groupe = $vue->parRubrique();
        $precedente = null;
    @endphp

    <div x-data="mosaique">
        @if (count($rubriques) > 1)
            <nav aria-label="{{ __('Filtrer les projets') }}" class="-mx-4 mb-6 overflow-x-auto px-4 motion-safe:animate-apparition md:mx-0 md:mb-10 md:px-0">
                <ul class="flex w-max gap-x-6 whitespace-nowrap text-[15px] md:mx-auto md:w-auto md:flex-wrap md:justify-center md:gap-y-2">
                    <li>
                        <button type="button" @click="filtrer('all', $el.textContent.trim())" :aria-pressed="filtre === 'all'"
                                class="border-b border-transparent pb-1 text-book-texte3 transition-colors hover:text-book-texte"
                                :class="filtre === 'all' && 'border-book-texte! text-book-texte!'">{{ __('Tous') }}</button>
                    </li>
                    @foreach ($rubriques as $rubrique)
                        <li>
                            <button type="button" @click="filtrer(@js($rubrique['cle']), $el.textContent.trim())" :aria-pressed="filtre === @js($rubrique['cle'])"
                                    class="border-b border-transparent pb-1 text-book-texte3 transition-colors hover:text-book-texte"
                                    :class="filtre === @js($rubrique['cle']) && 'border-book-texte! text-book-texte!'">{{ $rubrique['nom'] }}</button>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <div class="mosaique mosaique-zoom" x-ref="grille">
            @foreach ($vue->visuels() as $i => $visuel)
                @if ($groupe && $visuel['rubrique'] !== $precedente)
                    {{-- Intertitre de rubrique, sur toute la largeur. --}}
                    <div data-visuel data-rubrique="{{ $visuel['rubrique'] }}" class="col-span-full transition duration-700">
                        <h2 @class(['px-1 pb-3 text-[18px] font-medium uppercase tracking-[.08em] text-book-texte', 'pt-10' => $precedente !== null])>{{ $visuel['nom_rubrique'] }}</h2>
                    </div>
                    @php $precedente = $visuel['rubrique']; @endphp
                @endif

                @php $prioritaire = $i < \App\Services\Book\VueBook::PRIORITAIRES; @endphp
                <figure data-visuel data-rubrique="{{ $visuel['rubrique'] }}" data-grand="{{ $visuel['grand'] }}"
                        data-titre="{{ $visuel['titre'] }}" data-nom-rubrique="{{ $visuel['nom_rubrique'] }}"
                        data-description="{{ $visuel['description'] }}"
                        class="group relative self-start transition duration-700 ease-[cubic-bezier(.22,.61,.36,1)]">
                    <div class="{{ ['small' => 'p-px', 'normal' => 'p-1', 'large' => 'p-2.5'][$vue->tailleVisuels()] }}">
                        <button type="button" class="relative block w-full cursor-zoom-in overflow-hidden bg-book-texte/5" @click="ouvrir($el.closest('figure'))"
                                aria-label="{{ trim(__('Agrandir').' '.$visuel['titre']) }}">
                            <img src="{{ $visuel['moyen'] }}"
                                 srcset="{{ $visuel['petit'] }} 320w, {{ $visuel['moyen'] }} 550w"
                                 sizes="(min-width: 1180px) 20vw, (min-width: 980px) 25vw, (min-width: 600px) 33vw, 50vw"
                                 alt="{{ $visuel['titre'] ?: $visuel['nom_rubrique'] }}"
                                 @if ($visuel['largeur'] && $visuel['hauteur'])
                                     width="{{ $visuel['largeur'] }}" height="{{ $visuel['hauteur'] }}"
                                 @else
                                     style="aspect-ratio: 1"
                                 @endif
                                 loading="{{ $prioritaire ? 'eager' : 'lazy' }}"
                                 fetchpriority="{{ $prioritaire ? 'high' : 'low' }}"
                                 decoding="async"
                                 class="block h-auto w-full object-cover transition duration-700 ease-out group-hover:scale-[1.04]">

                            {{-- Titre au survol, comme .titre-hover de mdl_zoom.css (ecrans a pointeur seulement). --}}
                            @if ($visuel['titre'] !== '')
                                <figcaption class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/40 p-4 text-center text-[16px] uppercase tracking-[.06em] text-white opacity-0 transition duration-300 [@media(hover:hover)]:group-hover:opacity-100">
                                    <span class="translate-y-2 transition duration-300 group-hover:translate-y-0">{{ $visuel['titre'] }}</span>
                                </figcaption>
                            @endif
                        </button>
                    </div>
                </figure>
            @endforeach
        </div>
    </div>

    @include('book.commun._visionneuse')
@endsection
