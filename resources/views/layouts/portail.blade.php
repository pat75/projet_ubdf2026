<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.head')

{{-- Charge apres les feuilles du front 2018 pour pouvoir les surcharger. --}}
@vite('resources/css/ubdf.css')
{{-- La marque est portee par le body : elle sert de selecteur aux regles
     qui ne valent que pour l'une des deux (l'en-tete de l'accueil, la
     remontee du bloc de recherche). --}}
<body class="marque_{{ $marque->code }} @yield('body_class', 'page_accueil')">

@include('partials.header')

@yield('content')

@include('partials.footer')
@include('partials.modals')
@include('partials.handlebars')

{{-- Contrat passe au JS du front (js2019/js_core_pages.js). --}}
<script>
    window.ubdf = @json($ubdf ?? []);
</script>
@stack('scripts')
</body>
</html>
