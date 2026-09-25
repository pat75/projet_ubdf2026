@props(['options', 'valeur' => null, 'libelle' => '', 'vide' => '—'])

{{-- Liste deroulante sans champ visible (voir Claude_design.md, « Édition
     sur place » — liste discrete) : le texte choisi, suivi d'une fleche a
     la meme place que le crayon des textes (`ml-2.5`). Le <select> natif
     reste present, invisible, pose par-dessus : clavier, clic et defilement
     tactile marchent comme sur n'importe quelle liste. --}}
<label class="flex flex-col gap-1 text-[12px] font-semibold uppercase tracking-[.06em] text-ub-texte3">
    @if ($libelle)
        <span>{{ $libelle }}</span>
    @endif

    <span class="relative inline-flex w-fit items-center text-[15px] font-normal normal-case tracking-normal text-ub-texte">
        <span>{{ $options[$valeur] ?? $vide }}</span>

        <svg class="ml-2.5 h-4 w-4 shrink-0 text-ub-texte3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
        </svg>

        <select {{ $attributes->merge(['class' => 'absolute inset-0 w-full cursor-pointer appearance-none opacity-0']) }}>
            <option value="">{{ $vide }}</option>
            @foreach ($options as $val => $label)
                <option value="{{ $val }}" @selected((string) $val === (string) $valeur)>{{ $label }}</option>
            @endforeach
        </select>
    </span>
</label>
