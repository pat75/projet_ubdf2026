/*
 * Inscription a la newsletter (pied de page, menu), ex-ub_newsletter de
 * ub_core_function_autres.js : envoi en ajax, message de retour affiche
 * 5 s sous le formulaire.
 */
export default function newsletter(Alpine) {
    Alpine.data('newsletter', () => ({
        message: '',
        erreur: false,
        minuterie: null,

        async envoyer(formulaire) {
            const url = new URL(formulaire.action, window.location.origin);
            new FormData(formulaire).forEach((valeur, nom) => url.searchParams.set(nom, valeur));

            let retour;
            try {
                const reponse = await fetch(url, { headers: { Accept: 'application/json' } });
                retour = reponse.ok ? await reponse.json() : { error: true, message: 'Inscription impossible pour le moment.' };
            } catch {
                retour = { error: true, message: 'Inscription impossible pour le moment.' };
            }

            this.afficher(retour.message, !!retour.error);
        },

        afficher(message, erreur) {
            clearTimeout(this.minuterie);
            this.message = message;
            this.erreur = erreur;
            this.minuterie = setTimeout(() => (this.message = ''), 5000);
        },
    }));
}
