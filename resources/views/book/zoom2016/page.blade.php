{{--
    Pages de contenu Zoom 2016 (Bio, actualites), ex-ultrabook_news :
    menu des pages dans le tiers gauche, texte dans les deux tiers.
    Mobile : menu deroulant au-dessus du texte.
--}}
@extends('book.zoom2016.layout')

@php
    $menu = $vue->menuPages();
    $multi = count($menu['rubriques']) > 1 || count($menu['rubriques'][0]['pages'] ?? []) > 1;
@endphp

@section('contenu')
    @if ($multi)
        <div x-data="{ ouvert: false }" class="mb-8 md:hidden" @click.outside="ouvert = false">
            <button type="button" @click="ouvert = ! ouvert" :aria-expanded="ouvert"
                    class="flex w-full items-center justify-between border-b border-book-filet py-2 text-[16px] text-book-texte">
                {{ collect($menu['rubriques'])->pluck('pages')->flatten(1)->firstWhere('active', true)['titre'] ?? $vue->lien('link_bio', __('Bio')) }}
                <svg class="size-5 transition-transform duration-300" :class="ouvert && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div x-show="ouvert" x-cloak x-transition.opacity class="mt-4">
                @include('book.commun._menu-pages')
            </div>
        </div>
    @endif

    <div @class(['md:grid md:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] md:gap-12' => $multi])>
        @if ($multi)
            <aside class="max-md:hidden motion-safe:animate-apparition">
                @include('book.commun._menu-pages')
            </aside>
        @endif

        <article @class(['contenu-page text-left motion-safe:animate-apparition motion-safe:[animation-delay:120ms]', 'mx-auto max-w-3xl' => ! $multi])>
            @if ($menu['page'])
                {!! book_actu_txt($menu['page']['img_html']) !!}
            @else
                <p>{{ __('Page introuvable') }}</p>
            @endif
        </article>
    </div>
@endsection
