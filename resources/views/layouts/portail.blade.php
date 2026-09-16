<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('partials.head')
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
</body>
</html>
