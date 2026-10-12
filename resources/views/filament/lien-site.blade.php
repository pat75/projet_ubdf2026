{{-- A droite du nom du site : maison, puis accueils Ultra-book et Dustfolio, dans un nouvel onglet. --}}
@php
    $bouton = 'display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border:1px solid rgb(209 213 219);border-radius:9999px;font-size:.875rem;color:rgb(55 65 81);text-decoration:none';
@endphp
<div style="display:inline-flex;align-items:center;gap:.5rem;margin-inline-start:.75rem">
    <a href="{{ lien('home') }}" target="_blank" rel="noopener" style="{{ $bouton }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true"><path d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z"></path></svg>
        Ultra-book
    </a>
    <a href="{{ config('marques.df.canonique') }}" target="_blank" rel="noopener" style="{{ $bouton }}">Dustfolio</a>
</div>
