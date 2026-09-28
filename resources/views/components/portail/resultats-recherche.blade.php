{{-- Suggestions d'un formulaire de recherche (resources/js/portail/recherche.js) :
     de simples etiquettes, identiques a celles de « Les recherches du
     moment » (/search) — couleurs coul_<domaine> via bloc_last_recherche.
     Mots-cles des books en tete, puis ceux du catalogue. --}}
<div class="suggestions_recherche bloc_last_recherche" x-show="resultats.length" x-cloak
     @mousedown.prevent>
    <template x-for="suggestion in resultats" :key="suggestion.domaine + suggestion.valeur">
        {{-- Pas de `basic` : son :hover Semantic blanchit le fond sous un
             texte blanc (voir portail.css, .suggestions_recherche). --}}
        <a class="ui large label cursor_effect" :class="suggestion.couleur ? 'coul_' + suggestion.couleur : ''"
           href="#" @click.prevent="choisir(suggestion)">
            <i class="chevron right icon"></i><strong x-text="suggestion.etiquette"></strong>
        </a>
    </template>
</div>
