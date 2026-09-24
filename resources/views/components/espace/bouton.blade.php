<button {{ $attributes->merge(['type' => 'submit', 'class' => 'bouton-espace px-6 py-2.5 text-[15px]']) }}>
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    <span>{{ $slot }}</span>
</button>
