{{-- Piece jointe d'un message du support (fil client et admin) : vignette
     de l'image ou icone PDF ; un clic l'ouvre dans une fenetre par-dessus
     la page. Styles en ligne : sert aussi dans Filament, qui ne compile pas
     les utilitaires du portail. Alpine est charge des deux cotes. --}}
@if ($msg->piece)
@php($lienPiece = $msg->lienPiece())
<span x-data="{ ouvert: false }" @keydown.escape.window="ouvert = false" style="display:block;margin-top:12px;white-space:normal">
<button type="button" @click="ouvert = true" title="{{ $msg->piece_nom }}"
        style="display:inline-flex;align-items:center;gap:8px;padding:0;border:0;background:none;cursor:zoom-in;color:inherit;font:inherit;text-align:left">
@if ($msg->pieceEstImage())
<img src="{{ $lienPiece }}" alt="{{ $msg->piece_nom }}" style="display:block;width:120px;height:90px;object-fit:cover;border-radius:4px">
@else
<svg width="40" height="48" viewBox="0 0 40 48" aria-hidden="true"><path d="M4 0h22l14 14v30a4 4 0 0 1-4 4H4a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4z" fill="#e2483d"/><path d="M26 0v10a4 4 0 0 0 4 4h10z" fill="#f29b93"/><text x="20" y="36" fill="#fff" font-family="Helvetica,Arial,sans-serif" font-size="11" font-weight="700" text-anchor="middle">PDF</text></svg>
<span style="font-size:14px;text-decoration:underline">{{ $msg->piece_nom }}</span>
@endif
</button>

<template x-teleport="body">
<div x-show="ouvert" x-cloak x-transition.opacity @click.self="ouvert = false" role="dialog" aria-modal="true" aria-label="{{ $msg->piece_nom }}"
     style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:24px;background:rgba(0,0,0,.8)">
<button type="button" @click="ouvert = false" aria-label="{{ __('Fermer') }}"
        style="position:absolute;top:14px;right:18px;border:0;background:none;color:#fff;font-size:34px;line-height:1;cursor:pointer">&times;</button>
@if ($msg->pieceEstImage())
<img src="{{ $lienPiece }}" alt="{{ $msg->piece_nom }}" style="max-width:92vw;max-height:88vh;object-fit:contain;box-shadow:0 10px 40px rgba(0,0,0,.5)">
@else
<template x-if="ouvert"><iframe src="{{ $lienPiece }}" title="{{ $msg->piece_nom }}" style="width:min(92vw,1000px);height:88vh;border:0;background:#fff"></iframe></template>
@endif
</div>
</template>
</span>
@endif
