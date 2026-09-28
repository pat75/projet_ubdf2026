{{-- Page contact Responsive 2014 : formulaire commun (le legacy l'affichait comme une page de rubrique). --}}
@extends('book.responsive.layout')

@section('contenu')
    <article class="max-w-xl text-left motion-safe:animate-apparition">
        <h2 class="ub_menu_titre ub_font_menut mb-8 text-[22px]">{{ __('Contact') }}</h2>
        @include('book.commun._formulaire-contact')
    </article>
@endsection
