@props(['libelle', 'quota', 'lien' => null])

{{-- Une ligne de compteur : la valeur, le plafond, le chevron qui mene a
     la rubrique concernee, et une jauge horizontale sous la ligne — plus
     lisible d'un coup d'oeil que l'ancien petit camembert. Rouge des que
     le plafond est franchi, comme dans l'espace d'origine. --}}
@php
    $valeur = (int) $quota['valeur'];
    $plafond = max(1, (int) $quota['plafond']);
    $part = min(1, $valeur / $plafond);
    $depasse = $valeur > $quota['plafond'];
@endphp

<div class="border-b border-ub-filet py-4 text-[16px] last:border-b-0">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <span>{{ $libelle }}</span>

        <span class="flex items-center gap-2 {{ $depasse ? 'text-ub-rouge' : '' }}">
            @if ($depasse)
                <span class="text-[11px]">{{ __('Vous avez dépassé la limite de votre formule') }}</span>
            @endif

            <span>{{ number_format($valeur, 0, ',', ' ') }} {{ $quota['unite'] }}</span>

            <em class="not-italic text-ub-texte3">( max: {{ number_format($quota['plafond'], 0, ',', ' ') }} {{ $quota['unite'] }} )</em>

            @if ($lien)
                <a href="{{ $lien }}" class="text-ub-texte hover:text-ub-rouge" aria-label="{{ $libelle }}">
                    <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0" />
                </a>
            @endif
        </span>
    </div>

    <div class="mt-2.5 h-2.5 overflow-hidden rounded-full bg-ub-fond" role="img"
         aria-label="{{ __(':part % du maximum', ['part' => round($part * 100)]) }}">
        <div class="h-full rounded-full {{ $depasse ? 'bg-ub-rouge' : 'bg-ub-accent' }}" style="width: {{ round($part * 100, 1) }}%"></div>
    </div>
</div>
