<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Book introuvable') }}</title>
    @vite('resources/css/book.css')
</head>
<body>
<main class="book_content book_introuvable">
    <h1>{{ __('Ce book n’existe pas ou n’est plus disponible.') }}</h1>
    <p>
        <a href="{{ $accueilPortail }}">{{ __('Retour à l’accueil') }}</a>
    </p>
</main>
</body>
</html>
