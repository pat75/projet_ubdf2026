{{-- Visionneuse d'un book, ouverte depuis sa carte ($store.visionneuse,
     resources/js/portail/visionneuse.js). HTML repris de Swipebox et du
     gabarit Handlebars tpl_book_open : les feuilles du front 2018 (swipebox,
     core.css #book_open) le mettent en forme. --}}
<div id="swipebox-overlay" x-data x-show="$store.visionneuse.ouverte" x-cloak
     x-transition.opacity.duration.500ms
     @touchstart.passive="$el._x = $event.changedTouches[0].clientX"
     @touchend="(d => Math.abs(d) > 50 && (d < 0 ? $store.visionneuse.suivante() : $store.visionneuse.precedente()))($event.changedTouches[0].clientX - $el._x)">

    <div id="swipebox-slider" class="mfp-btn_next" style="display: block; transition: transform .4s ease;"
         :style="{ transform: `translateX(${-100 * $store.visionneuse.index}%)` }"
         @click="$store.visionneuse.suivante()">
        <template x-for="(image, i) in $store.visionneuse.images" :key="i">
            <div class="slide" :class="{ current: i === $store.visionneuse.index }">
                {{-- Seules l'image courante et ses voisines sont chargees. --}}
                <template x-if="Math.abs(i - $store.visionneuse.index) <= 1">
                    <img :src="image.src" :alt="image.titre">
                </template>
            </div>
        </template>
    </div>

    <div id="swipebox-caption" x-text="$store.visionneuse.image.titre"></div>

    <div id="swipebox-action">
        <a id="swipebox-prev" class="hide-desktop" @click.stop="$store.visionneuse.precedente()"></a>
        <a id="swipebox-next" class="hide-desktop" @click.stop="$store.visionneuse.suivante()"></a>
    </div>
    <a id="swipebox-close" @click="$store.visionneuse.fermer()"></a>

    <div class="mfp-container">
        <div id="book_open">
            <div id="book_fd_top"></div>

            <template x-if="$store.visionneuse.suivant">
                <div id="mfp-book_suivant" @click="$store.visionneuse.bookSuivant()">
                    <a id="mfp-book_suivant_lien">
                        <div class="suivant_legende">book suivant</div>
                        <div class="suivant"></div>
                    </a>
                </div>
            </template>

            <div class="ui container no-margin">
                <h2 class="ui header">
                    <img :src="$store.visionneuse.avatar" class="ui circular image" :alt="$store.visionneuse.fiche.book_prenom_nom"
                         x-show="$store.visionneuse.avatar">
                    <div class="content">
                        <span x-text="$store.visionneuse.fiche.book_prenom_nom"></span>
                        <div class="sub header">
                            <span x-text="$store.visionneuse.fiche.book_type"></span>
                            <span x-text="$store.visionneuse.fiche.book_statut"></span>
                        </div>
                    </div>

                    <div class="ui list">
                        <div class="item localise_" x-show="$store.visionneuse.fiche.book_ville">
                            <i class="map marker alternate icon"></i>
                            <div class="ville" x-text="$store.visionneuse.fiche.book_ville"></div>
                            <div class="pays" x-text="$store.visionneuse.fiche.book_pays"></div>
                        </div>
                        <div class="item link_goto_book_">
                            <a :href="$store.visionneuse.urlBook" target="_blank" rel="noopener"><i class="icon folder outline"></i>Book complet</a>
                        </div>
                        <div class="item action_">
                            <div id="msg_send" class="ui left labeled button ubdf_bouton link_ultra-book_contact cursor_effect transition visible"
                                 title="Je souhaiterais vous contacter" @click="$store.visionneuse.contacter()">
                                <div class="ui grey right pointing label">
                                    <i class="envelope icon"></i>
                                </div>
                                <div class="ui basic inverted button">Contacter</div>
                            </div>
                        </div>
                    </div>
                </h2>

                <div class="actions">
                    <div class="ui horizontal list">
                        <div class="item" id="dispo">
                            <button type="button" class="ui icon button"
                                    :class="$store.visionneuse.fiche.book_dispo === 'true' ? 'olive' : 'orange'"
                                    :title="$store.visionneuse.fiche.book_dispo === 'true' ? 'Je suis disponible actuellement pour une commande' : 'Je ne suis pas disponible pour le moment'">
                                <i class="icon" :class="$store.visionneuse.fiche.book_dispo === 'true' ? 'coffee' : 'plane'"></i>
                            </button>
                        </div>
                        <div class="item">
                            <div class="ui inverted basic button memobook_add cursor_effect"
                                 x-show="! $store.memo.contient($store.visionneuse.login)"
                                 @click="$store.visionneuse.memoriser()">
                                <i class="heart icon"></i><span> Mémoriser</span>
                            </div>
                            <div class="no_button" x-show="$store.memo.contient($store.visionneuse.login)" x-cloak>
                                <i class="heart red icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ub_img_action">
                    <div class="ub_icone precedent mfp-btn_prev cursor_effect" @click="$store.visionneuse.precedente()"></div>
                    <div class="ub_img_nb">
                        <strong x-text="$store.visionneuse.index + 1"></strong> /
                        <span class="ub_img_nb_total" x-text="$store.visionneuse.images.length"></span>
                    </div>
                    <div class="ub_icone suivant big mfp-btn_next cursor_effect" @click="$store.visionneuse.suivante()"></div>
                </div>
            </div>
        </div>
    </div>
</div>
