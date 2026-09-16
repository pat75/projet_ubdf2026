@extends('layouts.portail')

@section('title', $actualite->title.' | Ultra-book')
@section('body_class', 'page_actu')

@section('content')
    <div class="ui container bloc_cms">
        <article>
            <h1>{{ $actualite->title }}</h1>

            @if ($actualite->published_at)
                <div class="meta">{{ $actualite->published_at->translatedFormat('j F Y') }}</div>
            @endif

            @if ($actualite->image)
                <img class="ui fluid image" src="{{ $actualite->image }}" alt="">
            @endif

            <div class="contenu_cms">{!! $actualite->body !!}</div>
        </article>

        @if ($suivantes->isNotEmpty())
            <h2>{{ __('À lire aussi') }}</h2>

            <div class="ui four doubling stackable cards">
                @foreach ($suivantes as $autre)
                    <a class="ui card" href="{{ lien('actualite', $autre->slug) }}">
                        <div class="content">
                            <div class="header">{{ $autre->title }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
