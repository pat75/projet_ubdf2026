{{-- Apres l'envoi : anneau de progression et texte en machine a ecrire, puis
     acces a l'espace. Meme x-data que le formulaire (resources/js/portail/inscription.js). --}}
<div id="inscription_segment_validation" x-show="vue === 'validation'" :class="{ active: vue === 'validation' }" x-cloak>
    {{-- Premiere phrase en machine a ecrire, seconde en fondu, meme taille et meme couleur ;
         l'anneau de progression en dessous. --}}
    <p class="inscription_machine" x-ref="machine" aria-live="polite"
       data-phrase1="{{ __('Préparation de votre espace et création de votre book') }}">
        {{-- Chaque phrase a son emplacement fixe : la premiere est ecrite par-dessus son propre
             texte invisible (meme coupure de lignes, rien ne bouge pendant la frappe), la seconde
             garde sa place des le depart et n'apparait qu'en fondu (3 s). --}}
        <span class="inscription_machine_zone">
            <span class="inscription_machine_fantome" aria-hidden="true">{{ __('Préparation de votre espace et création de votre book') }}</span>
            <span class="inscription_machine_ecrit"><span x-text="lignes[0]"></span><span class="inscription_machine_curseur" x-show="! pret" aria-hidden="true"></span></span>
        </span>
        <span class="inscription_machine_zone inscription_machine_pret" :class="{ visible: pret }">{{ __('Votre portfolio est maintenant prêt !') }}</span>
    </p>

    {{-- Juste sous la seconde phrase. Place reservee des le depart (opacite, pas display) :
         en apparaissant, il ne decale rien. --}}
    <div class="inscription_segment_bravo" :class="{ visible: bravo }" @click="allerEspace()">
        <h4 class="btn_acceder_espace">{{ __('Pour rejoindre la sélection placez au minimum une douzaine d’images') }}</h4>
        <h3 class="btn_acceder_espace" style="margin-top: 2.5em">{{ __('Accéder à mon compte') }}<i class="ui arrow right teal icon" style="margin-left: .6em"></i></h3>
    </div>

    {{-- Hauteur gardee quand l'anneau disparait : le reste ne remonte pas. --}}
    <canvas id="make_progress_canvas" x-ref="progression" :style="progression >= 100 && 'visibility: hidden'"></canvas>
</div>
