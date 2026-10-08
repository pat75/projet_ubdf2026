{{--
    Book Grid 2015 : mise en page, en Tailwind et Alpine
    (resources/css/book.css, resources/js/book.js). Remplace
    ultrabook_2015_type, _ultrabook__header, ultrabook_menugauche et
    ultrabook_footer de model_old/grid2015 (jQuery, djax, Fotorama, iScroll).

    Ordinateur : en-tete fixe a gauche (visuel, MENU, texte libre, pied), contenu a droite.
    Mobile     : en-tete en haut ; le menu s'ouvre en plein ecran.
    Hors accueil : fleche retour et maison a la place du bouton MENU.

    $vue : App\Services\Book\VueGrid2015 ; $b : App\Services\Book\ContexteBook.
--}}
@php
    $rubriques = $vue->rubriquesPortfolio();
    $pages = $vue->menuPages();
    $titrePtf = $vue->titreMenu('ub_menu_titre_ptf', __('Portfolio'));
    $titreBio = $vue->titreMenu('ub_menu_titre_actu', __('Bio'));
    $accueil = $b->page_type === 'accueil';
    $polices = $vue->urlPolicesBook();
    $fond = $vue->imageDeFond();
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Dosis:wght@400;500;600;700&display=swap">
    @if ($polices)
        <link rel="stylesheet" href="{{ $polices }}">
    @endif

    @stack('entete')

    @vite(['resources/css/book.css', 'resources/js/book.js'])

    <style>{!! $vue->cssReglages() !!}</style>

    @if (trim($vue->texte('expert_css')) !== '')
        <style>{!! trim($vue->texte('expert_css')) !!}</style>
    @endif
</head>
<body @if ($vue->edition()) x-data :class="{ 'edition': $store.edition.actif, 'lg:!pl-0': $store.edition.cadre || ! $store.edition.actif }" @endif
      class="{{ $vue->edition() ? 'mode-edition lg:pl-[24rem] max-lg:pt-12' : '' }} modele-grid min-h-screen bg-book-fond bg-cover bg-fixed bg-center font-texte text-book-texte antialiased"
      style="{{ $vue->variables() }}{{ $fond ? ';background-image:url('.e($fond).')' : '' }}" id="{{ $b->page_type }}">

    <div x-data="{ menu: false }" @keydown.escape.window="menu = false"
         class="mx-auto flex min-h-screen max-w-[1600px] flex-col lg:flex-row">

        {{-- En-tete --}}
        <header class="flex shrink-0 flex-col px-5 pt-6 motion-safe:animate-apparition lg:sticky lg:top-0 lg:h-screen lg:w-[300px] lg:px-[72px] lg:pb-8 lg:pt-24">
            <div class="flex items-center justify-between gap-4 lg:block">
                <a href="/" class="block min-w-0">
                    <h1 @class(['text-[19px] font-medium lowercase leading-tight', 'sr-only' => $vue->logo()])>{{ $b->cont_book_titre ?: $vue->nomCreateur() }}</h1>
                    @if ($logo = $vue->logo())
                        <img src="{{ $logo }}" alt="{{ $vue->nomCreateur() }}" class="max-h-16 w-auto max-w-full lg:max-h-40">
                    @endif
                </a>

                <div class="flex shrink-0 items-center gap-3 lg:mt-6">
                    @unless ($accueil)
                        <a href="/" class="p-1 transition-opacity hover:opacity-60" aria-label="{{ __('Retour à l’accueil') }}">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
                        </a>
                    @endunless
                    <button type="button" @click="menu = true" :aria-expanded="menu" aria-controls="menu-grid"
                            class="flex items-center gap-3 py-1 text-[16px] font-semibold uppercase tracking-[.04em] transition-opacity hover:opacity-60">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                        {{ __('Menu') }}
                    </button>
                </div>
            </div>

            @if ($vue->texteLibre('cont_menu_gauche') !== '')
                <div class="texte-libre mt-5 text-[14px] leading-snug max-lg:hidden">{!! $vue->texteLibre('cont_menu_gauche') !!}</div>
            @endif

            <footer class="mt-auto pt-8 text-[13px] max-lg:hidden">
                @include('book.grid2015._pied')
            </footer>
        </header>

        {{-- Menu plein ecran --}}
        <div id="menu-grid" x-show="menu" x-cloak x-transition.opacity.duration.300ms
             class="fixed inset-0 z-50 overflow-y-auto bg-book-fond/97 px-6 py-16 backdrop-blur-sm lg:px-[72px] lg:py-24" role="dialog" aria-modal="true" aria-label="{{ __('Menu du book') }}">
            <button type="button" @click="menu = false" class="absolute right-5 top-5 p-2 transition-opacity hover:opacity-60" aria-label="{{ __('Fermer') }}">
                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19"/></svg>
            </button>

            <nav class="mx-auto flex max-w-5xl flex-col gap-10 md:flex-row md:gap-16" x-show="menu" x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="translate-y-4 opacity-0">
                <a href="/" class="ub_menu_titre ub_font_menut block w-fit border-b border-current pb-2 text-[22px] font-semibold uppercase">{{ __('Accueil') }}</a>

                @if ($rubriques)
                    <div class="min-w-48">
                        @if ($titrePtf)
                            <p class="ub_menu_titre ub_font_menut border-b border-current pb-2 text-[22px] font-semibold uppercase"><x-book.texte-editable cle="ub_menu_titre_ptf" tag="span" :edition="$vue->edition()">{{ $titrePtf['texte'] ?? __('Portfolio') }}</x-book.texte-editable></p>
                        @endif
                        <ul class="mt-3 flex flex-col">
                            @foreach ($rubriques as $rubrique)
                                <li class="border-b border-book-filet">
                                    <a href="/{{ $rubrique['url'] }}" @if ($rubrique['active']) aria-current="page" @endif
                                       @if ($rubrique['verrou']) rel="nofollow" data-verrou @endif
                                       @class(['ub_font_menu_newsr flex items-center gap-2 py-2 text-[17px] uppercase transition-opacity hover:opacity-60', 'font-bold' => $rubrique['active']])>{{ $rubrique['nom'] }}@if ($rubrique['verrou']) <x-book.cadenas /> @endif</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($pages['rubriques'])
                    <div class="min-w-48">
                        @if ($titreBio)
                            <p class="ub_menu_titre ub_font_menut border-b border-current pb-2 text-[22px] font-semibold uppercase"><x-book.texte-editable cle="ub_menu_titre_actu" tag="span" :edition="$vue->edition()">{{ $titreBio['texte'] ?? __('Bio') }}</x-book.texte-editable></p>
                        @endif
                        <ul class="mt-3 flex flex-col">
                            @foreach ($pages['rubriques'] as $rubrique)
                                @unless ($pages['seule'])
                                    <li class="pt-3 text-[13px] font-semibold uppercase tracking-[.06em] text-book-texte3">{{ $rubrique['nom'] }}</li>
                                @endunless
                                @foreach ($rubrique['pages'] as $page)
                                    @php $courante = $page['active'] && $b->page_type === 'news'; @endphp
                                    <li class="border-b border-book-filet">
                                        <a href="/{{ $page['url'] }}" @if ($courante) aria-current="page" @endif
                                           @class(['ub_font_menu_newsp block py-2 text-[17px] uppercase transition-opacity hover:opacity-60', 'font-bold' => $courante])>{{ $page['titre'] }}</a>
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <a href="/contact" class="ub_menu_titre ub_font_menut block w-fit border-b border-current pb-2 text-[22px] font-semibold uppercase">{{ __('Contact') }}</a>
            </nav>
        </div>

        <main class="min-w-0 flex-1 px-5 pb-10 pt-6 lg:px-4 lg:pr-10 lg:pt-24">
            @yield('contenu')
        </main>

        <footer class="px-5 pb-8 text-[13px] lg:hidden">
            @if ($vue->texteLibre('cont_menu_gauche') !== '')
                <div class="texte-libre mb-6 text-[14px] leading-snug">{!! $vue->texteLibre('cont_menu_gauche') !!}</div>
            @endif
            @include('book.grid2015._pied')
        </footer>
    </div>

    <button type="button" x-data="hautDePage" x-show="visible" x-cloak x-transition.opacity @click="monter()"
            class="fixed bottom-5 right-5 z-30 flex size-11 items-center justify-center rounded-full bg-book-texte/80 text-book-fond hover:bg-book-texte"
            aria-label="{{ __('Haut de page') }}">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 19V5m-6 6 6-6 6 6"/></svg>
    </button>

    @if ($vue->edition())
        @include('book.commun._edition')
    @else
        @include('book.commun._a-propos')
        @include('book.commun._pixel')
    @endif

    @if (! empty($b->cont_analytic))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $b->cont_analytic }}"></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', @js($b->cont_analytic));</script>
    @endif
</body>
</html>
