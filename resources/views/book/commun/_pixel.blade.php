{{-- Pixels d'un book vu par un visiteur (pas par son createur) :
     - statistiques du book (StatsBookController), sur son propre domaine ;
     - dernieres visites d'un visiteur connecte (VisiteBookController), sur
       le domaine du portail, le seul ou sa session existe. Le navigateur
       l'y envoie : le book est sur un sous-domaine du meme site. --}}
<img src="/ubstats.gif?r={{ random_int(0, 9999) }}" width="1" height="1" alt="" class="hidden">
@if (($login = request()->route('login')) && isset($marque) && $marque->domaineBooks)
    <img src="https://{{ $marque->domaineBooks }}/ubvisite/{{ $login }}.gif" width="1" height="1" alt="" class="hidden" referrerpolicy="no-referrer">
@endif
