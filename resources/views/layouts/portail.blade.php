<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.head')

{{-- Charge apres les feuilles du front 2018 pour pouvoir les surcharger. --}}
@vite('resources/css/ubdf.css')
<body class="@yield('body_class', 'page_accueil')">

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
