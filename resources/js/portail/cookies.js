import { ecrireCookie, lireCookie } from './cookie';

/*
 * Bandeau d'information sur les cookies (partials/footer), ex-cookie_rgpd
 * de js_core_pages.js : affiche tant que le visiteur ne l'a pas touche,
 * puis masque un an. Le legacy passait 365 comme options de $.cookie :
 * le cookie ne durait que la session et le bandeau revenait a chaque visite.
 */
const COOKIE = 'ubdf_cookieconsent';

export default function cookies(Alpine) {
    Alpine.data('bandeauCookies', () => ({
        visible: lireCookie(COOKIE) === null,

        accepter() {
            ecrireCookie(COOKIE, 'show', 365);
            this.visible = false;
        },
    }));
}
