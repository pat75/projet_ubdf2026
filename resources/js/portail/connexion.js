import { jetonRecaptcha } from './recaptcha';

/*
 * Fenetre de connexion (partials/modals), ex-login et mdp_forget de
 * js_core_inscription.js : connexion, puis bascule vers la demande de
 * mot de passe oublie et son resultat.
 *
 * La connexion reste un formulaire classique (POST /ubaction__user_open,
 * redirection) ; la demande de mot de passe passe en fetch sur
 * /inscription, qui repond { error, error_msg | header, msg }.
 */
export default function connexion(Alpine) {
    Alpine.data('connexion', () => ({
        vue: 'connexion', // connexion | mdp | resultat
        erreurLogin: '',
        erreurMail: '',
        chargement: false,
        resultat: { erreur: false, titre: '', texte: '' },

        verifierLogin(valeur) {
            if (!valeur.trim()) return 'Indiquer une valeur';
            if (valeur.includes('@')) return 'Indiquez votre identifiant (pas votre mail)';
            return '';
        },

        async connecter(formulaire) {
            this.erreurLogin = this.verifierLogin(formulaire.login.value);
            if (this.erreurLogin) return;

            formulaire.querySelector('[name="g-recaptcha-response"]').value = await jetonRecaptcha();
            formulaire.submit();
        },

        async demanderMotDePasse(formulaire) {
            const mail = formulaire.us_mail.value.trim();
            this.erreurMail = !mail ? 'Indiquer votre mail' : mail.length < 5 ? 'Votre mail doit contenir plus de 5 caractères' : '';
            if (this.erreurMail) return;

            this.chargement = true;
            const donnees = new FormData(formulaire);
            donnees.set('g-recaptcha-response', await jetonRecaptcha());

            let retour;
            try {
                const reponse = await fetch('/inscription', { method: 'POST', body: donnees, headers: { Accept: 'application/json' } });
                retour = await reponse.json();
            } catch {
                retour = { error: true, error_msg: ['Envoi impossible pour le moment.'] };
            }

            this.chargement = false;
            this.resultat = retour.error
                ? { erreur: true, titre: [].concat(retour.error_msg).join(' '), texte: '' }
                : { erreur: false, titre: retour.header, texte: retour.msg };
            this.vue = 'resultat';
        },
    }));
}
