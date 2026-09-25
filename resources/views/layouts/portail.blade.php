<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.head')

{{-- Charge apres les feuilles du front 2018 pour pouvoir les surcharger.
     portail.js (Alpine) porte tout le JavaScript du portail. --}}
@vite(['resources/css/ubdf.css', 'resources/css/portail.css', 'resources/js/portail.js'])
<meta name="recaptcha" content="{{ config('services.recaptcha.key') }}">
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

{{-- Contrat passe au JS du portail (resources/js/portail/cartes.js). --}}
<script>
    window.ubdf = @json(($ubdf ?? []) + ['inscription' => lien('inscription.page')]);
</script>
@stack('scripts')
</body>
</html>
