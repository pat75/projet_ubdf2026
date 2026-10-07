{{-- Detail des mots-cles IA d'un creatif (AnalyseIA, action « Mots-clés »). --}}
<div style="display:flex;flex-direction:column;gap:1rem">
    @foreach ($medias as $media)
        <div style="display:flex;gap:1rem;align-items:flex-start">
            <img src="{{ $media->url('carre_183') }}" alt="" width="92" height="92" loading="lazy" style="flex-shrink:0;object-fit:cover">
            <div style="min-width:0">
                <div style="font-weight:700">{{ $media->ai_title }}</div>
                <div style="font-size:13px;opacity:.75">{{ $media->ai_description }}</div>
                <div style="display:flex;flex-wrap:wrap;gap:.25rem;margin-top:.5rem">
                    @foreach ($media->tags->sortBy('lang') as $tag)
                        <x-filament::badge :color="$tag->lang === 'fr' ? 'info' : 'gray'">{{ $tag->label }}</x-filament::badge>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
