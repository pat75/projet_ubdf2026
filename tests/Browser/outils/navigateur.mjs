/*
 * Ouverture de pages pour les outils du filet (front.mjs, inspecter.mjs).
 *
 * Seuls le site et les CDN de bibliotheques sont servis ; analytics,
 * reseaux sociaux et images tierces sont coupes, pour un resultat stable.
 */
import { chromium } from 'playwright';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { BASE, DOMAINE_LOCAL, adresse } from './pages.mjs';

const AUTORISES = [new URL(BASE).hostname, 'cdnjs.cloudflare.com', 'cdn.jsdelivr.net', 'code.jquery.com',
    'ajax.googleapis.com', 'www.google.com', 'www.gstatic.com', 'fonts.googleapis.com', 'fonts.gstatic.com'];
const autorise = url => { const h = new URL(url).hostname; return AUTORISES.some(a => h === a || h.endsWith('.' + a)); };

/*
 * Chromium n'atteint pas Internet depuis ce poste : les fichiers des CDN
 * sont telecharges par Node, puis gardes dans un cache disque (.cache/),
 * ce qui rend aussi chaque passage identique au precedent.
 */
const CACHE = new URL('./.cache/', import.meta.url).pathname;
mkdirSync(CACHE, { recursive: true });

async function servirCdn(route) {
    const url = route.request().url();
    const fichier = CACHE + createHash('sha1').update(url).digest('hex');
    if (!existsSync(fichier)) {
        try {
            const r = await fetch(url);
            writeFileSync(fichier + '.type', r.headers.get('content-type') ?? 'application/octet-stream');
            writeFileSync(fichier, Buffer.from(await r.arrayBuffer()));
        } catch {
            return route.abort();
        }
    }
    return route.fulfill({ body: readFileSync(fichier), contentType: readFileSync(fichier + '.type', 'utf8'),
        headers: { 'access-control-allow-origin': '*' } });
}

export const aiguiller = route => {
    const url = route.request().url();
    if (new URL(url).hostname.endsWith(DOMAINE_LOCAL)) return route.continue();
    return autorise(url) ? servirCdn(route) : route.abort();
};


/* Page prete a l'emploi, pour les outils ponctuels. */
export async function ouvrir(url) {
    const navigateur = await chromium.launch();
    const ctx = await navigateur.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1280, height: 900 } });
    await ctx.route('**/*', aiguiller);
    const page = await ctx.newPage();
    page.on('pageerror', e => console.log('JS :', e.message));
    await page.goto(adresse(url), { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.waitForTimeout(4000);
    return { page, fermer: () => navigateur.close() };
}
