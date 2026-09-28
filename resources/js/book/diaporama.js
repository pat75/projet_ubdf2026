/*
 * Diaporama du portfolio (book/responsive/portfolio), ex-Fotorama 4.6
 * (ub_fotorama_mdl2014 de core.js) : une image a la fois, fondu entre les
 * images, precedente / suivante en boucle, vignettes ou points, clavier,
 * glisser du doigt, plein ecran natif.
 *
 * Les images sont dans la page (le serveur les rend, pour le
 * referencement) : seules la courante et ses voisines sont chargees.
 */
export default function diaporama(Alpine) {
    Alpine.data('diaporama', (total = 0) => ({
        index: 0,
        total,
        pleinEcran: false,

        init() {
            // Lien direct vers une image : #3 ouvre la troisieme.
            const n = parseInt(location.hash.slice(1), 10);
            if (n > 0 && n <= this.total) this.index = n - 1;

            document.addEventListener('fullscreenchange', () => (this.pleinEcran = document.fullscreenElement === this.$root));
        },

        aller(pas) {
            if (this.total) this.voir((this.index + pas + this.total) % this.total);
        },

        voir(i) {
            this.index = i;
            history.replaceState(null, '', `#${i + 1}`);
            // Garde la vignette courante visible dans la bande.
            this.$nextTick(() => this.$refs.vignettes?.children[i]?.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' }));
        },

        // Charge la courante et ses voisines, pas les autres.
        proche(i) {
            const d = Math.abs(i - this.index);

            return d <= 1 || d === this.total - 1;
        },

        clavier(e) {
            if (e.key === 'ArrowRight') this.aller(1);
            if (e.key === 'ArrowLeft') this.aller(-1);
        },

        debutGlisser(e) {
            this._x = e.changedTouches[0].clientX;
        },

        finGlisser(e) {
            const d = e.changedTouches[0].clientX - this._x;
            if (Math.abs(d) > 50) this.aller(d < 0 ? 1 : -1);
        },

        basculerPleinEcran() {
            document.fullscreenElement ? document.exitFullscreen() : this.$root.requestFullscreen?.();
        },
    }));
}
