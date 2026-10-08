<x-mail::message :petit-logo="true">
{!! e($message->corpsPourMail()) !!}

<a href="{{ $lienEspace }}" style="color:#000000;font-weight:bold;text-decoration:none;">Se connecter à mon espace &rarr;</a>

@if ($visuel)
<p style="text-align:center;margin:28px 0 0;"><img src="{{ $visuel }}" alt="" height="{{ $hauteurVisuel }}" style="height:{{ $hauteurVisuel }}px;width:auto;border:0;"></p>
@endif

<x-slot:subcopy>
Vous ne souhaitez plus recevoir nos conseils pour la réalisation de votre book ? [Se désabonner]({{ $lienDesabonnement }})
</x-slot:subcopy>
</x-mail::message>
