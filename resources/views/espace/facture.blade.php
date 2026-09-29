{{-- Reprise de ub_content_facture__ub/df.tlp.php : une page A4 a imprimer. --}}
@php
    $ht = round((float) $facture->amount - (float) $facture->vat, 2);
    $taux = $facture->issued_at && $facture->issued_at->year < 2014 ? '19,6' : '20';
    $marqueNom = $facture->brand === 'df' ? 'Dustfolio' : 'Ultra-book';
    // Logo en data URI : dompdf n'a pas acces aux URL du site.
    $logo = $facture->brand === 'df' ? null
        : 'data:image/svg+xml;base64,'.base64_encode((string) file_get_contents(public_path('img_front/ultra-book_logo_nb.svg')));
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $marqueNom }} | {{ __('Facture') }} n° {{ $facture->numero() }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; }
        #fac { position: relative; margin: 10px; padding: 15px 40px 20px; border: 1px solid #999; border-radius: 8px; width: 550px; min-height: 850px; }
        /* Tableau et non flexbox : dompdf ne connait pas flexbox. La
           derniere ligne de l'expediteur passe dans la 2e rangee : le
           destinataire commence a sa hauteur. */
        .entete { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .entete td { padding: 0; vertical-align: top; line-height: 1.35; }
        .entete p { margin: 0; }
        .expediteur { width: 300px; }
        .marque-de { font-size: 9.2px; }
        .espace { display: block; height: 6px; }
        /* Bloc cale contre la marge droite, texte aligne a gauche dedans. */
        .destinataire { padding-top: 15px; text-align: right; }
        .destinataire p { display: inline-block; text-align: left; }
        .entete img { display: block; margin-bottom: 20px; }
        .entete h4 { color: #777; font-size: 11px; font-weight: normal; }
        .num { margin: 60px 0 20px; }
        .num h2 { font-size: 13px; margin-bottom: 4px; }
        .des div { border-bottom: 1px solid #999; padding: 10px; }
        .tot { width: 100%; border-collapse: collapse; }
        .tot td { border-bottom: 1px solid #999; padding: 10px; }
        .tot td + td { text-align: right; }
        .conditions { margin: 60px 0 20px; font-size: 8px; }
        .pied { position: absolute; left: 40px; right: 40px; bottom: 20px; font-size: 8px; text-align: center; color: #555; }
        @media print { #fac { border: 0; } .imprimer { display: none; } }
        /* PDF : pas de cadre (c'est un habillage d'ecran), marges portees
           par la page A4, pied colle au bas de la page. */
        @page { margin: 15mm 18mm; }
        .pdf #fac { margin: 0; padding: 0; border: 0; width: auto; min-height: 0; position: static; }
        .pdf .num h2 { font-size: 17px; }
        .pdf .pied { position: fixed; left: 0; right: 0; bottom: 0; }
    </style>
</head>
<body class="{{ ($pdf ?? false) ? 'pdf' : '' }}">
@unless ($pdf ?? false)
    <p class="imprimer">
        <button type="button" onclick="window.print()">{{ __('Imprimer') }}</button>
        <a href="{{ route('espace.facture.pdf', $facture) }}">{{ __('Télécharger en PDF') }}</a>
    </p>
@endunless
<div id="fac">
    <table class="entete"><tr>
        <td class="expediteur">
        @if ($logo)
            <img src="{{ $logo }}" alt="{{ $marqueNom }}" style="width:111px">
        @else
            <strong style="font-size:18px">{{ $marqueNom }}</strong>
        @endif
        <p>
            <strong>{{ $marqueNom }} / {{ $editeur['raison_sociale'] }}</strong><br>
            <span class="marque-de">{{ __(':marque est une marque de la société :societe SAS', ['marque' => $marqueNom, 'societe' => $editeur['raison_sociale']]) }}</span><br>
            <span class="espace"></span>
            {!! nl2br(e($editeur['adresse'])) !!}<br>
            <span class="espace"></span>
            {{ $facture->brand === 'df' ? 'Dustfolio.com' : 'Ultra-book.com' }}
        </p>
        </td>
        <td></td>
    </tr><tr>
        <td>{{ __('Application de création de book en ligne') }}</td>
        @php($entreprise = $client->billingProfile)

        <td class="destinataire"><p>
            {{-- Le createur qui facture en professionnel est identifie par
                 sa raison sociale et son SIRET : c'est ce que reclame la
                 facturation electronique. Les autres gardent la
                 presentation d'avant. --}}
            @if ($entreprise)
                <strong>{{ $entreprise->company_name }}</strong><br>
                {{ $entreprise->address }}<br>
                {{ trim($entreprise->postcode.' '.$entreprise->city) }}<br>
                <span class="espace"></span>
                {{ __('SIRET') }} : {{ $entreprise->siretLisible() }}<br>
                @if ($entreprise->vat_number)
                    {{ __('TVA') }} : {{ $entreprise->vat_number }}<br>
                @endif
            @else
                @foreach ([$client->company, $client->lastname, $client->firstname, $client->address, trim($client->zipcode.' '.$client->city), $client->country] as $ligne)
                    @if ($ligne) {{ $ligne }}<br> @endif
                @endforeach
            @endif
            <br>{{ $client->email }}
        </p></td>
    </tr></table>
    <div class="num">
        <h2>{{ __('Facture') }} n° {{ $facture->numero() }}</h2>
        {{ __('Date') }} : {{ $facture->issued_at?->format('Y-m-d') }}
    </div>
    <div class="des">
        <div><strong>{{ $facture->label }}</strong></div>
        <div>
            <strong>{{ __('Formule :marque', ['marque' => $marqueNom]) }}</strong><br>
            {{ __('Nature de l’opération : prestation de services') }}
        </div>
    </div>
    <table class="tot">
        <tr><td>{{ __('Somme intermédiaire') }}</td><td>{{ number_format($ht, 2, ',', ' ') }} € HT</td></tr>
        <tr><td>TVA {{ $taux }} %</td><td>{{ number_format((float) $facture->vat, 2, ',', ' ') }} €</td></tr>
        <tr><td>{{ __('Montant total payé') }}</td><td><strong>{{ number_format((float) $facture->amount, 2, ',', ' ') }}</strong> € TTC</td></tr>
    </table>
    <div class="conditions">
        Passée la date d'échéance ci-dessous, une pénalité de retard égale à trois fois le taux d'intérêt légal sera exigible, ainsi qu'une indemnité forfaitaire pour frais de recouvrement de 40 € (article L441-10 du Code de commerce, article D441-5). Pas d'escompte pour paiement anticipé.<br><br>
        {{ __('Date d’échéance') }} : <b>{{ $facture->issued_at?->format('Y-m-d') }}</b>
    </div>
    <div class="pied">
        <img src="data:image/png;base64,{{ base64_encode((string) file_get_contents(public_path('img_front/thank-you.png'))) }}" alt="{{ __('Merci') }}" style="width:90px; margin-bottom:64px">
        <br>
        <strong>{{ $editeur['raison_sociale'] }} SAS</strong>, au capital de 1 000 €<br>
        {{ $editeur['mentions'] }} - TVA intra-communautaire {{ $editeur['tva_intra'] }}
    </div>
</div>
</body>
</html>
