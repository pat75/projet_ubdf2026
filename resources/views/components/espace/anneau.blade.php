@props(['parts', 'couleurs' => ['#ff5a72', '#2d9cdb', '#f2c94c', '#3dd1c0']])

{{-- L'anneau des visites par surface. L'original le dessinait avec
     Chart.js ; ici c'est un SVG, quatre arcs poses sur un cercle par le
     jeu de `stroke-dasharray`. Pas de bibliotheque pour quatre nombres. --}}
@php
    $total = array_sum($parts);
    $rayon = 80;
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
        <svg viewBox="0 0 200 200" class="mx-auto block w-full max-w-[380px]" role="img"
             aria-label="{{ __('Répartition des visites par support') }}">
            <g transform="rotate(-90 100 100)" fill="none" stroke-width="52">
                @foreach ($arcs as $arc)
                    @if ($arc['longueur'] > 0)
                        <circle cx="100" cy="100" r="{{ $rayon }}" stroke="{{ $arc['couleur'] }}"
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
