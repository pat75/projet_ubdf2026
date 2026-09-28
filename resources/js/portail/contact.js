/*
 * Demande de contact a un creatif, depuis sa visionneuse
 * (partials/visionneuse : le formulaire prend la place du diaporama,
 * $store.visionneuse.contactOuvert), ex-intermediate_btn et suivantes de
 * js_core_cards.js, gabarits Handlebars tpl_bloc_modal_content_ajax_*.
 *
 * POST /intermediate_send (ContactController) : reponse toujours 200,
 * { error, error_list: { champ: [messages] } }. Le captcha est une image
 * servie par /captcha_img, rechargee a l'ouverture et apres un refus.
 */
export default function contact(Alpine) {
    Alpine.data('contactCreatif', () => ({
        vue: 'formulaire', // formulaire | envoi | merci | erreur
        erreurs: {},
        captcha: '',
        visuel: '',

        get book() {
            return Alpine.store('visionneuse');
        },

        init() {
            this.$watch('book.contactOuvert', (ouvert) => {
                if (ouvert) this.preparer();
            });
        },

        preparer() {
            this.vue = 'formulaire';
            this.erreurs = {};
            this.visuel = this.book.image.src ?? '';
            this.$refs.formulaire?.reset();
            this.rechargerCaptcha();
        },

        rechargerCaptcha() {
            this.captcha = `/captcha_img?${Date.now()}`;
            if (this.$refs.formulaire) this.$refs.formulaire.captcha_answer.value = '';
        },

        verifier(f) {
            const mail = f.us_mail.value.trim();
            const message = f.us_message.value.trim();
            const code = f.captcha_answer.value.trim();
            this.erreurs = {
                // Meme minimum que DemandeContactRequest (min:10).
                us_message: !message ? 'Champ vide' : message.length < 10 ? 'Votre message est trop court (10 caractères minimum).' : '',
                us_nom_prenom: f.us_nom_prenom.value.trim() ? '' : 'Champ vide',
                us_mail: !mail ? 'Champ vide' : mail.includes('@') ? '' : 'Il ne s’agit pas d’un mail',
                captcha_answer: !code ? 'Recopiez le code' : code.length < 4 ? 'Code incomplet' : '',
            };
            return Object.values(this.erreurs).every((e) => !e);
        },

        async envoyer(f) {
            if (!this.verifier(f)) return;

            this.vue = 'envoi';
            let retour;
            try {
                const reponse = await fetch('/intermediate_send', {
                    method: 'POST',
                    body: new FormData(f),
                    headers: { Accept: 'application/json' },
                });
                retour = await reponse.json();
            } catch {
                retour = { error: true, error_list: { envoi: ['Envoi impossible pour le moment.'] } };
            }

            if (!retour.error) {
                this.vue = 'merci';
                // Le diaporama reprend son etat initial une fois le message envoye.
                setTimeout(() => {
                    this.book.contactOuvert = false;
                    this.vue = 'formulaire';
                }, 2500);
                return;
            }

            // Code du captcha refuse : retour au formulaire, nouveau code.
            if (retour.error_list?.captcha_answer) {
                this.rechargerCaptcha();
                this.erreurs = { captcha_answer: 'Code incorrect, essayez à nouveau.' };
                this.vue = 'formulaire';
                return;
            }

            // Refus sur un champ du formulaire : message sous le champ, comme
            // pour un mail invalide. Sinon (envoi, quota...), liste generale.
            const champs = ['us_message', 'us_nom_prenom', 'us_mail'];
            const liste = retour.error_list ?? {};
            if (Object.keys(liste).length && Object.keys(liste).every((c) => champs.includes(c))) {
                this.erreurs = Object.fromEntries(Object.entries(liste).map(([c, m]) => [c, [m].flat()[0]]));
                this.vue = 'formulaire';
                return;
            }
            this.erreurs = { liste: Object.values(liste).flat() };
            this.vue = 'erreur';
        },

        retour() {
            this.rechargerCaptcha();
            this.vue = 'formulaire';
        },
    }));
}
