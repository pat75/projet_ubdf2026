{{--
    Book Zoom 2016 : mise en page, en Tailwind et Alpine
    (resources/css/book.css, resources/js/book.js). Remplace
    _ultrabook__header, ultrabook_type, ultrabook_footer* et ultrabook_partage
    du dossier themes/zoom2016 (jQuery, Isotope, Fotorama, TweenMax, djax).

    Bandeau de couleur (reglage .ub_couleur_nav) : photo, presentation,
    menu ; triangle sous la rubrique courante. Fond de page : .ub_couleur_fond.
    Couleurs de texte deduites de la clarte de chaque fond (VueZoom2016::variables).

    $vue : App\Services\Book\VueZoom2016 ; $b : App\Services\Book\ContexteBook.
--}}
@php
    $liens = [
        ['url' => '/', 'libelle' => $vue->lien('link_accueil', __('Portfolio')), 'actif' => in_array($b->page_type, ['accueil', 'portfolio'], true)],
        ['url' => '/actualites', 'libelle' => $vue->lien('link_bio', __('Bio')), 'actif' => $b->page_type === 'news'],
        ['url' => '/contact', 'libelle' => $vue->lien('link_contact', __('Contact')), 'actif' => $b->page_type === 'contact'],
    ];
    $polices = collect(['Dosis:wght@300;400;500;700', ...array_map(fn ($p) => str_replace(' ', '+', $p), $vue->policesUtilisees())])
        ->map(fn ($p) => 'family='.str_replace(' ', '+', $p))->implode('&');
    $partage = $vue->actif('ptf_activer_sociaux') ? $vue->partage() : [];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('book.commun._seo')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?{{ $polices }}&display=swap">

    @if ($vue->visuels() !== [] && in_array($b->page_type, ['accueil', 'portfolio'], true))
        {{-- Premier visuel : demande avant meme l'analyse de la page (LCP). --}}
        <link rel="preload" as="image" href="{{ $vue->visuels()[0]['moyen'] }}"
              imagesrcset="{{ $vue->visuels()[0]['petit'] }} 320w, {{ $vue->visuels()[0]['moyen'] }} 550w"
              imagesizes="(min-width: 1180px) 20vw, (min-width: 980px) 25vw, (min-width: 600px) 33vw, 50vw" fetchpriority="high">
    @endif

    @vite(['resources/css/book.css', 'resources/js/book.js'])
</head>
<body class="modele-zoom min-h-screen bg-book-fond font-texte text-book-texte2 antialiased" style="{{ $vue->variables() }}" id="{{ $b->page_type }}">

    <div x-data="{ menu: false }" class="flex min-h-screen flex-col">

        {{-- Bandeau : photo, presentation, menu --}}
        <header @class(['relative bg-book-bandeau text-book-bandeau-texte',
            // Bandeau de la couleur du fond : un filet les separe.
            'border-b border-book-filet' => strtolower($vue->couleurBandeau()) === strtolower($vue->couleurFond())])>
            <div class="mx-auto flex max-w-[1200px] flex-col items-center px-4 pb-5 pt-5 text-center md:px-6 md:pb-0 md:pt-8">

                <button type="button" class="absolute left-3 top-5 p-2 md:hidden" @click="menu = ! menu"
                        :aria-expanded="menu" aria-controls="menu-book" aria-label="{{ __('Menu') }}">
                    <svg x-show="! menu" class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    <svg x-show="menu" x-cloak class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>

                <a href="/" class="block motion-safe:animate-apparition">
                    <h1 class="sr-only">{{ $vue->nomCreateur() }}</h1>
                    @if ($vue->aPhoto())
                        <img src="{{ $vue->photo() }}" alt="{{ $vue->nomCreateur() }}" fetchpriority="high"
                             class="max-h-20 w-auto max-w-[70vw] object-contain md:max-h-40">
                    @endif
                </a>

                @if ($vue->presentation() !== '')
                    <div class="texte-libre mt-4 max-w-3xl text-[15px] leading-snug motion-safe:animate-apparition motion-safe:[animation-delay:120ms] md:mt-6 md:text-[17px]">
                        {!! $vue->presentation() !!}
                    </div>
                @endif

                <nav id="menu-book" aria-label="{{ __('Menu du book') }}"
                     class="mt-4 w-full max-md:hidden md:mt-6" :class="menu && 'max-md:block!'">
                    <ul class="flex flex-col items-center gap-1 pb-4 md:flex-row md:justify-center md:gap-12 md:pb-0">
                        @foreach ($liens as $lien)
                            <li>
                                <a href="{{ $lien['url'] }}" @if ($lien['actif']) aria-current="page" @endif @class([
                                    'relative block py-2 text-[16px] transition-opacity hover:opacity-100 md:pb-7 md:pt-3',
                                    'font-medium opacity-100' => $lien['actif'],
                                    'opacity-85' => ! $lien['actif'],
                                    // Triangle sous la rubrique courante, a cheval sur le bord du bandeau.
                                    'md:after:absolute md:after:left-1/2 md:after:top-full md:after:-translate-x-1/2 md:after:border-x-[13px] md:after:border-t-[13px] md:after:border-x-transparent md:after:border-t-book-bandeau md:after:content-[\'\']' => $lien['actif'],
                                ])>{{ $lien['libelle'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>

            @if ($partage)
                <ul class="absolute right-4 top-4 hidden gap-2 md:flex" aria-label="{{ __('Partager') }}">
                    @foreach ($partage as $reseau => $url)
                        <li>
                            <a href="{{ $url }}" target="_blank" rel="nofollow noopener" title="{{ __('Partager sur :reseau', ['reseau' => $reseau]) }}"
                               class="flex size-8 items-center justify-center rounded-full border border-current/40 text-[11px] font-medium opacity-70 transition hover:opacity-100">
                                <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! [
                                    'Facebook' => '<path d="M14 8h3V4h-3c-2.8 0-4 1.7-4 4.3V10H7v4h3v8h4v-8h3l1-4h-4V8.5c0-.3.2-.5.5-.5z"/>',
                                    'X' => '<path d="M17.8 3h3.1l-6.8 7.8L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L2 3h6.4l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5z"/>',
                                    'LinkedIn' => '<path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3V9.5zm7 0h3.8v1.6h.1c.5-1 1.8-2 3.8-2 4 0 4.8 2.6 4.8 6V21h-4v-5.2c0-1.2 0-2.8-1.7-2.8s-2 1.3-2 2.7V21h-4V9.5z"/>',
                                    'Pinterest' => '<path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.5 1.9-2.5.9 0 1.3.7 1.3 1.5 0 .9-.6 2.3-.9 3.5-.3 1.1.5 1.9 1.6 1.9 1.9 0 3.3-2 3.3-4.9 0-2.6-1.8-4.4-4.5-4.4-3 0-4.8 2.3-4.8 4.6 0 .9.4 1.9.8 2.4.1.1.1.2.1.3l-.3 1.2c0 .2-.2.3-.4.2-1.4-.7-2.2-2.7-2.2-4.3 0-3.5 2.5-6.7 7.3-6.7 3.8 0 6.8 2.7 6.8 6.4 0 3.8-2.4 6.9-5.8 6.9-1.1 0-2.2-.6-2.6-1.3l-.7 2.7c-.3 1-1 2.2-1.4 2.9A10 10 0 1 0 12 2z"/>',
                                ][$reseau] ?? '' !!}</svg>
                                <span class="sr-only">{{ __('Partager sur :reseau', ['reseau' => $reseau]) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </header>

        <main class="mx-auto w-full max-w-[1200px] flex-1 px-4 pt-8 md:px-6 md:pt-14">
            @yield('contenu')
        </main>

        <footer class="px-4 pb-10 pt-14 text-center text-[13px] text-book-texte3">
            @if ($pied = $vue->piedDePage())
                <div class="texte-libre mb-3">{!! $pied !!}</div>
            @endif
            @if ($vue->mentionPlateforme())
                <a href="https://{{ $b->inc_url_dom_www }}" target="_blank" rel="noopener" class="opacity-70 hover:opacity-100">
                    {{ __('Fonctionne avec') }} {{ $b->inc_site_name }}
                </a>
            @endif
        </footer>
    </div>

    {{-- Retour en haut --}}
    <button type="button" x-data="hautDePage" x-show="visible" x-cloak x-transition.opacity @click="monter()"
            class="fixed bottom-5 right-5 z-40 flex size-11 items-center justify-center rounded-full bg-book-bandeau text-book-bandeau-texte shadow-md hover:opacity-90"
            aria-label="{{ __('Haut de page') }}">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 19V5m-6 6 6-6 6 6"/></svg>
    </button>

    @unless ($vue->edition())
        {{-- Pixel de statistiques du book (StatsBookController) : pas pour son createur. --}}
        <img src="/ubstats.gif?r={{ random_int(0, 9999) }}" width="1" height="1" alt="" class="hidden">
    @endunless

    @if (! empty($b->cont_analytic))
        {{-- Mesure propre au createur, en GA4, s'il en a declare une. --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $b->cont_analytic }}"></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', @js($b->cont_analytic));</script>
    @endif
</body>
</html>
