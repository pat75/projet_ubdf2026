@props(['route'])

{{-- Les rubriques pas encore livrees n'apparaissent pas : pas de lien mort.

     Un element de liste separe d'un filet gris clair, sauf le dernier ;
     la rubrique ouverte passe au rouge et prend une puce, comme dans
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

    <li class="border-b border-ub-gris-clair px-px py-1 last:border-b-0">
        <a href="{{ route(nom_route($route)) }}"
           @class([
               'no-underline',
               'text-ub-rouge' => $actif,
               'text-ub-texte hover:text-ub-gris-moyen' => ! $actif,
           ])
           @if ($actif) aria-current="page" @endif>@if ($actif)<span aria-hidden="true">★ </span>@endif{{ $slot }}</a>
    </li>
@endif
