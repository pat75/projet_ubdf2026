/*
 * Pages du front visitees par front.mjs, et interactions a verifier sur
 * chacune. Une action echoue si elle leve une exception (element absent,
 * modale qui ne s'ouvre pas...).
 */
export const BASE = process.env.FRONT_BASE ?? 'https://ubdf2026.ultra-book.name';
export const DUST = process.env.FRONT_DUST ?? 'https://ubdf-dust-2026.ultra-book.name';

/* Hotes servis par Valet : Ultra-book, Dustfolio et les books. */
export const DOMAINE_LOCAL = 'ultra-book.name';

/* Une URL de la liste est relative au portail Ultra-book, ou absolue. */
export const adresse = url => url.startsWith('http') ? url : BASE + url;

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

/* Pied de page et bandeau cookies, sur l'accueil. */
const pied = {
    'bandeau cookies': async page => {
        await page.context().clearCookies();
        await page.reload({ waitUntil: 'domcontentloaded' });
        await visible(page, '#cookie-policy.show');
        await page.locator('#cookie-policy').click();
        await page.locator('#cookie-policy:not(.show)').waitFor({ state: 'attached', timeout: 3000 });
        const cookies = await page.context().cookies();
        if (!cookies.some(c => c.name === 'ubdf_cookieconsent' && c.expires > Date.now() / 1000 + 86400 * 300)) {
            throw new Error('cookie ubdf_cookieconsent absent ou trop court');
        }
    },
    'newsletter': async page => {
        const bloc = page.locator('footer .newsletter, .newsletter').filter({ has: page.locator('form') }).last();
        await bloc.locator('input[name=mail]').fill('sonde@example.test');
        await bloc.locator('input[name=mail]').press('Enter');
        await bloc.locator('.retour span').waitFor({ state: 'visible', timeout: 5000 });
    },
};

export const pages = [
    { url: '/', actions: { ...entete, ...pied } },
    { url: '/recherche', actions: entete },
    { url: '/annuaire', actions: entete },
    { url: '/annuaire_b' },
    { url: '/illustrateur', actions: entete },
    { url: '/les-ultra-books' },
    { url: '/les-ultra-selections' },
    { url: '/actus' },
    { url: '/meilleurs-illustrateurs' },
    { url: '/formules' },
    { url: DUST + '/en', actions: entete },
    { url: DUST + '/fr' },
];
