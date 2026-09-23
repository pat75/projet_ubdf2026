<x-mail::message>
# {{ __('Votre nouveau mot de passe') }}

{{ __('Bonjour,') }}

{{ __('À votre demande, nous avons réinitialisé le mot de passe de votre compte :marque.', ['marque' => $marque->nom]) }}

<x-mail::panel>
{{ __('Identifiant :') }} **{{ $creatif->login }}**
{{ __('Mot de passe :') }} **{{ $motDePasse }}**
</x-mail::panel>

{{ __('Ce mot de passe est provisoire : changez-le dès votre première connexion, dans votre espace, rubrique « Mon compte ».') }}

<x-mail::button :url="$lienConnexion">
{{ __('Me connecter') }}
</x-mail::button>

{{ __('Si vous n’êtes pas à l’origine de cette demande, prévenez-nous : votre ancien mot de passe ne fonctionne plus.') }}

{{ $marque->nom }}
</x-mail::message>
