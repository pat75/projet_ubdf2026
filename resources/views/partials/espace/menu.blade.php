@php($creatif = auth()->user())

{{--
 | Menu de droite, repris de ubdf_menu_droite.tlp.php : titre « Mon
 | compte » et deconnexion, bandeau de formule, liens de service, puis
 | deux listes — le compte et le book.
 |
 | L'original posait le menu en `position: fixed`. Ici c'est le conteneur
 | qui est `sticky` (voir le gabarit) : meme effet, sans sortir la colonne
 | du flux ni la faire chevaucher le pied de page.
--}}
<nav class="panneau-espace p-5 text-[15px]" aria-label="{{ __('Navigation de l’espace') }}">

    <h3 class="mb-2 font-titre text-[22px] font-light">
        <a href="{{ route(nom_route('espace.compte')) }}" class="ml-3 text-[#070707] hover:text-ub-gris-moyen">{{ __('Mon compte') }}</a>
    </h3>

    {{-- Bandeau de formule : rouge et gras pour une formule payante, gris
         pour la gratuite, comme dans l'original. --}}
    <div @class([
        'ml-3.5 text-[13px]',
        'font-bold text-ub-payante' => $creatif->plan,
        'font-medium text-ub-gris-fonce' => ! $creatif->plan,
    ])>
        @if ($creatif->plan)
            {{ __('Formule :marque', ['marque' => $marque->nom]) }} ★
        @else
            {{ __('Formule gratuite') }}
        @endif

        @if ($creatif->in_home_selection)
            <div class="text-ub-selection">{{ __('Sélection') }} ★</div>
        @endif
    </div>

    <div class="mx-3 mb-4 mt-3 space-y-1">
        <div>
            <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"
               class="text-ub-book hover:opacity-50">{{ __('Voir mon book') }}</a>
        </div>
        <div>
            <form method="post" action="{{ route(nom_route('deconnexion')) }}">
                @csrf
                <button type="submit" class="text-ub-sortie hover:opacity-50">{{ __('Se déconnecter') }}</button>
            </form>
        </div>
    </div>

    {{-- Le compte. --}}
    <ul class="mx-3 mb-9 mt-5">
        <x-espace.nav-lien route="espace">{{ __('Tableau de bord') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.compte">{{ __('Mon compte') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.formule">{{ __('Ma formule') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.messages">{{ __('Mes messages') }}</x-espace.nav-lien>

        {{-- Trois ecrans que l'espace de 2020 n'avait pas ; ils prennent
             place a la suite, dans le meme habillage. --}}
        <x-espace.nav-lien route="espace.statistiques">{{ __('Mes statistiques') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.exporter">{{ __('Exporter mon book') }}</x-espace.nav-lien>
    </ul>

    {{-- Le book. Le premier element de la liste sert de titre : pas de
         filet, une graisse legere et un corps de 22 px. --}}
    <ul class="mx-3 mb-8 mt-6">
        <li class="mb-1.5 pb-2 font-titre text-[22px] font-light text-black">{{ __('Mon book') }}</li>
        <x-espace.nav-lien route="espace.galeries">{{ __('Les portfolios') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.pages">{{ __('Les pages de contenu') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.design">{{ __('Habillage') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.diffusion">{{ __('Diffusion') }}</x-espace.nav-lien>
    </ul>
</nav>
