/*
 * Visionneuse du portfolio (book/ultra2020/_visionneuse), ex-Magnific Popup
 * (portfolio.init_open de core.js) : image en grand, legende (rubrique,
 * titre, description), precedente / suivante en boucle, clavier, glisser
 * du doigt, fermeture au fond ou a Echap.
 */
export default function visionneuse(Alpine) {
    Alpine.store('visionneuse', {
        ouverte: false,
        images: [],
        index: 0,

        get image() {
            return this.images[this.index] ?? {};
        },

        ouvrir(images, index) {
            this.images = images;
            this.index = Math.max(0, index);
            this.ouverte = true;
            document.documentElement.classList.add('overflow-hidden');
        },

        fermer() {
            this.ouverte = false;
            document.documentElement.classList.remove('overflow-hidden');
        },

        aller(pas) {
            const total = this.images.length;
            if (total) this.index = (this.index + pas + total) % total;
        },
    });

    document.addEventListener('keydown', (e) => {
        const v = Alpine.store('visionneuse');
        if (!v.ouverte) return;
        if (e.key === 'Escape') v.fermer();
        if (e.key === 'ArrowRight') v.aller(1);
        if (e.key === 'ArrowLeft') v.aller(-1);
    });
}
