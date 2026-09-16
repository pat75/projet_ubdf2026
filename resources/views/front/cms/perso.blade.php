{{-- Bloc « personne » du code court [perso] de l'ancien theme WordPress.
     Rendu une fois a l'import, pas a chaque affichage. --}}
<div class="ub_perso" @if ($style) style="{{ $style }}" @endif>
    <div class="ub_perso_img vcenter">
        <img src="{{ $image }}" alt="{{ $nom }}">
    </div>
    <div class="ub_perso_txt vcenter">
        <h3 style="margin:0">{{ $nom }}</h3>
        <i>{{ $role }}</i>
    </div>
</div>
