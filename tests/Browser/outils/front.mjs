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
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { pages, BASE } from './pages.mjs';

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

const aiguiller = route => {
    const url = route.request().url();
    if (new URL(url).hostname.endsWith(new URL(BASE).hostname)) return route.continue();
    return autorise(url) ? servirCdn(route) : route.abort();
};

const filtre = process.argv[2];
const liste = filtre ? pages.filter(p => p.url === filtre) : pages;
const navigateur = await chromium.launch();
let echecs = 0;

for (const p of liste) {
    const ctx = await navigateur.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1280, height: 900 } });
    await ctx.route('**/*', aiguiller);
    const page = await ctx.newPage();
    const erreurs = [];
    page.on('pageerror', e => erreurs.push('JS : ' + e.message));
    page.on('response', r => r.status() >= 500 && erreurs.push(`HTTP ${r.status()} : ${r.url()}`));

    try {
        // `load` attend chaque vignette : trop lent sur un disque de dev.
        await page.goto(BASE + p.url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await page.waitForTimeout(4000);
        // Chaque action repart d'une page neuve : une modale restee
        // ouverte ne doit pas faire echouer la suivante.
        for (const [i, [nom, action]] of Object.entries(Object.entries(p.actions ?? {}))) {
            if (i > 0) {
                await page.goto(BASE + p.url, { waitUntil: 'domcontentloaded', timeout: 30000 });
                await page.waitForTimeout(3000);
            }
            try { await action(page); } catch (e) { erreurs.push(`action « ${nom} » : ${e.message.split('\n')[0]}`); }
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
