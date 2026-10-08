@props(['nom'])

{{-- Les quelques pictogrammes du menu et du tableau de bord. Ils sont
     dessines ici plutot que tires d'une fonte d'icones : six traces ne
     valent pas une dependance de plus. --}}
@php
    $traces = [
        'maison' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 11l9-7 9 7M5 9.5V20h5v-6h4v6h5V9.5"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="1.5"/><circle cx="8.5" cy="9.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 16l-5-5-9 9"/>',
        'plus' => '<circle cx="5" cy="12" r="1.3"/><circle cx="12" cy="12" r="1.3"/><circle cx="19" cy="12" r="1.3"/>',
        'sortie' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-4M10 8l4 4-4 4M14 12H3"/>',
        'lien' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'reglage' => '<circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 7.9 19l-.1.1A2 2 0 1 1 5 16.3l.1-.1a1.6 1.6 0 0 0-1.1-2.7H3a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 5 7.9L4.9 7.8A2 2 0 1 1 7.7 5l.1.1a1.6 1.6 0 0 0 2.7-1.1V3a2 2 0 1 1 4 0v.1A1.6 1.6 0 0 0 16.1 5l.1-.1A2 2 0 1 1 19 7.7l-.1.1a1.6 1.6 0 0 0 1.1 2.7h.1a2 2 0 1 1 0 4H20a1.6 1.6 0 0 0-1.5 1z"/>',
        'oeil' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'diffusion' => '<circle cx="12" cy="12" r="1.8"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.2 8.2a5.4 5.4 0 0 0 0 7.6M15.8 8.2a5.4 5.4 0 0 1 0 7.6M5.3 5.3a9.5 9.5 0 0 0 0 13.4M18.7 5.3a9.5 9.5 0 0 1 0 13.4"/>',
        'aide' => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 9.5a2.6 2.6 0 0 1 5 1c0 1.8-2.5 2-2.5 3.5"/><path stroke-linecap="round" d="M12 17.2h.01"/>',
        'dossier' => '<rect x="3.5" y="3.5" width="17" height="17" rx="2"/><path stroke-linecap="round" d="M12 8.5v7M8.5 12h7"/>',
        'partage' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-8 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 14v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'vues' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'coeur' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.3-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.7-7 9-7 9z"/>',
        'horloge' => '<circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/>',
        'graphe' => '<path stroke-linecap="round" d="M5 20V10M12 20V4M19 20v-7"/>',
        'mobile' => '<rect x="7" y="3" width="10" height="18" rx="1.5"/><path stroke-linecap="round" d="M11 18h2"/>',
        'tablette' => '<rect x="4.5" y="3" width="15" height="18" rx="1.5"/><path stroke-linecap="round" d="M11 18h2"/>',
        'ordinateur' => '<rect x="3" y="4.5" width="18" height="12" rx="1.5"/><path stroke-linecap="round" d="M8.5 20h7M12 16.5V20"/>',
        'utilisateur' => '<circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5"/>',
        'enveloppe' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.5 7 8.5 6 8.5-6"/>',
        'pdf' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M9 13h1.5a1.5 1.5 0 0 1 0 3H9v-3zm0 3v2"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'inline-block h-5 w-5']) }}
     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
    {!! $traces[$nom] ?? '' !!}
</svg>
