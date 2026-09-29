<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ __('Connexion en tant que :email…', ['email' => $visiteur->email]) }}</title>
    <meta name="robots" content="noindex">
</head>
{{--
 | Page de relais, comme pour un createur : le bouton du back-office ne
 | sait produire qu'une redirection, or la prise d'identite change l'etat
 | de la session et doit passer par un POST protege.
--}}
<body onload="document.forms[0].submit()" style="font-family: system-ui, sans-serif; margin: 4rem auto; max-width: 30rem; text-align: center">
<form method="post" action="{{ route('admin.prise-identite-visiteur', ['visiteur' => $visiteur]) }}">
    @csrf
    <p>{{ __('Ouverture du compte de :email…', ['email' => $visiteur->email]) }}</p>
    <button type="submit">{{ __('Continuer') }}</button>
</form>
</body>
</html>
