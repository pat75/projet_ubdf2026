@php
    $creatif = auth()->user();
    // Calcule une fois par le layout, qui inclut aussi la barre mobile.
    $messagesNonLus ??= $creatif->conversationsNonLues();
    $memoTotal ??= $creatif->memoBooks()->count();
    // Noms choisis pour les onglets « images » et « pages » du book, entre parentheses.
    $nomMenuImages = trim((string) data_get($creatif->bookSetting?->theme_settings, 'data.nav_link.name_portfolio'));
    $nomMenuPages = trim((string) data_get($creatif->bookSetting?->theme_settings, 'data.nav_link.name_page'));
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
    <x-espace.nav-lien route="memobook" :pastille="$memoTotal" pastille-evenement="memo-change">{{ __('Mon mémoBook') }}</x-espace.nav-lien>
    {{-- Des la premiere demande au support : lien vers la derniere discussion,
         pastille couleur d accent tant qu'une reponse n'a pas ete lue. --}}
    @if ($support = \App\Models\SupportTicket::dernierePour($creatif))
        <x-espace.nav-lien :href="$support->lien()" :pastille="(int) $support->reponseNonLue()" couleur="text-ub-accent-fonce" pastille-fond="bg-ub-accent-fonce">{{ __('Support') }}</x-espace.nav-lien>
    @endif
    <x-espace.nav-lien route="espace.aide">{{ __('Aide') }}</x-espace.nav-lien>

    <x-espace.nav-groupe>{{ __('Mon portfolio') }}</x-espace.nav-groupe>
    <x-espace.nav-lien route="espace.design" icone="reglage">{{ __('Configurer') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.diffusion" icone="diffusion">{{ __('Diffuser') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.statistiques" icone="graphe">{{ __('Statistiques') }}</x-espace.nav-lien>

    <x-espace.nav-groupe>{{ __('Contenu du portfolio') }}</x-espace.nav-groupe>
    <x-espace.nav-lien route="espace.galeries">{{ __('Images') }}@if ($nomMenuImages !== '') <span class="font-light">({{ $nomMenuImages }})</span>@endif</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.pages">{{ __('Pages') }}@if ($nomMenuPages !== '') <span class="font-light">({{ $nomMenuPages }})</span>@endif</x-espace.nav-lien>
    <x-espace.nav-lien route="espace.exporter">{{ __('Exporter') }}</x-espace.nav-lien>

    <div class="mx-3 my-2.5 h-px bg-ub-filet"></div>

    <form method="post" action="{{ route(nom_route('deconnexion')) }}">
        @csrf
        <button type="submit" class="flex w-full items-center gap-2 rounded-ub px-3 py-2 text-left text-ub-sortie hover:bg-[#fdf2ea]">
            <x-espace.icone nom="sortie" class="h-4 w-4 shrink-0" />
            {{ __('Déconnexion') }}
        </button>
    </form>
</nav>
