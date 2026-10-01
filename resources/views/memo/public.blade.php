<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $auteur ? __('La sélection de :nom', ['nom' => $auteur]) : __('Une sélection de books') }} | {{ $marque->nom }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/img_front/favicon/favicon-32x32.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap">
    @vite(['resources/css/espace.css'])
</head>
{{-- MemoBook partage publiquement (MemoPublicController) : la liste des
     books seule, dans la charte de l'espace. --}}
<body class="min-h-screen bg-ub-fond font-courant text-ub-texte antialiased">

<header class="bg-white shadow-[0_7px_20px_rgba(0,0,0,0.2)]">
    <div class="mx-auto flex h-[85px] max-w-[1140px] items-center justify-between px-4 min-[900px]:px-5">
        <a href="{{ lien('home') }}" aria-label="{{ $marque->nom }}">
            <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" class="w-[100px] min-[900px]:w-[120px]">
        </a>
        <a href="{{ lien('home') }}" class="bouton-espace bouton-espace-petit px-4">{{ __('Découvrir :marque', ['marque' => $marque->nom]) }}</a>
    </div>
</header>

<main class="mx-auto max-w-[1140px] px-4 pb-18 pt-8 min-[900px]:px-5 min-[900px]:pt-11">
    <div class="mb-2 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('mémoBook partagé') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">
                {{ $auteur ? __('La sélection de :nom', ['nom' => $auteur]) : __('Une sélection de books') }}
            </h1>

            {{-- Qui partage : un createur, avec sa photo ou ses initiales
                 (comme dans l'espace). Un visiteur n'a ni nom ni avatar, et
                 son adresse mail n'est jamais montree. --}}
            @if ($createur)
                @php($profil = app(\App\Services\Espace\AffichageProfil::class))
                <a href="{{ $createur->bookUrl() }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-3 no-underline hover:opacity-80">
                    @if ($photo = $profil->photoUrl($createur))
                        <img src="{{ $photo }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover">
                    @else
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-[14px] font-bold text-white"
                              style="background: {{ $profil->couleur($createur) }}" aria-hidden="true">{{ $profil->initiales($createur) }}</span>
                    @endif
                    <span class="leading-tight">
                        <span class="block text-[13px] text-ub-texte3">{{ __('Partagé par') }}</span>
                        <span class="block text-[17px]">
                            <span class="font-semibold text-ub-texte">{{ $createur->fullName() }}</span>
                            @if ($createur->category)
                                <span class="ml-1 font-light text-ub-texte3">{{ __($createur->category->name) }}</span>
                            @endif
                        </span>
                    </span>
                </a>
            @endif
        </div>
        {{-- Export PDF et son option (code QR de chaque book), comme sur la
             page du proprietaire. Page sans Alpine : un <details> et trois
             lignes de script suffisent. --}}
        @if ($books->isNotEmpty())
            <div class="flex items-center gap-2">
                <a id="memo-pdf" href="{{ lien('memobook.public.pdf', ['jeton' => $partage->jeton]) }}" target="_blank" rel="noopener"
                   data-base="{{ lien('memobook.public.pdf', ['jeton' => $partage->jeton]) }}"
                   class="bouton-espace bouton-espace-grand px-5">{{ __('Exporter en PDF') }}</a>
                <details class="relative">
                    <summary class="bouton-espace-grand flex w-[43px] cursor-pointer list-none items-center justify-center border border-ub-bord bg-white px-0 text-ub-texte hover:bg-ub-fond [&::-webkit-details-marker]:hidden"
                             title="{{ __('Options du PDF') }}" aria-label="{{ __('Options du PDF') }}">
                        <x-espace.icone nom="reglage" class="h-4 w-4" />
                    </summary>
                    <div class="absolute right-0 top-full z-20 mt-2 w-72 border border-ub-bord bg-white p-4 shadow-ub">
                        <label class="flex cursor-pointer items-center justify-between gap-4">
                            <span class="text-[14px] text-ub-texte">{{ __('Code QR de chaque book') }}</span>
                            <input id="memo-pdf-qr" type="checkbox" class="peer sr-only">
                            <span class="flex h-6.5 w-[46px] shrink-0 justify-start rounded-[13px] bg-[#d6d6d3] p-[3px] transition-colors duration-200 peer-checked:justify-end peer-checked:bg-ub-accent! peer-focus-visible:ring-2 peer-focus-visible:ring-ub-accent/40">
                                <span class="h-5 w-5 rounded-full bg-white shadow-[0_1px_3px_rgba(0,0,0,.2)]"></span>
                            </span>
                        </label>
                        <p class="mt-2 text-[12px] text-ub-texte3">{{ __('Ajouté sous chaque book, il ouvre son portfolio depuis un téléphone.') }}</p>
                    </div>
                </details>
            </div>
            <script>
                document.getElementById('memo-pdf-qr').addEventListener('change', (e) => {
                    const lien = document.getElementById('memo-pdf');
                    lien.href = lien.dataset.base + (e.target.checked ? '?qr=1' : '');
                });
            </script>
        @endif
    </div>

    @if ($books->isEmpty())
        <p class="mt-8 text-[15px] text-ub-texte3">{{ __('Cette sélection est vide pour le moment.') }}</p>
    @else
        @include('memo.partials.grille', ['interactif' => false])
    @endif
</main>
</body>
</html>
