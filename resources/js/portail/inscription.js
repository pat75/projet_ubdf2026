
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
        // Machine a ecrire pendant la progression (repris de ubdiff, start.blade.php) :
        // lignes deja tapees, la derniere en cours.
        lignes: [],
        // Seconde phrase : affichee d'un bloc, en fondu (3 s).
        pret: false,
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
                // Les erreurs de Laravel lui-meme (419 session expiree, 500…)
                // repondent { message } sans `error` : prises pour un succes,
                // elles menaient a https://undefined/undefined.
                if (!reponse.ok || retour.error !== false) {
                    retour.error = true;
                    retour.error_msg ??= [this.t(reponse.status === 419
                        ? 'Votre session a expiré : rechargez la page, svp.'
                        : 'Enregistrement impossible pour le moment.')];
                }
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

        /*
         * Ecran final, sur le modele de ubdiff (start.blade.php) : une seule
         * zone de texte en machine a ecrire. La premiere phrase s'ecrit
         * pendant l'anneau de progression, la seconde apparait d'un bloc
         * en fondu (3 s) ; puis le rappel des 12 images et le bouton apparaissent.
         * Sans clic, on entre seul dans l'espace 4 s plus tard.
         */
        async animerProgression() {
            const { phrase1 = '' } = this.$refs.machine?.dataset ?? {};
            this.bravo = false;
            this.pret = false;
            this.lignes = [''];

            await Promise.all([this.anneau(), this.ecrire(phrase1, DUREE_PROGRESSION * 0.85)]);
            await new Promise((fin) => setTimeout(fin, 500));
            this.pret = true;
            await new Promise((fin) => setTimeout(fin, 3000));

            this.bravo = true;
            setTimeout(() => this.allerEspace(), 4000);
        },

        // Anneau de progression 0 -> 100 % ; resolu a 100 %.
        anneau() {
            return new Promise((fin) => {
                const debut = performance.now();
                const etape = (maintenant) => {
                    this.progression = Math.min(100, Math.round(((maintenant - debut) / DUREE_PROGRESSION) * 100));
                    this.dessiner();
                    this.progression < 100 ? requestAnimationFrame(etape) : fin();
                };
                requestAnimationFrame(etape);
            });
        },

        // Ecrit `texte` lettre a lettre sur la derniere ligne, en `duree` ms ;
        // un temps un peu plus long separe chaque mot (PAUSE_MOT lettres).
        ecrire(texte, duree) {
            const PAUSE_MOT = 2.5;
            const lettres = [...texte.trim().replace(/\s+/g, ' ')];
            const espaces = lettres.filter((c) => c === ' ').length;
            const unite = duree / Math.max(1, lettres.length - espaces + espaces * PAUSE_MOT);

            return new Promise((fin) => {
                const suivant = (i) => {
                    if (i >= lettres.length) return fin();
                    this.lignes[this.lignes.length - 1] += lettres[i];
                    setTimeout(() => suivant(i + 1), unite * (lettres[i] === ' ' ? PAUSE_MOT : 1));
                };
                suivant(0);
            });
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
