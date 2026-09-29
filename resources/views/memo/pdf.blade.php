<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $titre }}</title>
    {{-- Meme presentation que la page web (memo/partials/grille) : annees,
         mois, cartes a couverture pleine largeur et vignette ronde a
         cheval. Deux colonnes, cartes de hauteur fixe. --}}
    <style>
        @page { margin: 12mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #2b2b2b; }
        h1 { font-size: 20pt; font-weight: normal; margin: 0 0 1mm; }
        .date { color: #777; font-size: 9pt; margin-bottom: 4mm; }
        .auteur { border-collapse: collapse; margin: 0 0 7mm; }
        .auteur td { padding: 0 3mm 0 0; vertical-align: middle; }
        .auteur img { width: 12mm; height: 12mm; }
        .auteur .initiales { width: 12mm; height: 12mm; border-radius: 6mm; }
        .auteur .initiales td { height: 12mm; padding: 0; font-size: 9pt; }
        .auteur .par { font-size: 8pt; color: #777; }
        .auteur .qui { font-size: 11pt; }
        .auteur .qui b { color: #222; }
        .auteur .qui span { color: #999; font-weight: normal; }
        .annee { font-size: 16pt; font-weight: normal; border-bottom: 1px solid #d9d9d6; padding-bottom: 1.5mm; margin: 5mm 0 0; }
        .mois { font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: .6pt; color: #777; margin: 4mm 0 2.5mm; }
        .mois span { font-weight: normal; text-transform: none; letter-spacing: 0; color: #999; }
        table.grille { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.grille td { width: 50%; vertical-align: top; padding: 0 2.5mm 5mm 0; }
        table.grille td + td { padding: 0 0 5mm 2.5mm; }
        .carte { background: #fff; position: relative; height: {{ $codesQr ? 98 : 82 }}mm; overflow: hidden; text-align: center; }
        /* Filet de la carte : un calque pose apres le contenu, donc dessine
           par-dessus la couverture (une bordure de .carte passerait dessous). */
        .filet { position: absolute; top: 0; left: 0; right: 0; bottom: 0; border: 1px solid #898988; }
        .couverture { display: block; width: 100%; height: 46mm; background: #e0e0de; }
        .avatar { width: 15mm; height: 15mm; margin: -7.5mm auto 0; }
        .avatar img { width: 15mm; height: 15mm; }
        .initiales { width: 15mm; height: 15mm; border-radius: 7.5mm; border-collapse: collapse; }
        .initiales td { height: 15mm; padding: 0; text-align: center; vertical-align: middle; color: #fff; font-weight: bold; font-size: 10pt; }
        .sans-avatar { height: 4mm; }
        .nom { font-size: 11.5pt; font-weight: bold; margin-top: 2.5mm; color: #222; }
        .metier { color: #999; font-size: 7.5pt; text-transform: uppercase; letter-spacing: .5pt; margin-top: 1mm; }
        .qr { width: 17mm; height: 17mm; margin-top: 2.5mm; }
        .url { display: block; margin: 2mm 4mm 0; font-size: 7.5pt; color: #333333; text-decoration: none; word-break: break-all; }
    </style>
</head>
<body>
    @if ($auteur)
        <table class="auteur"><tr>
            <td>
                @if ($auteur['avatar'])
                    <img src="{{ $auteur['avatar'] }}" alt="">
                @else
                    <table class="initiales" style="background: {{ $auteur['couleur'] }}"><tr><td>{{ $auteur['initiales'] }}</td></tr></table>
                @endif
            </td>
            <td>
                <div class="par">{{ __('Partagé par') }}</div>
                <div class="qui"><b>{{ $auteur['nom'] }}</b> <span>{{ $auteur['metier'] }}</span></div>
            </td>
        </tr></table>
    @endif
    <h1>{{ $titre }}</h1>
    <div class="date">{{ trans_choice(':n book|:n books', $fiches->count(), ['n' => $fiches->count()]) }} — {{ now()->translatedFormat('j F Y') }} — {{ $marque->nom ?? config('app.name') }}</div>

    @foreach ($fiches->groupBy(fn ($f) => $f['memorise_le']->format('Y')) as $annee => $ficheAnnee)
        <div class="annee">{{ $annee }}</div>

        @foreach ($ficheAnnee->groupBy(fn ($f) => $f['memorise_le']->format('m')) as $mois => $ficheMois)
            <div class="mois">
                {{ \Illuminate\Support\Carbon::create((int) $annee, (int) $mois, 1)->translatedFormat('F') }}
                <span>· {{ trans_choice(':n book|:n books', $ficheMois->count(), ['n' => $ficheMois->count()]) }}</span>
            </div>

            <table class="grille">
                @foreach ($ficheMois->chunk(2) as $ligne)
                    <tr>
                        @foreach ($ligne as $fiche)
                            <td>
                                <div class="carte">
                                    @if ($fiche['image'])
                                        <img class="couverture" src="{{ $fiche['image'] }}" alt="">
                                    @else
                                        <div class="couverture"></div>
                                    @endif

                                    <div class="avatar">
                                        @if ($fiche['avatar'])
                                            <img src="{{ $fiche['avatar'] }}" alt="">
                                        @else
                                            <table class="initiales" style="background: {{ $fiche['couleur'] }}"><tr><td>{{ $fiche['initiales'] }}</td></tr></table>
                                        @endif
                                    </div>

                                    <div class="nom">{{ $fiche['nom'] }}</div>
                                    <div class="metier">{{ $fiche['metier'] }}</div>

                                    @if ($fiche['qr'])
                                        <img class="qr" src="{{ $fiche['qr'] }}" alt="">
                                    @endif
                                    <a class="url" href="{{ $fiche['url'] }}">{{ $fiche['url'] }}</a>
                                    <div class="filet"></div>
                                </div>
                            </td>
                        @endforeach
                        @if ($ligne->count() < 2)<td></td>@endif
                    </tr>
                @endforeach
            </table>
        @endforeach
    @endforeach
</body>
</html>
