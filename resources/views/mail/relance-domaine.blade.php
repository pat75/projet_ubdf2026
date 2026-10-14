{{--
 | Relance avant l'echeance d'un nom de domaine (RelanceDomaine) : J-30,
 | J-15, J-5. Tableaux et styles en ligne, comme relance-formule.
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $marque->nom }}</title>
</head>
<body style="margin:0;background:#f3f2f0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3f2f0"><tbody><tr><td align="center" style="padding:32px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="width:100%;max-width:600px;border:1px solid #e6e4e0;font-family:Helvetica,Arial,sans-serif;"><tbody>

<tr><td align="center" style="padding:32px 24px 26px;border-bottom:1px solid #eeece8;">
@if ($marque->code === 'df')
<img src="{{ asset('img_front_df/dustfolio_logo.png') }}" width="144" height="47" alt="{{ $marque->nom }}" style="display:block;border:0;margin:0 auto;">
@else
<img src="{{ asset('img_admin/ultra-book_logo_nb.gif') }}" width="113" height="40" alt="{{ $marque->nom }}" style="display:block;border:0;margin:0 auto;">
@endif
</td></tr>

<tr><td style="padding:40px 40px 0;">
<h1 style="margin:0 0 28px;font-size:28px;line-height:34px;color:#1a1716;">{{ __('Votre nom de domaine') }}<br>{{ __('expire dans :n jours.', ['n' => $joursRestants]) }}</h1>
<p style="margin:0 0 16px;font-size:16px;line-height:25px;color:#3a3633;">{{ __('Bonjour,') }}</p>
<p style="margin:0 0 16px;font-size:16px;line-height:25px;color:#3a3633;">{!! __('Votre nom de domaine :nom arrive à échéance le :date.', ['nom' => '<strong style="color:#1a1716;">'.e($domaine->nom).'</strong>', 'date' => e($echeance)]) !!}</p>
<p style="margin:0 0 28px;font-size:16px;line-height:25px;color:#3a3633;">{{ __('Pensez à le renouveler.') }}<br>{{ __('Sans renouvellement, il ne pointera plus vers votre book et vous risquez de le perdre : il redeviendra libre pour tout le monde.') }}</p>
</td></tr>

<tr><td style="padding:0 40px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tbody><tr><td bgcolor="#1a1716" style="border-radius:4px;"><a href="{{ $lienDomaine }}" style="display:block;padding:13px 24px;font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;">{{ __('Gérer mon nom de domaine') }} →</a></td></tr></tbody></table>
</td></tr>

<tr><td style="padding:32px 40px 40px;font-size:15px;line-height:24px;color:#3a3633;">
<p style="margin:0 0 20px;">{{ __('Pour toute question, écrivez-nous à') }} <a href="mailto:{{ $marque->email }}" style="color:#1a1716;font-weight:bold;">{{ $marque->email }}</a>.</p>
<p style="margin:0;">{{ __('Bonne journée,') }}<br><strong style="color:#1a1716;">{{ __('L’équipe :marque', ['marque' => $marque->nom]) }}</strong></p>
</td></tr>

</tbody></table>
</td></tr></tbody></table>
</body>
</html>
