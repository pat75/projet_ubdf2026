<x-mail::message :petit-logo="true">
{{-- Meme mise en page que le mail du coach (mail/coaching). --}}
<div>{!! str_replace(["\r", "\n"], '', nl2br(e($reponse?->corps))) !!}</div>

<a href="{{ $lien }}" style="color:#000000;font-weight:bold;text-decoration:none;">Répondre au support &rarr;</a>

@if ($visuel)
<p style="text-align:center;margin:28px 0 0;"><img src="{{ $visuel }}" alt="" height="{{ $hauteurVisuel }}" style="height:{{ $hauteurVisuel }}px;width:auto;border:0;"></p>
@endif

<x-slot:subcopy>
Ce lien vous est personnel : il rouvre toute la discussion.<br>
Ne le transmettez pas.
</x-slot:subcopy>
</x-mail::message>
