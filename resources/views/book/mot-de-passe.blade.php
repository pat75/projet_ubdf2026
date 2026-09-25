<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $galerie->name }} — {{ $book }}</title>
    {{-- Page commune aux onze habillages du book : sobre, sans feuille
         externe, pour ne dependre d'aucun theme. --}}
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;
               font-family: Helvetica, Arial, sans-serif; color: #2b2b2b; background: #f4f4f3; }
        main { width: 100%; max-width: 380px; padding: 36px 28px; background: #fff; text-align: center;
               box-shadow: 0 1px 3px rgba(0, 0, 0, .08); }
        svg { width: 40px; height: 40px; color: #2b2b2b; }
        .book { margin: 18px 0 4px; font-size: 13px; letter-spacing: .08em; text-transform: uppercase; color: #777; }
        h1 { margin: 0 0 22px; font-size: 24px; font-weight: 300; }
        label { display: block; margin-bottom: 8px; font-size: 14px; color: #555; }
        input { width: 100%; height: 43px; padding: 0 12px; border: 1px solid #d9d9d6; font-size: 16px; }
        input:focus { outline: none; border-color: #2b2b2b; }
        button { width: 100%; height: 43px; margin-top: 12px; border: 0; background: #000; color: #fff; font-size: 15px; font-weight: bold; cursor: pointer; }
        button:hover { background: #333; }
        .erreur { margin: 12px 0 0; font-size: 14px; color: #a12a2a; }
        .retour { display: inline-block; margin-top: 22px; font-size: 13px; color: #777; }
    </style>
</head>
<body>
<main>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/><path d="M12 15v2"/>
    </svg>
    <div class="book">{{ $book }}</div>
    <h1>{{ $galerie->name }}</h1>

    <form method="post">
        @csrf
        <label for="mot_de_passe">{{ __('Ce portfolio est protégé. Saisissez son mot de passe.') }}</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required autofocus autocomplete="off">
        @if ($erreur)
            <p class="erreur" role="alert">{{ $erreur }}</p>
        @endif
        <button type="submit">{{ __('Ouvrir le portfolio') }}</button>
    </form>

    <a class="retour" href="/">{{ __('← Retour au book') }}</a>
</main>
</body>
</html>
