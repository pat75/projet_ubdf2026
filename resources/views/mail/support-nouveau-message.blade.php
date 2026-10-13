<x-mail::message :petit-logo="true">
{{-- Meme mise en page que le mail du coach (mail/coaching). --}}
<p><strong>{{ $ticket->user ? $ticket->user->fullName().' ('.$ticket->user->login.')' : $ticket->email }}</strong> a écrit au support :</p>

<div>{!! str_replace(["\r", "\n"], '', nl2br(e($dernier?->corps))) !!}</div>

<a href="{{ $lienAdmin }}" style="color:#000000;font-weight:bold;text-decoration:none;">Répondre dans l’admin &rarr;</a>
</x-mail::message>
