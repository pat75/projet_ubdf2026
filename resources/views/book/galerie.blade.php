@extends('book.layout')

@section('title', $galerie->name.' — '.$book->fullName())

@section('content')
    <h2>{{ $galerie->name }}</h2>

    @if ($galerie->children->isNotEmpty())
        <nav class="book_sous_galeries">
            @foreach ($galerie->children as $enfant)
                <a href="{{ route('book.galerie', ['login' => $book->login, 'slug' => $enfant->slug]) }}">{{ $enfant->name }}</a>
            @endforeach
        </nav>
    @endif

    <div class="book_media_grid">
        @forelse ($galerie->media as $media)
            <figure class="book_media">
                <a href="{{ $media->url() }}" target="_blank">
                    <img src="{{ $media->url('ptf_medium') }}" alt="{{ $media->alt ?? $media->title }}" loading="lazy">
                </a>
                @if ($media->title)
                    <figcaption>{{ $media->title }}</figcaption>
                @endif
            </figure>
        @empty
            <p>{{ __('Cette galerie ne contient encore aucun visuel.') }}</p>
        @endforelse
    </div>
@endsection
