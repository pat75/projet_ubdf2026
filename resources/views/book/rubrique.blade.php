@extends('book.layout')

@section('title', $rubrique->title.' — '.$book->fullName())

@section('content')
    <h2>{{ $rubrique->title }}</h2>

    @if ($rubrique->children->isNotEmpty())
        <nav class="book_sous_rubriques">
            @foreach ($rubrique->children as $enfant)
                <a href="{{ route('book.rubrique', ['login' => $book->login, 'slug' => $enfant->slug]) }}">{{ $enfant->title }}</a>
            @endforeach
        </nav>
    @endif

    @forelse ($rubrique->articles as $article)
        <article class="book_article">
            <h3>{{ $article->title }}</h3>
            @if ($article->image)
                <img src="{{ route('book.media.declinaison', ['login' => $book->login, 'declinaison' => 'ptf_medium', 'file' => $article->image]) }}" alt="">
            @endif
            <div class="book_article_corps">{!! $article->body !!}</div>
        </article>
    @empty
        <p>{{ __('Cette rubrique ne contient encore aucun article.') }}</p>
    @endforelse
@endsection
