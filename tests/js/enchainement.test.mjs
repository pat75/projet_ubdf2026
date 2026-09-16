/**
 * Defilement infini : conditions reelles du navigateur.
 *
 * Deux contraintes que les premiers tests ne reproduisaient pas, et qui
 * expliquaient que le defilement ne marche pas alors qu'ils passaient :
 *
 *   1. jQuery est charge par LABjs de facon ASYNCHRONE, donc il n'existe
 *      pas quand ce script s'execute. Ici il n'est injecte qu'apres.
 *   2. La page est plus courte que la fenetre : aucun evenement de
 *      defilement n'est jamais emis.
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

dom.window.ubdf = { per_page: 10, cartes_url: '/cartes/illustrateur', cartes_params: { selection: 'sel' } };

// Le script est evalue AVANT que jQuery soit disponible, comme en production.
dom.window.eval(fs.readFileSync('public/js/ubdf-infinite.js', 'utf8'));

const demandees = [];

// LABjs livre jQuery un peu plus tard.
setTimeout(() => {
    dom.window.eval(fs.readFileSync('public/html_pages_v2018/_/js_cdn/jquery-1.12.4.min.js', 'utf8'));
    const $ = dom.window.jQuery;

    dom.window.ub_ill_plus_de_book = { btn_slide() {} };
    dom.window.ub_infinit = { post_traitement_dom() {} };

    // Page plus courte que la fenetre : pas de defilement possible.
    $.fn.height = function () { return 500; };

    // 27 books repartis comme en base : 10, 10, 7, puis fin.
    const pages = { 1: 10, 2: 10, 3: 7, 4: 0 };

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
}, 400);

setTimeout(() => {
    const $ = dom.window.jQuery;
    const cartes = $ ? $('#accueil_portfolio .ui.card').length : 0;
    const cachees = $ ? $('#accueil_portfolio .ui.card.newitem_hide').length : 0;
    const ok = cartes === 27 && cachees === 0 && demandees.join(',') === '1,2,3';

    console.log(`pages demandees : ${demandees.join(', ') || 'aucune'}`);
    console.log(`cartes affichees : ${cartes} (attendu 27), invisibles : ${cachees}`);
    console.log(ok ? '[enchainement] OK' : '[enchainement] ECHEC');
    process.exit(ok ? 0 : 1);
}, 4000);
