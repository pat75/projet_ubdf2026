@props(['texte'])

{{-- Bulle d'aide au survol (ou au focus clavier) d'une icone d'action :
     sous l'icone, centree, pointe vers le haut. Texte 14px (12px + 15 %). --}}
<span {{ $attributes->merge(['class' => 'group/bulle relative inline-flex']) }}>
    {{ $slot }}
    <span role="tooltip"
          class="pointer-events-none invisible absolute top-full left-1/2 z-20 mt-3 -translate-x-1/2 whitespace-nowrap bg-gray-900 px-3 py-2 text-[14px] text-white opacity-0 transition-opacity duration-150 group-hover/bulle:visible group-hover/bulle:opacity-100 group-focus-within/bulle:visible group-focus-within/bulle:opacity-100">
        {{ $texte }}
        <span class="absolute -top-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-gray-900" aria-hidden="true"></span>
    </span>
</span>
