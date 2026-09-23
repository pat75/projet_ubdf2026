@props(['actif' => false, 'libelle' => ''])

{{-- Interrupteur de la maquette : 46 x 26, le bouton glisse d'un bord a
     l'autre. C'est un vrai <button> : il est atteignable au clavier et
     annonce son etat par `aria-pressed`. --}}
<button type="button" role="switch" aria-pressed="{{ $actif ? 'true' : 'false' }}"
        {{ $attributes->merge(['class' => 'flex h-6.5 w-[46px] shrink-0 cursor-pointer rounded-[13px] p-[3px] transition-colors duration-200 '.($actif ? 'justify-end bg-ub-accent' : 'justify-start bg-[#d6d6d3]')]) }}>
    <span class="h-5 w-5 rounded-full bg-white shadow-[0_1px_3px_rgba(0,0,0,.2)]"></span>
    <span class="sr-only">{{ $libelle }}</span>
</button>
