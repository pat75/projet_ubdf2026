/**
 * Verifie que le defilement enchaine les pages jusqu'a epuisement, meme
 * quand la page reste plus courte que la fenetre : dans ce cas le
 * navigateur n'emet aucun evenement de defilement, et le chargement
 * s'arretait apres la premiere page.
 */
import { JSDOM } from 'jsdom';
import fs from 'node:fs';

const dom = new JSDOM(`<!doctype html><html><body>
  <div class="ui container bloc_portfolios">
    <div class="visibility infinite">
      <div class="ui five doubling cards" id="accueil_portfolio">
        <div id="position_card_last"></div>
      </div>
      <div class="ui horizontal icon divider result_end"></div>
      <div class="ui large centered inline text loader"></div>
    </div>
  </div>
</body></html>`, { url: 'https://ubdf2026.ultra-book.name/illustrateur', pretendToBeVisual: true });

global.window = dom.window;
global.document = dom.window.document;

dom.window.eval(fs.readFileSync('public/html_pages_v2018/_/js_cdn/jquery-1.12.4.min.js', 'utf8'));
const $ = dom.window.jQuery;

dom.window.ubdf = { per_page: 10, category: 'illustrateur', selection: 'sel' };
dom.window.ub_ill_plus_de_book = { btn_slide() {} };
dom.window.ub_infinit = { post_traitement_dom() {} };

// La page reste plus courte que la fenetre : aucun defilement possible.
$.fn.height = function () { return 500; };
dom.window.document.documentElement.scrollTop = 0;

// 27 books repartis comme en base : 10, 10, 7, puis fin.
const pages = { 1: 10, 2: 10, 3: 7, 4: 0 };
const demandees = [];

$.getJSON = (url) => {
    const page = Number(url.split('/').pop());
    demandees.push(page);

    const n = pages[page] ?? 0;
    const html = Array.from({ length: n }, (_, i) =>
        `<div class="ui card newitem_hide" id="user_p${page}b${i}" data-user="p${page}b${i}"></div>`).join('\n');

    const d = $.Deferred();
    setTimeout(() => d.resolve({ html, count: n, fin: n < 10 }), 10);
    return d.promise();
};

new dom.window.Function('jQuery', fs.readFileSync('public/js/ubdf-infinite.js', 'utf8'))($);

setTimeout(() => {
    const cartes = $('#accueil_portfolio .ui.card').length;
    const cachees = $('#accueil_portfolio .ui.card.newitem_hide').length;
    const ok = cartes === 27 && cachees === 0 && demandees.join(',') === '1,2,3';

    console.log(`pages demandees : ${demandees.join(', ')}`);
    console.log(`cartes affichees : ${cartes} (attendu 27), invisibles : ${cachees}`);
    console.log(ok ? '[enchainement] OK' : '[enchainement] ECHEC');
    process.exit(ok ? 0 : 1);
}, 3000);
