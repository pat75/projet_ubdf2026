/*
 * Inscription a la newsletter (pied de page, menu), ex-ub_newsletter de
 * ub_core_function_autres.js : envoi en ajax, message de retour affiche
 * 5 s sous le formulaire. POST /newsletter (NewsletterController),
 * reponse { error, message }.
 */
export default function newsletter(Alpine) {
    Alpine.data('newsletter', () => ({
        message: '',
        erreur: false,
        minuterie: null,

        async envoyer(formulaire) {
            let retour;
            try {
                const reponse = await fetch(formulaire.action, {
                    method: 'POST',
                    body: new FormData(formulaire),
                    headers: { Accept: 'application/json' },
                });
                retour = reponse.status === 200 || reponse.status === 422
                    ? await reponse.json()
                    : { error: true, message: 'Inscription impossible pour le moment.' };
            } catch {
                retour = { error: true, message: 'Inscription impossible pour le moment.' };
            }

            this.afficher(retour.message, !!retour.error);
            if (!retour.error) formulaire.reset();
        },

        afficher(message, erreur) {
            clearTimeout(this.minuterie);
            this.message = message;
            this.erreur = erreur;
            this.minuterie = setTimeout(() => (this.message = ''), 5000);
        },
    }));
}
