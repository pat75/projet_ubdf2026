{{-- Info-bulle sous un avatar : fond anthracite, fleche vers le haut.
     A poser dans un parent `relative group/avatar`. --}}
<span role="tooltip"
      class="pointer-events-none invisible absolute left-1/2 top-full z-20 mt-2.5 -translate-x-1/2 whitespace-nowrap bg-[#2e2e2e] px-2.5 py-1.5 text-[13px] font-semibold text-white opacity-0 transition-opacity duration-150 group-hover/avatar:visible group-hover/avatar:opacity-100 group-focus-within/avatar:visible group-focus-within/avatar:opacity-100">
    <span class="absolute -top-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-[#2e2e2e]" aria-hidden="true"></span>
    <span class="relative">{{ $slot }}</span>
</span>
