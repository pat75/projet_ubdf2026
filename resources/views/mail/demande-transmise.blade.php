<x-mail::message>
# Votre demande a été transmise

Nous l'avons remise à **{{ $conversation->user->fullName() }}**. La réponse
vous parviendra dans le fil de discussion ci-dessous.

**Objet :** {{ $conversation->objet() }}

<x-mail::panel>
{{ $conversation->messages->first()?->body }}
</x-mail::panel>

<x-mail::button :url="$lien">
Suivre ma demande
</x-mail::button>

Conservez ce lien : il vous permet de relire l'échange et d'y répondre.
Il est valable {{ config('messagerie.lien_valide_jours') }} jours après le dernier message.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
