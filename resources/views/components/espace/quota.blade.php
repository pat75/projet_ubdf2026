@props(['libelle', 'quota', 'lien' => null])

{{-- Une ligne de compteur : la valeur, un camembert de remplissage, le
     plafond, et le chevron qui mene a la rubrique concernee. Rouge des
     que le plafond est franchi, comme dans l'espace d'origine. --}}
@php
    $valeur = (int) $quota['valeur'];
    $plafond = max(1, (int) $quota['plafond']);
    $part = min(1, $valeur / $plafond);
    $depasse = $valeur > $quota['plafond'];

    // Le camembert : un cercle dont le trait pointille dessine la part
    // remplie, vu de face (rayon 8, perimetre ~50,3).
    $perimetre = 2 * M_PI * 8;
@endphp

<div class="flex items-center justify-between gap-4 border-b border-ub-gris-clair py-4 text-[16px]">
    <span>{{ $libelle }}</span>

    <span class="flex items-center gap-2 {{ $depasse ? 'text-ub-rouge' : '' }}">
        @if ($depasse)
            <span class="text-[11px]">{{ __('Vous avez dépassé la limite de votre formule') }}</span>
        @endif

        <span>{{ number_format($valeur, 0, ',', ' ') }} {{ $quota['unite'] }}</span>

        <svg viewBox="0 0 20 20" class="h-4 w-4 -rotate-90" role="img"
             aria-label="{{ __(':part % du maximum', ['part' => round($part * 100)]) }}">
            <circle cx="10" cy="10" r="9" fill="#ffffff" stroke="currentColor" stroke-width="1" opacity="0.4"/>
            <circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="16"
                    stroke-dasharray="{{ round($part * $perimetre, 2) }} {{ round($perimetre, 2) }}"/>
        </svg>

        <em class="not-italic text-ub-gris-fonce">( max: {{ number_format($quota['plafond'], 0, ',', ' ') }} {{ $quota['unite'] }} )</em>

        @if ($lien)
            <a href="{{ $lien }}" class="text-ub-texte hover:text-ub-rouge" aria-label="{{ $libelle }}">❯</a>
        @endif
    </span>
</div>
