<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $book->fullName())</title>
    @if ($book->bookSetting?->description)
        <meta name="description" content="{{ Str::limit(strip_tags($book->bookSetting->description), 160) }}">
    @endif
    @vite('resources/css/book.css')
</head>
<body class="book_theme_{{ $book->bookSetting?->theme ?? 'mdl_default' }}"
      style="{{ $book->bookSetting?->background_color ? 'background-color:'.$book->bookSetting->background_color.';' : '' }}">

<header class="book_header">
    @if ($book->bookSetting?->bio_photo)
        <img class="book_bio_photo"
             src="{{ route('book.media.declinaison', ['login' => $book->login, 'declinaison' => 'adm_medium', 'file' => $book->bookSetting->bio_photo]) }}"
             alt="">
    @endif
    <h1><a href="{{ route('book.home', ['login' => $book->login]) }}">{{ $book->fullName() }}</a></h1>
    @if ($book->category)
        <p class="book_metier">{{ $book->category->name }}</p>
    @endif

    <nav class="book_nav">
        @foreach ($navGaleries as $lien)
            <a href="{{ route('book.galerie', ['login' => $book->login, 'slug' => $lien->slug]) }}">{{ $lien->name }}</a>
        @endforeach
        @foreach ($navRubriques as $lien)
            <a href="{{ route('book.rubrique', ['login' => $book->login, 'slug' => $lien->slug]) }}">{{ $lien->title }}</a>
        @endforeach
        {{-- Le formulaire de contact (js_core_cards.js, /intermediate_send)
             vit dans la fenetre modale du portail. Plutot que d'en
             dupliquer la mecanique JS sur le sous-domaine du book, ce lien
             renvoie vers la fiche du createur sur le portail, ou il
             fonctionne deja. --}}
        <a href="{{ $book->portfolioUrl() }}">{{ __('Contacter') }}</a>
    </nav>
</header>

<main class="book_content">
    @yield('content')
</main>

<footer class="book_footer">
    @if ($book->bookSetting?->footer)
        <p>{!! nl2br(e($book->bookSetting->footer)) !!}</p>
    @endif
    <p class="book_powered">{{ __('Créé avec :marque', ['marque' => $marque->nom]) }}</p>
</footer>

</body>
</html>
