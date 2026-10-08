{{-- Visionneuse d'un book, ouverte depuis sa carte ($store.visionneuse,
     resources/js/portail/visionneuse.js). Mise en page propre (#vn, classes
     vn-*, styles dans resources/css/portail.css) : plus rien de Swipebox ni
     de core.css. Mobile : une colonne (barre, fiche, actions, image,
     legende, compteur) ; desktop : fiche et actions sur une ligne. --}}
<div id="vn" x-data x-show="$store.visionneuse.ouverte" x-cloak
     :class="$store.visionneuse.glissement && 'vn-' + $store.visionneuse.glissement"
     x-transition.opacity.duration.300ms
     role="dialog" aria-modal="true" :aria-label="$store.visionneuse.fiche.book_prenom_nom">
    <div class="vn-cadre">
        {{-- Barre du haut : fermer, book suivant. --}}
        <div class="vn-barre">
            {{-- Croix et « book suivant » de la version precedente : deux
                 traits fins, et un chevron en filet de 1px. --}}
            <button type="button" class="vn-fermer" @click="$store.visionneuse.fermer()" aria-label="{{ __('Fermer') }}"></button>
            <button type="button" class="vn-suivant" x-show="$store.visionneuse.suivant" @click="$store.visionneuse.bookSuivant()">
                <span class="vn-suivant-legende">book suivant</span>
                <span class="vn-chevron" aria-hidden="true"></span>
            </button>
        </div>

        {{-- Fiche du createur et actions. --}}
        <div class="vn-entete">
            <div class="vn-identite">
                <div class="vn-avatar">
                    <img x-show="$store.visionneuse.avatar" :src="$store.visionneuse.avatar" alt="">
                    <span x-show="! $store.visionneuse.avatar" x-text="$store.visionneuse.initiales"></span>
                </div>
                <div class="vn-noms">
                    <div class="vn-nom" x-text="$store.visionneuse.fiche.book_prenom_nom"></div>
                    {{-- Domaine / statut / lieu sur une seule ligne, meme taille :
                         seules la graisse et la teinte de gris les distinguent. --}}
                    <div class="vn-domaine" x-show="$store.visionneuse.fiche.book_type || $store.visionneuse.fiche.book_statut_libelle || $store.visionneuse.lieu">
                        <span class="vn-type" x-text="$store.visionneuse.fiche.book_type"></span><span class="vn-statut" x-show="$store.visionneuse.fiche.book_statut_libelle" x-text="$store.visionneuse.fiche.book_statut_libelle"></span><span class="vn-lieu" x-show="$store.visionneuse.lieu"><span class="vn-barre-oblique" x-show="$store.visionneuse.fiche.book_type || $store.visionneuse.fiche.book_statut_libelle">/</span><span x-text="$store.visionneuse.lieu"></span></span>
                    </div>
                </div>
            </div>

            <div class="vn-actions">
                {{-- Memoriser (store Alpine `memo`) : un second clic retire. --}}
                <button type="button" class="vn-action vn-memo"
                        :aria-label="$store.memo.contient($store.visionneuse.login) ? @js(__('Mémorisé')) : @js(__('Mémoriser'))"
                        :class="{ 'vn-memorise': $store.memo.contient($store.visionneuse.login) }"
                        :aria-pressed="$store.memo.contient($store.visionneuse.login)"
                        @click="$store.memo.contient($store.visionneuse.login) ? $store.visionneuse.oublier() : $store.visionneuse.memoriser()">
                    <svg class="vn-icone vn-icone-petite" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 20s-7.5-4.6-7.5-10.2A4.3 4.3 0 0 1 12 7.2a4.3 4.3 0 0 1 7.5 2.6C19.5 15.4 12 20 12 20z"/>
                    </svg>
                    <span class="vn-memo-libelle" x-text="$store.memo.contient($store.visionneuse.login) ? @js(__('Mémorisé')) : @js(__('Mémoriser'))">{{ __('Mémoriser') }}</span>
                </button>
                <a class="vn-action" :href="$store.visionneuse.urlBook" target="_blank" rel="noopener">
                    <svg class="vn-icone vn-icone-petite" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 6.5C10 5 7 4.5 3.5 5v13c3.5-.5 6.5 0 8.5 1.5 2-1.5 5-2 8.5-1.5V5C17 4.5 14 5 12 6.5zM12 6.5v13"/>
                    </svg>
                    <span>{{ __('Book complet') }}</span>
                </a>
                <button type="button" class="vn-action vn-action-plein" id="vn-contacter"
                        :class="{ 'vn-actif': $store.visionneuse.contactOuvert }"
                        @click="$store.visionneuse.contactOuvert ? $store.visionneuse.contactOuvert = false : $store.visionneuse.contacter()">
                    <svg class="vn-icone vn-icone-petite" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3.5 6.5h17v11h-17zM3.5 7l8.5 6.5L20.5 7"/>
                    </svg>
                    <span>{{ __('Contacter') }}</span>
                </button>
            </div>
        </div>

        {{-- Image : un clic avance, glisser au doigt change d'image. --}}
        <div class="vn-scene" x-show="! $store.visionneuse.contactOuvert"
             style="cursor: url('/img_front/ub_f_droite.png'), pointer"
             @touchstart.passive="$el._x = $event.changedTouches[0].clientX"
             @touchend="(d => Math.abs(d) > 50 && (d < 0 ? $store.visionneuse.suivante() : $store.visionneuse.precedente()))($event.changedTouches[0].clientX - $el._x)">
            <div class="vn-piste" :class="{ 'vn-sans-transition': $store.visionneuse.sansTransition }"
                 :style="`transform: translateX(${-100 * $store.visionneuse.position}%)`"
                 @click="$store.visionneuse.cliquer()">
                <template x-for="(image, i) in $store.visionneuse.diapos" :key="i">
                    <div class="vn-diapo" :class="{ 'vn-courante': i === $store.visionneuse.position }">
                        {{-- Seules l'image courante et ses voisines sont chargees. --}}
                        <template x-if="Math.abs(i - $store.visionneuse.position) <= 1 && ! (image.video && $store.visionneuse.lecture && i === $store.visionneuse.position)">
                            <img :src="image.src" :alt="image.titre">
                        </template>
                        {{-- Video : sa vignette (bouton lecture compris), puis le lecteur au clic. --}}
                        <template x-if="image.video && $store.visionneuse.lecture && i === $store.visionneuse.position">
                            <iframe class="vn-video" :src="image.video" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <div class="vn-legende" x-show="! $store.visionneuse.contactOuvert" x-text="$store.visionneuse.image.titre"></div>

        <div class="vn-contact" x-show="$store.visionneuse.contactOuvert" x-cloak>
            @include('partials.visionneuse-contact')
        </div>

        {{-- Folio de la version precedente : chevron fin, « 1 / 6 », grand
             chevron. Le precedent est masque sur mobile (glisser au doigt). --}}
        <div class="vn-folio" x-show="! $store.visionneuse.contactOuvert">
            <button type="button" class="vn-folio-precedent" @click="$store.visionneuse.precedente()" aria-label="{{ __('Image précédente') }}"></button>
            <span class="vn-folio-nb"><strong x-text="$store.visionneuse.index + 1"></strong> / <span x-text="$store.visionneuse.images.length"></span></span>
            <button type="button" class="vn-folio-suivant" @click="$store.visionneuse.suivante()" aria-label="{{ __('Image suivante') }}"></button>
        </div>
    </div>
</div>
