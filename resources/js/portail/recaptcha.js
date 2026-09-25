/*
 * Jeton reCAPTCHA v3, demande au moment de l'envoi d'un formulaire.
 *
 * Le JavaScript de 2019 le demandait a l'ouverture de la fenetre et le
 * rafraichissait toutes les 150 s : un jeton v3 expire au bout de deux
 * minutes, et un visiteur lent envoyait un jeton perime. La cle vient de
 * config('services.recaptcha.key') (meta du layout).
 */
let chargement = null;

function charger(cle) {
    chargement ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(cle)}`;
        script.async = true;
        script.onload = () => window.grecaptcha.ready(resolve);
        script.onerror = () => {
            chargement = null;
            reject(new Error('reCAPTCHA indisponible'));
        };
        document.head.appendChild(script);
    });

    return chargement;
}

/** Jeton pour l'action attendue par App\Services\Auth\Recaptcha. */
export async function jetonRecaptcha(action = 'validate_captcha') {
    const cle = document.querySelector('meta[name="recaptcha"]')?.content;
    if (!cle) {
        return '';
    }

    try {
        if (!window.grecaptcha?.execute) {
            await charger(cle);
        }
        return await window.grecaptcha.execute(cle, { action });
    } catch {
        // Le serveur refusera le formulaire et le dira : mieux que bloquer ici.
        return '';
    }
}
