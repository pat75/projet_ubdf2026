<x-mail::message>
# {{ __('Votre archive est prête') }}

{{ __('La copie de vos informations (:taille) peut être téléchargée depuis la page « Exporter » de votre espace, jusqu’au :date.', [
    'taille' => \Illuminate\Support\Number::fileSize((int) $export->taille, 1),
    'date' => $export->expire_at->format('d/m/Y'),
]) }}

<x-mail::button :url="$lien">
{{ __('Télécharger mon archive') }}
</x-mail::button>

<x-slot:subcopy>
{{ __('Passé ce délai, l’archive est supprimée : vous pourrez en demander une nouvelle.') }}
</x-slot:subcopy>
</x-mail::message>
