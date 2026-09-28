{{--
    Portfolio Pinter 2013 (ex-ultrabook_2012_portfolio + Isotope + Fancybox) :
    tout le portfolio en mosaique de 2 a 3 colonnes, filtre par les rubriques
    de la colonne (evenement mosaique-filtrer), titre au survol, visionneuse.
    Comportement : x-data="mosaique" (resources/js/book/mosaique.js).
--}}
@extends('book.responsive.layout')

@section('contenu')
    <div x-data="mosaique">
        <div class="mosaique mosaique-pinter" x-ref="grille">
            @foreach ($vue->visuels() as $i => $visuel)
                @php $prioritaire = $i < \App\Services\Book\VueBook::PRIORITAIRES; @endphp
                <figure data-visuel data-rubrique="{{ $visuel['rubrique'] }}" data-grand="{{ $visuel['grand'] }}"
                        data-titre="{{ $visuel['titre'] }}" data-nom-rubrique="{{ $visuel['nom_rubrique'] }}"
                        data-description="{{ $visuel['description'] }}"
                        class="group relative self-start transition duration-700 ease-[cubic-bezier(.22,.61,.36,1)]">
                    <div class="p-1">
                    <button type="button" class="relative block w-full cursor-zoom-in overflow-hidden bg-book-texte/5" @click="ouvrir($el.closest('figure'))"
                            aria-label="{{ trim(__('Agrandir').' '.$visuel['titre']) }}">
                        <img src="{{ $visuel['moyen'] }}"
                             srcset="{{ $visuel['petit'] }} 320w, {{ $visuel['moyen'] }} 550w"
                             sizes="(min-width: 1024px) 240px, 50vw"
                             alt="{{ $visuel['titre'] ?: $visuel['nom_rubrique'] }}"
                             @if ($visuel['largeur'] && $visuel['hauteur'])
                                 width="{{ $visuel['largeur'] }}" height="{{ $visuel['hauteur'] }}"
                             @else
                                 style="aspect-ratio: 1"
                             @endif
                             loading="{{ $prioritaire ? 'eager' : 'lazy' }}"
                             fetchpriority="{{ $prioritaire ? 'high' : 'low' }}"
                             decoding="async"
                             class="block h-auto w-full transition duration-700 ease-out group-hover:scale-[1.03]">

                        @if ($visuel['titre'] !== '')
                            <figcaption class="ub_font_ptf_titre pointer-events-none absolute inset-x-0 bottom-0 bg-white/85 px-3 py-2 text-left opacity-0 transition duration-300 [@media(hover:hover)]:group-hover:opacity-100">
                                {{ $visuel['titre'] }}
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
