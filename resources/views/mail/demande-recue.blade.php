<x-mail::message>
# Vous avez reçu une demande

**{{ $conversation->sender_name }}**@if ($conversation->sender_company) ({{ $conversation->sender_company }})@endif
vous a écrit depuis votre book.

**Objet :** {{ $conversation->objet() }}
  
**Le :** {{ $conversation->messages->first()?->created_at?->translatedFormat('j F Y à H:i') }}
@if ($conversation->request_detail)
**Précision :** {{ $conversation->request_detail }}
@endif

<x-mail::panel>
{{ $conversation->messages->first()?->body }}
</x-mail::panel>

<x-mail::button :url="$lien" align="left">
Lire et répondre
</x-mail::button>

Répondez depuis ce lien : votre adresse reste masquée tant que vous ne la
communiquez pas vous-même.

Ce lien est valable {{ config('messagerie.lien_valide_jours') }} jours après le dernier message.

<x-slot:subcopy>
Vous ne pouvez pas répondre directement à cet e-mail : passez par la messagerie {{ config('app.name') }} pour poursuivre l’échange.
</x-slot:subcopy>
</x-mail::message>
