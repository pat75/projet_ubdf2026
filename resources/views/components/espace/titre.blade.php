{{-- Titre d'ecran de l'espace : .h1_page_titre de l'original, soit du
     Lato maigre en 28 px, avec 24 px d'air au-dessus et 44 px en dessous. --}}
<h1 {{ $attributes->merge(['class' => 'mb-11 mt-6 font-titre text-[28px] font-light text-black']) }}>{{ $slot }}</h1>
