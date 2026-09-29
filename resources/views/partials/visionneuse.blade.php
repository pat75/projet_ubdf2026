{{-- Visionneuse d'un book, ouverte depuis sa carte ($store.visionneuse,
     resources/js/portail/visionneuse.js). HTML repris de Swipebox et du
     gabarit Handlebars tpl_book_open : les feuilles du front 2018 (swipebox,
     core.css #book_open) le mettent en forme. --}}
<div id="swipebox-overlay" x-data x-show="$store.visionneuse.ouverte" x-cloak
     x-transition.opacity.duration.500ms
     @touchstart.passive="$el._x = $event.changedTouches[0].clientX"
     @touchend="(d => Math.abs(d) > 50 && (d < 0 ? $store.visionneuse.suivante() : $store.visionneuse.precedente()))($event.changedTouches[0].clientX - $el._x)">

    {{-- x-show et :style sur un meme element : Alpine restaure mal le
         display d'origine au retour. Le masquage passe donc par :style,
         avec les deux proprietes ensemble. --}}
    <div id="swipebox-slider" class="mfp-btn_next" style="transition: transform .4s ease;"
         :style="{ transform: `translateX(${-100 * $store.visionneuse.index}%)`, display: $store.visionneuse.contactOuvert ? 'none' : 'block' }"
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

    <div id="swipebox-caption" x-show="! $store.visionneuse.contactOuvert && $store.visionneuse.image.titre" x-text="$store.visionneuse.image.titre"></div>

    <div id="swipebox-action" x-show="! $store.visionneuse.contactOuvert">
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
                        </div>
                    </div>

                    <div class="ui list">
                        <div class="item localise_" x-show="$store.visionneuse.fiche.book_ville">
                            <i class="map marker alternate icon"></i>
                            <div class="ville"
                                 x-text="$store.visionneuse.fiche.book_pays ? $store.visionneuse.fiche.book_ville + ', ' + $store.visionneuse.fiche.book_pays : $store.visionneuse.fiche.book_ville"></div>
                        </div>
                        <div class="item link_goto_book_ sans_cadre">
                            <a :href="$store.visionneuse.urlBook" target="_blank" rel="noopener">
                                <svg class="icone_svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/>
                                </svg>
                                Book complet
                            </a>
                        </div>
                        <div class="item action_" x-show="! $store.visionneuse.contactOuvert">
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

                <div class="actions" x-show="! $store.visionneuse.contactOuvert">
                    <div class="ui horizontal list">
                        <div class="item">
                            <div class="memobook_add sans_cadre cursor_effect" title="{{ __('Ajouter au mémo book') }}"
                                 x-show="! $store.memo.contient($store.visionneuse.login)"
                                 @click="$store.visionneuse.memoriser()">
                                <svg class="icone_svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20.5S2 14.4 2 7.6A5 5 0 0 1 12 5a5 5 0 0 1 10 2.6c0 6.8-10 12.9-10 12.9z"/>
                                </svg>
                            </div>
                            <div class="no_button sans_cadre cursor_effect" x-show="$store.memo.contient($store.visionneuse.login)" x-cloak
                                 @click="$store.visionneuse.oublier()" title="{{ __('Retirer du mémo book') }}">
                                <svg class="icone_svg" viewBox="0 0 24 24" fill="#db2828" stroke="#db2828" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20.5S2 14.4 2 7.6A5 5 0 0 1 12 5a5 5 0 0 1 10 2.6c0 6.8-10 12.9-10 12.9z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ub_img_action" x-show="! $store.visionneuse.contactOuvert">
                    <div class="ub_icone precedent mfp-btn_prev cursor_effect" @click="$store.visionneuse.precedente()"></div>
                    <div class="ub_img_nb">
                        <strong x-text="$store.visionneuse.index + 1"></strong> /
                        <span class="ub_img_nb_total" x-text="$store.visionneuse.images.length"></span>
                    </div>
                    <div class="ub_icone suivant big mfp-btn_next cursor_effect" @click="$store.visionneuse.suivante()"></div>
                </div>

                @include('partials.visionneuse-contact')
            </div>
        </div>
    </div>
</div>
