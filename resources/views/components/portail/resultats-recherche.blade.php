{{-- Suggestions d'un formulaire de recherche (resources/js/portail/recherche.js),
     dans le HTML des resultats de Semantic search (type category). --}}
<div class="results transition" :class="{ visible: resultats.length }" x-show="resultats.length" x-cloak
     @mousedown.prevent>
    <template x-for="[domaine, liste] in groupes" :key="domaine">
        <div class="category" :class="'cat_' + domaine">
            <div class="name" x-text="domaine"></div>
            <div class="results">
                <template x-for="suggestion in liste" :key="suggestion.valeur">
                    <a class="result txtblanc" :class="'coul_' + domaine" href="#" @click.prevent="choisir(suggestion)">
                        <div class="content">
                            <div class="title" x-text="suggestion.titre"></div>
                            <div class="description">
                                <span x-text="suggestion.description"></span>
                                <template x-if="suggestion.alias">
                                    <span class="dom_metier"><span class="fonticon-arrow-right icon"></span><span x-text="suggestion.alias"></span></span>
                                </template>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </template>
</div>
