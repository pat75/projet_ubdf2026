/*
 * Pages du front visitees par front.mjs, et interactions a verifier sur
 * chacune. Une action echoue si elle leve une exception (element absent,
 * modale qui ne s'ouvre pas...).
 */
export const BASE = process.env.FRONT_BASE ?? 'https://ubdf2026.ultra-book.name';

const visible = async (page, selecteur) => {
    await page.locator(selecteur).first().waitFor({ state: 'visible', timeout: 5000 });
};

/* Interactions communes a toutes les pages du portail (en-tete). */
const entete = {
    'modale connexion': async page => {
        await page.locator('.btn_connection:visible').first().click({ timeout: 5000 });
        await visible(page, '.ui.modal.modal_connection');
    },
    'modale créer un book': async page => {
        await page.locator('.btn_modal_creerbook:visible').first().click({ timeout: 5000 });
        await visible(page, '.ui.modal.modal_creerbook');
    },
};

export const pages = [
    { url: '/', actions: entete },
    { url: '/recherche', actions: entete },
    { url: '/annuaire', actions: entete },
    { url: '/annuaire_b' },
    { url: '/illustrateur', actions: entete },
    { url: '/les-ultra-books' },
    { url: '/les-ultra-selections' },
    { url: '/actus' },
    { url: '/meilleurs-illustrateurs' },
    { url: '/formules' },
    { url: '/en' },
];
