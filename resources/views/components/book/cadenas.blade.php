{{-- Cadenas d'un portfolio protege par mot de passe, dans les menus des books :
     a la taille du texte et a sa couleur (currentColor). --}}
<svg {{ $attributes->merge(['class' => 'inline-block size-[1em] shrink-0 align-[-.1em]']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="{{ __('Protégé par mot de passe') }}"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
