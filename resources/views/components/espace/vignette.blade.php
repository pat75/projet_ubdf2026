@props(['creatif'])

{{-- Vignette du createur. Faute de photo, ses initiales sur un fond tire
     de son identifiant : toujours la meme couleur pour le meme compte. --}}
@php
    $photo = $creatif->bookSetting?->thumbnail ? $creatif->thumbnailUrl() : null;

    $mots = preg_split('/\s+/', trim($creatif->fullName()), -1, PREG_SPLIT_NO_EMPTY) ?: [$creatif->login];
    $initiales = mb_strtoupper(count($mots) > 1
        ? mb_substr($mots[0], 0, 1).mb_substr(end($mots), 0, 1)
        : mb_substr($mots[0], 0, 2));

    $palette = ['#4285f4', '#ea4335', '#fbbc05', '#34a853', '#5e35b1', '#00897b',
        '#43a047', '#e53935', '#1e88e5', '#f4511e', '#3949ab', '#039be5'];
    $couleur = $palette[crc32($creatif->login) % count($palette)];
@endphp

@if ($photo)
    <img src="{{ $photo }}" alt="{{ $creatif->fullName() }}"
         {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover']) }}>
@else
    <span {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-full text-sm font-bold text-white']) }}
          style="background-color: {{ $couleur }}" aria-hidden="true">{{ $initiales }}</span>
@endif
