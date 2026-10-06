@php
    $echeance = $creatif->echeanceFormule()?->translatedFormat('j F Y');
    $reabo = config('formules.options.3');
@endphp
@if ($joursRestants < 0)
Votre formule {{ $marque->nom }} est arrivée à échéance{{ $echeance ? ' le '.$echeance : '' }}.
@elseif ($joursRestants === 0)
Votre formule {{ $marque->nom }} arrive à échéance aujourd’hui.
@else
Votre formule {{ $marque->nom }} arrive à échéance le {{ $echeance }}.
@endif

Bonjour,

Votre book : {{ $creatif->bookUrl() }}

Avec la formule premium, votre portfolio gagne en visibilité auprès des professionnels et des maisons d’édition qui consultent {{ $marque->nom }}. Renouvelez votre formule dès maintenant pour obtenir la meilleure visibilité et rester en contact avec les commanditaires.
@if ($reabo)

Tarif réservé au renouvellement : formule {{ $reabo['mois'] }} mois à {{ number_format($reabo['ttc'], 2, ',', ' ') }} € TTC.
@endif

Renouveler : {{ $lienFormule }}
Votre identifiant : {{ $creatif->login }}

{{ $joursRestants < 0 ? 'En attendant, votre compte repasse' : 'Sans renouvellement, votre compte repassera' }} en formule gratuite. Aucune image ne sera supprimée ; la visibilité publique est limitée aux 24 premières images.

Contact : {{ $marque->email }}
L’équipe {{ $marque->nom }}
