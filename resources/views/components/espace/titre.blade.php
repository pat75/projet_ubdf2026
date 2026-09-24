{{-- Titre d'ecran de l'espace : meme typo que le h1 de Mes messages,
     page de reference du bloc d'en-tete (voir Claude_design.md). --}}
<h1 {{ $attributes->merge(['class' => 'mb-11 mt-6 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte']) }}>{{ $slot }}</h1>
