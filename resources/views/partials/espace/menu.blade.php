@php
    $creatif = auth()->user();
    // Les demandes qui portent au moins un message non lu, ecrit par le
    // client — celui du createur ne compte pas.
    $messagesNonLus = $creatif->conversations()
        ->where('is_spam', false)
        ->whereHas('messages', fn ($q) => $q->where('from_owner', false)->whereNull('read_at'))
        ->count();
@endphp

{{--
 | Menu de droite, repris de ubdf_menu_droite.tlp.php et de la capture du
 | compte adolie : trois groupes — le compte, le portfolio, son contenu —
 | puis les deux liens de service.
 |
 | L'original posait le menu en `position: fixed`. Ici c'est le conteneur
 | qui est `sticky` (voir le gabarit) : meme effet, sans sortir la colonne
 | du flux ni chevaucher le pied de page.
--}}
<nav class="panneau-espace px-6 py-5 text-[15px]" aria-label="{{ __('Navigation de l’espace') }}">

    <div class="flex items-start justify-between">
        <h2 class="font-titre text-[22px] font-light text-[#070707]">{{ __('Mon compte') }}</h2>

        <form method="post" action="{{ route(nom_route('deconnexion')) }}">
            @csrf
            <button type="submit" class="text-ub-sortie hover:opacity-50" title="{{ __('Déconnexion') }}">
                <x-espace.icone nom="sortie" class="h-5 w-5" />
                <span class="sr-only">{{ __('Déconnexion') }}</span>
            </button>
        </form>
    </div>

    {{-- Formule : cramoisi gras quand elle est payante, gris sinon ; le
         fanion de selection editoriale juste en dessous. --}}
    <div @class([
        'mt-1 text-[13px] leading-6',
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

    <ul class="mt-5">
        <x-espace.nav-lien route="espace">{{ __('Tableau de bord') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.compte">{{ __('Mon compte') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.formule">{{ __('Ma formule') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.messages" :pastille="$messagesNonLus">{{ __('Mes messages') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.aide" icone="aide" couleur="text-[#cf57f3]">{{ __('Aide') }}</x-espace.nav-lien>
    </ul>

    <h2 class="mt-7 font-titre text-[22px] font-light text-[#070707]">{{ __('Mon portfolio') }}</h2>

    <ul class="mt-2">
        <x-espace.nav-lien route="espace.design" icone="reglage">{{ __('Configurer') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.galeries" icone="oeil">{{ __('Modifier') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.diffusion" icone="diffusion">{{ __('Diffuser') }}</x-espace.nav-lien>
    </ul>

    <h2 class="mt-7 font-titre text-[22px] font-light text-[#070707]">{{ __('Contenu du portfolio') }}</h2>

    <ul class="mt-2">
        <x-espace.nav-lien route="espace.galeries">{{ __('Images') }}</x-espace.nav-lien>
        <x-espace.nav-lien route="espace.pages">{{ __('Pages') }}</x-espace.nav-lien>
    </ul>

    <div class="mt-8 space-y-3">
        <div>
            <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 text-ub-book hover:opacity-50">
                <x-espace.icone nom="lien" class="h-4 w-4" />{{ __('Voir mon book') }}
            </a>
        </div>
        <div>
            <form method="post" action="{{ route(nom_route('deconnexion')) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 text-ub-sortie hover:opacity-50">
                    <x-espace.icone nom="sortie" class="h-4 w-4" />{{ __('Déconnexion') }}
                </button>
            </form>
        </div>
    </div>
</nav>
