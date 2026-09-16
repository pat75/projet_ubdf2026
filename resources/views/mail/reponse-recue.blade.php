<x-mail::message>
# {{ $auteur }} vous a répondu

**Objet :** {{ $conversation->objet() }}

<x-mail::panel>
{{ $conversation->messages->last()?->body }}
</x-mail::panel>

<x-mail::button :url="$lien">
Ouvrir la discussion
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
