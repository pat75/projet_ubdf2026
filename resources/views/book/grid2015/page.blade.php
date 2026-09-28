{{-- Pages Grid 2015 (Bio, actualites), ex-ultrabook_news : titre, bouton fermer, contenu. --}}
@extends('book.grid2015.layout')

@section('contenu')
    @php $page = $vue->menuPages()['page']; @endphp

    <article class="max-w-3xl motion-safe:animate-apparition">
        <div class="mb-6 flex items-start justify-between gap-4">
            <h2 class="ub_font_ptf_titre text-[18px] font-bold uppercase">{{ $page ? strip_tags($page['img_titre']) : __('Page introuvable') }}</h2>
            <a href="/" aria-label="{{ __('Fermer') }}" class="-mt-1 shrink-0 p-2 transition-opacity hover:opacity-60">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19"/></svg>
            </a>
        </div>
        @if ($page)
            <div class="contenu-page">{!! book_actu_txt($page['img_html']) !!}</div>
        @endif
    </article>
@endsection
