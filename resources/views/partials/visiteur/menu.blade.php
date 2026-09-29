@php
    $visiteur = auth('visitor')->user();
    $memo = app(\App\Services\Memo\MemoBooks::class);
    $memoTotal = $memo->compter($visiteur);
    // Fils ou le createur a ecrit sans que le visiteur ait lu.
    $messagesNonLus = $memo->conversations($visiteur)->withTrashed()
        ->whereHas('messages', fn ($q) => $q->where('from_owner', true)->whereNull('read_at'))->count();
@endphp

<nav class="carte-espace flex flex-col gap-0.5 p-2.5 text-[15px]" aria-label="{{ __('Navigation du compte') }}">
    <x-espace.nav-groupe>{{ __('Mon compte visiteur') }}</x-espace.nav-groupe>
    <x-espace.nav-lien route="visiteur.tableau" icone="maison">{{ __('Tableau de bord') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="visiteur.messages" icone="enveloppe" :pastille="$messagesNonLus">{{ __('Mes messages') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="visiteur.compte" icone="utilisateur">{{ __('Mon compte') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="memobook" icone="coeur" :pastille="$memoTotal" pastille-evenement="memo-change">{{ __('Mémo book') }}</x-espace.nav-lien>

    <div class="mx-3 my-2.5 h-px bg-ub-filet"></div>

    <form method="post" action="{{ route(nom_route('deconnexion')) }}">
        @csrf
        <button type="submit" class="flex w-full items-center gap-2 rounded-ub px-3 py-2 text-left text-ub-sortie hover:bg-[#fdf2ea]">
            <x-espace.icone nom="sortie" class="h-4 w-4 shrink-0" />
            {{ __('Déconnexion') }}
        </button>
    </form>
</nav>
