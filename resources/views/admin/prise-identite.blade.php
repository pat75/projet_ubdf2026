<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ __('Connexion en tant que :login…', ['login' => $creatif->login]) }}</title>
    <meta name="robots" content="noindex">
</head>
{{--
 | Page de relais. Le bouton du back-office ne sait produire qu'une
 | redirection, or la prise d'identite change l'etat de la session et doit
 | donc passer par un POST protege : ce formulaire se soumet de lui-meme.
 | Le bouton reste la pour les rares cas ou le script ne part pas.
--}}
<body onload="document.forms[0].submit()" style="font-family: system-ui, sans-serif; margin: 4rem auto; max-width: 30rem; text-align: center">
<form method="post" action="{{ route('admin.prise-identite', ['creatif' => $creatif]) }}">
    @csrf
    <p>{{ __('Ouverture de l’espace de :login…', ['login' => $creatif->login]) }}</p>
    <button type="submit">{{ __('Continuer') }}</button>
</form>
</body>
</html>
