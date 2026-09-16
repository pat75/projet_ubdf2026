<x-mail::message>
# Vous avez reçu une demande

**{{ $conversation->sender_name }}**@if ($conversation->sender_company) ({{ $conversation->sender_company }})@endif
vous a écrit depuis votre book.

**Objet :** {{ $conversation->objet() }}
@if ($conversation->request_detail)
**Précision :** {{ $conversation->request_detail }}
@endif

<x-mail::panel>
{{ $conversation->messages->first()?->body }}
</x-mail::panel>

<x-mail::button :url="$lien">
Lire et répondre
</x-mail::button>

Répondez depuis ce lien : votre adresse reste masquée tant que vous ne la
communiquez pas vous-même.

Ce lien est valable {{ config('messagerie.lien_valide_jours') }} jours après le dernier message.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
