<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-2 rounded-full bg-black px-6 py-2 text-sm font-light text-white transition duration-150 ease-in-out hover:bg-gray-800 disabled:opacity-60 sm:text-base dark:bg-black dark:hover:bg-gray-600']) }}>
    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    <span>{{ $slot }}</span>
</button>
