{{--
    Books Ultra-frais et Ultra-zen : mise en page commune, en Tailwind et
    Alpine (resources/css/book.css, resources/js/book.js). Remplace
    _ultrabook__layout et ultrabook_type du dossier themes/ultra2020.

    Ultra-frais : en-tete centre au-dessus du contenu, menu horizontal.
    Ultra-zen   : en-tete en colonne a gauche, menu vertical.

    $vue : App\Services\Book\VueUltra2020 ; $b : App\Services\Book\ContexteBook.
--}}
@php
    $zen = $vue->zen();
    $edition = $vue->edition();
    $entete = $vue->entete();
    $taille = $vue->tailleEntete();
    $reseaux = $vue->reseaux();
    $titrePage = ucfirst($b->cont_page_titre);
    $description = trim($b->cont_page_meta);
    $imagePartage = $b->visuel_accueil !== '' ? $vue->photo() : ($vue->visuels()[0]['grand'] ?? '');
    $liens = [
        ['url' => '/portfolio', 'cle' => 'name_portfolio', 'libelle' => $vue->lien('name_portfolio', 'Portfolio'), 'actif' => in_array($b->page_type, ['accueil', 'portfolio'], true)],
        ['url' => '/actualites', 'cle' => 'name_page', 'libelle' => $vue->lien('name_page', 'Bio'), 'actif' => $b->page_type === 'news'],
        ['url' => '/contact', 'cle' => 'name_contact', 'libelle' => $vue->lien('name_contact', 'Contact'), 'actif' => $b->page_type === 'contact'],
    ];
    $tailles = $entete && $entete['type'] === 'photo'
        ? ['S' => 'size-15', 'M' => 'size-[90px]', 'L' => $zen ? 'size-[70px] md:size-[180px]' : 'size-[90px] md:size-[180px]'][$taille]
        : ['S' => 'h-[55px]', 'M' => 'h-[90px]', 'L' => 'h-[90px] md:h-[180px]'][$taille];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($edition)
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex">
    @endif
    <title>{{ $titrePage }}</title>
    <meta name="description" content="{{ trim('book '.$description) }}">
    <meta name="keywords" content="{{ __('Ultra-book, creation de book,') }} {{ str_replace(['[&quot;', '&quot;]', '&quot;,&quot;'], ['', '', ','], $b->cont_page_key) }}">

    <link rel="icon" href="{{ $b->icone }}">
    <link rel="apple-touch-icon" href="{{ $b->icone_iphone }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $titrePage }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ request()->url() }}">
    @if ($imagePartage)
        <meta property="og:image" content="{{ url($imagePartage) }}">
        <meta name="twitter:image" content="{{ url($imagePartage) }}">
    @endif
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $titrePage }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;300;400;500;600;700&display=swap">

    {{-- Dans la page et non dans book.css : Vite reecrirait ces chemins
         vers son serveur de developpement, qui ne sert pas public/. --}}
    <style>
        @foreach ([300 => 'Light', 400 => 'Regular', 700 => 'Bold'] as $graisse => $fichier)
            @font-face { font-family: 'HKGrotesk'; src: url('/2012_web/ultra2020/fonts/HK-Grotesk/Fonts/WEB/HKGrotesk-{{ $fichier }}.woff2') format('woff2'); font-weight: {{ $graisse }}; font-display: swap; }
        @endforeach
    </style>

    @vite(['resources/css/book.css', 'resources/js/book.js'])

    @if (trim((string) ($vue->pref->expert_css ?? '')) !== '')
        {{-- CSS libre du createur (reglage expert), comme le legacy. --}}
        <style>{!! trim($vue->pref->expert_css) !!}</style>
    @endif
</head>
<body @if ($edition) x-data :class="{ 'edition': $store.edition.actif, 'lg:!pl-0': $store.edition.cadre || ! $store.edition.actif }" @endif class="{{ $edition ? 'lg:pl-[24rem]' : '' }} {{ $zen ? 'theme_ultrazen' : 'theme_ultrafrais' }} {{ $vue->couleur() }} min-h-screen bg-book-fond font-texte text-book-texte2 antialiased"
      id="{{ $b->page_type }}">

    @if ($vue->curseur())
        <div x-data="curseur" x-show="actif" x-cloak aria-hidden="true"
             class="pointer-events-none fixed left-0 top-0 z-[60] -ml-4 -mt-4 size-8 rounded-full border border-book-texte transition-[scale] duration-200"
             :class="gros ? 'scale-150 bg-book-texte/10' : 'scale-100'"
             :style="`translate: ${x}px ${y}px`"></div>
    @endif

    <div x-data="{ menu: false }" @class([
        'mx-auto max-w-[1200px] px-4 md:px-6',
        // Place de la barre du mode edition.
        'pt-10' => ! $edition,
        'pt-20' => $edition,
        'md:grid md:grid-cols-[minmax(0,1fr)_minmax(0,3fr)] md:gap-10' => $zen,
        // Ultra-frais : pied de page toujours en bas de l'ecran, meme si la page est courte.
        'flex min-h-screen flex-col' => ! $zen,
    ])>

        {{-- En-tete : icone ou photo, titre, description, menu --}}
        <header @class([
            'relative flex flex-col',
            'items-center text-center' => ! $zen,
            'items-center text-center md:sticky md:top-10 md:items-start md:self-start md:text-left' => $zen,
        ])>
            <button type="button" @class([
                        'z-50 p-2 md:hidden',
                        'absolute right-0 top-0' => $zen,
                        '-mt-6 mb-4' => ! $zen,
                    ]) @click="menu = ! menu"
                    :aria-expanded="menu" aria-label="{{ __('Menu') }}">
                <svg x-show="! menu" class="size-7 text-book-texte" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                <svg x-show="menu" x-cloak class="size-7 text-book-texte" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>

            <a href="/" class="block transition duration-300 max-md:origin-top" :class="menu && 'max-md:scale-50'" data-curseur>
                @if ($entete && $entete['type'] === 'photo')
                    <img src="{{ $entete['src'] }}" alt="{{ $vue->texte('titre') }}" @class(['rounded-full object-cover', $tailles])>
                @elseif ($entete && $entete['type'] === 'icone')
                    <svg viewBox="0 0 24 24" @class(['w-auto fill-book-texte', $tailles]) aria-hidden="true">{!! $entete['trace'] !!}</svg>
                @elseif ($entete)
                    {!! $entete['html'] !!}
                @endif
            </a>

            <a href="/" class="mt-5 block" data-curseur>
                <x-book.texte-editable cle="titre" tag="h1" :edition="$edition" @class([
                    'font-titre text-[26px] leading-[1.43] text-book-texte2 md:text-[32px]',
                    'font-semibold' => ! $zen,
                    'font-normal leading-[1.23]' => $zen,
                ])>{!! $vue->texte('titre') !!}</x-book.texte-editable>
            </a>

            @if ($vue->texte('description') !== '' || $edition)
                <div class="mt-3.5"><x-book.texte-editable cle="description" tag="h2" :edition="$edition" class="font-texte text-[18px] font-light leading-[1.17] text-book-texte2 md:text-[22px]">{!! $vue->texte('description') !!}</x-book.texte-editable></div>
            @endif

            <nav x-data="soulignement" @mouseleave="revenir()" aria-label="{{ __('Menu du book') }}" @class([
                'relative mt-5 max-md:hidden',
                'flex gap-7' => ! $zen,
                'flex flex-col gap-2.5 md:items-start' => $zen,
            ]) :class="menu && 'max-md:flex! max-md:flex-col max-md:items-center max-md:gap-4'">
                @foreach ($liens as $lien)
                    <a href="{{ $lien['url'] }}" @mouseenter="placer($el)" data-curseur
                       @if ($lien['actif']) aria-current="page" @endif
                       @class([
                           'pb-1 text-book-texte2 transition-colors hover:text-book-texte',
                           'font-titre text-[14px] font-light uppercase tracking-[1.46px]' => ! $zen,
                           'font-texte text-[15px]' => $zen,
                           'font-semibold text-book-texte' => $lien['actif'],
                       ])><x-book.texte-editable :cle="'nav_link.'.$lien['cle']" tag="span" :edition="$edition">{!! $lien['libelle'] !!}</x-book.texte-editable></a>
                    @if ($zen && $loop->index === 1 && View::hasSection('sous-menu'))
                        <div class="mb-2 pl-3 max-md:hidden">@yield('sous-menu')</div>
                    @endif
                @endforeach
                @unless ($zen)
                    <span class="absolute bottom-0 h-px bg-book-filet transition-all duration-300 max-md:hidden"
                          :style="`left:${trait.left}px;width:${trait.width}px;opacity:${trait.opacity}`" aria-hidden="true"></span>
                @endunless
            </nav>

            @if ($zen && $reseaux)
                <ul class="mt-8 hidden flex-col gap-1.5 text-[14px] md:flex">
                    @foreach ($reseaux as $reseau => $url)
                        <li><a href="{{ $url }}" target="_blank" rel="noopener" class="text-book-texte3 hover:text-book-texte" data-curseur>{{ $reseau }}</a></li>
                    @endforeach
                </ul>
            @endif
        </header>

        <main @class(['mt-10', 'md:mt-0' => $zen, 'flex-1' => ! $zen])>
            @yield('contenu')
        </main>

        <footer @class(['pb-20 pt-10 text-center font-titre text-[12px] font-extralight tracking-[.43px] text-book-texte3', 'md:col-span-2' => $zen])>
            @if ($reseaux)
                <ul @class(['mb-4 flex flex-wrap justify-center gap-x-10 gap-y-2 text-[15px]', 'md:hidden' => $zen])>
                    @foreach ($reseaux as $reseau => $url)
                        <li><a href="{{ $url }}" target="_blank" rel="noopener" class="hover:text-book-texte" data-curseur>{{ $reseau }}</a></li>
                    @endforeach
                </ul>
            @endif
            <div class="pied-book">{!! $vue->texte('footer') !!}</div>
        </footer>
    </div>

    {{-- Retour en haut --}}
    <button type="button" x-data="hautDePage" x-show="visible" x-cloak x-transition.opacity @click="monter()"
            class="fixed bottom-5 right-5 z-40 flex size-11 items-center justify-center rounded-full bg-book-texte/80 text-book-fond hover:bg-book-texte"
            aria-label="{{ __('Haut de page') }}" data-curseur>
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 19V5m-6 6 6-6 6 6"/></svg>
    </button>

    @if ($edition)
        @include('book.ultra2020._edition')
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
