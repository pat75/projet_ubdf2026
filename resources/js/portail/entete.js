/*
 * En-tete du portail (partials/header, partials/modals), ex-ubdf_accueil
 * de js_core_pages.js : infobulles du menu, menu plein ecran, recherche
 * mobile, bouton de retour en haut.
 */

/*
 * x-infobulle="'.popup_ptf'" : au survol de l'element, affiche le popup
 * Semantic designe (classes transition hidden -> visible), centre sous
 * lui. Le popup reste ouvert tant que la souris est dessus (hoverable).
 * Modificateur : x-infobulle.lent garde le popup 400 ms apres la sortie.
 */
function infobulle(Alpine) {
    Alpine.directive('infobulle', (el, { expression, modifiers }, { evaluate, cleanup }) => {
        const popup = document.querySelector(evaluate(expression));
        if (!popup) return;

        // Rattache au body : le popup des metiers est range dans un bloc du
        // menu masque, ou il resterait invisible (Semantic le deplacait).
        if (popup.parentElement !== document.body) {
            document.body.appendChild(popup);
        }

        const delaiMasquage = modifiers.includes('lent') ? 400 : 100;
        let minuterie = null;

        /*
         * En `fixed` sur la fenetre, centre sous le declencheur. En
         * `!important` : core.css (2019) pose sur ces popups des marges et
         * des positions forcees (.popup_memobook margin-left -100px,
         * .popup_ptf 76px/16px, top/right sur mobile) qui les decalaient.
         */
        const placer = () => {
            const cible = el.getBoundingClientRect();
            const largeur = popup.offsetWidth;
            const gauche = Math.min(
                Math.max(8, cible.left + cible.width / 2 - largeur / 2),
                window.innerWidth - largeur - 8,
            );
            const styles = {
                position: 'fixed',
                top: `${cible.bottom + 36}px`,
                left: `${Math.max(8, gauche)}px`,
                right: 'auto',
                bottom: 'auto',
                margin: '0',
                transform: 'none',
            };
            for (const [propriete, valeur] of Object.entries(styles)) {
                popup.style.setProperty(propriete, valeur, 'important');
            }
        };
        const montrer = () => {
            clearTimeout(minuterie);
            popup.classList.remove('hidden');
            popup.classList.add('visible', 'bottom', 'center');
            placer();
        };
        const cacher = () => {
            clearTimeout(minuterie);
            minuterie = setTimeout(() => {
                popup.classList.remove('visible');
                popup.classList.add('hidden');
            }, delaiMasquage);
        };

        /*
         * Sur le popup lui-meme : le garder ouvert, sans le replacer. Un
         * meme popup peut avoir plusieurs declencheurs (l'icone filtre et
         * l'item « METIERS », masque) : chacun le replacerait sous lui, et
         * le dernier inscrit — masque, donc en 0,0 — l'envoyait en haut a
         * gauche des que la souris y entrait.
         */
        const garder = () => clearTimeout(minuterie);

        el.addEventListener('mouseenter', montrer);
        el.addEventListener('mouseleave', cacher);
        popup.addEventListener('mouseenter', garder);
        popup.addEventListener('mouseleave', cacher);
        cleanup(() => {
            el.removeEventListener('mouseenter', montrer);
            el.removeEventListener('mouseleave', cacher);
            popup.removeEventListener('mouseenter', garder);
            popup.removeEventListener('mouseleave', cacher);
        });
    });
}

export default function entete(Alpine) {
    infobulle(Alpine);

    /* Menu plein ecran (bouton ☰) : memes classes `fs` que le legacy. */
    Alpine.store('menu', {
        ouvert: false,

        basculer() {
            this.ouvert = !this.ouvert;
            document.querySelectorAll('.menu-bg, .menu-burger, #overlay-menu, #menu-top-fixed, #menu-top-fixed-mobile')
                .forEach((el) => el.classList.toggle('fs', this.ouvert));
        },
    });

    /* Barre mobile : bascule entre le logo et le champ de recherche. */
    Alpine.data('barreMobile', () => ({
        recherche: false,
    }));

    /* Bouton de retour en haut, affiche une fois l'en-tete depasse. */
    Alpine.data('retourHaut', () => ({
        init() {
            const repere = document.querySelector('.top_position_show');
            if (!repere) return;
            new IntersectionObserver(([entree]) => {
                this.$el.classList.toggle('show', !entree.isIntersecting && entree.boundingClientRect.top < 0);
            }).observe(repere);
        },

        remonter() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    }));
}
