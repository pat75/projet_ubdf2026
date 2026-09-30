<x-mail::message>
# {{ __('Nouvelle adresse') }}

{{ __('Vous avez demandé à utiliser cette adresse pour le book :login. Elle ne sera enregistrée qu’une fois confirmée.', ['login' => $compte->login]) }}

<x-mail::button :url="$lienConfirmation">
{{ __('Confirmer cette adresse') }}
</x-mail::button>

{{ __('Ce lien est valable 24 heures. Si vous n’êtes pas à l’origine de cette demande, ignorez ce message : rien ne change.') }}
</x-mail::message>
