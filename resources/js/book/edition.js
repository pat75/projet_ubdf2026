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

// sessionStorage peut etre indisponible (navigation privee...).
const memoire = {
    get(cle) {
        try { return sessionStorage.getItem('edition.' + cle); } catch { return null; }
    },
    set(cle, valeur) {
        try { sessionStorage.setItem('edition.' + cle, String(valeur)); } catch { /* sans consequence */ }
    },
};

export default function edition(Alpine) {
    // Page rechargee apres un reglage : ni fondu de la mosaique, ni
    // transition d'entree ; le createur voit son book tel quel, aussitot.
    // Aussi dans le cadre d'apercu, recharge avec la page.
    if (memoire.get('sansAnimation') === '1' || (window.self !== window.top && window.parent.document.documentElement.classList.contains('sans-animation'))) {
        document.documentElement.classList.add('sans-animation');
        memoire.set('sansAnimation', '0');
        setTimeout(() => document.documentElement.classList.remove('sans-animation'), 1500);
    }

    // Dans le cadre d'apercu (mobile, tablette) : le book tel qu'un visiteur le voit.
    const cadre = window.self !== window.top;

    Alpine.store('edition', {
        actif: !cadre,
        cadre,
        format: memoire.get('format') ?? 'desktop',

        choisirFormat(format) {
            this.format = format;
            memoire.set('format', format);
        },

        // Tablette : verticale ou horizontale.
        paysage: memoire.get('paysage') === '1',

        pivoter() {
            this.paysage = !this.paysage;
            memoire.set('paysage', this.paysage ? '1' : '0');
        },

        panneau: false,
        chargement: false,
        message: '',
        erreur: false,

        signaler(message, erreur = false) {
            this.message = message;
            this.erreur = erreur;
            clearTimeout(this.minuterie);
            this.minuterie = setTimeout(() => (this.message = ''), 3000);
        },
    });

    // Texte modifiable sur place (x-book.texte-editable), meme comportement
    // que x-espace.champ-editable de l'espace : clic sur le texte ou le
    // crayon, Entree ou sortie enregistre, Echap annule, coche 4 s.
    Alpine.data('texteBook', (cle) => ({
        edition: false,
        enregistrement: false,
        valide: false,
        avant: '',
        minuteur: null,

        // Le texte est dans un lien : en edition, le clic edite au lieu de naviguer.
        clic(e) {
            if (!Alpine.store('edition').actif) return;
            e.preventDefault();
            e.stopPropagation();
            this.editer();
        },

        editer() {
            if (this.edition) return;
            this.edition = true;
            this.avant = this.$refs.texte.textContent.trim();
            this.$nextTick(() => {
                const el = this.$refs.texte;
                el.focus();
                const plage = document.createRange();
                plage.selectNodeContents(el);
                plage.collapse(false);
                getSelection().removeAllRanges();
                getSelection().addRange(plage);
            });
        },

        touche(e) {
            if (e.key === 'Enter') { e.preventDefault(); this.$refs.texte.blur(); }
            if (e.key === 'Escape') {
                e.preventDefault();
                this.$refs.texte.textContent = this.avant;
                this.$refs.texte.blur();
            }
        },

        async valider() {
            if (!this.edition) return;
            this.edition = false;
            const valeur = this.$refs.texte.textContent.trim();
            if (valeur === this.avant) return;

            this.enregistrement = true;
            try {
                this.$refs.texte.textContent = await enregistrer(cle, valeur);
                this.valide = true;
                clearTimeout(this.minuteur);
                this.minuteur = setTimeout(() => (this.valide = false), 4000);
            } catch (e) {
                this.$refs.texte.textContent = this.avant;
                Alpine.store('edition').signaler(e.message, true);
            } finally {
                setTimeout(() => (this.enregistrement = false), 1200);
            }
        },
    }));

    // Apercu mobile / tablette : dimensions de l'appareil (ecran CSS reel +
    // contour) et echelle pour tenir dans la colonne, marge de 32px.
    Alpine.data('apercuAppareil', () => ({
        largeurZone: 0,
        hauteurZone: 0,

        init() {
            this.mesurer();
        },

        mesurer() {
            this.largeurZone = this.$refs.zone.clientWidth;
            this.hauteurZone = this.$refs.zone.clientHeight;
        },

        get mobile() {
            return Alpine.store('edition').format === 'mobile';
        },

        get bord() {
            return this.mobile ? 14 : 22;
        },

        get ecran() {
            if (this.mobile) return { l: 390, h: 844 };
            return Alpine.store('edition').paysage ? { l: 1180, h: 820 } : { l: 820, h: 1180 };
        },

        get total() {
            return { l: this.ecran.l + 2 * this.bord, h: this.ecran.h + 2 * this.bord };
        },

        get echelle() {
            if (!this.largeurZone) return 1;
            return Math.min(1, (this.largeurZone - 64) / this.total.l, (this.hauteurZone - 64) / this.total.h);
        },
    }));

    Alpine.data('reglagesBook', () => ({
        init() {
            this.$root.scrollTop = Number(memoire.get('defilement') ?? 0);
            memoire.set('defilement', 0);
        },

        async regler(cle, valeur, recharger = true) {
            try {
                await enregistrer(cle, valeur);
                if (recharger) {
                    // Le panneau reste en place : on garde son defilement.
                    Alpine.store('edition').chargement = true;
                    memoire.set('defilement', this.$root.scrollTop);
                    memoire.set('sansAnimation', '1');
                    window.location.reload();
                } else {
                    Alpine.store('edition').signaler('Enregistré');
                }
            } catch (e) {
                Alpine.store('edition').chargement = false;
                Alpine.store('edition').signaler(e.message, true);
            }
        },
    }));

    // Bloc repliable du panneau ; reste ouvert apres le rechargement.
    Alpine.data('blocReglage', (nom) => ({
        ouvert: memoire.get('bloc.' + nom) === '1',

        basculer() {
            this.ouvert = ! this.ouvert;
            memoire.set('bloc.' + nom, this.ouvert ? '1' : '0');
        },
    }));
}
