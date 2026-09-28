{{--
    Book Responsive 2014 : mise en page, en Tailwind et Alpine
    (resources/css/book.css, resources/js/book.js). Remplace
    ultrabook_2014_type et ultrabook_2012_menugauche de model_old/responsive
    (jQuery, Fotorama, meanMenu, UItoTop, ub_book_core_mdl2012.js).

    Ordinateur : colonne de gauche (visuel, textes libres, menu) et contenu.
    Mobile     : barre du haut au titre du book ; le menu glisse depuis la gauche.
    Typographie et couleurs du createur : classes .ub_font_* (cssReglages).

    $vue : App\Services\Book\VueResponsive2014 ; $b : App\Services\Book\ContexteBook.
--}}
@php
    $rubriques = $vue->rubriquesPortfolio();
    $pages = $vue->menuPages();
    $accueil = $vue->titreMenu('ub_menu_titre_accueil', '[accueil-noir]');
    $titrePtf = $vue->titreMenu('ub_menu_titre_ptf', __('Portfolio'));
    $titreBio = $vue->titreMenu('ub_menu_titre_actu', __('Bio'));
    $fond = $vue->imageDeFond();
    $polices = $vue->urlPolicesBook();
    $bandeau = $vue->bandeau();
    $vignettes = $vue->vignettesColonne();
    // Classique 2015 : l'accueil prend toute la largeur, la colonne reste le menu mobile.
    $pleineLargeur = $b->page_type === 'accueil' && ! $vue->colonneSurAccueil();
    // Pinter : la colonne filtre la mosaique au lieu de changer de page.
    $filtre = $vue->filtreColonne() && in_array($b->page_type, ['accueil', 'portfolio'], true);
    [$blocHaut, $blocBas] = $vue->blocsColonne();
    $cadre = $vue->couleurCadre();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($vue->edition())
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endif
    @include('book.commun._seo')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ruda:wght@400;700&display=swap">
    @if ($polices)
        <link rel="stylesheet" href="{{ $polices }}">
    @endif

    @stack('entete')

    @vite(['resources/css/book.css', 'resources/js/book.js'])

    {{-- Typographie et couleurs reglees par le createur (ex-ub_book_core_mdl2012.js). --}}
    <style>{!! $vue->cssReglages() !!}</style>

    @if (trim($vue->texte('expert_css')) !== '')
        {{-- CSS libre du createur (reglage expert), comme Ultra-frais. --}}
        <style>{!! trim($vue->texte('expert_css')) !!}</style>
    @endif
</head>
<body @if ($vue->edition()) x-data :class="{ 'edition': $store.edition.actif, 'lg:!pl-0': $store.edition.cadre || ! $store.edition.actif }" @endif
      class="{{ $vue->edition() ? 'mode-edition lg:pl-[24rem] max-lg:pt-12' : '' }} modele-responsive min-h-screen bg-book-fond bg-cover bg-fixed bg-center font-texte text-book-texte2 antialiased"
      style="{{ $vue->variables() }}{{ $fond ? ';background-image:url('.e($fond).')' : '' }}" id="{{ $b->page_type }}">

    {{-- Pinter : page encadree, d'une autre couleur que le fond. --}}
    <div @if ($cadre) class="mx-auto max-w-[1000px] shadow-[0_0_18px_rgba(0,0,0,.25)] lg:pb-2" style="background-color: {{ $cadre }}" @endif>

    @if (array_filter(array_column($bandeau, 'src')))
        {{-- Bandeau de visuels : trois servant de menu (Classique 2015) ou un seul (Pinter). --}}
        <nav aria-label="{{ __('Bandeau') }}" class="mx-auto flex max-w-[1280px] motion-safe:animate-apparition lg:px-10 lg:pt-4">
            @foreach ($bandeau as $case)
                <a href="{{ $case['url'] }}" class="block min-w-0 overflow-hidden transition-opacity hover:opacity-85" style="flex: {{ $case['largeur'] }} 1 0%">
                    @if ($case['src'])
                        <img src="{{ $case['src'] }}" alt="{{ $case['libelle'] }}" width="{{ $case['largeur'] }}" height="{{ $case['hauteur'] ?? 110 }}" fetchpriority="high"
                             style="aspect-ratio: {{ $case['largeur'] }} / {{ $case['hauteur'] ?? 110 }}" class="block h-auto w-full object-cover">
                    @else
                        <span class="sr-only">{{ $case['libelle'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    @endif

    <div x-data="{ menu: false }" @keydown.escape.window="menu = false" @class([
        'mx-auto max-w-[1280px] lg:grid lg:gap-10 lg:px-10',
        'lg:grid-cols-[240px_minmax(0,1fr)]' => ! $pleineLargeur,
        'lg:grid-cols-1' => $pleineLargeur,
    ])>

        {{-- Mobile : barre du haut --}}
        <div class="sticky top-0 z-40 flex items-center justify-between gap-4 in-[.mode-edition]:top-12 bg-book-texte px-4 py-3 text-book-fond lg:hidden">
            <a href="/" class="truncate text-[15px] uppercase tracking-[.06em]">{{ $b->cont_book_titre ?: $vue->nomCreateur() }}</a>
            <button type="button" @click="menu = ! menu" :aria-expanded="menu" aria-controls="colonne-book" aria-label="{{ __('Menu') }}" class="-mr-2 p-2">
                <svg x-show="! menu" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                <svg x-show="menu" x-cloak class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>

        {{-- Mobile : voile derriere le menu --}}
        <div x-show="menu" x-cloak x-transition.opacity @click="menu = false" class="fixed inset-0 z-40 bg-black/40 lg:hidden" aria-hidden="true"></div>

        {{-- Colonne : visuel, textes libres, menu --}}
        <aside id="colonne-book"
               class="fixed inset-y-0 left-0 z-50 w-[82vw] max-w-80 -translate-x-full overflow-y-auto bg-book-fond px-6 py-8 shadow-xl transition-transform duration-300 lg:static lg:z-auto lg:w-auto lg:max-w-none lg:translate-x-0 lg:overflow-visible lg:bg-transparent lg:px-0 lg:py-10 lg:shadow-none {{ $pleineLargeur ? 'lg:hidden' : '' }}"
               :class="menu && 'translate-x-0!'">
            <div class="motion-safe:animate-apparition lg:sticky lg:top-10">
                <a href="/" class="block">
                    <h1 class="sr-only">{{ $vue->nomCreateur() }}</h1>
                    @if ($logo = $vue->logo())
                        <img src="{{ $logo }}" alt="{{ $vue->nomCreateur() }}" class="max-h-40 w-auto max-w-full">
                    @endif
                </a>

                @if ($vue->texteLibre('cont_menu_gauche') !== '')
                    <div class="texte-libre mt-5 text-[14px] leading-snug">{!! $vue->texteLibre('cont_menu_gauche') !!}</div>
                @endif

                @if ($bloc = $vue->blocAccueil($blocHaut))
                    <div class="contenu-page mt-5 text-[14px]">{!! $bloc !!}</div>
                @endif

                <nav aria-label="{{ __('Menu du book') }}" class="mt-8 flex flex-col gap-6 text-[14px]">
                    @if ($accueil)
                        <a href="/" class="ub_menu_titre ub_font_menut block w-fit" @if ($b->page_type === 'accueil') aria-current="page" @endif>
                            @if ($accueil['type'] === 'maison')
                                <svg @class(['size-5', 'text-white' => $accueil['clair'], 'text-black' => ! $accueil['clair']]) viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3 2 12h3v8h5v-5h4v5h5v-8h3L12 3z"/></svg>
                                <span class="sr-only">{{ __('Accueil') }}</span>
                            @else
                                {{ $accueil['texte'] }}
                            @endif
                        </a>
                    @endif

                    @if ($rubriques)
                        <div>
                            @if ($titrePtf)
                                <p class="ub_menu_titre ub_font_menut mb-2"><x-book.texte-editable cle="ub_menu_titre_ptf" tag="span" :edition="$vue->edition()">{{ $titrePtf['texte'] ?? __('Portfolio') }}</x-book.texte-editable></p>
                            @endif
                            <ul class="flex flex-col gap-1.5 pl-1" @if ($filtre) x-data="{ filtre: 'all' }" @mosaique-change.window="filtre = $event.detail" @endif>
                                @foreach ($rubriques as $rubrique)
                                    <li>
                                        @if ($filtre)
                                            <button type="button" @click="$dispatch('mosaique-filtrer', { cle: @js($rubrique['cle']), nom: @js($rubrique['nom']) }); menu = false"
                                                    :aria-pressed="filtre === @js($rubrique['cle'])"
                                                    class="ub_font_menu_newsr inline-block text-left opacity-80 transition-opacity hover:opacity-100"
                                                    :class="filtre === @js($rubrique['cle']) && 'font-bold opacity-100!'">{{ $rubrique['nom'] }}</button>
                                            @continue
                                        @endif
                                        <a href="/{{ $rubrique['url'] }}" @if ($rubrique['active']) aria-current="page" @endif @class([
                                            'ub_font_menu_newsr inline-block transition-opacity hover:opacity-100',
                                            'font-bold opacity-100' => $rubrique['active'],
                                            'opacity-80' => ! $rubrique['active'],
                                        ])>{{ $rubrique['nom'] }}</a>
                                        @if ($rubrique['active'] && count($vignettes) > 1)
                                            {{-- Classique 2015 : vignettes de la rubrique, qui pilotent le diaporama. --}}
                                            <div x-data="{ courante: 0 }" @diaporama-change.window="courante = $event.detail"
                                                 class="mb-2 mt-2 grid grid-cols-8 gap-1">
                                                @foreach ($vignettes as $vignette)
                                                    <button type="button" @click="$dispatch('diaporama-voir', {{ $vignette['index'] }}); menu = false"
                                                            aria-label="{{ $vignette['titre'] }}" :aria-current="courante === {{ $vignette['index'] }}"
                                                            class="aspect-square overflow-hidden border transition"
                                                            :class="courante === {{ $vignette['index'] }} ? 'border-book-texte' : 'border-book-filet opacity-70 hover:opacity-100'">
                                                        <img src="{{ $vignette['src'] }}" alt="" width="40" height="40" loading="lazy" decoding="async" class="size-full object-cover">
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                                @if ($filtre && count($rubriques) > 1)
                                    <li>
                                        <button type="button" @click="$dispatch('mosaique-filtrer', { cle: 'all', nom: '' }); menu = false" :aria-pressed="filtre === 'all'"
                                                class="ub_font_menu_newsr inline-block opacity-80 transition-opacity hover:opacity-100"
                                                :class="filtre === 'all' && 'font-bold opacity-100!'">{{ __('Tout afficher') }}</button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    @if ($pages['rubriques'])
                        <div>
                            @if ($titreBio)
                                <p class="ub_menu_titre ub_font_menut mb-2"><x-book.texte-editable cle="ub_menu_titre_actu" tag="span" :edition="$vue->edition()">{{ $titreBio['texte'] ?? __('Bio') }}</x-book.texte-editable></p>
                            @endif
                            <ul class="flex flex-col gap-2 pl-1">
                                @foreach ($pages['rubriques'] as $rubrique)
                                    <li>
                                        @unless ($pages['seule'])
                                            <a href="/{{ $rubrique['url'] }}" class="ub_font_menu_newsr font-semibold">{{ $rubrique['nom'] }}</a>
                                        @endunless
                                        <ul @class(['flex flex-col gap-1.5', 'mt-1.5 pl-3' => ! $pages['seule']])>
                                            @foreach ($rubrique['pages'] as $page)
                                                @php $courante = $page['active'] && $b->page_type === 'news'; @endphp
                                                <li>
                                                    <a href="/{{ $page['url'] }}" @if ($courante) aria-current="page" @endif @class([
                                                        'ub_font_menu_newsp inline-block uppercase tracking-[.04em] transition-opacity hover:opacity-100',
                                                        'font-bold opacity-100' => $courante,
                                                        'opacity-80' => ! $courante,
                                                    ])>{{ $page['titre'] }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <a href="/contact" @if ($b->page_type === 'contact') aria-current="page" @endif
                       class="ub_menu_titre ub_font_menut block w-fit">{{ __('Contact') }}</a>
                </nav>

                @if ($vue->texteLibre('cont_menu_gauche2') !== '')
                    <div class="texte-libre mt-8 text-[14px] leading-snug">{!! $vue->texteLibre('cont_menu_gauche2') !!}</div>
                @endif

                @if ($bloc = $vue->blocAccueil($blocBas))
                    <div class="contenu-page mt-8 text-[14px]">{!! $bloc !!}</div>
                @endif

                @if ($partage = $vue->partage())
                    @include('book.commun._partage', ['partage' => $partage, 'classe' => 'mt-8 flex gap-2 text-book-texte2'])
                @endif
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-col px-4 pt-5 lg:px-0 lg:pt-10">
            <main class="flex-1">
                @yield('contenu')
            </main>

            <footer class="pb-10 pt-12 text-[12px] text-book-texte3">
                @if ($reseaux = $vue->reseaux())
                    {{-- Profils du createur (reglage « social_link »). --}}
                    <ul class="mb-4 flex flex-wrap gap-x-6 gap-y-1 text-[14px]">
                        @foreach ($reseaux as $reseau => $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener me" class="capitalize hover:text-book-texte">{{ $reseau }}</a></li>
                        @endforeach
                    </ul>
                @endif
                @if ($pied = $vue->piedDePage())
                    <div class="texte-libre">{!! $pied !!}</div>
                @elseif ($vue->mentionPlateforme())
                    <a href="https://{{ $b->inc_url_dom_www }}" target="_blank" rel="noopener" class="hover:text-book-texte">
                        <strong class="font-bold text-book-texte2">{{ $b->inc_site_name }}</strong> | {{ __('création de book en ligne') }}
                    </a>
                @endif
            </footer>
        </div>
    </div>

    </div>

    {{-- Retour en haut --}}
    <button type="button" x-data="hautDePage" x-show="visible" x-cloak x-transition.opacity @click="monter()"
            class="fixed bottom-5 right-5 z-30 flex size-11 items-center justify-center rounded-full bg-book-texte/80 text-book-fond hover:bg-book-texte"
            aria-label="{{ __('Haut de page') }}">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 19V5m-6 6 6-6 6 6"/></svg>
    </button>

    @if ($vue->edition())
        @include('book.commun._edition')
    @else
        {{-- Pixel de statistiques du book (StatsBookController) : pas pour son createur. --}}
        <img src="/ubstats.gif?r={{ random_int(0, 9999) }}" width="1" height="1" alt="" class="hidden">
    @endif

    @if (! empty($b->cont_analytic))
        {{-- Mesure propre au createur, en GA4, s'il en a declare une. --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $b->cont_analytic }}"></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', @js($b->cont_analytic));</script>
    @endif
</body>
</html>
