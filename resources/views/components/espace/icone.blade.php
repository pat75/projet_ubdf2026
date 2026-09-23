@props(['nom'])

{{-- Les quelques pictogrammes du menu et du tableau de bord. Ils sont
     dessines ici plutot que tires d'une fonte d'icones : six traces ne
     valent pas une dependance de plus. --}}
@php
    $traces = [
        'sortie' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-4M10 8l4 4-4 4M14 12H3"/>',
        'lien' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'reglage' => '<circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 7.9 19l-.1.1A2 2 0 1 1 5 16.3l.1-.1a1.6 1.6 0 0 0-1.1-2.7H3a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 5 7.9L4.9 7.8A2 2 0 1 1 7.7 5l.1.1a1.6 1.6 0 0 0 2.7-1.1V3a2 2 0 1 1 4 0v.1A1.6 1.6 0 0 0 16.1 5l.1-.1A2 2 0 1 1 19 7.7l-.1.1a1.6 1.6 0 0 0 1.1 2.7h.1a2 2 0 1 1 0 4H20a1.6 1.6 0 0 0-1.5 1z"/>',
        'oeil' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'diffusion' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 20c6-10 10-13 16-15-1 7-4 12-9 13l-3 1z"/><path stroke-linecap="round" d="M9 15c-2 1-3 3-3 5"/>',
        'aide' => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 9.5a2.6 2.6 0 0 1 5 1c0 1.8-2.5 2-2.5 3.5"/><path stroke-linecap="round" d="M12 17.2h.01"/>',
        'dossier' => '<rect x="3.5" y="3.5" width="17" height="17" rx="2"/><path stroke-linecap="round" d="M12 8.5v7M8.5 12h7"/>',
        'partage' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-8 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 14v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'vues' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'coeur' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.3-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.7-7 9-7 9z"/>',
        'graphe' => '<path stroke-linecap="round" d="M5 20V10M12 20V4M19 20v-7"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'inline-block h-5 w-5']) }}
     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
    {!! $traces[$nom] ?? '' !!}
</svg>
