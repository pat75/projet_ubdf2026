import { jetonRecaptcha } from './recaptcha';

/*
 * Fenetre « Gardez votre memo book » (partials/modals) : ouvre un compte
 * visiteur (POST /memo/compte, InscriptionVisiteurController) avec la
 * selection du localStorage, puis va sur la page du memo, connecte.
 */
export default function memoCompte(Alpine) {
    Alpine.data('memoCompte', () => ({
        envoi: false,
        erreur: '',
        existe: false,

        async creer(formulaire) {
            const email = formulaire.email.value.trim();
            const password = formulaire.password.value;

            this.existe = false;
            if (!email.includes('@')) return (this.erreur = 'Indiquez une adresse e-mail valide.');
            if (password.length < 8) return (this.erreur = 'Le mot de passe doit faire au moins 8 caractères.');

            this.envoi = true;
            try {
                const reponse = await fetch('/memo/compte', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        email,
                        password,
                        logins: Alpine.store('memo').logins,
                        'g-recaptcha-response': await jetonRecaptcha().catch(() => ''),
                    }),
                });
                const retour = await reponse.json();

                if (reponse.ok && retour.url) {
                    try { localStorage.removeItem('books'); } catch { /* rien a vider */ }
                    window.location.href = retour.url;
                    return;
                }

                const erreurs = retour.errors ?? {};
                this.existe = (erreurs.email ?? []).some((m) => m.includes('connectez'));
                this.erreur = Object.values(erreurs).flat()[0] ?? retour.message ?? 'Création impossible pour le moment.';
            } catch {
                this.erreur = 'Création impossible pour le moment.';
            }
            this.envoi = false;
        },
    }));
}
