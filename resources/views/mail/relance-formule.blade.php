{{--
 | Relance d'abonnement (RelanceFormule) : J-5, jour J, ou deja echue
 | (relance manuelle du back-office). Mise en page en tableaux et styles en
 | ligne, seuls compris par tous les clients mail.
--}}
@php
    $echeance = $creatif->echeanceFormule()?->translatedFormat('j F Y');
    $site = rtrim($marque->canonique, '/');
    $domaineSite = preg_replace('#^https?://(www\.)?#', '', $site);
    $book = $creatif->bookUrl();
    $reabo = config('formules.options.3');
    $prix = fn (float $v) => number_format($v, 2, ',', ' ').' €';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $marque->nom }}</title>
<style>body{margin:0;background:#f3f2f0;} a{color:#1a1716;} a:hover{color:#e36a3c;}</style>
</head>
<body style="margin:0;background:#f3f2f0;">
<div style="background:#f3f2f0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3f2f0"><tbody><tr><td align="center" style="padding:32px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="width:100%;max-width:600px;border:1px solid #e6e4e0;font-family:Helvetica,Arial,sans-serif;"><tbody>

<tr><td align="center" style="padding:32px 24px 26px;border-bottom:1px solid #eeece8;">
@if ($marque->code === 'df')
<img src="{{ asset('img_front_df/dustfolio_logo.png') }}" width="144" height="47" alt="{{ $marque->nom }}" style="display:block;border:0;margin:0 auto;">
@else
{{-- Logo d'origine (280 x 99), affiche a 113 px : 20 % de moins que les 140 px des autres mails. --}}
<img src="{{ asset('img_admin/ultra-book_logo_nb.gif') }}" width="113" height="40" alt="{{ $marque->nom }}" style="display:block;border:0;margin:0 auto;">
@endif
<p style="margin:12px 0 0;font-size:20px;color:#1a1716;">une mine de <strong>créatifs</strong></p>
</td></tr>

<tr><td style="padding:40px 40px 0;">
<h1 style="margin:0 0 28px;font-size:28px;line-height:34px;color:#1a1716;">
@if ($joursRestants < 0)
Votre formule {{ $marque->nom }}<br>est arrivée à échéance.
@elseif ($joursRestants === 0)
Votre formule {{ $marque->nom }}<br>arrive à échéance aujourd’hui.
@else
Votre formule {{ $marque->nom }}<br>arrive à échéance dans {{ $joursRestants }} jours.
@endif
</h1>
<p style="margin:0 0 16px;font-size:16px;line-height:25px;color:#3a3633;">Bonjour,</p>
<p style="margin:0 0 28px;font-size:16px;line-height:25px;color:#3a3633;">
@if ($joursRestants < 0)
Nous vous informons que la formule associée à votre book est arrivée à échéance{{ $echeance ? ' le '.$echeance : '' }}.
@elseif ($joursRestants === 0)
Nous vous informons que la formule associée à votre book arrive à échéance aujourd’hui.
@else
Nous vous informons que la formule associée à votre book arrive à échéance le {{ $echeance }}.
@endif
</p>
</td></tr>

<tr><td style="padding:0 40px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#c9c9da" style="border:1px solid #b5b5cd;border-radius:6px;"><tbody><tr><td style="padding:24px 26px;">
<p style="margin:0 0 6px;font-size:12px;letter-spacing:1.5px;color:#3d3b5c;">VOTRE BOOK</p>
<p style="margin:0 0 18px;font-size:20px;font-weight:bold;"><a href="{{ $book }}" style="color:#1a1716;text-decoration:none;">{{ preg_replace('#^https?://#', '', $book) }}</a></p>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tbody><tr><td style="border-radius:4px;border:1px solid #1a1716;"><a href="{{ $book }}" style="display:block;padding:11px 22px;font-size:15px;font-weight:bold;color:#1a1716;text-decoration:none;">Voir mon book →</a></td></tr></tbody></table>
</td></tr></tbody></table></td></tr>

<tr><td style="padding:36px 40px 0;">
<h2 style="margin:0 0 14px;font-size:20px;line-height:26px;color:#1a1716;">Pourquoi renouveler votre formule ?</h2>
<p style="margin:0 0 20px;font-size:16px;line-height:25px;color:#3a3633;">Avec la formule premium, votre portfolio gagne en visibilité auprès des professionnels et des maisons d’édition qui consultent {{ $marque->nom }}.<br>Vous gardez un outil simple pour présenter et diffuser votre travail. Renouvelez votre formule dès maintenant pour obtenir la meilleure visibilité et rester en contact avec les commanditaires.</p>
@if ($reabo)
{{-- Tarif reserve au renouvellement (option 3 de config/formules.php). --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#1a1716" style="border-radius:6px;"><tbody><tr><td style="padding:20px 24px;">
<p style="margin:0 0 6px;font-size:12px;letter-spacing:1.5px;font-weight:bold;color:#e36a3c;">TARIF RÉSERVÉ AU RENOUVELLEMENT</p>
<p style="margin:0 0 6px;font-size:16px;line-height:24px;color:#ffffff;">Formule {{ $reabo['mois'] }} mois :
@if (! empty($reabo['barre']))
<span style="text-decoration:line-through;color:#a8a29c;padding-right:18px;">{{ $prix($reabo['barre']) }}</span>
@endif
<strong style="font-size:22px;color:#ffffff;">{{ $prix($reabo['ttc']) }} TTC</strong></p>
<p style="margin:0;font-size:14px;line-height:21px;color:#cfcfcf;">Uniquement pour le renouvellement d’une formule.</p>
</td></tr></tbody></table>
@endif
</td></tr>

<tr><td style="padding:32px 40px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#bcbcd1" style="border-radius:8px;"><tbody><tr><td style="padding:24px;">
<p style="margin:0 0 14px;font-size:12px;letter-spacing:1.5px;font-weight:bold;color:#1a1716;">COMMENT RENOUVELER</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tbody>
<tr><td width="32" valign="top" style="font-size:15px;line-height:23px;font-weight:bold;color:#3d3b5c;padding:0 0 10px;">1</td><td style="font-size:15px;line-height:23px;color:#1a1716;padding:0 0 10px;">Connectez-vous sur<br><a href="{{ $site }}" style="color:#1a1716;font-weight:bold;text-decoration:none;">{{ $domaineSite }}</a></td></tr>
<tr><td width="32" valign="top" style="font-size:15px;line-height:23px;font-weight:bold;color:#3d3b5c;padding:0 0 10px;">2</td><td style="font-size:15px;line-height:23px;color:#1a1716;padding:0 0 10px;">Puis suivez le chemin :<br><a href="{{ $lienFormule }}" style="color:#1a1716;font-weight:bold;text-decoration:none;">Menu &gt; Ma formule &gt; Prendre une formule</a></td></tr>
</tbody></table>
<p style="margin:4px 0 18px;font-size:14px;line-height:21px;color:#1f1d33;">Les durées de 6 ou 12 mois sont cumulables avec la durée restante.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="border-radius:6px;"><tbody><tr><td style="padding:16px 20px;font-size:14px;line-height:23px;color:#3a3633;">
Votre identifiant de connexion : <strong style="color:#1a1716;">{{ $creatif->login }}</strong><br>
Votre mot de passe : <a href="{{ $site }}" style="color:#1a1716;">mot de passe oublié</a>
</td></tr></tbody></table>
</td></tr></tbody></table></td></tr>

<tr><td style="padding:28px 40px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-left:3px solid #e36a3c;"><tbody><tr><td style="padding:2px 0 2px 18px;">
<p style="margin:0 0 8px;font-size:16px;font-weight:bold;color:#1a1716;">Important</p>
<p style="margin:0 0 6px;font-size:15px;line-height:23px;color:#3a3633;">{{ $joursRestants < 0 ? 'En attendant, votre compte repasse' : 'Sans renouvellement, votre compte repassera' }} en formule gratuite.</p>
<p style="margin:0 0 6px;font-size:15px;line-height:23px;color:#3a3633;">Aucune image ne sera supprimée ou modifiée.</p>
<p style="margin:0;font-size:15px;line-height:23px;color:#3a3633;">La visibilité publique de votre portfolio est simplement limitée aux <strong style="color:#1a1716;">24 premières images</strong>.</p>
</td></tr></tbody></table></td></tr>

<tr><td style="padding:32px 40px 40px;font-size:15px;line-height:24px;color:#3a3633;">
<p style="margin:0 0 20px;">Pour toute information complémentaire, vous pouvez nous écrire à <a href="mailto:{{ $marque->email }}" style="color:#1a1716;font-weight:bold;">{{ $marque->email }}</a>.</p>
<p style="margin:0;">Bonne journée,<br><strong style="color:#1a1716;">L’équipe {{ $marque->nom }}</strong></p>
</td></tr>

</tbody></table>
<p style="margin:20px 0 0;font-family:Helvetica,Arial,sans-serif;font-size:12px;line-height:18px;color:#8a847e;">Cet email concerne votre compte {{ $marque->nom }} et l’état actuel de votre formule.</p>
</td></tr></tbody></table>
</div>
</body>
</html>
