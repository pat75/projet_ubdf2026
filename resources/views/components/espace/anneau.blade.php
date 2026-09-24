@props(['parts', 'couleurs' => ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0']])

{{-- L'anneau des visites par surface, et le detail par formule a cote :
     un chemin en pourcentage lit plus vite qu'une legende seule. L'original
     le dessinait avec Chart.js ; ici c'est un SVG, quatre arcs poses sur un
     cercle par le jeu de `stroke-dasharray`. Pas de bibliotheque pour
     quatre nombres. --}}
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
            'pourcentage' => $total > 0 ? round($portion * 100) : 0,
            'longueur' => $portion * $perimetre,
            'decalage' => -$debut * $perimetre,
        ];

        $debut += $portion;
        $i++;
    }
@endphp

<div {{ $attributes }}>
    @if ($total > 0)
        <div class="grid items-center gap-8 sm:grid-cols-[auto_1fr]">
            <svg viewBox="0 0 {{ $boite }} {{ $boite }}" class="mx-auto block w-55" role="img"
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
                <circle cx="{{ $centre }}" cy="{{ $centre }}" r="{{ $rayon - $trait / 2 }}" fill="white"></circle>
                <text x="{{ $centre }}" y="{{ $centre }}" text-anchor="middle" dominant-baseline="middle"
                      class="fill-ub-texte3" style="font-size: 13px">{{ __(':n formats', ['n' => count($arcs)]) }}</text>
            </svg>

            <div class="flex flex-col gap-4">
                @foreach ($arcs as $arc)
                    <div>
                        <div class="flex items-center gap-2.5">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-[3px]" style="background-color: {{ $arc['couleur'] }}"></span>
                            <span class="text-[15px] font-bold text-ub-texte">{{ $arc['libelle'] }}</span>
                            <span class="ml-auto text-[15px] text-ub-texte">{{ number_format($arc['valeur'], 0, ',', ' ') }}</span>
                            <span class="w-11 text-right text-[13px] text-ub-texte3">{{ $arc['pourcentage'] }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded bg-ub-fond">
                            <div class="h-full rounded" style="width: {{ $arc['pourcentage'] }}%; background-color: {{ $arc['couleur'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="py-10 text-center text-ub-texte3">{{ __('Aucune visite enregistrée pour le moment.') }}</p>
    @endif
</div>
