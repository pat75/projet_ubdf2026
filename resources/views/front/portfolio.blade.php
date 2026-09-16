@extends('layouts.portail')

@section('title', $book->fullName().' - '.$book->category?->name.' | Ultra-book')
@section('description', Str::limit(strip_tags($book->bookSetting?->description ?? ''), 160))
@section('body_class', 'page_book_single')

@section('content')
    <div class="ui container bloc_portfolios book_single">
        <h1>{{ $book->fullName() }}</h1>
        <div class="meta">
            <a class="group coul_{{ $book->category?->slug }}">{{ $book->category?->name }}</a>
            @if ($book->city)<span class="ville">{{ $book->city }}</span>@endif
        </div>

        @if ($book->bookSetting?->description)
            <div class="bio">{!! nl2br(e($book->bookSetting->description)) !!}</div>
        @endif

        <div class="ui five doubling cards">
            @foreach ($book->media->take(20) as $media)
                <div class="ui card">
                    <a class="ui fluid image" href="{{ $media->url() }}" target="_blank">
                        <img src="{{ $media->url() }}" alt="{{ $media->alt ?? $media->title }}">
                    </a>
                    @if ($media->title)
                        <div class="content center aligned">
                            <div class="header">{{ $media->title }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <a class="ui button" href="{{ $book->bookUrl() }}" target="_blank">
            {{ __('Voir le book complet') }}
        </a>
    </div>
@endsection
