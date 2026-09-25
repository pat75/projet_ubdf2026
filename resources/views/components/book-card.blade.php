{{-- Carte d'un book sur le portail.
     Structure et classes reprises telles quelles du front 2018 : les
     attributs data-* sont lus par resources/js/portail/visionneuse.js. --}}
@props(['book', 'nouvelle' => false, 'apparition' => null])

@php
    $category = $book->category?->slug ?? 'autre';
    $name = $book->fullName();
    $cover = $book->media->first();
@endphp

<div class="ui card {{ $category }} ptf_index_static dimmable cursor_effect {{ $nouvelle ? 'newitem_hide' : '' }}"
     id="user_{{ $book->login }}"
     data-user="{{ $book->login }}"
     data-us_="us_prenom_nom"
     data-user_detail='@json($detail, JSON_UNESCAPED_UNICODE)'
     data-slider='@json($slider, JSON_UNESCAPED_UNICODE)'
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

        <img src="{{ $cover?->url('front_desk') }}"
             alt="{{ $cover?->title }} - {{ $name }}-{{ $category }}">
    </a>

    <div class="content center aligned">
        @if ($book->bookSetting?->thumbnail)
            <img class="ui avatar image" data-us_="us_vign"
                 src="{{ $book->thumbnailUrl('carre_183') }}" alt="{{ $name }}-{{ $category }}" />
        @endif

        <div class="header">{{ $name }}</div>
        <div class="meta">
            <a class="group coul_{{ $category }}"
               href="{{ $book->portfolioUrl() }}"
               title="{{ $category }}">{{ $book->category?->name }}</a>
        </div>
    </div>

    <div class="extra content">
        <div class="left floated">
            <div class="vues">
                <i class="fonticon-eye3 icon"></i><span class="stats_vue"> </span>
            </div>
        </div>
        <div class="right floated">
            <div class="like">
                <i class="fonticon-heart2 icon"></i><span class="stats_sel"> </span>
            </div>
        </div>
    </div>
</div>
