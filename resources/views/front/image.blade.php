@extends('layouts.portail')

@php
    $nom = $creatif->fullName();
    $metier = __(\App\Support\Metier::find($creatif->category?->slug ?? 'autre')['name'] ?? '');
    $avatar = $creatif->thumbnailUrl('carre_183');
@endphp

@section('title', $media->ai_title.' — '.$nom.' | '.$marque->nom)
@section('description', texte_seo($media->ai_description, 155))
@section('og_image', $media->url('ptf_medium'))
{{-- Detail d'un visuel : utile au visiteur, pas aux moteurs (une page par
     image serait du contenu mince). Les liens restent suivis. --}}
@section('robots', 'noindex, follow')
@section('body_class', 'page_image')

@section('content')
    <div class="ui container bloc_portfolios page_image">
        <div class="ui stackable two column grid">
            <div class="column">
                <a href="{{ $creatif->bookUrl() }}" target="_blank">
                    <img class="ui fluid image" src="{{ $media->url('ptf_medium') }}" alt="{{ $media->ai_title }}"
                         @if ($media->width) width="{{ $media->width }}" height="{{ $media->height }}" @endif>
                </a>
            </div>

            <div class="column">
                <h1>{{ $media->ai_title }}</h1>
                @if ($media->ai_description)
                    <p class="description">{{ $media->ai_description }}</p>
                @endif

                @if ($media->tags->isNotEmpty())
                    <div class="ui labels motscles">
                        @foreach ($media->tags as $tag)
                            <a class="ui label" href="{{ $tag->url() }}">{{ $tag->label }}</a>
                        @endforeach
                    </div>
                @endif

                <div class="ui items createur">
                    <div class="item">
                        <a class="ui tiny circular image" href="{{ $creatif->bookUrl() }}" target="_blank">
                            @if ($avatar)
                                <img src="{{ $avatar }}" alt="{{ $nom }}" width="80" height="80">
                            @else
                                <span class="initiales">{{ mb_strtoupper(mb_substr($creatif->firstname, 0, 1).mb_substr($creatif->lastname, 0, 1)) }}</span>
                            @endif
                        </a>
                        <div class="middle aligned content">
                            <a class="header" href="{{ $creatif->bookUrl() }}" target="_blank">{{ $creatif->firstname }} {{ $creatif->lastname }}</a>
                            @if ($metier)<div class="meta">{{ $metier }}</div>@endif
                            <div class="extra">
                                <a class="ui button" href="{{ $creatif->bookUrl() }}" target="_blank">{{ __('Voir le book complet') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($autres->isNotEmpty())
            <h2>{{ __('Autres images de :nom', ['nom' => $nom]) }}</h2>
            <div class="ui six doubling cards">
                @foreach ($autres as $autre)
                    <a class="ui card" href="{{ $autre->pageUrl() }}">
                        <div class="image"><img src="{{ $autre->url('carre_183') }}" alt="{{ $autre->ai_title }}" width="183" height="183" loading="lazy"></div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
