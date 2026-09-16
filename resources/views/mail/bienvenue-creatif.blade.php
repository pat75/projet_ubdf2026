<x-mail::message>
# {{ __('Bienvenue') }}

{{ __('Votre portfolio est ouvert. Voici de quoi le retrouver.') }}

<x-mail::panel>
{{ __('Identifiant :') }} **{{ $compte->login }}**

{{ __('Adresse de votre book :') }} {{ $compte->bookUrl() }}
</x-mail::panel>

{{ __('Confirmez votre adresse pour que nous puissions vous joindre — notamment quand un visiteur vous écrit.') }}

<x-mail::button :url="$lienConfirmation">
{{ __('Confirmer mon adresse') }}
</x-mail::button>

{{ $marque->nom }}
</x-mail::message>
