{{--
    Portfolio en mosaique (ex-portfolio.tlp.php) : filtre par rubrique,
    chargement progressif, apparition en fondu, visionneuse.
    Comportement : x-data="mosaique" (resources/js/book/mosaique.js).
--}}
@extends('book.ultra2020.layout')

@section('contenu')
    @php
        $zen = $vue->zen();
        $rubriques = $vue->rubriques();
        $marges = ['small' => 'p-0', 'normal' => 'px-[3px] py-1.5', 'large' => 'px-2.5 py-5'][$vue->tailleVisuels()];
    @endphp

    <div x-data="mosaique">
        @if (count($rubriques) > 1)
            {{-- « les projets » : survol sur ordinateur, clic sur mobile. --}}
            <div @class(['relative mb-8 flex', 'justify-center' => ! $zen, 'justify-center md:justify-start' => $zen])
                 @mouseenter="window.matchMedia('(hover: hover)').matches && (menu = true)"
                 @mouseleave="window.matchMedia('(hover: hover)').matches && (menu = false)"
                 @click.outside="menu = false">
                @php
                    $typo = [
                        'flex items-center gap-4 py-2 text-book-texte',
                        'font-titre text-[13px] tracking-[1.3px] uppercase' => ! $zen,
                        'font-texte text-[15px] font-semibold lowercase' => $zen,
                    ];
                    $plus = '<svg class="size-6 transition-transform duration-300" :class="menu && \'rotate-45\'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><path d="M12 2v20M2 12h20"/></svg>';
                @endphp
                @if ($vue->edition())
                    {{-- En edition : l'intitule se modifie sur place, hors du bouton
                         (un crayon ne peut pas etre dans un bouton) ; le + ouvre le filtre. --}}
                    <div @class($typo)>
                        <x-book.texte-editable cle="nav_link.name_projets" tag="span" :edition="true">{{ $vue->lien('name_projets', __('les projets')) }}</x-book.texte-editable>
                        <button type="button" @click="menu = ! menu" :aria-expanded="menu" aria-label="{{ __('Filtrer les projets') }}" data-curseur>{!! $plus !!}</button>
                    </div>
                @else
                    <button type="button" @click="menu = ! menu" :aria-expanded="menu" data-curseur @class($typo)>
                        <span x-ref="libelle" x-text="libelle">{{ $vue->lien('name_projets', __('les projets')) }}</span>
                        {!! $plus !!}
                    </button>
                @endif

                <div x-show="menu" x-cloak x-transition.opacity.duration.200ms
                     @class([
                         'absolute top-full z-30 flex min-w-48 flex-col gap-2.5 bg-book-fond px-4 py-3 shadow-sm',
                         'left-1/2 -translate-x-1/2 items-center' => ! $zen,
                         'max-md:left-1/2 max-md:-translate-x-1/2 md:left-0 items-start' => $zen,
                     ])>
                    <button type="button" x-ref="tous" @click="filtrer('all', $el.textContent.trim())" data-curseur
                            class="text-[14px] text-book-texte3 hover:text-book-texte" :class="filtre === 'all' && 'font-semibold text-book-texte'">{{ __('Tous') }}</button>
                    @foreach ($rubriques as $rubrique)
                        <button type="button" @click="filtrer(@js($rubrique['cle']), $el.textContent.trim())" data-curseur
                                class="text-[14px] text-book-texte3 hover:text-book-texte"
                                :class="filtre === @js($rubrique['cle']) && 'font-semibold text-book-texte'">{{ $rubrique['nom'] }}</button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mosaique" x-ref="grille">
            @foreach ($vue->visuels() as $i => $visuel)
                @php $prioritaire = $i < \App\Services\Book\VueUltra2020::PRIORITAIRES; @endphp
                <figure data-visuel data-rubrique="{{ $visuel['rubrique'] }}" data-grand="{{ $visuel['grand'] }}"
                        data-titre="{{ $visuel['titre'] }}" data-nom-rubrique="{{ $visuel['nom_rubrique'] }}"
                        data-description="{{ $visuel['description'] }}"
                        class="group relative self-start transition duration-700 ease-[cubic-bezier(.22,.61,.36,1)]">
                    <div @class([$marges])>
                        <button type="button" class="relative block w-full cursor-zoom-in overflow-hidden" @click="ouvrir($el.closest('figure'))" data-curseur>
                            <img src="{{ $visuel['moyen'] }}"
                                 srcset="{{ $visuel['moyen'] }} 550w, {{ $visuel['grand'] }} 1980w"
                                 sizes="(max-width: 767px) 100vw, (max-width: 1200px) 50vw, 400px"
                                 alt="{{ $visuel['titre'] }}"
                                 @if ($visuel['largeur'] && $visuel['hauteur'])
                                     width="{{ $visuel['largeur'] }}" height="{{ $visuel['hauteur'] }}"
                                 @else
                                     style="aspect-ratio: 4 / 3"
                                 @endif
                                 loading="{{ $prioritaire ? 'eager' : 'lazy' }}"
                                 fetchpriority="{{ $prioritaire ? 'high' : 'low' }}"
                                 decoding="async"
                                 class="block h-auto w-full bg-book-texte/5 object-cover transition-opacity duration-500 group-hover:opacity-15">

                            {{-- Titre au survol, comme .masonry-title du legacy. --}}
                            <figcaption class="pointer-events-none absolute bottom-[15%] left-[15%] right-4 origin-left scale-0 text-left text-book-texte opacity-0 transition duration-300 group-hover:scale-100 group-hover:opacity-80">
                                <span class="block text-[20px] font-extralight leading-[30px]">{{ $visuel['titre'] }}</span>
                                <span class="mt-1 block text-[12px] font-light">{{ $visuel['nom_rubrique'] }}</span>
                            </figcaption>
                        </button>
                    </div>
                </figure>
            @endforeach
        </div>
    </div>

    @include('book.ultra2020._visionneuse')
@endsection
