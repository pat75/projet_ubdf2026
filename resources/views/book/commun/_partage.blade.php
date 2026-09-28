{{-- Boutons de partage de la page ($partage : reseau => URL, VueBook::partage des modeles ; $classe : placement). --}}
<ul class="{{ $classe }}" aria-label="{{ __('Partager') }}">
    @foreach ($partage as $reseau => $url)
        <li>
            <a href="{{ $url }}" target="_blank" rel="nofollow noopener" title="{{ __('Partager sur :reseau', ['reseau' => $reseau]) }}"
               class="flex size-8 items-center justify-center rounded-full border border-current/40 text-[11px] font-medium opacity-70 transition hover:opacity-100">
                <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! [
                    'Facebook' => '<path d="M14 8h3V4h-3c-2.8 0-4 1.7-4 4.3V10H7v4h3v8h4v-8h3l1-4h-4V8.5c0-.3.2-.5.5-.5z"/>',
                    'X' => '<path d="M17.8 3h3.1l-6.8 7.8L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L2 3h6.4l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5z"/>',
                    'LinkedIn' => '<path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3V9.5zm7 0h3.8v1.6h.1c.5-1 1.8-2 3.8-2 4 0 4.8 2.6 4.8 6V21h-4v-5.2c0-1.2 0-2.8-1.7-2.8s-2 1.3-2 2.7V21h-4V9.5z"/>',
                    'Pinterest' => '<path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.5 1.9-2.5.9 0 1.3.7 1.3 1.5 0 .9-.6 2.3-.9 3.5-.3 1.1.5 1.9 1.6 1.9 1.9 0 3.3-2 3.3-4.9 0-2.6-1.8-4.4-4.5-4.4-3 0-4.8 2.3-4.8 4.6 0 .9.4 1.9.8 2.4.1.1.1.2.1.3l-.3 1.2c0 .2-.2.3-.4.2-1.4-.7-2.2-2.7-2.2-4.3 0-3.5 2.5-6.7 7.3-6.7 3.8 0 6.8 2.7 6.8 6.4 0 3.8-2.4 6.9-5.8 6.9-1.1 0-2.2-.6-2.6-1.3l-.7 2.7c-.3 1-1 2.2-1.4 2.9A10 10 0 1 0 12 2z"/>',
                ][$reseau] ?? '' !!}</svg>
                <span class="sr-only">{{ __('Partager sur :reseau', ['reseau' => $reseau]) }}</span>
            </a>
        </li>
    @endforeach
</ul>
