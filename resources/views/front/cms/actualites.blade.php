@extends('layouts.portail')

@section('title', 'Actualités | Ultra-book')
@section('body_class', 'page_actus')

@section('content')
    <div class="ui container bloc_cms">
        <div class="bloc_titre">
            <h1>{{ __('Actualités') }}</h1>
        </div>

        <div class="ui three doubling stackable cards liste_actus">
            @foreach ($actualites as $actualite)
                <a class="ui card" href="{{ route('actualite', $actualite->slug) }}">
                    @if ($actualite->image)
                        <div class="image"><img src="{{ $actualite->image }}" alt=""></div>
                    @endif
                    <div class="content">
                        <div class="header">{{ $actualite->title }}</div>
                        @if ($actualite->published_at)
                            <div class="meta">{{ $actualite->published_at->translatedFormat('j F Y') }}</div>
                        @endif
                        @if ($actualite->excerpt)
                            <div class="description">{{ Str::limit(strip_tags($actualite->excerpt), 140) }}</div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        {{ $actualites->links() }}
    </div>
@endsection
