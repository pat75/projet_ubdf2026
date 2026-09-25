/*
 * Visionneuse d'un book ouverte depuis sa carte (partials/visionneuse),
 * ex-ub_ill_plus_de_book.btn_slide de js_core_cards.js et le plugin
 * jQuery Swipebox. Le HTML reprend celui de Swipebox et du gabarit
 * tpl_book_open : les feuilles du front 2018 le mettent en forme.
 *
 * La carte porte ses donnees (<x-book-card>) : data-user,
 * data-user_detail (fiche) et data-slider (visuels).
 *
 * Mémo book : les books retenus par le visiteur, dans localStorage
 * (cle « books », meme format que le legacy pour garder les selections
 * existantes). Le legacy signalait aussi chaque ajout au serveur de
 * statistiques de production (extra-book.com) : retire.
 */
const MOBILE = 750;

function lireJson(texte, defaut) {
    try {
        return JSON.parse(texte) ?? defaut;
    } catch {
        return defaut;
    }
}

function memo(Alpine) {
    Alpine.store('memo', {
        books: [],

        init() {
            try {
                this.books = lireJson(localStorage.getItem('books'), []);
            } catch {
                this.books = []; // stockage indisponible (navigation privee...)
            }
        },

        contient(login) {
            return this.books.some((b) => b.id_user === login);
        },

        ajouter(login, slider) {
            if (this.contient(login)) return;
            this.books = [...this.books, { id_user: login, us_dir: login, us_memob_date: new Date(), slider: JSON.stringify(slider) }];
            try {
                localStorage.setItem('books', JSON.stringify(this.books));
            } catch {
                // Selection gardee pour la page courante seulement.
            }
        },
    });
}

export default function visionneuse(Alpine) {
    memo(Alpine);

    Alpine.store('visionneuse', {
        ouverte: false,
        login: '',
        fiche: {},
        slider: {},
        avatar: '',
        urlBook: '',
        images: [],
        index: 0,
        suivant: null, // carte du book suivant dans la grille

        get image() {
            return this.images[this.index] ?? {};
        },

        ouvrir(carte) {
            this.login = carte.dataset.user;
            this.fiche = lireJson(carte.dataset.user_detail, {});
            this.slider = lireJson(carte.dataset.slider, { book_img: [] });
            this.urlBook = carte.querySelector('[data-url]')?.dataset.url ?? '';
            // Vignette « _tiny » de la carte -> « _small » dans le panneau.
            this.avatar = (carte.querySelector('.avatar')?.getAttribute('src') ?? '')
                .replace(/vignette_home_(.*)_tiny(\.jpg|png|gif)/, 'vignette_home_$1_small$2');
            const petitEcran = window.screen.width < MOBILE;
            this.images = (this.slider.book_img ?? []).map((v) => ({
                src: (petitEcran && v.fichier_mobile) || v.fichier,
                titre: v.title ?? '',
            }));
            this.index = 0;
            this.suivant = carte.nextElementSibling?.matches('.ui.card[data-user]') ? carte.nextElementSibling : null;
            this.ouverte = true;
            document.documentElement.classList.add('swipebox-html');
            history.replaceState(null, '', `#${this.login}`);
        },

        fermer() {
            if (!this.ouverte) return;
            this.ouverte = false;
            document.documentElement.classList.remove('swipebox-html');
            history.replaceState(null, '', window.location.pathname + window.location.search);
        },

        allerA(index) {
            const total = this.images.length;
            if (!total) return;
            this.index = (index + total) % total; // boucle, comme loopAtEnd
        },

        suivante() {
            this.allerA(this.index + 1);
        },

        precedente() {
            this.allerA(this.index - 1);
        },

        bookSuivant() {
            if (this.suivant) {
                this.ouvrir(this.suivant);
            }
        },

        // Contact du creatif (fenetre « intermediaire »).
        contacter() {
            Alpine.store('modale').ouvrir('intermediaire');
        },

        memoriser() {
            Alpine.store('memo').ajouter(this.login, this.slider);
        },
    });

    document.addEventListener('keydown', (e) => {
        const v = Alpine.store('visionneuse');
        if (!v.ouverte || Alpine.store('modale').ouverte) return;
        if (e.key === 'ArrowRight') v.suivante();
        if (e.key === 'ArrowLeft') v.precedente();
        if (e.key === 'Escape') v.fermer();
    });
}
