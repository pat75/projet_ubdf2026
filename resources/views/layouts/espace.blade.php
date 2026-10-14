<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Mon espace')) | {{ $marque->nom }}</title>

    {{-- Icones d'onglet navigateur, reprises du portail 2018
         (@include('partials.head') / img_front/favicon). --}}
    <link rel="icon" type="image/png" sizes="32x32" href="/img_front/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img_front/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img_front/favicon/apple-icon-180x180.png">
    <link rel="manifest" href="/img_front/favicon/manifest.json">
    <meta name="theme-color" content="#ffffff">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap"></noscript>

    {{-- La fonte d'icones du site (font_icon) : elle porte les
         pictogrammes des reseaux sociaux et quelques signes du tableau de
         bord. Elle ne met en forme que les classes `fonticon-*`. --}}
    <link rel="stylesheet" href="{{ asset('html_pages_v2018/_/font_icon/style.css').'?v=2' }}">

    @vite(['resources/css/espace.css', 'resources/js/espace.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-ub-fond font-courant text-ub-texte antialiased">

@if (session()->has('prise_identite'))
    {{-- Un administrateur regarde l'espace par-dessus l'epaule du createur.
         Bandeau rouge, en haut de chaque page : on ne doit jamais oublier
         qu'on agit sous une autre identite. --}}
    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 bg-red-700 px-4 py-2 text-center text-sm text-white">
        <span>{{ __('Vous êtes connecté en tant que :login.', ['login' => auth()->user()->login]) }}</span>
        <form method="post" action="{{ route('admin.prise-identite.rendre') }}">
            @csrf
            <button type="submit" class="font-semibold underline">{{ __('Revenir au back-office') }}</button>
        </form>
    </div>
@endif

@include('partials.espace.entete')

@php($messagesNonLus = auth()->user()->conversationsNonLues())

{{-- Contenu a gauche, menu a droite. Sous 900 px, le menu quitte la page
     pour la barre d'onglets du bas (partials/espace/barre-mobile). --}}
<main class="mx-auto flex max-w-[1140px] flex-wrap items-start gap-8 px-5 pb-18 pt-11">

    <div class="flex min-w-0 flex-[1_1_560px] flex-col gap-5">
        @if (session('paiement'))
            <div class="flex flex-col items-center text-center">
                @if (session('paiement_ok'))
                    <img src="{{ asset('img_front/thank-you.png') }}" alt="{{ __('Merci') }}" width="200" height="200" id="visuel-merci" class="w-[200px] mix-blend-multiply">
                    {{-- Feu d'artifice de pixels couleur tonique, parti du visuel. --}}
                    <script>
                    (() => {
                        if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                        const lancer = () => {
                            const img = document.getElementById('visuel-merci');
                            const r = img.getBoundingClientRect();
                            const cx = r.left + r.width / 2, cy = r.top + r.height / 2;
                            const c = Object.assign(document.createElement('canvas'), { width: innerWidth, height: innerHeight });
                            c.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:60';
                            document.body.appendChild(c);
                            const ctx = c.getContext('2d');
                            const couleur = getComputedStyle(document.documentElement).getPropertyValue('--color-ub-accent').trim() || '#16a9b5';
                            const parts = [];
                            // Trois salves successives depuis le centre du visuel.
                            [0, 350, 700].forEach((delai) => setTimeout(() => {
                                for (let i = 0; i < 90; i++) {
                                    const a = Math.random() * Math.PI * 2, v = 3 + Math.random() * 9;
                                    parts.push({ x: cx, y: cy, vx: Math.cos(a) * v, vy: Math.sin(a) * v, vie: 1, t: 3 + Math.random() * 3 });
                                }
                            }, delai));
                            const debut = performance.now();
                            const image = (now) => {
                                ctx.clearRect(0, 0, c.width, c.height);
                                ctx.fillStyle = couleur;
                                for (const p of parts) {
                                    p.vx *= 0.985; p.vy = p.vy * 0.985 + 0.12;
                                    p.x += p.vx; p.y += p.vy; p.vie -= 0.009;
                                    if (p.vie <= 0) continue;
                                    ctx.globalAlpha = p.vie;
                                    ctx.fillRect(Math.round(p.x), Math.round(p.y), p.t, p.t);
                                }
                                if (now - debut < 3200) requestAnimationFrame(image); else c.remove();
                            };
                            requestAnimationFrame(image);
                        };
                        document.readyState === 'complete' ? lancer() : addEventListener('load', lancer);
                    })();
                    </script>
                @endif
                <p class="my-[30px] text-[22px] font-semibold leading-snug text-ub-succes">✓ {{ session('paiement') }}
                    @if (session('paiement_detail'))
                        <span class="mt-1 block text-[16px] font-normal text-ub-texte2">{{ session('paiement_detail') }}</span>
                    @endif
                </p>
                @if (session('paiement_lien'))
                    <a href="{{ session('paiement_lien')[0] }}" class="bouton-espace bouton-espace-grand -mt-2 mb-[30px] px-6">{{ session('paiement_lien')[1] }}</a>
                @endif
            </div>
        @endif
        @if (session('statut'))
            <div class="self-start rounded-ub bg-[#e7f5ec] px-3.5 py-2 text-[14px] text-[#1f7a4a]">✓ {{ session('statut') }}</div>
        @endif

        @yield('content')
    </div>

    <aside class="sticky top-6 hidden min-w-60 flex-[0_1_280px] flex-col gap-4 min-[900px]:flex">
        @include('partials.espace.menu')
    </aside>
</main>

{{-- Sur mobile, facon WebApp : pas de pied de page, la barre d'onglets
     en tient lieu. --}}
<div class="hidden min-[900px]:block">
    @include('partials.pied-commun')
    {{-- Sur mobile, la barre d'onglets occupe le bas de l'ecran. --}}
    <x-support.bouton />
</div>

@include('partials.espace.barre-mobile')

{{-- Menu plein ecran du portail, ouvert par le burger de l'entete. --}}
<x-portail.menu-plein-ecran />

@livewireScripts
</body>
</html>
