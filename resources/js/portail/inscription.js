import { jetonRecaptcha } from './recaptcha';

/*
 * Fenetre « Creer un book » (partials/modals), ex-inscription de
 * js_core_inscription.js. Deux volets : identifiant et metier, puis
 * mot de passe, nom, mail et conditions. L'envoi part en fetch sur
 * /inscription (InscriptionController), qui repond
 * { error, error_msg: [...] } ou { error: false, url_domaine, url_action }.
 *
 * Les regles reprennent celles d'InscriptionRequest : le serveur reste
 * juge, le navigateur evite seulement un aller-retour. Le JavaScript de
 * 2019 acceptait un mot de passe de 2 caracteres, que le serveur refusait.
 */
const MOTIF_LOGIN = /^[a-z0-9_-]+$/;
const DUREE_PROGRESSION = 5000;

export default function inscription(Alpine) {
    Alpine.data('inscription', () => ({
        vue: 'formulaire', // formulaire | erreur | validation
        volet: 1,
        erreurs: {},
        messagesErreur: [],
        chargement: false,
        login: '',
        loginLibre: null,
        progression: 0,
        bravo: false,
        urlEspace: null,

        init() {
            // Fermer la fenetre apres l'inscription mene a l'espace, comme
            // le lien « Accedez a votre espace ».
            Alpine.store('modale').reglages('creerbook', {
                apresFermeture: () => this.allerEspace(),
            });
        },

        async verifierLogin() {
            const login = this.login.trim().toLowerCase();
            if (login.length < 3 || !MOTIF_LOGIN.test(login)) {
                this.loginLibre = null;
                return;
            }
            try {
                const reponse = await fetch(`/inscription?us_login=${encodeURIComponent(login)}`);
                // Reponse perimee si la saisie a change entre-temps.
                if (login === this.login.trim().toLowerCase()) {
                    this.loginLibre = (await reponse.text()) === 'true';
                }
            } catch {
                this.loginLibre = null;
            }
        },

        async suivant(formulaire) {
            await this.verifierLogin();
            const login = this.login.trim().toLowerCase();
            this.erreurs = {
                us_login: !login ? 'Indiquer votre nom'
                    : login.length < 3 ? 'Votre nom de book/identifiant doit contenir plus de 3 caractères'
                    : !MOTIF_LOGIN.test(login) ? 'Caractères incorrects : lettres minuscules, chiffres, - et _'
                    : this.loginLibre === false ? 'Ce nom existe déjà'
                    : '',
                us_type: formulaire.us_type.value ? '' : 'Sélectionner un métier ou domaine',
            };
            if (!this.valide()) return;

            this.volet = 2;
        },

        async envoyer(formulaire) {
            const mail = formulaire.us_mail.value.trim();
            this.erreurs = {
                us_pass: formulaire.us_pass.value.length < 8 ? 'Votre mot de passe doit contenir au moins 8 caractères' : '',
                us_nom: formulaire.us_nom.value.trim() ? '' : 'Indiquer votre nom',
                us_mail: !mail ? 'Indiquer votre mail' : !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(mail) ? 'Il ne s’agit pas d’un mail' : '',
                us_licence: formulaire.us_licence.checked ? '' : 'Vous devez accepter les conditions d’utilisation',
            };
            if (!this.valide()) return;

            this.chargement = true;
            const donnees = new FormData(formulaire);
            donnees.set('us_login', this.login.trim().toLowerCase());
            donnees.set('g-recaptcha-response', await jetonRecaptcha());

            let retour;
            try {
                const reponse = await fetch('/inscription', { method: 'POST', body: donnees, headers: { Accept: 'application/json' } });
                retour = await reponse.json();
            } catch {
                retour = { error: true, error_msg: ['Enregistrement impossible pour le moment.'] };
            }
            this.chargement = false;

            if (retour.error) {
                this.messagesErreur = [].concat(retour.error_msg ?? []);
                this.vue = 'erreur';
                return;
            }

            this.urlEspace = `${window.location.protocol}//${retour.url_domaine}/${retour.url_action}`;
            this.vue = 'validation';
            this.animerProgression();
        },

        valide() {
            return Object.values(this.erreurs).every((e) => !e);
        },

        recommencer() {
            this.messagesErreur = [];
            this.vue = 'formulaire';
        },

        // Anneau de progression 0 -> 100 %, puis le message « Bravo ».
        animerProgression() {
            const debut = performance.now();
            const etape = (maintenant) => {
                this.progression = Math.min(100, Math.round(((maintenant - debut) / DUREE_PROGRESSION) * 100));
                this.dessiner();
                if (this.progression < 100) {
                    requestAnimationFrame(etape);
                } else {
                    this.bravo = true;
                }
            };
            requestAnimationFrame(etape);
        },

        dessiner() {
            const toile = this.$refs.progression;
            const ctx = toile?.getContext('2d');
            if (!ctx) return;
            const depart = 4.72;
            ctx.clearRect(0, 0, toile.width, toile.height);
            ctx.lineWidth = 24;
            ctx.fillStyle = ctx.strokeStyle = '#00b5ad';
            ctx.textAlign = 'center';
            ctx.font = "14px 'Source Sans Pro'";
            ctx.fillText(`${this.progression}%`, toile.width * 0.5 - 10, toile.height * 0.5 + 4, toile.width);
            ctx.beginPath();
            ctx.arc(140, 75, 54, depart, (this.progression / 100) * Math.PI * 2 + depart, false);
            ctx.stroke();
        },

        allerEspace() {
            if (this.urlEspace) {
                window.location.href = this.urlEspace;
            }
        },
    }));
}
