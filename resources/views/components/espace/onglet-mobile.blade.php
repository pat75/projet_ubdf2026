@props(['route', 'icone', 'pastille' => 0])

{{-- Onglet de la barre mobile de l'espace (partials/espace/barre-mobile).
     Meme regle d'allumage que <x-espace.nav-lien> : la rubrique reste
     active sur ses sous-pages, sauf le tableau de bord (`espace`). --}}
@if (Route::has(nom_route($route)))
    @php
        $motifs = [$route, '*.'.$route];
        if (str_contains($route, '.')) {
            array_push($motifs, $route.'.*', '*.'.$route.'.*');
        }
        $actif = request()->routeIs(...$motifs);
    @endphp

    <a href="{{ route(nom_route($route)) }}"
       @class([
           'relative flex flex-1 flex-col items-center gap-1 pt-2 pb-1.5 text-[11px] no-underline',
           'font-semibold text-ub-accent-texte' => $actif,
           'text-ub-texte3' => ! $actif,
       ])
       @if ($actif) aria-current="page" @endif>
        <span class="relative">
            <x-espace.icone :nom="$icone" class="h-6 w-6" />
            @if ($pastille > 0)
                <span class="absolute -right-2.5 -top-1.5 min-w-[18px] rounded-full bg-ub-messages px-1 text-center text-[11px] font-bold leading-[18px] text-white">{{ $pastille }}</span>
            @endif
        </span>
        <span>{{ $slot }}</span>
    </a>
@endif
