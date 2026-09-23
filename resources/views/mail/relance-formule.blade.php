<x-mail::message>
# {{ __('Votre formule arrive à échéance') }}

{{ __('Bonjour,') }}

@if ($joursRestants > 0)
{{ __('Votre formule :marque se termine dans :n jours, le :date.', ['marque' => $marque->nom, 'n' => $joursRestants, 'date' => $creatif->echeanceFormule()?->translatedFormat('j F Y')]) }}
@else
{{ __('Votre formule :marque se termine aujourd’hui.', ['marque' => $marque->nom]) }}
@endif

{{ __('Sans renouvellement, votre book reste en ligne mais repasse en formule gratuite : le nombre de visuels et de pages y est limité.') }}

<x-mail::button :url="$lienFormule">
{{ __('Renouveler ma formule') }}
</x-mail::button>

<x-mail::panel>
{{ __('Votre book :') }} {{ $creatif->bookUrl() }}
</x-mail::panel>

{{ $marque->nom }}
</x-mail::message>
