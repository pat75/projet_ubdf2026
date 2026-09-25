/*
 * Outil de mise au point : ouvre une page, execute un script dans le
 * navigateur et affiche son resultat. Memes regles reseau que front.mjs.
 *   node tests/Browser/outils/inspecter.mjs /url "clic:.selecteur" "saisie:#champ=texte"
 *       "attente:ms" "chargement:" "js:expression" "capture:fichier.png"
 */
import { ouvrir } from './navigateur.mjs';

const [url, ...etapes] = process.argv.slice(2);
const { page, fermer } = await ouvrir(url);
for (const etape of etapes) {
    const [type, ...reste] = etape.split(':');
    const valeur = reste.join(':');
    if (type === 'clic') await page.locator(valeur).first().click({ timeout: 5000 });
    if (type === 'saisie') { const [sel, ...t] = valeur.split('='); await page.locator(sel).first().fill(t.join('=')); }
    if (type === 'chargement') await page.waitForLoadState('domcontentloaded');
    if (type === 'attente') await page.waitForTimeout(Number(valeur));
    if (type === 'js') console.log(JSON.stringify(await page.evaluate(valeur), null, 1));
    if (type === 'capture') await page.screenshot({ path: valeur });
}
await fermer();
