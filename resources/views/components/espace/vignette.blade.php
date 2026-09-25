@props(['creatif'])

{{-- Vignette du createur : photo, ou a defaut ses initiales sur un fond
     tire de son identifiant (voir App\Services\Espace\AffichageProfil). --}}
@php
    $profil = app(\App\Services\Espace\AffichageProfil::class);
    $photo = $profil->photoUrl($creatif);
@endphp

@if ($photo)
    <img src="{{ $photo }}" alt="{{ $creatif->fullName() }}" data-avatar-profil
         {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover']) }}>
@else
    <span data-avatar-profil {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-full text-sm font-bold text-white']) }}
          style="background-color: {{ $profil->couleur($creatif) }}" aria-hidden="true">{{ $profil->initiales($creatif) }}</span>
@endif
