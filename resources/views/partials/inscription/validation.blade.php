{{-- Apres l'envoi : anneau de progression puis acces a l'espace. Meme x-data que le formulaire. --}}
<!-- validation : progression puis acces a l'espace -->
<div id="inscription_segment_validation" x-show="vue === 'validation'" :class="{ active: vue === 'validation' }" x-cloak>
    <canvas id="make_progress_canvas" x-ref="progression" x-show="! bravo"></canvas>

    {{-- Machine a ecrire, mot a mot, pendant la progression (resources/js/portail/inscription.js). --}}
    <p class="inscription_machine" x-ref="machine" x-show="! bravo" aria-live="polite"
       data-texte="{{ __('Création de votre portfolio') }} -stop- {{ __('préparation de votre espace') }} -stop- {{ __('mise en ligne de votre book') }}">
        <template x-for="(ligne, i) in lignes" :key="i">
            <span class="inscription_machine_ligne"><span x-text="ligne"></span><span class="inscription_machine_curseur" x-show="i === lignes.length - 1" aria-hidden="true"></span></span>
        </template>
    </p>
    <div class="inscription_segment_bravo" x-show="bravo" x-cloak
         x-transition.opacity.duration.400ms @click="allerEspace()">
        <h2 class="btn_acceder_espace">{{ __('Bravo, maintenant vous avez votre portfolio !') }}</h2>
        <h4 class="btn_acceder_espace">{{ __('Pour rejoindre la sélection placez au minimum une douzaine d’images') }}</h4>
        <h3 class="btn_acceder_espace" style="margin-top: 2.5em">{{ __('Accéder à mon compte') }}<i class="ui arrow right teal icon" style="margin-left: .6em"></i></h3>
    </div>
</div>
