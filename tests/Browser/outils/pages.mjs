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

/* Parcours complets, sur l'accueil seulement (ils postent au serveur). */
const parcours = {
    'connexion échouée': async page => {
        await page.locator('.btn_connection:visible').first().click({ timeout: 5000 });
        await page.locator('#login').fill('sonde-inconnue');
        await page.locator('#pass').fill('mauvais');
        await page.locator('.valider_submit_login').click();
        await page.waitForLoadState('domcontentloaded');
        await page.waitForTimeout(3000);
        await visible(page, '.modal_connection .negative.message:has-text("incorrect")');
    },
    'mot de passe oublié': async page => {
        await page.locator('.btn_connection:visible').first().click({ timeout: 5000 });
        await page.locator('#btn_mdp_forget').click();
        await page.locator('#us_mail_mdp').fill('sonde@example.test');
        await page.locator('.valider_submit_mdp').click();
        await visible(page, '#segment_mdp_showOk .message:visible');
    },
    // Sans envoi final : la sonde ne cree pas de compte.
    'création de book, validations': async page => {
        await page.locator('.btn_modal_creerbook:visible').first().click({ timeout: 5000 });
        const fenetre = page.locator('[data-modale=creerbook]');
        await fenetre.locator('.valider_volet_1').click();
        await visible(page, '[data-modale=creerbook] .prompt.show:has-text("Indiquer votre nom")');
        await visible(page, '[data-modale=creerbook] .prompt.show:has-text("métier")');
        await fenetre.locator('#us_login').fill('adolie');
        await page.waitForTimeout(800);
        await fenetre.locator('.valider_volet_1').click();
        await visible(page, '[data-modale=creerbook] .prompt.show:has-text("existe")');
        await fenetre.locator('#us_login').fill('sonde-' + Date.now());
        await fenetre.locator('.dropdown_nav_metiers_').click();
        await fenetre.locator('.dropdown_nav_metiers .item[data-value=graphiste]').click();
        await fenetre.locator('.valider_volet_1').click();
        await visible(page, '[data-modale=creerbook] .volet_2');
        await fenetre.locator('#mdp-desac').fill('court');
        await fenetre.locator('.valider_submit').click();
        await visible(page, '[data-modale=creerbook] .prompt.show:has-text("8 caractères")');
        await visible(page, '[data-modale=creerbook] .prompt.show:has-text("conditions")');
    },
    'modale recherche': async page => {
        await page.locator('#search-menu').click({ timeout: 5000 });
        await visible(page, '#bloc_rechercher_top_menu_modal');
        await page.locator('[data-modale=recherche] .close').first().click();
        await page.locator('#bloc_rechercher_top_menu_modal').waitFor({ state: 'hidden', timeout: 3000 });
    },
};

/* En-tete : menu plein ecran, infobulles, retour en haut. */
const entetePlus = {
    'menu plein écran': async page => {
        await page.locator('#menu-top-fixed .menu-burger').click({ timeout: 5000 });
        await visible(page, '#overlay-menu.fs');
        await page.locator('#menu-top-fixed .menu-burger').click();
        await page.locator('#overlay-menu:not(.fs)').waitFor({ state: 'attached', timeout: 3000 });
    },
    'infobulle métiers': async page => {
        await page.locator('#menu-top-fixed .link_menu_top_ptf.icon_domaine').hover();
        await visible(page, '.popup_ptf.visible');
    },
    'retour en haut': async page => {
        await page.mouse.wheel(0, 3000);
        await visible(page, '.btn_top_move.show');
        await page.locator('.btn_top_move').click();
        await page.waitForFunction(() => window.scrollY < 50, null, { timeout: 5000 });
    },
};

/* Recherche : suggestions de mots-cles, saisie libre (nom). */
const rechercheParcours = {
    'recherche par suggestion': async page => {
        const champ = page.locator('.bloc_accueil .rech2018 input[name=q], .recherche_nom .rech2018 input[name=q]').first();
        await champ.fill('illus');
        await visible(page, '.recherche_nom .rech2018 .results.visible .result');
        await page.locator('.recherche_nom .rech2018 .results .result').first().click();
        await page.waitForURL(/\/recherche\?.*type_recherche=mcles/, { timeout: 10000 });
    },
    'recherche par nom': async page => {
        const champ = page.locator('.recherche_nom .rech2018 input[name=q]').first();
        await champ.fill('adolie');
        await champ.press('Enter');
        await page.waitForURL(/\/recherche\?.*q=adolie.*type_recherche=pseudo/, { timeout: 10000 });
        await visible(page, '#accueil_portfolio .ui.card');
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
    { url: '/', actions: { ...entete, ...entetePlus, ...parcours, ...rechercheParcours, ...pied } },
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
