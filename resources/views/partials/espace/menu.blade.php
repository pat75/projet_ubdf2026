@php
    $creatif = auth()->user();
    // Calcule une fois par le layout, qui inclut aussi la barre mobile.
    $messagesNonLus ??= $creatif->conversationsNonLues();
@endphp

{{-- Carte du createur : vignette, nom, lien vers le book, et ses deux
     distinctions eventuelles. --}}
<div class="carte-espace p-5">
    <div class="flex items-center gap-3">
        <x-espace.vignette :creatif="$creatif" class="h-12 w-12" />

        <div class="min-w-0">
            <div class="text-[17px] font-semibold">{{ $creatif->fullName() }}</div>
            <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"
               class="text-[14px] text-ub-accent-fonce hover:underline">{{ __('Voir mon book') }} ↗</a>
        </div>
    </div>

    @if ($creatif->plan || $creatif->in_home_selection)
        <div class="mt-3.5 flex flex-wrap gap-1.5">
            @if ($creatif->plan)
                <span class="rounded-full bg-ub-formule-fond px-2.5 py-0.5 text-[12px] font-bold text-ub-formule">★ {{ __('Formule :marque', ['marque' => $marque->nom]) }}</span>
            @endif
            @if ($creatif->in_home_selection)
                <span class="rounded-full bg-ub-formule-fond px-2.5 py-0.5 text-[12px] font-bold text-ub-formule">★ {{ __('Sélection') }}</span>
            @endif
        </div>
    @endif
</div>

<nav class="carte-espace flex flex-col gap-0.5 p-2.5 text-[15px]" aria-label="{{ __('Navigation de l’espace') }}">

    <x-espace.nav-groupe>{{ __('Mon compte') }}</x-espace.nav-groupe>
    <x-espace.nav-lien route="espace">{{ __('Tableau de bord') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.compte">{{ __('Mon compte') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.formule">{{ __('Ma formule') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.messages" :pastille="$messagesNonLus">{{ __('Mes messages') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.aide">{{ __('Aide') }}</x-espace.nav-lien>

    <x-espace.nav-groupe>{{ __('Mon portfolio') }}</x-espace.nav-groupe>
    <x-espace.nav-lien route="espace.design" icone="reglage">{{ __('Configurer') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.diffusion" icone="diffusion">{{ __('Diffuser') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.statistiques" icone="graphe">{{ __('Statistiques') }}</x-espace.nav-lien>

    <x-espace.nav-groupe>{{ __('Contenu du portfolio') }}</x-espace.nav-groupe>
    <x-espace.nav-lien route="espace.galeries">{{ __('Images') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.pages">{{ __('Pages') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.exporter">{{ __('Exporter') }}</x-espace.nav-lien>

    <div class="mx-3 my-2.5 h-px bg-ub-filet"></div>

    <form method="post" action="{{ route(nom_route('deconnexion')) }}">
        @csrf
        <button type="submit" class="w-full rounded-ub px-3 py-2 text-left text-ub-sortie hover:bg-[#fdf2ea]">{{ __('Déconnexion') }}</button>
    </form>
</nav>
