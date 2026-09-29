{{-- Aperçu du corps tel qu'il partira : contenu déjà nettoyé à l'enregistrement. --}}
<div class="prose max-w-none bg-white p-6 text-[15px] leading-relaxed text-zinc-900">
    {!! str_replace('{prenom}', e(__('Camille')), (string) $campagne->body) !!}
</div>
