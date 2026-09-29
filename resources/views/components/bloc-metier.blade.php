{{-- Bloc d'une categorie sur l'accueil : titre, cartes, compteur et lien
     « voir tous ». Structure et classes reprises du front 2018. --}}
@props(['slug', 'books', 'total'])

@php
    $url = lien_metier($slug);
    $pluriel = App\Support\Metier::pluriel($slug);
@endphp

<div class="ui container bloc_portfolios">

    <div class="bloc_titre" x-apparition>
        <a href="{{ $url }}">
            <h2 class="metier_group coultxt_{{ $slug }}">{{ App\Support\Metier::titreBloc($slug) }}</h2>
            <div class="sub_title">{{ App\Support\Metier::sousTitre($slug) }}</div>
        </a>
    </div>

    <div class="visibility">
        <div class="ui five doubling cards">
            @foreach ($books as $book)
                {{-- Cartes en cascade : 70 ms de plus par carte, plafonne. --}}
                <x-book-card :book="$book" :apparition="min($loop->index * 70, 420)" />
            @endforeach

            {{-- Derniere carte du bloc : compteur et acces a la categorie. --}}
            <div class="ui card dimmable cat_link" x-apparition:zoom.{{ min(count($books) * 70, 490) }}>
                <a href="{{ $url }}" class="metier_carre_all_link cursor_effect">
                    <div class="content center aligned">
                        <div class="cat_link_icon">
                            <span class="fonticon-chevron-right"></span>
                        </div>
                        <div class="cat_link_txt">
                            <strong>{{ number_format($total, 0, ',', ' ') }}</strong>
                            <br/>
                            {{ $pluriel }}
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="grid-item width100 grid-metier iscrool_newitem voir_tous_metier_link" x-apparition>
        <a href="{{ $url }}" class="metier_carre_all_link">
            <span class="fonticon-uniF006 fonticon_b18"></span>
            {{ __('Voir tous les') }} <strong>{{ $pluriel }}</strong>
        </a>
    </div>

</div>
