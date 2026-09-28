{{-- Page contact Grid 2015 : formulaire commun. --}}
@extends('book.grid2015.layout')

@section('contenu')
    <article class="max-w-xl motion-safe:animate-apparition">
        <h2 class="ub_menu_titre ub_font_menut mb-8 text-[18px] font-bold uppercase">{{ __('Contact') }}</h2>
        @include('book.commun._formulaire-contact')
    </article>
@endsection
