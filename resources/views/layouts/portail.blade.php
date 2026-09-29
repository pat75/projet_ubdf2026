<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.head')

{{-- Charge apres les feuilles du front 2018 pour pouvoir les surcharger.
     portail.js (Alpine) porte tout le JavaScript du portail. --}}
@vite(['resources/css/ubdf.css', 'resources/css/portail.css', 'resources/js/portail.js'])
<meta name="recaptcha" content="{{ config('services.recaptcha.key') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- La marque est portee par le body : elle sert de selecteur aux regles
     qui ne valent que pour l'une des deux (l'en-tete de l'accueil, la
     remontee du bloc de recherche). --}}
<body class="marque_{{ $marque->code }} @yield('body_class', 'page_accueil')">

{{-- Pages plein ecran (creer un book) : ni menu du haut ni pied de page. --}}
@unless (View::hasSection('plein_ecran'))
    @include('partials.header')
@endunless

@yield('content')

@unless (View::hasSection('plein_ecran'))
    @include('partials.footer')
@endunless
@include('partials.modals')
@include('partials.visionneuse')

{{-- Contrat passe au JS du portail (resources/js/portail/cartes.js).
     `memo` : le memo en base d'un visiteur ou d'un creatif connecte
     (store Alpine `memo`, resources/js/portail/visionneuse.js). --}}
@php
    $memo = app(\App\Services\Memo\MemoBooks::class);
    $memoProprietaire = $memo->proprietaire();
    $contratJs = ($ubdf ?? []) + [
        'inscription' => lien('inscription.page'),
        'memo' => [
            'connecte' => (bool) $memoProprietaire,
            'visiteur' => $memoProprietaire instanceof \App\Models\Visitor,
            'logins' => $memoProprietaire ? $memo->logins($memoProprietaire) : [],
        ],
    ];
@endphp
<script>
    window.ubdf = @json($contratJs);
</script>
@stack('scripts')
</body>
</html>
