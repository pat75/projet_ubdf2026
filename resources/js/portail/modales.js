/*
 * Fenetres modales du portail (<x-portail.modale>), a la place du module
 * modal de Semantic UI : une seule ouverte a la fois, fermee par Echap.
 *
 * Les rappels `apresOuverture` / `apresFermeture` reprennent les onShow /
 * onHidden de Semantic, dont le JavaScript de 2019 se sert encore (voir
 * js2019/pont_alpine.js).
 */
export default function modales(Alpine) {
    Alpine.store('modale', {
        ouverte: null,
        rappels: {},

        ouvrir(nom) {
            if (this.ouverte && this.ouverte !== nom) {
                this.fermer();
            }
            this.ouverte = nom;
            document.body.classList.add('dimmable', 'dimmed', 'scrolling');
            Alpine.nextTick(() => {
                // Comme l'option autofocus de Semantic : premier champ visible.
                const champ = [...document.querySelectorAll(`[data-modale="${nom}"] input:not([type=hidden]), [data-modale="${nom}"] textarea`)]
                    .find((el) => el.offsetParent !== null && !el.disabled);
                champ?.focus({ preventScroll: true });
                this.rappels[nom]?.apresOuverture?.();
            });
        },

        fermer() {
            const nom = this.ouverte;
            if (!nom) {
                return;
            }
            this.ouverte = null;
            document.body.classList.remove('dimmed', 'scrolling');
            setTimeout(() => this.rappels[nom]?.apresFermeture?.(), 300);
        },

        reglages(nom, rappels) {
            this.rappels[nom] = { ...this.rappels[nom], ...rappels };
        },
    });

    // En capture, et consomme la touche : Echap ferme la fenetre, pas
    // aussi la visionneuse posee dessous (son ecouteur passe ensuite).
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && Alpine.store('modale').ouverte) {
            e.stopPropagation();
            Alpine.store('modale').fermer();
        }
    }, true);
}
