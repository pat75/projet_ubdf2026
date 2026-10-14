{{ __('Votre nom de domaine :nom expire dans :n jours, le :date.', ['nom' => $domaine->nom, 'n' => $joursRestants, 'date' => $echeance]) }}

{{ __('Bonjour,') }}

{{ __('Pensez à le renouveler.') }}
{{ __('Sans renouvellement, il ne pointera plus vers votre book et vous risquez de le perdre : il redeviendra libre pour tout le monde.') }}

{{ __('Gérer mon nom de domaine') }} : {{ $lienDomaine }}

{{ __('Contact') }} : {{ $marque->email }}
{{ __('L’équipe :marque', ['marque' => $marque->nom]) }}
