<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Mon mémo book') }}</title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1b1b1b; }
        h1 { font-size: 20pt; font-weight: normal; margin: 0 0 4mm; }
        .date { color: #777; font-size: 9pt; margin-bottom: 8mm; }
        table { width: 100%; border-collapse: collapse; }
        td { width: 33%; vertical-align: top; padding: 0 3mm 7mm; }
        .image { width: 100%; height: 38mm; background: #eee; overflow: hidden; }
        .image img { width: 100%; }
        .nom { font-size: 11pt; font-weight: bold; margin-top: 2.5mm; }
        .metier { color: #777; font-size: 8.5pt; text-transform: uppercase; }
        .url { font-size: 8.5pt; color: #1d5fb8; word-break: break-all; }
    </style>
</head>
<body>
    <h1>{{ __('Mon mémo book') }}</h1>
    <div class="date">{{ trans_choice(':n book|:n books', $fiches->count(), ['n' => $fiches->count()]) }} — {{ now()->translatedFormat('j F Y') }} — {{ $marque->nom ?? config('app.name') }}</div>

    <table>
        @foreach ($fiches->chunk(3) as $ligne)
            <tr>
                @foreach ($ligne as $fiche)
                    <td>
                        <div class="image">@if ($fiche['image'])<img src="{{ $fiche['image'] }}" alt="">@endif</div>
                        <div class="nom">{{ $fiche['nom'] }}</div>
                        <div class="metier">{{ $fiche['metier'] }}</div>
                        <a class="url" href="{{ $fiche['url'] }}">{{ $fiche['url'] }}</a>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</body>
</html>
