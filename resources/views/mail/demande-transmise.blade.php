<x-mail::message>
# Votre demande a été transmise

Nous l'avons remise à **{{ $conversation->user->fullName() }}**. La réponse
vous parviendra dans le fil de discussion ci-dessous.

**Objet :** {{ $conversation->objet() }}
  
**Le :** {{ $conversation->messages->first()?->created_at?->translatedFormat('j F Y à H:i') }}

<x-mail::panel>
{{ $conversation->messages->first()?->body }}
</x-mail::panel>

<x-mail::button :url="$lien" align="left">
Suivre ma demande
</x-mail::button>

Conservez ce lien : il vous permet de relire l'échange et d'y répondre.
Il est valable {{ config('messagerie.lien_valide_jours') }} jours après le dernier message.

<x-slot:subcopy>
Vous ne pouvez pas répondre directement à cet e-mail : passez par la messagerie {{ config('app.name') }} pour poursuivre l’échange.
</x-slot:subcopy>
</x-mail::message>
