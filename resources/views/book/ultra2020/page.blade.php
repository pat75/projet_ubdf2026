{{--
    Pages de contenu (Bio, actualites), ex-page.tlp.php.
    Ultra-frais : menu des pages a gauche du texte.
    Ultra-zen   : menu des pages sous « Bio », dans la colonne d'en-tete
                  (section « sous-menu » du layout).
    Mobile      : menu deroulant au-dessus du texte.
--}}
@extends('book.ultra2020.layout')

@php
    $menu = $vue->menuPages();
    $multi = count($menu['rubriques']) > 1 || count($menu['rubriques'][0]['pages'] ?? []) > 1;
@endphp

@if ($vue->zen() && $multi)
    @section('sous-menu')
        @include('book.commun._menu-pages')
    @endsection
@endif

@section('contenu')
    @if ($multi)
        <div x-data="{ ouvert: false }" class="mb-8 md:hidden" @click.outside="ouvert = false">
            <button type="button" @click="ouvert = ! ouvert" :aria-expanded="ouvert"
                    class="mx-auto flex items-center gap-4 py-2 font-titre text-[13px] uppercase tracking-[1.3px] text-book-texte">
                {!! $vue->lien('name_page', 'Bio') !!}
                <svg class="size-6 transition-transform duration-300" :class="ouvert && 'rotate-45'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><path d="M12 2v20M2 12h20"/></svg>
            </button>
            <div x-show="ouvert" x-cloak x-transition.opacity class="mt-3 flex justify-center">
                @include('book.commun._menu-pages')
            </div>
        </div>
    @endif

    <div @class(['md:grid md:grid-cols-[minmax(0,1fr)_minmax(0,3fr)] md:gap-10' => ! $vue->zen() && $multi])>
        @if (! $vue->zen() && $multi)
            <aside class="max-md:hidden">
                @include('book.commun._menu-pages')
            </aside>
        @endif

        <article class="contenu-page text-left">
            @if ($menu['page'])
                {!! book_actu_txt($menu['page']['img_html']) !!}
            @else
                <p>{{ __('Page introuvable') }}</p>
            @endif
        </article>
    </div>
@endsection
