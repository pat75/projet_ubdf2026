{{--
 | Mail d'inscription (BienvenueCreatif) : rappel du book, de l'identifiant
 | et lien de confirmation de l'adresse. Meme gabarit que la relance de
 | formule (mail.relance-formule) : tableaux et styles en ligne, seuls
 | compris par tous les clients mail.
--}}
@php
    $site = rtrim($marque->canonique, '/');
    $domaineSite = preg_replace('#^https?://(www\.)?#', '', $site);
    $book = $compte->bookUrl();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
<img src="{{ asset('img_admin/ultra-book_logo_nb.gif') }}" width="113" height="40" alt="{{ $marque->nom }}" style="display:block;border:0;margin:0 auto;">
@endif
<p style="margin:12px 0 0;font-size:20px;color:#1a1716;">{{ __('Une mine de créatifs') }}</p>
</td></tr>

<tr><td style="padding:40px 40px 0;">
<h1 style="margin:0 0 28px;font-size:28px;line-height:34px;color:#1a1716;">{{ __('Bienvenue') }}<br>{{ __('Votre portfolio est ouvert') }}.</h1>
<p style="margin:0 0 16px;font-size:16px;line-height:25px;color:#3a3633;">{{ __('Bonjour,') }}</p>
<p style="margin:0 0 28px;font-size:16px;line-height:25px;color:#3a3633;">{{ __('Votre portfolio est ouvert. Voici de quoi le retrouver.') }}</p>
</td></tr>

<tr><td style="padding:0 40px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#c9c9da" style="border:1px solid #b5b5cd;border-radius:6px;"><tbody><tr><td style="padding:24px 26px;">
<p style="margin:0 0 6px;font-size:12px;letter-spacing:1.5px;color:#3d3b5c;">{{ mb_strtoupper(__('Votre book')) }}</p>
<p style="margin:0 0 18px;font-size:20px;font-weight:bold;"><a href="{{ $book }}" style="color:#1a1716;text-decoration:none;">{{ preg_replace('#^https?://#', '', $book) }}</a></p>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tbody><tr><td style="border-radius:4px;border:1px solid #1a1716;"><a href="{{ $book }}" style="display:block;padding:11px 22px;font-size:15px;font-weight:bold;color:#1a1716;text-decoration:none;">{{ __('Voir mon book') }} →</a></td></tr></tbody></table>
</td></tr></tbody></table></td></tr>

<tr><td style="padding:36px 40px 0;">
<h2 style="margin:0 0 14px;font-size:20px;line-height:26px;color:#1a1716;">{{ __('Confirmez votre adresse') }}</h2>
<p style="margin:0 0 20px;font-size:16px;line-height:25px;color:#3a3633;">{{ __('Confirmez votre adresse pour que nous puissions vous joindre — notamment quand un visiteur vous écrit.') }}</p>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tbody><tr><td bgcolor="#1a1716" style="border-radius:4px;"><a href="{{ $lienConfirmation }}" style="display:block;padding:13px 26px;font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;">{{ __('Confirmer mon adresse') }} →</a></td></tr></tbody></table>
</td></tr>

<tr><td style="padding:32px 40px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#bcbcd1" style="border-radius:8px;"><tbody><tr><td style="padding:24px;">
<p style="margin:0 0 14px;font-size:12px;letter-spacing:1.5px;font-weight:bold;color:#1a1716;">{{ mb_strtoupper(__('Pour vous connecter')) }}</p>
<p style="margin:0 0 18px;font-size:15px;line-height:23px;color:#1a1716;">{{ __('Rendez-vous sur') }}<br><a href="{{ $site }}" style="color:#1a1716;font-weight:bold;text-decoration:none;">{{ $domaineSite }}</a></p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="border-radius:6px;"><tbody><tr><td style="padding:16px 20px;font-size:14px;line-height:23px;color:#3a3633;">
{{ __('Identifiant :') }} <strong style="color:#1a1716;">{{ $compte->login }}</strong>
</td></tr></tbody></table>
</td></tr></tbody></table></td></tr>

<tr><td style="padding:32px 40px 40px;font-size:15px;line-height:24px;color:#3a3633;">
<p style="margin:0 0 20px;">{{ __('Pour toute information complémentaire, vous pouvez nous écrire à') }} <a href="mailto:{{ $marque->email }}" style="color:#1a1716;font-weight:bold;">{{ $marque->email }}</a>.</p>
<p style="margin:0;">{{ __('Bonne journée,') }}<br><strong style="color:#1a1716;">{{ __('L’équipe :marque', ['marque' => $marque->nom]) }}</strong></p>
</td></tr>

</tbody></table>
<p style="margin:20px 0 0;font-family:Helvetica,Arial,sans-serif;font-size:12px;line-height:18px;color:#8a847e;">{{ __('Cet email confirme l’ouverture de votre compte :marque.', ['marque' => $marque->nom]) }}</p>
</td></tr></tbody></table>
</div>
</body>
</html>
