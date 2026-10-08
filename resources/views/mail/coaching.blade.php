<x-mail::message :petit-logo="true">
{{ $message->corps }}

<a href="{{ $lienEspace }}" style="color:#000000;font-weight:bold;text-decoration:none;">Se connecter à mon espace &rarr;</a>

<x-slot:subcopy>
Vous ne souhaitez plus recevoir nos conseils pour la réalisation de votre book ? [Se désabonner]({{ $lienDesabonnement }})
</x-slot:subcopy>
</x-mail::message>
