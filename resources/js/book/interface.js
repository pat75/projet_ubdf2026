/*
 * Elements communs des books Ultra-frais / Ultra-zen, ex-core.js :
 *
 * - x-data="soulignement" : le trait sous le menu glisse vers le lien
 *   survole et revient sur la page courante (ex-plugin jQuery LavaLamp) ;
 * - x-data="curseur" : cercle qui suit la souris et grossit sur les
 *   elements [data-curseur] (reglage « cursor » du createur, ub_fn_cursor) ;
 *   ecrans tactiles exclus ;
 * - x-data="hautDePage" : bouton de retour en haut apres un ecran de
 *   defilement (nav.nav_top, ex-Semantic visibility).
 */
export default function interfaceBook(Alpine) {
    Alpine.data('soulignement', () => ({
        trait: { left: 0, width: 0, opacity: 0 },

        init() {
            this.$nextTick(() => this.revenir());
            window.addEventListener('resize', () => this.revenir());
        },

        placer(el) {
            if (!el) return;
            this.trait = { left: el.offsetLeft, width: el.offsetWidth, opacity: 1 };
        },

        revenir() {
            const actif = this.$el.querySelector('[aria-current="page"]');
            actif ? this.placer(actif) : (this.trait = { ...this.trait, opacity: 0 });
        },
    }));

    Alpine.data('curseur', () => ({
        actif: false,
        x: -100,
        y: -100,
        gros: false,

        init() {
            if (!window.matchMedia('(pointer: fine)').matches) return;
            this.actif = true;
            document.addEventListener('mousemove', (e) => {
                this.x = e.clientX;
                this.y = e.clientY;
                this.gros = !!e.target.closest?.('[data-curseur], a, button');
            });
        },
    }));

    Alpine.data('hautDePage', () => ({
        visible: false,

        init() {
            const suivre = () => (this.visible = window.scrollY > window.innerHeight * 0.8);
            window.addEventListener('scroll', suivre, { passive: true });
            suivre();
        },

        monter() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    }));
}
