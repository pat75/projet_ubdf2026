{{-- Cartes servies au defilement infini. Meme composant que le premier
     ecran, avec « nouvelle » pour declencher le fondu a l'insertion. --}}
@foreach ($books as $book)
    <x-book-card :book="$book" :nouvelle="true" />
@endforeach
