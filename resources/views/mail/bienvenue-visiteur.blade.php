<x-mail::message>
# {{ __('Bienvenue') }}

{{ __('Votre mémo book est enregistré. Retrouvez-le depuis n’importe quel appareil en vous connectant avec cette adresse.') }}

{{ __('Confirmez votre adresse : vous retrouverez aussi dans votre compte les messages envoyés aux créatifs avec elle.') }}

<x-mail::button :url="$lienConfirmation">
{{ __('Confirmer mon adresse') }}
</x-mail::button>

{{ $marque->nom }}
</x-mail::message>
