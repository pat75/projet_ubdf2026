{{-- Contenu redige au back-office : deja nettoye a l'enregistrement. --}}
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $campagne->subject }}</title>
</head>
<body style="margin:0;background:#f4f4f5;font-family:Arial,sans-serif;color:#18181b;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;">
                <tr>
                    <td style="padding:28px 32px;font-size:15px;line-height:1.6;">
                        {!! $corps !!}
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 32px 24px;font-size:12px;color:#71717a;">
                        <a href="{{ $lienDesabonnement }}" style="color:#71717a;">{{ __('Ne plus recevoir ces messages') }}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
