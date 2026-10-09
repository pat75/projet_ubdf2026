{{ __('Votre portfolio est ouvert. Voici de quoi le retrouver.') }}

{{ __('Votre book') }} : {{ $compte->bookUrl() }}
{{ __('Identifiant :') }} {{ $compte->login }}

{{ __('Confirmez votre adresse pour que nous puissions vous joindre — notamment quand un visiteur vous écrit.') }}
{{ $lienConfirmation }}

{{ $marque->email }}
{{ __('L’équipe :marque', ['marque' => $marque->nom]) }}
