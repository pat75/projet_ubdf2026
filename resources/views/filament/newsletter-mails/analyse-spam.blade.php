{{-- Relance l'analyse IA tant qu'il reste des adresses a analyser sur la
     page : une cle neuve a chaque rendu redeclenche wire:init. --}}
<div>
    @if ($reste)
        <div wire:key="analyse-spam-{{ $reste }}-{{ $echecs }}" wire:init="analyserSpamIA" class="text-sm text-gray-500">
            Analyse IA des adresses en cours ({{ $reste }} restante{{ $reste > 1 ? 's' : '' }})…
        </div>
    @endif
</div>
