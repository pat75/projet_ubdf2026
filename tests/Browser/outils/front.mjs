/*
 * Filet de securite du remplacement de jQuery (_doc/17) : visite les pages
 * du front sur le site Valet et releve les erreurs JavaScript. Seuls le
 * site et les CDN de bibliotheques sont servis ; analytics, reseaux
 * sociaux et images tierces sont coupes, pour un resultat stable.
 *
 *   node tests/Browser/outils/front.mjs            toutes les pages
 *   node tests/Browser/outils/front.mjs /recherche  une seule page (URL exacte)
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';
import { aiguiller } from './navigateur.mjs';
import { pages, adresse } from './pages.mjs';

const filtre = process.argv[2];
const liste = filtre ? pages.filter(p => p.url === filtre) : pages;
const navigateur = await chromium.launch();
let echecs = 0;

for (const p of liste) {
    const ctx = await navigateur.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1280, height: 900 } });
    await ctx.route('**/*', aiguiller);
    const page = await ctx.newPage();
    const erreurs = [];
    page.on('pageerror', e => erreurs.push('JS : ' + e.message + (process.env.PILE ? '\n' + e.stack : '')));
    page.on('response', r => r.status() >= 500 && erreurs.push(`HTTP ${r.status()} : ${r.url()}`));

    try {
        // `load` attend chaque vignette : trop lent sur un disque de dev.
        await page.goto(adresse(p.url), { waitUntil: 'domcontentloaded', timeout: 30000 });
        await page.waitForTimeout(4000);
        if (!(await page.evaluate(() => !!window.Alpine))) erreurs.push('Alpine absent');
        // Chaque action repart d'une page neuve : une modale restee
        // ouverte ne doit pas faire echouer la suivante.
        for (const [i, [nom, action]] of Object.entries(Object.entries(p.actions ?? {}))) {
            if (i > 0) {
                await page.goto(adresse(p.url), { waitUntil: 'domcontentloaded', timeout: 30000 });
                await page.waitForTimeout(3000);
            }
            try {
                await action(page);
                // CAPTURES=dossier : une capture apres chaque action, pour
                // comparer le rendu avant / apres une conversion.
                if (process.env.CAPTURES) {
                    mkdirSync(process.env.CAPTURES, { recursive: true });
                    await page.screenshot({ path: `${process.env.CAPTURES}/${(new URL(adresse(p.url)).host + new URL(adresse(p.url)).pathname).replace(/\W+/g, '_')}-${nom.replace(/\W+/g, '_')}.png` });
                }
            } catch (e) { erreurs.push(`action « ${nom} » : ${e.message.split('\n')[0]}`); }
        }
    } catch (e) {
        erreurs.push('chargement : ' + e.message.split('\n')[0]);
    }

    console.log(`${erreurs.length ? 'ECHEC' : 'OK   '} ${p.url}`);
    erreurs.forEach(e => console.log('      ' + e));
    echecs += erreurs.length ? 1 : 0;
    await ctx.close();
}

await navigateur.close();
console.log(`\n${liste.length - echecs}/${liste.length} pages sans erreur`);
process.exit(echecs ? 1 : 0);
