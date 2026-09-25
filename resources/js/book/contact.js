/*
 * Formulaire de contact du book (book/ultra2020/contact), ex-contact.* de
 * core.js (validation Semantic UI, envoi ajax, reCAPTCHA).
 *
 * POST /contact (BookController::envoyer) : `{error: false}` en cas de
 * succes, `{errors: [...]}` sinon. Captcha local (App\Services\Captcha) :
 * le code est consomme a chaque envoi, l'image est donc renouvelee apres
 * un refus.
 */
const MAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function contact(Alpine) {
    Alpine.data('contactBook', () => ({
        vue: 'formulaire', // formulaire | envoi | merci
        erreurs: {},
        serveur: [],

        // Nouvelle image, donc nouveau code : chaque code ne sert qu'une fois.
        nouveauCode() {
            const img = this.$refs.captcha;
            img.src = `${img.dataset.src}?${Date.now()}`;
            this.$root.querySelector('[name=captcha]').value = '';
        },

        verifier(f) {
            const message = f.fm_contact_message.value.trim();
            const mail = f.fm_contact_mail.value.trim();
            const code = f.captcha.value.trim();
            this.erreurs = {
                message: message.length >= 10 ? '' : this.$root.dataset.msgMessage,
                mail: MAIL.test(mail) ? '' : this.$root.dataset.msgMail,
                captcha: code.length === 4 ? '' : this.$root.dataset.msgCaptcha,
            };

            return !Object.values(this.erreurs).some(Boolean);
        },

        async envoyer(f) {
            this.serveur = [];
            if (!this.verifier(f)) return;

            this.vue = 'envoi';
            let retour;
            try {
                const reponse = await fetch(f.action, { method: 'POST', body: new FormData(f), headers: { Accept: 'application/json' } });
                retour = await reponse.json();
            } catch {
                retour = { errors: [this.$root.dataset.msgEnvoi] };
            }

            if (retour.errors?.length) {
                this.serveur = retour.errors;
                this.vue = 'formulaire';
                this.nouveauCode();

                return;
            }

            this.vue = 'merci';
        },
    }));
}
