/*
 * Formulaire de contact du book (book/ultra2020/contact), ex-contact.* de
 * core.js (validation Semantic UI, envoi ajax, reCAPTCHA).
 *
 * POST /contact (BookController::envoyer) : `{error: false}` en cas de
 * succes, `{errors: [...]}` sinon. Le reCAPTCHA v2 n'est affiche que s'il
 * est configure (data-cle) ; le serveur ignore la verification sinon.
 */
const MAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function contact(Alpine) {
    Alpine.data('contactBook', () => ({
        vue: 'formulaire', // formulaire | envoi | merci
        erreurs: {},
        serveur: [],
        widget: null,

        init() {
            const cle = this.$refs.captcha?.dataset.cle;
            if (!cle) return;

            window.ubdfCaptcha = () => {
                this.widget = window.grecaptcha.render(this.$refs.captcha, { sitekey: cle });
            };
            const script = document.createElement('script');
            script.src = `https://www.google.com/recaptcha/api.js?onload=ubdfCaptcha&render=explicit&hl=${document.documentElement.lang}`;
            script.async = true;
            document.head.append(script);
        },

        verifier(f) {
            const message = f.fm_contact_message.value.trim();
            const mail = f.fm_contact_mail.value.trim();
            this.erreurs = {
                message: message.length >= 10 ? '' : this.$root.dataset.msgMessage,
                mail: MAIL.test(mail) ? '' : this.$root.dataset.msgMail,
            };

            return !this.erreurs.message && !this.erreurs.mail;
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
                if (this.widget !== null) window.grecaptcha?.reset(this.widget);

                return;
            }

            this.vue = 'merci';
        },
    }));
}
