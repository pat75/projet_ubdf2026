{{-- Relance l'analyse IA tant qu'il reste des demandes a analyser sur la
     page : une cle neuve a chaque rendu redeclenche wire:init. --}}
<div>
    @if ($reste)
        <div wire:key="analyse-spam-{{ $reste }}-{{ $echecs }}" wire:init="analyserSpamIA" class="text-sm text-gray-500">
            Analyse IA des messages en cours ({{ $reste }} restant{{ $reste > 1 ? 's' : '' }})…
        </div>
    @endif
</div>
