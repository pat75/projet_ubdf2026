@props(['petitLogo' => false])
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="{{ asset('img_admin/ultra-book_logo_nb.gif') }}" width="{{ $petitLogo ? 112 : 140 }}" height="{{ $petitLogo ? 40 : 50 }}" alt="{{ config('app.name') }}" style="display:block;border:0;">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
{{ config('app.name') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
