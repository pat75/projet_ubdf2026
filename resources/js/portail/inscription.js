
/*
 * Page « Creer un book » (front/creer-un-book), ex-fenetre d'inscription de
 * js_core_inscription.js. Une seule etape, comme /register sur Tesli :
 * nom, mail, adresse du book, mot de passe, captcha et conditions. Envoi en fetch sur
 * /inscription (InscriptionController), qui repond
 * { error, error_msg: [...] } ou { error: false, url_domaine, url_action }.
 *
 * Les regles reprennent celles d'InscriptionRequest : le serveur reste
 * juge, le navigateur evite seulement un aller-retour. Le JavaScript de
 * 2019 acceptait un mot de passe de 2 caracteres, que le serveur refusait.
 *
 * `textes` : traductions des messages, indexees par la phrase francaise
 * comme les catalogues lang/*.json (`x-data="inscription(@js(...))"`).
 * Un message absent reste en francais.
 */
const MOTIF_LOGIN = /^[a-z0-9_-]+$/;
const DUREE_PROGRESSION = 5000;

export default function inscription(Alpine) {
    Alpine.data('inscription', (textes = {}) => ({
        t: (phrase) => textes[phrase] ?? phrase,
        vue: 'formulaire', // formulaire | erreur | validation
        erreurs: {},
        messagesErreur: [],
        chargement: false,
        login: '',
        loginLibre: null,
        progression: 0,
        bravo: false,
        urlEspace: null,
        // Image du captcha local (App\Services\Captcha\Captcha, formulaire « inscription »).
        captcha: '',

        // Formulaire en une etape : le captcha s'affiche des l'arrivee.
        init() {
            this.rechargerCaptcha();
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

        // Nouvelle image, donc nouveau code : chaque code ne sert qu'une fois.
        rechargerCaptcha() {
            this.captcha = `/captcha/inscription?${Date.now()}`;
            const champ = this.$root.querySelector('[name="captcha"]');
            if (champ) champ.value = '';
        },

        async envoyer(formulaire) {
            await this.verifierLogin();
            const login = this.login.trim().toLowerCase();
            const mail = formulaire.us_mail.value.trim();
            this.erreurs = {
                us_login: !login ? this.t('Choisissez l’adresse de votre book')
                    : login.length < 3 ? this.t('Votre nom de book/identifiant doit contenir plus de 3 caractères')
                    : !MOTIF_LOGIN.test(login) ? this.t('Caractères incorrects : lettres minuscules, chiffres, - et _')
                    : this.loginLibre === false ? this.t('Ce nom existe déjà')
                    : '',
                us_pass: formulaire.us_pass.value.length < 8 ? this.t('Votre mot de passe doit contenir au moins 8 caractères') : '',
                us_nom: formulaire.us_nom.value.trim() ? '' : this.t('Indiquer votre nom'),
                us_mail: !mail ? this.t('Indiquer votre mail') : !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(mail) ? this.t('Il ne s’agit pas d’un mail') : '',
                us_licence: formulaire.us_licence.checked ? '' : this.t('Vous devez accepter les conditions d’utilisation'),
                captcha: formulaire.captcha.value.trim().length === 4 ? '' : this.t('Recopiez les 4 caractères de l’image'),
            };
            if (!this.valide()) return;

            this.chargement = true;
            const donnees = new FormData(formulaire);
            donnees.set('us_login', this.login.trim().toLowerCase());

            let retour;
            try {
                const reponse = await fetch('/inscription', { method: 'POST', body: donnees, headers: { Accept: 'application/json' } });
                retour = await reponse.json();
            } catch {
                retour = { error: true, error_msg: [this.t('Enregistrement impossible pour le moment.')] };
            }
            this.chargement = false;

            if (retour.error) {
                this.rechargerCaptcha();
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
