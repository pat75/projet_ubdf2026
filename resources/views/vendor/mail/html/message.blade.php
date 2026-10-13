@props(['petitLogo' => false])
@php
    // Logo, lien et pied suivent la marque du mail (Ultra-book ou Dustfolio).
    $marqueMail = \App\Support\Marque::duMail();
    $hauteurLogo = $petitLogo ? 40 : 50;
    [$l, $h] = @getimagesize(public_path(ltrim($marqueMail->logoMail, '/'))) ?: [280, 99];
@endphp
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="$marqueMail->canonique">
<img src="{{ asset(ltrim($marqueMail->logoMail, '/')) }}" width="{{ (int) round($hauteurLogo * $l / $h) }}" height="{{ $hauteurLogo }}" alt="{{ $marqueMail->nom }}" style="display:block;border:0;">
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
© {{ date('Y') }} {{ $marqueMail->nom }}. Tous droits réservés.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
