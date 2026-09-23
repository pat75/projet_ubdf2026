@props(['route', 'icone' => null, 'pastille' => 0, 'couleur' => null])

{{-- Les rubriques pas encore livrees n'apparaissent pas : pas de lien mort.

     Un element de liste separe d'un filet gris clair, sauf le dernier ;
     la rubrique ouverte passe au rouge et prend son chevron, comme dans
     l'espace d'origine (ul.ub_nav_user de core_user_admin.less). --}}
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

    <li class="border-b border-ub-gris-clair last:border-b-0">
        <a href="{{ route(nom_route($route)) }}"
           @class([
               'flex items-center gap-2 py-1.5 no-underline',
               'text-ub-rouge' => $actif,
               ($couleur ?? 'text-ub-texte hover:text-ub-gris-moyen') => ! $actif,
           ])
           @if ($actif) aria-current="page" @endif>

            @if ($actif)
                <span aria-hidden="true" class="-ml-2.5 text-xs">❯</span>
            @elseif ($icone)
                <x-espace.icone :nom="$icone" class="h-4 w-4" />
            @endif

            <span>{{ $slot }}</span>

            @if ($pastille > 0)
                {{-- Le compteur de messages non lus, en pastille violette. --}}
                <span class="ml-1 rounded-full bg-[#9b51e0] px-2 py-px text-[11px] font-semibold text-white">{{ $pastille }}</span>
            @endif
        </a>
    </li>
@endif
