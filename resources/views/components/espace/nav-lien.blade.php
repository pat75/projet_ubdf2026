@props(['route', 'icone' => null, 'pastille' => 0])

{{-- Les rubriques pas encore livrees n'apparaissent pas : pas de lien mort.
     La rubrique ouverte prend le fond leger de l'accent. --}}
@if (Route::has(nom_route($route)))
    @php
        /*
         | Une rubrique reste allumee sur ses sous-pages — `espace.galeries`
         | le reste sur `espace.galeries.show`. Le tableau de bord fait
         | exception : son nom de route, `espace`, prefixe tous les autres,
         | et `espace.*` l'allumait sur chaque ecran de l'espace.
         */
        $motifs = [$route, '*.'.$route];

        if (str_contains($route, '.')) {
            $motifs[] = $route.'.*';
            $motifs[] = '*.'.$route.'.*';
        }

        $actif = request()->routeIs(...$motifs);
    @endphp

    <a href="{{ route(nom_route($route)) }}"
       @class([
           'flex items-center gap-2 rounded-ub px-3 py-2 no-underline',
           'bg-ub-accent-fond font-semibold text-ub-accent-texte' => $actif,
           'text-[#333] hover:bg-[#f5f5f3]' => ! $actif,
       ])
       @if ($actif) aria-current="page" @endif>

        @if ($icone)
            <x-espace.icone :nom="$icone" class="h-4 w-4 shrink-0" />
        @endif

        <span class="flex-1">{{ $slot }}</span>

        @if ($pastille > 0)
            {{-- Le compteur de messages non lus. --}}
            <span class="rounded-[10px] bg-ub-messages px-2 py-px text-[12px] font-bold text-white">{{ $pastille }}</span>
        @endif
    </a>
@endif
