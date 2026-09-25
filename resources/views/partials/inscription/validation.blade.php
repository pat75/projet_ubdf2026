{{-- Apres l'envoi : anneau de progression puis acces a l'espace. Meme x-data que le formulaire. --}}
<!-- validation : progression puis acces a l'espace -->
<div id="inscription_segment_validation" x-show="vue === 'validation'" :class="{ active: vue === 'validation' }" x-cloak>
    <canvas id="make_progress_canvas" x-ref="progression" x-show="! bravo"></canvas>
    <div class="inscription_segment_bravo" x-show="bravo" x-cloak
         x-transition.opacity.duration.400ms @click="allerEspace()">
        <h2 class="btn_acceder_espace">{{ __('Bravo, maintenant vous avez votre portfolio !') }}</h2>
        <h4 class="btn_acceder_espace">{{ __('Pour rejoindre la sélection placez au minimum une douzaine d’images') }}</h4>
        <h3 class="btn_acceder_espace">{{ __('Accédez à votre espace') }}<i class="ui arrow right teal icon"></i></h3>
    </div>
</div>
