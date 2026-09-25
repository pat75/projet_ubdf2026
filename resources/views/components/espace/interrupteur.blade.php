@props(['actif' => false, 'libelle' => '', 'petit' => false])

{{-- Interrupteur de la maquette : 46 x 26, le bouton glisse d'un bord a
     l'autre. `petit` : reduit de 40 % (28 x 16). C'est un vrai <button> :
     il est atteignable au clavier et annonce son etat par `aria-pressed`. --}}
<button type="button" role="switch" aria-pressed="{{ $actif ? 'true' : 'false' }}"
        {{ $attributes->merge(['class' => 'flex shrink-0 cursor-pointer transition-colors duration-200 '
            .($petit ? 'h-4 w-7 rounded-lg p-0.5 ' : 'h-6.5 w-[46px] rounded-[13px] p-[3px] ')
            .($actif ? 'justify-end bg-ub-accent' : 'justify-start bg-[#d6d6d3]')]) }}>
    <span class="{{ $petit ? 'h-3 w-3' : 'h-5 w-5' }} rounded-full bg-white shadow-[0_1px_3px_rgba(0,0,0,.2)]"></span>
    <span class="sr-only">{{ $libelle }}</span>
</button>
