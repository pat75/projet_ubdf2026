<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Book introuvable') }}</title>
    <style>
        body { margin: 0; font-family: Helvetica, Arial, sans-serif; color: #222; background: #fff; }
        .book_introuvable { max-width: 640px; margin: 80px auto; padding: 0 16px; text-align: center; }
        .book_introuvable h1 { font-size: 1.3rem; font-weight: normal; }
        .book_introuvable a { color: #222; }
    </style>
</head>
<body>
<main class="book_introuvable">
    <h1>{{ __('Ce book n’existe pas ou n’est plus disponible.') }}</h1>
    <p>
        <a href="{{ $accueilPortail }}">{{ __('Retour à l’accueil') }}</a>
    </p>
</main>
</body>
</html>
