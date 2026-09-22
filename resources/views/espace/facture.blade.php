{{-- Reprise de ub_content_facture__ub/df.tlp.php : une page A4 a imprimer. --}}
@php
    $ht = round((float) $facture->amount - (float) $facture->vat, 2);
    $taux = $facture->issued_at && $facture->issued_at->year < 2014 ? '19,6' : '20';
    $marqueNom = $facture->brand === 'df' ? 'Dustfolio' : 'Ultra-book';
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $marqueNom }} | {{ __('Facture') }} n° {{ $facture->numero() }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; }
        #fac { margin: 10px; padding: 15px 40px 20px; border: 1px solid #999; border-radius: 8px; width: 550px; min-height: 850px; }
        .entete { margin: 30px 0 0 330px; }
        .entete h4 { color: #777; font-size: 11px; font-weight: normal; }
        .num { margin: 60px 0 20px; }
        .num h2 { font-size: 16px; margin-bottom: 4px; }
        .des div, .tot div { border-bottom: 1px solid #999; padding: 10px; }
        .tot div { display: flex; justify-content: space-between; }
        .conditions { margin: 60px 0 20px; font-size: 8px; }
        @media print { #fac { border: 0; } .imprimer { display: none; } }
    </style>
</head>
<body>
<p class="imprimer"><button type="button" onclick="window.print()">{{ __('Imprimer') }}</button></p>
<div id="fac">
    <div class="entete">
        <strong style="font-size:18px">{{ $marqueNom }}</strong>
        <h4><span style="color:#000">{{ $editeur['raison_sociale'] }}</span><br>{!! nl2br(e($editeur['adresse'])) !!}</h4>
        <p>{{ $facture->brand === 'df' ? 'www.dustfolio.com' : 'www.ultra-book.com' }}<br>{{ __('Application de création de book en ligne') }}</p>
        <p>
            @foreach ([$client->company, $client->lastname, $client->firstname, $client->address, trim($client->zipcode.' '.$client->city), $client->country] as $ligne)
                @if ($ligne) {{ $ligne }}<br> @endif
            @endforeach
            <br>{{ $client->email }}
        </p>
    </div>
    <div class="num">
        <h2>{{ __('Facture') }} n° {{ $facture->numero() }}</h2>
        {{ __('Date') }} : {{ $facture->issued_at?->format('Y-m-d') }}
    </div>
    <div class="des">
        <div><strong>{{ $facture->label }}</strong></div>
        <div>
            <strong>{{ str_replace($marqueNom, $marqueNom.' - SaaS', (string) $facture->designation) }}</strong><br><br>
            {{ __('Mise à disposition du logiciel SaaS :marque', ['marque' => $marqueNom]) }}<br>
            {{ __('Assistance par e-mail et maintenance') }}
        </div>
    </div>
    <div class="tot">
        <div><span>{{ __('Somme intermédiaire') }}</span><span>{{ number_format($ht, 2, ',', ' ') }} € HT</span></div>
        <div><span>TVA {{ $taux }} %</span><span>{{ number_format((float) $facture->vat, 2, ',', ' ') }} €</span></div>
        <div><span>{{ __('Montant total payé') }}</span><span><strong>{{ number_format((float) $facture->amount, 2, ',', ' ') }}</strong> € TTC</span></div>
    </div>
    <div class="conditions">
        {{ __('Date d’échéance') }} : <b>{{ $facture->issued_at?->format('Y-m-d') }}</b><br>
        Passée la date d'échéance ci-dessus, une pénalité de retard de 3 fois le taux légal sera appliquée (Loi 2008-776 du 4 août 2008) ainsi qu'une indemnité forfaitaire pour frais de recouvrement de 40 euros (Décret 2012-1115 du 2 octobre 2012).
        <br><br><br>
        <strong>{{ $editeur['raison_sociale'] }}</strong> : {{ $editeur['mentions'] }}<br>TVA intra-communautaire {{ $editeur['tva_intra'] }}
    </div>
</div>
</body>
</html>
