{{-- Pages de contenu Responsive 2014 (Bio, actualites), ex-ultrabook_2012_news : le menu est dans la colonne. --}}
@extends('book.responsive.layout')

@section('contenu')
    @php $menu = $vue->menuPages(); @endphp

    <article class="contenu-page max-w-3xl text-left motion-safe:animate-apparition">
        @if ($menu['page'])
            {!! book_actu_txt($menu['page']['img_html']) !!}
        @else
            <p>{{ __('Page introuvable') }}</p>
        @endif
    </article>
@endsection
