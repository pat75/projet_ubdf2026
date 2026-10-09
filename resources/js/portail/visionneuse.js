/*
 * Visionneuse d'un book ouverte depuis sa carte (partials/visionneuse),
 * ex-ub_ill_plus_de_book.btn_slide de js_core_cards.js et le plugin
 * jQuery Swipebox. Mise en page propre (#vn, styles dans portail.css),
 * independante de Swipebox et de core.css.
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
 * a jour. Anonyme, rien n'est memorise : le coeur ouvre la fenetre
 * `memo-compte` (connexion ou compte visiteur gratuit) et garde le book
 * en attente (sessionStorage), verse dans le compte une fois connecte.
 * La cle `books` du legacy (selection anonyme d'avant) rejoint aussi le
 * compte a la connexion.
 */
const CLE_LOCALE = 'books';
const CLE_ATTENTE = 'memo_en_attente';

function lireAttente() {
    try {
        return sessionStorage.getItem(CLE_ATTENTE);
    } catch {
        return null;
    }
}

function ecrireAttente(login) {
    try {
        login ? sessionStorage.setItem(CLE_ATTENTE, login) : sessionStorage.removeItem(CLE_ATTENTE);
    } catch {
        // sans sessionStorage, le book clique n'est pas reporte
    }
}

/** Books a verser dans le compte : selection legacy + book en attente. */
export function aVerser() {
    const attente = lireAttente();
    const logins = lireLocal();
    return attente && !logins.includes(attente) ? [attente, ...logins] : logins;
}

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

            if (!this.connecte) return;

            this.logins = serveur.logins ?? [];

            // Le book clique avant de se connecter rejoint le compte.
            const locale = aVerser();
            if (locale.length) {
                poster('/memo/fusionner', { logins: locale })
                    .then((r) => { this.logins = r.logins; ecrireLocal([]); ecrireAttente(null); })
                    .catch(() => {});
            }
        },

        contient(login) {
            return this.logins.includes(login);
        },

        async ajouter(login) {
            if (!login || this.contient(login)) return;

            // Anonyme : connexion ou compte d'abord, le book attend.
            if (!this.connecte) {
                ecrireAttente(login);
                Alpine.store('modale').ouvrir('memo-compte');
                return;
            }

            this.logins = [login, ...this.logins];
            try {
                this.logins = (await poster('/memo/ajouter', { login })).logins;
            } catch {
                this.logins = this.logins.filter((l) => l !== login);
            }
        },

        async retirer(login) {
            if (!this.contient(login)) return;
            const avant = this.logins;
            this.logins = this.logins.filter((l) => l !== login);

            try {
                this.logins = (await poster('/memo/retirer', { login })).logins;
            } catch {
                this.logins = avant;
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
        contactOuvert: false, // formulaire de contact a la place du diaporama
        glissement: '', // passage au book suivant : 'sortie' puis 'entree'
        position: 0, // diapo affichee dans la piste, copies de bout comprises
        sansTransition: false, // recalage invisible apres un passage en boucle
        lecture: false, // video de la diapo courante en cours de lecture

        get image() {
            return this.images[this.index] ?? {};
        },

        // Piste du diaporama : la derniere image copiee en tete et la
        // premiere en queue, pour boucler en glissant dans le meme sens.
        get diapos() {
            const n = this.images.length;
            return n > 1 ? [this.images[n - 1], ...this.images, this.images[0]] : this.images;
        },

        // Pastille sans avatar : deux initiales du nom.
        get initiales() {
            const mots = (this.fiche.book_prenom_nom ?? '').trim().split(/\s+/).filter(Boolean);
            if (mots.length >= 2) return (mots[0][0] + mots[mots.length - 1][0]).toUpperCase();
            return (mots[0] ?? '').slice(0, 2).toUpperCase();
        },

        // Localisation sous le domaine : « Bordeaux, France ».
        get lieu() {
            return [this.fiche.book_ville, this.fiche.book_pays].filter((v) => (v ?? '').trim()).join(', ');
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
                video: v.video ?? '',
            }));
            this.lecture = false;
            this.index = 0;
            this.position = this.images.length > 1 ? 1 : 0;
            this.sansTransition = false;
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
            this.lecture = false;
            document.documentElement.classList.remove('swipebox-html');
            history.replaceState(null, '', window.location.pathname + window.location.search);
        },

        // Boucle : apres la derniere, la piste glisse sur la copie de la
        // premiere, puis se recale sans transition sur la vraie premiere
        // (et inversement avant la premiere).
        allerA(index) {
            this.lecture = false; // quitter la diapo arrete la video
            const total = this.images.length;
            if (total < 2) return;
            this.index = (index + total) % total;
            this.sansTransition = false;
            this.position = index + 1;

            clearTimeout(this.recalage);
            if (this.position === 0 || this.position === total + 1) {
                this.recalage = setTimeout(() => {
                    this.sansTransition = true;
                    this.position = this.index + 1;
                    requestAnimationFrame(() => requestAnimationFrame(() => { this.sansTransition = false; }));
                }, 400);
            }
        },

        // Clic sur la diapo : une video se lance, une image passe a la suivante.
        cliquer() {
            if (this.image.video && !this.lecture) {
                this.lecture = true;
                return;
            }
            this.suivante();
        },

        suivante() {
            this.allerA(this.index + 1);
        },

        precedente() {
            this.allerA(this.index - 1);
        },

        // Passage au book suivant : le book courant sort vers la gauche,
        // le suivant entre par la droite (classes vn-sortie / vn-entree).
        bookSuivant() {
            if (!this.suivant || this.glissement) return;
            const suivant = this.suivant;
            this.glissement = 'sortie';
            setTimeout(() => {
                this.ouvrir(suivant);
                this.glissement = 'entree';
                // Deux images : la position de depart est peinte avant le retour.
                requestAnimationFrame(() => requestAnimationFrame(() => { this.glissement = ''; }));
            }, 250);
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
