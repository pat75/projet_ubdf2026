{{-- Sommaire lisible de l'archive « Mes donnees » (App\Services\Espace\ExportCompte).
     Page autonome : ouverte hors ligne depuis le ZIP, sans aucune ressource
     externe. Les liens pointent vers les fichiers de l'archive. --}}
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $creatif->fullName() }} — mes données</title>
    <style>
        body { margin: 0; background: #f6f6f4; color: #1d1d1b; font: 15px/1.55 system-ui, -apple-system, sans-serif; }
        main { max-width: 960px; margin: 0 auto; padding: 40px 20px 80px; }
        h1 { font-weight: 300; font-size: 32px; margin: 0 0 4px; }
        h2 { font-weight: 400; font-size: 21px; margin: 48px 0 14px; padding-bottom: 8px; border-bottom: 1px solid #ddd; }
        h3 { font-weight: 600; font-size: 16px; margin: 26px 0 10px; }
        .sous { color: #6b6b66; font-size: 13px; }
        .carte { background: #fff; border-radius: 6px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,.07); }
        dl { display: grid; grid-template-columns: 200px 1fr; gap: 6px 16px; margin: 0; }
        dt { color: #6b6b66; }
        dd { margin: 0; word-break: break-word; }
        .grille { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
        .grille a { display: block; aspect-ratio: 1; background: #eee; border-radius: 4px; overflow: hidden; }
        .grille img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .profil { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; float: right; }
        .message { margin: 8px 0; padding: 10px 14px; background: #efefed; white-space: pre-wrap; }
        .message.moi { background: #e3edf7; margin-left: 40px; }
        .fichiers li { margin: 3px 0; }
        a { color: #1f5f99; }
    </style>
</head>
<body>
<main>
    @if ($profil['visuel_de_profil'])
        <img class="profil" src="{{ $profil['visuel_de_profil'] }}" alt="">
    @endif
    <h1>{{ $creatif->fullName() }}</h1>
    <div class="sous">{{ '@'.$creatif->login }} · {{ __('Données exportées le :date', ['date' => now()->format('d/m/Y à H:i')]) }}</div>

    <h2>{{ __('Contenu de l’archive') }}</h2>
    <ul class="fichiers">
        <li><a href="compte.json">compte.json</a> — {{ __('votre compte, votre métier, les réglages de votre book, votre facturation') }}</li>
        <li><a href="portfolios.json">portfolios.json</a> — {{ __('vos galeries et vos visuels') }}</li>
        <li><a href="pages.json">pages.json</a> — {{ __('vos rubriques, pages et actualités') }}</li>
        <li><a href="messages.json">messages.json</a> — {{ __('vos demandes et échanges') }}</li>
        <li><a href="factures.json">factures.json</a> — {{ __('vos factures') }}</li>
        <li>images/, profil/, pages/ — {{ __('vos images en haute définition') }}</li>
    </ul>

    <h2>{{ __('Compte') }}</h2>
    <div class="carte">
        <dl>
            @foreach (['E-mail' => $creatif->email, 'Prénom' => $creatif->firstname, 'Nom' => $creatif->lastname,
                       'Société' => $creatif->company, 'Téléphone' => $creatif->phone ?: $creatif->mobile,
                       'Adresse' => trim($creatif->address.' '.$creatif->zipcode.' '.$creatif->city),
                       'Site web' => $creatif->website, 'Book' => $creatif->bookUrl(),
                       'Inscrit le' => $creatif->created_at?->format('d/m/Y')] as $libelle => $valeur)
                @if (filled($valeur))
                    <dt>{{ __($libelle) }}</dt><dd>{{ $valeur }}</dd>
                @endif
            @endforeach
        </dl>
    </div>

    <h2>{{ __('Portfolios') }}</h2>
    @forelse ($portfolios as $portfolio)
        <h3>{{ $portfolio['name'] }} <span class="sous">· {{ trans_choice(':n visuel|:n visuels', count($portfolio['visuels']), ['n' => count($portfolio['visuels'])]) }}</span></h3>
        <div class="grille">
            @foreach ($portfolio['visuels'] as $visuel)
                @if ($visuel['fichier'])
                    <a href="{{ $visuel['fichier'] }}" title="{{ $visuel['title'] ?? '' }}"><img src="{{ $visuel['fichier'] }}" alt="{{ $visuel['alt'] ?? '' }}" loading="lazy"></a>
                @endif
            @endforeach
        </div>
    @empty
        <p class="sous">{{ __('Aucun portfolio.') }}</p>
    @endforelse

    <h2>{{ __('Pages') }}</h2>
    <p>{{ trans_choice(':n rubrique|:n rubriques', count($pages['rubriques']), ['n' => count($pages['rubriques'])]) }},
       {{ trans_choice(':n page ou actualité|:n pages ou actualités', count($pages['pages_et_actualites']), ['n' => count($pages['pages_et_actualites'])]) }}
       — {{ __('détail dans') }} <a href="pages.json">pages.json</a>.</p>

    <h2>{{ __('Messages') }}</h2>
    @forelse ($messages as $conversation)
        <h3>{{ $conversation['subject'] ?: __('Demande') }} <span class="sous">· {{ $conversation['sender_name'] ?? '' }} {{ $conversation['sender_email'] ?? '' }}</span></h3>
        @foreach ($conversation['messages'] as $message)
            <div class="message {{ $message['de'] === 'moi' ? 'moi' : '' }}">{{ $message['texte'] }}</div>
            <div class="sous">{{ $message['de'] }} · {{ $message['date'] ? \Illuminate\Support\Carbon::parse($message['date'])->format('d/m/Y H:i') : '' }}</div>
        @endforeach
    @empty
        <p class="sous">{{ __('Aucun message.') }}</p>
    @endforelse

    <h2>{{ __('Factures') }}</h2>
    @forelse ($factures as $facture)
        <div>{{ $facture['number'] ?? '' }} — {{ $facture['label'] ?? $facture['designation'] ?? '' }} — {{ $facture['amount'] ?? '' }} {{ $facture['currency'] ?? '' }}</div>
    @empty
        <p class="sous">{{ __('Aucune facture.') }}</p>
    @endforelse
</main>
</body>
</html>
