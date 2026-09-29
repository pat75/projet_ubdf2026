@php
    $visiteur = auth('visitor')->user();
    $memoTotal = app(\App\Services\Memo\MemoBooks::class)->compter($visiteur);
@endphp

<nav class="carte-espace flex flex-col gap-0.5 p-2.5 text-[15px]" aria-label="{{ __('Navigation du compte') }}">
    <x-espace.nav-groupe>{{ __('Mon compte visiteur') }}</x-espace.nav-groupe>
    <x-espace.nav-lien route="visiteur.tableau">{{ __('Tableau de bord') }}</x-espace.nav-lien>
    <x-espace.nav-lien route="memobook" :pastille="$memoTotal" pastille-evenement="memo-change">{{ __('Mon mémoBook') }}</x-espace.nav-lien>

    <div class="mx-3 my-2.5 h-px bg-ub-filet"></div>

    <form method="post" action="{{ route(nom_route('deconnexion')) }}">
        @csrf
        <button type="submit" class="w-full rounded-ub px-3 py-2 text-left text-ub-sortie hover:bg-[#fdf2ea]">{{ __('Déconnexion') }}</button>
    </form>
</nav>
