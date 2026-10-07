{{-- Blocs metiers de l'accueil, rendus une fois puis gardes en cache
     (AccueilController::index) : identiques pour tous les visiteurs. --}}
@foreach ($blocs as $bloc)
    <x-bloc-metier :slug="$bloc['slug']" :books="$bloc['books']" :total="$bloc['total']" :premier="$loop->first" />
@endforeach
