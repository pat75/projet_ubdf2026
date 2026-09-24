<x-mail::message>
# {{ $auteur }} vous a répondu

**Objet :** {{ $conversation->objet() }}
  
**Le :** {{ $conversation->messages->last()?->created_at?->translatedFormat('j F Y à H:i') }}

<x-mail::panel>
{{ $conversation->messages->last()?->body }}
</x-mail::panel>

<x-mail::button :url="$lien" align="left">
Ouvrir la discussion
</x-mail::button>

<x-slot:subcopy>
Vous ne pouvez pas répondre directement à cet e-mail : passez par la messagerie {{ config('app.name') }} pour poursuivre l’échange.
</x-slot:subcopy>
</x-mail::message>
