/*
 * Demande de contact a un creatif, depuis sa visionneuse (fenetre
 * « intermediaire » de partials/modals), ex-intermediate_btn et suivantes
 * de js_core_cards.js, gabarits Handlebars tpl_bloc_modal_content_ajax_*.
 *
 * POST /intermediate_send (ContactController) : reponse toujours 200,
 * { error, error_list: { champ: [messages] } }. Le captcha est une image
 * servie par /captcha_img, rechargee a chaque ouverture et apres un refus.
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
            Alpine.store('modale').reglages('intermediaire', {
                apresOuverture: () => this.preparer(),
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
            const code = f.captcha_answer.value.trim();
            this.erreurs = {
                us_message: f.us_message.value.trim() ? '' : 'Champ vide',
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
                return;
            }

            // Code du captcha refuse : retour au formulaire, nouveau code.
            if (retour.error_list?.captcha_answer) {
                this.rechargerCaptcha();
                this.erreurs = { captcha_answer: 'Code incorrect, essayez à nouveau.' };
                this.vue = 'formulaire';
                return;
            }

            this.erreurs = { liste: Object.values(retour.error_list ?? {}).flat() };
            this.vue = 'erreur';
        },

        retour() {
            this.rechargerCaptcha();
            this.vue = 'formulaire';
        },
    }));
}
