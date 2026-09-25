/*
 * Portfolio en mosaique (book/ultra2020/portfolio), ex-portfolio.tlp.php
 * (resizeMasonryItem, imagesLoaded, chargement differe) et
 * portfolio.init_filter_* de core.js.
 *
 * - Mosaique : grille a lignes de 1px (.mosaique, book.css). Chaque visuel
 *   s'etend sur autant de lignes que la hauteur de son contenu, remesuree
 *   par un ResizeObserver : au chargement de l'image comme au
 *   redimensionnement, sans bibliotheque.
 * - Chargement progressif : le serveur marque les premiers visuels
 *   `loading="eager"` (premier ecran, priorite haute), les suivants
 *   `loading="lazy"` : le navigateur ne les demande qu'a l'approche du
 *   defilement. Un emplacement provisoire (aspect-ratio) evite de tout
 *   charger d'un coup quand les dimensions ne sont pas connues.
 * - Apparition : chaque visuel monte en fondu quand il est a la fois
 *   charge et visible, en cascade sur la ligne (comme l'accueil du portail).
 * - Filtre « les projets » : une rubrique ou toutes, les visuels
 *   reapparaissent en fondu.
 */
const reduit = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const CACHE = ['opacity-0', 'translate-y-8'];

export default function mosaique(Alpine) {
    Alpine.data('mosaique', () => ({
        filtre: 'all',
        libelle: '',
        menu: false,

        init() {
            this.libelle = this.$refs.libelle?.textContent.trim() ?? '';
            this.items = [...this.$el.querySelectorAll('[data-visuel]')];

            this.mesure = new ResizeObserver((entrees) => entrees.forEach(({ target }) => this.etendre(target.parentElement)));
            this.vue = new IntersectionObserver((entrees) => entrees.forEach((e) => {
                e.target._visible = e.isIntersecting;
                this.reveler(e.target);
            }), { rootMargin: '0px 0px -5% 0px' });

            this.items.forEach((item) => {
                const img = item.querySelector('img');
                const pret = () => {
                    item._charge = true;
                    img.style.removeProperty('aspect-ratio');
                    this.reveler(item);
                };

                if (!reduit()) item.classList.add(...CACHE);
                img.complete && img.naturalWidth ? pret() : img.addEventListener('load', pret, { once: true });
                img.addEventListener('error', pret, { once: true });

            });

            // Etendues calculees sur la grille en hauteur naturelle, puis
            // seulement passage en lignes de 1px (voir .mosaique.prete).
            this.items.forEach((item) => this.etendre(item));
            this.$refs.grille.classList.add('prete');
            this.items.forEach((item) => {
                this.mesure.observe(item.firstElementChild);
                this.vue.observe(item);
            });
        },

        destroy() {
            this.mesure?.disconnect();
            this.vue?.disconnect();
        },

        etendre(item) {
            const hauteur = item.firstElementChild.getBoundingClientRect().height;
            if (hauteur > 0) item.style.gridRowEnd = `span ${Math.ceil(hauteur)}`;
        },

        // Fondu a l'apparition, decale selon la colonne pour une cascade.
        reveler(item) {
            if (!item._charge || !item._visible || !item.classList.contains('opacity-0')) return;
            const colonne = Math.round(item.offsetLeft / Math.max(1, item.offsetWidth));
            item.style.transitionDelay = `${Math.min(colonne, 4) * 90}ms`;
            item.classList.remove(...CACHE);
        },

        filtrer(cle, libelle) {
            this.filtre = cle;
            this.libelle = libelle;
            this.menu = false;

            this.items.forEach((item) => {
                const garde = cle === 'all' || item.dataset.rubrique === cle;
                item.hidden = !garde;
                if (garde && !reduit()) {
                    item.style.transitionDelay = '0ms';
                    item.classList.add(...CACHE);
                }
            });

            // Laisse la grille se recomposer avant de rejouer l'apparition.
            requestAnimationFrame(() => requestAnimationFrame(() => this.items.forEach((item) => {
                if (!item.hidden) {
                    this.etendre(item);
                    this.reveler(item);
                }
            })));
        },

        // Visuels affiches, pour la visionneuse.
        visibles() {
            return this.items.filter((item) => !item.hidden);
        },

        ouvrir(item) {
            const liste = this.visibles();
            Alpine.store('visionneuse').ouvrir(liste.map((el) => ({
                src: el.dataset.grand,
                titre: el.dataset.titre,
                rubrique: el.dataset.nomRubrique,
                description: el.dataset.description,
            })), liste.indexOf(item));
        },
    }));
}
