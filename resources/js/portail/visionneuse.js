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

/*
 * Memo book (store `memo`).
 *
 * Connecte (visiteur ou creatif), la selection vit en base : chaque
 * ajout ou retrait passe par /memo/* (MemoController), qui rend la liste
 * a jour. Anonyme, elle reste dans le localStorage (cle `books` du
 * legacy) et le premier coeur de la session propose d'ouvrir un compte
 * visiteur (fenetre `memo-compte`), qui recoit alors cette selection.
 */
const CLE_LOCALE = 'books';
const CLE_PROPOSE = 'memo_compte_propose';

function lireLocal() {
    try {
        return lireJson(localStorage.getItem(CLE_LOCALE), []).map((b) => b.id_user).filter(Boolean);
    } catch {
        return []; // stockage indisponible (navigation privee...)
    }
}

function ecrireLocal(logins) {
    try {
        if (logins.length) {
            localStorage.setItem(CLE_LOCALE, JSON.stringify(logins.map((l) => ({ id_user: l, us_dir: l }))));
        } else {
            localStorage.removeItem(CLE_LOCALE);
        }
    } catch {
        // Selection gardee pour la page courante seulement.
    }
}

async function poster(url, donnees) {
    const reponse = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify(donnees),
    });
    if (!reponse.ok) throw new Error(`memo ${reponse.status}`);
    return reponse.json();
}

function memo(Alpine) {
    Alpine.store('memo', {
        logins: [],
        connecte: false,
        visiteur: false,

        // Compatibilite avec les gabarits : `$store.memo.books.length`.
        get books() {
            return this.logins;
        },

        init() {
            const serveur = window.ubdf?.memo ?? {};
            this.connecte = !!serveur.connecte;
            this.visiteur = !!serveur.visiteur;

            if (!this.connecte) {
                this.logins = lireLocal();
                return;
            }

            this.logins = serveur.logins ?? [];

            // Une selection faite avant de se connecter rejoint le compte.
            const locale = lireLocal();
            if (locale.length) {
                poster('/memo/fusionner', { logins: locale })
                    .then((r) => { this.logins = r.logins; ecrireLocal([]); })
                    .catch(() => {});
            }
        },

        contient(login) {
            return this.logins.includes(login);
        },

        async ajouter(login) {
            if (!login || this.contient(login)) return;
            this.logins = [login, ...this.logins];

            if (this.connecte) {
                try {
                    this.logins = (await poster('/memo/ajouter', { login })).logins;
                } catch {
                    this.logins = this.logins.filter((l) => l !== login);
                }
                return;
            }

            ecrireLocal(this.logins);
            this.proposerCompte();
        },

        async retirer(login) {
            if (!this.contient(login)) return;
            const avant = this.logins;
            this.logins = this.logins.filter((l) => l !== login);

            if (this.connecte) {
                try {
                    this.logins = (await poster('/memo/retirer', { login })).logins;
                } catch {
                    this.logins = avant;
                }
                return;
            }

            ecrireLocal(this.logins);
        },

        // Une fois par session : proposer, pas harceler.
        proposerCompte() {
            try {
                if (sessionStorage.getItem(CLE_PROPOSE)) return;
                sessionStorage.setItem(CLE_PROPOSE, '1');
            } catch {
                // sans sessionStorage, on propose a chaque fois
            }
            Alpine.store('modale').ouvrir('memo-compte');
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
        contactOuvert: false, // formulaire de contact a la place du diaporama

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
            // Une legende laissee au nom du fichier d'origine (ex.
            // "photo_final_02.jpg") n'est d'aucune utilite au visiteur :
            // seul un texte ecrit par le createur est affiche.
            const nomDeFichier = /\.(jpe?g|png|gif|webp|svg|bmp|tiff?)$/i;
            this.images = (this.slider.book_img ?? []).map((v) => ({
                src: (petitEcran && v.fichier_mobile) || v.fichier,
                titre: nomDeFichier.test((v.title ?? '').trim()) ? '' : (v.title ?? ''),
            }));
            this.index = 0;
            this.suivant = carte.nextElementSibling?.matches('.ui.card[data-user]') ? carte.nextElementSibling : null;
            this.contactOuvert = false;
            this.ouverte = true;
            document.documentElement.classList.add('swipebox-html');
            history.replaceState(null, '', `#${this.login}`);

            // Dernieres visites d'un visiteur connecte (VisiteBookController).
            if (Alpine.store('memo').visiteur) {
                new Image().src = `/ubvisite/${encodeURIComponent(this.login)}.gif`;
            }
        },

        fermer() {
            if (!this.ouverte) return;
            this.ouverte = false;
            this.contactOuvert = false;
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

        // Contact du creatif : le formulaire prend la place du diaporama.
        contacter() {
            this.contactOuvert = true;
        },

        memoriser() {
            Alpine.store('memo').ajouter(this.login);
        },

        oublier() {
            Alpine.store('memo').retirer(this.login);
        },
    });

    document.addEventListener('keydown', (e) => {
        const v = Alpine.store('visionneuse');
        if (!v.ouverte || Alpine.store('modale').ouverte) return;
        if (v.contactOuvert) {
            if (e.key === 'Escape') v.contactOuvert = false;
            return;
        }
        if (e.key === 'ArrowRight') v.suivante();
        if (e.key === 'ArrowLeft') v.precedente();
        if (e.key === 'Escape') v.fermer();
    });
}
