{{-- Pied de l'en-tete Grid 2015 (ex-ultrabook_footer) : reseaux, partage, pied du createur ou mention de la plateforme. --}}
@if ($reseaux = $vue->reseaux())
    <ul class="mb-4 flex flex-wrap gap-x-5 gap-y-1 text-[14px]">
        @foreach ($reseaux as $reseau => $url)
            <li><a href="{{ $url }}" target="_blank" rel="noopener me" class="capitalize transition-opacity hover:opacity-60">{{ $reseau }}</a></li>
        @endforeach
    </ul>
@endif
@if ($partage = $vue->partage())
    @include('book.commun._partage', ['partage' => $partage, 'classe' => 'mb-4 flex gap-2'])
@endif
@if ($pied = $vue->piedDePage())
    <div class="texte-libre">{!! $pied !!}</div>
@elseif ($vue->mentionPlateforme())
    <a href="https://{{ $b->inc_url_dom_www }}" target="_blank" rel="noopener" class="font-semibold transition-opacity hover:opacity-60">{{ $b->inc_site_name }}</a>
@endif
