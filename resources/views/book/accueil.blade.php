@extends('book.layout')

@section('content')
    @if ($book->bookSetting?->description)
        <div class="book_intro">{!! nl2br(e($book->bookSetting->description)) !!}</div>
    @endif

    <div class="book_galeries_grid">
        @forelse ($navGaleries as $galerie)
            <a class="book_galerie_tuile" href="{{ route('book.galerie', ['login' => $book->login, 'slug' => $galerie->slug]) }}">
                {{ $galerie->name }}
            </a>
        @empty
            <p>{{ __('Ce book ne contient encore aucune galerie.') }}</p>
        @endforelse
    </div>
@endsection
