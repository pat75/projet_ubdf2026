{{-- Carte d'un book sur le portail.
     Structure et classes reprises telles quelles du front 2018 : les
     attributs data-* sont lus par resources/js/portail/visionneuse.js. --}}
@props(['book', 'nouvelle' => false, 'apparition' => null, 'prioritaire' => false])

@php
    $category = $book->category?->slug ?? 'autre';
    $name = $book->fullName();
    $cover = $book->visuelsMiniBook()->first();
@endphp

<div class="ui card {{ $category }} ptf_index_static dimmable cursor_effect {{ $nouvelle ? 'newitem_hide' : '' }}"
     id="user_{{ $book->login }}"
     data-user="{{ $book->login }}"
     data-us_="us_prenom_nom"
     data-user_detail="{{ json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
     data-slider="{{ json_encode($slider, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
     data-motcles=''
     @if ($apparition !== null) x-apparition.{{ $apparition }} @endif
>
    <a class="ui fluid image dimmable"
       href="{{ $book->bookUrl() }}"
       target="_blank"
       data-user="{{ $book->login }}"
       data-url="{{ $book->bookUrl() }}"
    >
        <div class="ui dimmer transition hidden">
            <div class="content">
                <div class="center">
                    <div class="ui inverted button">ZOOM</div>
                </div>
            </div>
        </div>

        {{-- Hors premiere rangee de l'accueil, le navigateur ne charge l'image
             qu'a l'approche de l'ecran (175 images sur l'accueil). --}}
        <img src="{{ $cover?->url('front_desk') }}"
             @if ($prioritaire) fetchpriority="high" @else loading="lazy" @endif decoding="async"
             alt="{{ filled($cover?->title) ? $cover->title.' - ' : '' }}{{ $name }}-{{ $category }}">
    </a>

    <div class="content center aligned">
        @if ($book->bookSetting?->thumbnail)
            <img class="ui avatar image" data-us_="us_vign" @unless ($prioritaire) loading="lazy" @endunless decoding="async"
                 src="{{ $book->thumbnailUrl('carre_183') }}" alt="{{ $name }}-{{ $category }}" />
        @endif

        <div class="header">{{ $name }}</div>
        <div class="meta">
            <a class="group coul_{{ $category }}"
               href="{{ $book->bookUrl() }}"
               title="{{ $category }}">{{ __($book->category?->name ?? '') }}</a>
        </div>
    </div>

    {{-- Vues et coeurs : chiffres du legacy (ubdf:legacy:accueil) plus ceux
         comptes depuis (User::vuesTotales). Comme le legacy : pas de coeur en dessous de 2. --}}
    <div class="extra content">
        <div class="left floated">
            <div class="vues">
                <i class="fonticon-eye3 icon"></i><span class="stats_vue">{{ $vues }}</span>
            </div>
        </div>
        @if ($book->coeursTotaux() >= 2)
            <div class="right floated">
                <div class="like">
                    <i class="fonticon-heart2 icon"></i><span class="stats_sel">{{ $book->coeursTotaux() }}</span>
                </div>
            </div>
        @endif
    </div>
</div>
