@props(['parts', 'couleurs' => ['#ff5a72', '#2d9cdb', '#f2c94c', '#3dd1c0']])

{{-- L'anneau des visites par surface. L'original le dessinait avec
     Chart.js ; ici c'est un SVG, quatre arcs poses sur un cercle par le
     jeu de `stroke-dasharray`. Pas de bibliotheque pour quatre nombres. --}}
@php
    $total = array_sum($parts);

    /*
     | L'epaisseur du trait deborde du rayon de part et d'autre : avec un
     | rayon de 80 et un trait de 52, l'anneau va jusqu'a 106 du centre.
     | La boite doit donc mesurer 212, sans quoi les quatre bords sont
     | rognes — c'etait le cas de la premiere version, calee sur 200.
     */
    $rayon = 80;
    $trait = 52;
    $centre = $rayon + $trait / 2;
    $boite = $centre * 2;
    $perimetre = 2 * M_PI * $rayon;

    $arcs = [];
    $debut = 0.0;
    $i = 0;

    foreach ($parts as $libelle => $valeur) {
        $portion = $total > 0 ? $valeur / $total : 0;

        $arcs[] = [
            'libelle' => $libelle,
            'valeur' => $valeur,
            'couleur' => $couleurs[$i % count($couleurs)],
            'longueur' => $portion * $perimetre,
            'decalage' => -$debut * $perimetre,
        ];

        $debut += $portion;
        $i++;
    }
@endphp

<div {{ $attributes }}>
    <ul class="mb-6 flex flex-wrap justify-center gap-x-6 gap-y-2 text-[13px]">
        @foreach ($arcs as $arc)
            <li class="flex items-center gap-2">
                <span class="inline-block h-3 w-6 rounded-sm" style="background-color: {{ $arc['couleur'] }}"></span>
                {{ $arc['libelle'] }}
            </li>
        @endforeach
    </ul>

    @if ($total > 0)
        <svg viewBox="0 0 {{ $boite }} {{ $boite }}" class="mx-auto block w-full max-w-95" role="img"
             aria-label="{{ __('Répartition des visites par support') }}">
            <g transform="rotate(-90 {{ $centre }} {{ $centre }})" fill="none" stroke-width="{{ $trait }}">
                @foreach ($arcs as $arc)
                    @if ($arc['longueur'] > 0)
                        <circle cx="{{ $centre }}" cy="{{ $centre }}" r="{{ $rayon }}" stroke="{{ $arc['couleur'] }}"
                                stroke-dasharray="{{ round($arc['longueur'], 2) }} {{ round($perimetre, 2) }}"
                                stroke-dashoffset="{{ round($arc['decalage'], 2) }}"></circle>
                    @endif
                @endforeach
            </g>
        </svg>
    @else
        <p class="py-10 text-center text-ub-gris-fonce">{{ __('Aucune visite enregistrée pour le moment.') }}</p>
    @endif
</div>
