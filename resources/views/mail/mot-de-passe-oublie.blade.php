<x-mail::message>
# {{ __('Réinitialiser votre mot de passe') }}

@if (count($comptes) > 1)
{{ __('Plusieurs comptes utilisent cette adresse. Choisissez celui dont vous voulez changer le mot de passe.') }}
@else
{{ __('Vous avez demandé à changer votre mot de passe.') }}
@endif

@foreach ($comptes as $compte)
<x-mail::button :url="$compte['lien']">
{{ $compte['login'] }}
</x-mail::button>
@endforeach

{{ __('Ce lien est valable :heures heures et ne sert qu\'une fois.', ['heures' => App\Services\Auth\MotDePasse::VALIDITE_HEURES]) }}

{{ __('Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message : votre mot de passe actuel reste valable.') }}

{{ config('app.name') }}
</x-mail::message>
