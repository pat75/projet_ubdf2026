@props(['book', 'jeton'])

{{--
 | Bandeau de revue : visible seulement par un administrateur venu du
 | back-office, avec un jeton valide (App\Services\Admin\RevueBooks).
 |
 | Tout est en style en ligne : le bandeau se pose dans dix modeles de
 | book differents, dont des gabarits legacy avec leurs propres feuilles,
 | et ne doit rien leur emprunter ni rien leur imposer.
--}}
@php
    $selectionne = (bool) $book->in_home_selection;
    $fond = $selectionne ? '#166534' : '#111827';
@endphp

<div style="position:sticky;top:0;left:0;right:0;z-index:2147483647;background:{{ $fond }};color:#fff;font:14px/1.4 -apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;padding:10px 16px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;box-shadow:0 2px 8px rgba(0,0,0,.35)">

    <span style="font-weight:700">{{ $book->login }}</span>

    <span style="opacity:.75">{{ $book->fullName() }}@if ($book->category?->name) — {{ $book->category->name }}@endif</span>

    <span style="opacity:.75">
        @if ($selectionne)
            {{ __('En sélection depuis le :date', ['date' => $book->home_selection_at?->format('d/m/Y') ?: '—']) }}
        @else
            {{ __('Pas en sélection') }}
        @endif
    </span>

    <form method="post" action="{{ route('book.revue.basculer', ['login' => $book->login, 'domaineBooks' => request()->route('domaineBooks')]) }}" style="margin:0 0 0 auto">
        <input type="hidden" name="{{ \App\Services\Admin\RevueBooks::PARAMETRE }}" value="{{ $jeton }}">

        <button type="submit" style="cursor:pointer;border:0;border-radius:0;padding:9px 18px;font:700 14px/1 inherit;color:{{ $selectionne ? '#111827' : '#fff' }};background:{{ $selectionne ? '#fff' : '#2563eb' }}">
            {{ $selectionne ? __('Retirer de la sélection') : __('Sélectionner ce book') }}
        </button>
    </form>
</div>
