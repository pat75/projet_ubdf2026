/*
 * Mode edition du book, pour son createur (ex-core_admin.js : admin_edit,
 * popup_open / popup_save, fn_theme, fn_visuel_size, fn_cursor...).
 *
 * - $store.edition.actif : decorations d'edition affichees ; « Apercu
 *   visiteur » les masque sans quitter la session.
 * - x-data="texteBook('titre')" : texte modifiable sur place (titre,
 *   description, intitules du menu, titre du contact). Clic, saisie,
 *   Entree ou sortie : enregistre ; Echap : annule. Rien n'est envoye si
 *   le texte n'a pas change.
 * - x-data="reglagesBook" : panneau des reglages (fond, taille des
 *   visuels, en-tete, curseur, reseaux, pied de page, CSS expert). Un
 *   reglage de mise en page recharge la page une fois enregistre.
 *
 * POST /reglages (EditionBookController::enregistrer) : {cle, valeur}.
 */
async function enregistrer(cle, valeur) {
    const reponse = await fetch('/reglages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify({ cle, valeur }),
    });
    const retour = await reponse.json().catch(() => ({}));
    if (!reponse.ok) throw new Error(retour.erreur ?? 'Enregistrement impossible.');

    return retour.valeur;
}

export default function edition(Alpine) {
    Alpine.store('edition', {
        actif: true,
        panneau: false,
        message: '',
        erreur: false,

        signaler(message, erreur = false) {
            this.message = message;
            this.erreur = erreur;
            clearTimeout(this.minuterie);
            this.minuterie = setTimeout(() => (this.message = ''), 3000);
        },
    });

    Alpine.data('texteBook', (cle) => ({
        enCours: false,

        init() {
            // Un texte place dans un lien : le clic edite au lieu de naviguer.
            this.$el.addEventListener('click', (e) => {
                if (!Alpine.store('edition').actif) return;
                e.preventDefault();
                this.editer();
            });
            this.$el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); this.$el.blur(); }
                if (e.key === 'Escape') { this.$el.textContent = this.avant; this.$el.blur(); }
            });
            this.$el.addEventListener('blur', () => this.terminer());
        },

        editer() {
            if (this.enCours) return;
            this.enCours = true;
            this.avant = this.$el.textContent.trim();
            this.$el.contentEditable = 'plaintext-only';
            this.$el.focus();
            const plage = document.createRange();
            plage.selectNodeContents(this.$el);
            plage.collapse(false);
            getSelection().removeAllRanges();
            getSelection().addRange(plage);
        },

        async terminer() {
            if (!this.enCours) return;
            this.enCours = false;
            this.$el.contentEditable = 'false';
            const valeur = this.$el.textContent.trim();
            if (valeur === this.avant) return;

            try {
                this.$el.textContent = await enregistrer(cle, valeur);
                Alpine.store('edition').signaler('Enregistré');
            } catch (e) {
                this.$el.textContent = this.avant;
                Alpine.store('edition').signaler(e.message, true);
            }
        },
    }));

    Alpine.data('reglagesBook', () => ({
        async regler(cle, valeur, recharger = true) {
            try {
                await enregistrer(cle, valeur);
                if (recharger) {
                    window.location.reload();
                } else {
                    Alpine.store('edition').signaler('Enregistré');
                }
            } catch (e) {
                Alpine.store('edition').signaler(e.message, true);
            }
        },
    }));
}
