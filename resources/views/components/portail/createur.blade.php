@props(['creatif'])

{{-- Avatar du createur, son nom en gras et, dessous, son metier en leger.
     Sans vignette : un rond aux initiales, de couleur stable pour un login. --}}
@php
    $nom = $creatif->fullName();
    $metier = __(\App\Support\Metier::find($creatif->category?->slug ?? 'autre')['name'] ?? '');
    $avatar = $creatif->thumbnailUrl('carre_183');
    $initiales = mb_strtoupper(mb_substr($creatif->firstname ?: $creatif->login, 0, 1).mb_substr($creatif->lastname ?? '', 0, 1));
    $couleurs = ['#4285F4', '#EA4335', '#34A853', '#5E35B1', '#00897B', '#E53935', '#1E88E5', '#F4511E', '#3949AB', '#8E24AA', '#D81B60', '#00ACC1', '#6D4C41'];
    $couleur = $couleurs[crc32($creatif->login) % count($couleurs)];
@endphp

<a href="{{ $creatif->bookUrl() }}" target="_blank" {{ $attributes->merge(['class' => 'flex items-center gap-3 text-inherit']) }}>
    @if ($avatar)
        <img src="{{ $avatar }}" alt="" width="56" height="56" loading="lazy" class="h-14 w-14 shrink-0 rounded-full object-cover">
    @else
        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full text-[18px] font-bold text-white" style="background-color: {{ $couleur }}">{{ $initiales }}</span>
    @endif
    <span class="flex min-w-0 flex-col">
        <span class="truncate font-bold">{{ $nom }}</span>
        @if ($metier)<span class="truncate font-light">{{ $metier }}</span>@endif
    </span>
</a>
