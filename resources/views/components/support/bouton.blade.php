{{-- Bouton flottant « Nous contacter », bas droite de chaque page du portail
     et de l'espace. Styles en ligne : le portail (Semantic UI) ne compile
     pas les utilitaires Tailwind. Masque sur les pages du support. --}}
@unless (request()->routeIs('support', '*.support', 'support.*', '*.support.*'))
    <a href="{{ lien('support') }}"
       aria-label="{{ __('Nous contacter') }}"
       style="position:fixed;right:20px;bottom:20px;z-index:60;display:inline-flex;align-items:center;gap:8px;padding:12px 18px;border-radius:9999px;background:#000;color:#fff;font:600 14px/1 'Source Sans 3',sans-serif;text-decoration:none;box-shadow:0 4px 14px rgba(0,0,0,.25)"
       onmouseover="this.style.background='#333'" onmouseout="this.style.background='#000'">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <span>{{ __('Nous contacter') }}</span>
    </a>
@endunless
