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
        ['url' => '/', 'cle' => 'link_accueil', 'libelle' => $vue->lien('link_accueil', __('Portfolio')), 'actif' => in_array($b->page_type, ['accueil', 'portfolio'], true)],
        ['url' => '/actualites', 'cle' => 'link_bio', 'libelle' => $vue->lien('link_bio', __('Bio')), 'actif' => $b->page_type === 'news'],
        ['url' => '/contact', 'cle' => 'link_contact', 'libelle' => $vue->lien('link_contact', __('Contact')), 'actif' => $b->page_type === 'contact'],
    ];
    $entete = $vue->entete();
    $taille = [
        'photo' => ['S' => 'max-h-14 md:max-h-20', 'M' => 'max-h-20 md:max-h-40', 'L' => 'max-h-28 md:max-h-60'],
        'icone' => ['S' => 'h-10 md:h-14', 'M' => 'h-14 md:h-24', 'L' => 'h-20 md:h-36'],
    ];
    $reseaux = $vue->reseaux();
    $polices = collect(['Dosis:wght@300;400;500;700', ...array_map(fn ($p) => str_replace(' ', '+', $p), $vue->policesUtilisees())])
        ->map(fn ($p) => 'family='.str_replace(' ', '+', $p))->implode('&');
    $partage = $vue->actif('ptf_activer_sociaux') ? $vue->partage() : [];
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?{{ $polices }}&display=swap">

    @if ($vue->visuels() !== [] && in_array($b->page_type, ['accueil', 'portfolio'], true))
        {{-- Premier visuel : demande avant meme l'analyse de la page (LCP). --}}
        <link rel="preload" as="image" href="{{ $vue->visuels()[0]['moyen'] }}"
              imagesrcset="{{ $vue->visuels()[0]['petit'] }} 320w, {{ $vue->visuels()[0]['moyen'] }} 550w"
              imagesizes="(min-width: 1180px) 20vw, (min-width: 980px) 25vw, (min-width: 600px) 33vw, 50vw" fetchpriority="high">
    @endif

    @vite(['resources/css/book.css', 'resources/js/book.js'])

    @if (trim($vue->texte('expert_css')) !== '')
        {{-- CSS libre du createur (reglage expert), comme Ultra-frais. --}}
        <style>{!! trim($vue->texte('expert_css')) !!}</style>
    @endif
</head>
<body @if ($vue->edition()) x-data :class="{ 'edition': $store.edition.actif, 'lg:!pl-0': $store.edition.cadre || ! $store.edition.actif }" @endif
      class="{{ $vue->edition() ? 'mode-edition lg:pl-[24rem] max-lg:pt-12' : '' }} modele-zoom min-h-screen bg-book-fond font-texte text-book-texte2 antialiased" style="{{ $vue->variables() }}" id="{{ $b->page_type }}">

    <div x-data="{ menu: false }" class="flex min-h-screen flex-col">

        {{-- Bandeau : photo, presentation, menu --}}
        <header class="relative bg-book-bandeau text-book-bandeau-texte">
            <div class="mx-auto flex max-w-[1200px] flex-col items-center px-4 pb-5 pt-5 text-center md:px-6 md:pb-0 md:pt-8">

                <button type="button" class="absolute left-3 top-5 p-2 md:hidden" @click="menu = ! menu"
                        :aria-expanded="menu" aria-controls="menu-book" aria-label="{{ __('Menu') }}">
                    <svg x-show="! menu" class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    <svg x-show="menu" x-cloak class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>

                <a href="/" class="block motion-safe:animate-apparition">
                    <h1 class="sr-only">{{ $vue->nomCreateur() }}</h1>
                    @if ($entete && $entete['type'] === 'photo')
                        <img src="{{ $entete['src'] }}" alt="{{ $vue->nomCreateur() }}" fetchpriority="high"
                             @class(['w-auto max-w-[70vw] object-contain', $taille['photo'][$vue->tailleEntete()]])>
                    @elseif ($entete)
                        <svg viewBox="0 0 24 24" @class(['w-auto fill-current', $taille['icone'][$vue->tailleEntete()]]) aria-hidden="true">{!! $entete['trace'] !!}</svg>
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
                                    'group block py-2 text-[16px] transition-opacity hover:opacity-100 md:pb-6 md:pt-3',
                                    'font-medium opacity-100' => $lien['actif'],
                                    'opacity-85' => ! $lien['actif'],
                                ])>
                                    {{-- Rubrique courante soulignee ; les autres, au survol.
                                         En mode edition : intitule modifiable sur place (crayon). --}}
                                    <x-book.texte-editable :cle="$lien['cle']" tag="span" :edition="$vue->edition()" @class([
                                        'border-b-2 pb-1 transition-colors duration-300',
                                        'border-current' => $lien['actif'],
                                        'border-transparent group-hover:border-current/40' => ! $lien['actif'],
                                    ])>{{ $lien['libelle'] }}</x-book.texte-editable>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>

            @if ($partage)
                @include('book.commun._partage', ['partage' => $partage, 'classe' => 'absolute right-4 top-4 hidden gap-2 md:flex'])
            @endif
        </header>

        <main class="mx-auto w-full max-w-[1200px] flex-1 px-4 pt-8 md:px-6 md:pt-14">
            @yield('contenu')
        </main>

        <footer class="px-4 pb-10 pt-14 text-center text-[13px] text-book-texte3">
            @if ($reseaux)
                {{-- Profils du createur (reglage « social_link »). --}}
                <ul class="mb-5 flex flex-wrap justify-center gap-x-8 gap-y-2 text-[15px]">
                    @foreach ($reseaux as $reseau => $url)
                        <li><a href="{{ $url }}" target="_blank" rel="noopener me" class="capitalize hover:text-book-texte">{{ $reseau }}</a></li>
                    @endforeach
                </ul>
            @endif
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
