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
global.navigator = dom.window.navigator;

// jQuery 1.12.4, celui que le site sert reellement
const jqSrc = fs.readFileSync('public/html_pages_v2018/_/js_cdn/jquery-1.12.4.min.js', 'utf8');
dom.window.eval(jqSrc);
const $ = dom.window.jQuery;

// Configuration injectee par le layout
dom.window.ubdf = { per_page: 10, cartes_url: '/cartes/illustrateur', cartes_params: { selection: 'sel' } };

/*
 * Scenario pilote par la variable d'environnement SCENARIO :
 *
 *   absent   — js_core_cards.js pas encore charge par LABjs. Referencer
 *              « ub_infinit » nu leverait une ReferenceError.
 *   partiel  — ub_infinit existe mais ub_ill_plus_de_book pas encore : c'est
 *              le cas qui laissait les cartes invisibles.
 *   complet  — front 2018 entierement charge, cas nominal.
 */
const scenario = process.env.SCENARIO || 'partiel';
const active = [];

if (scenario === 'partiel') {
    dom.window.ub_infinit = {
        post_traitement_dom(sel) { return ub_ill_plus_de_book.btn_slide(sel); }
    };
}

if (scenario === 'complet') {
    dom.window.ub_ill_plus_de_book = { btn_slide(sel) { active.push(sel); } };
    dom.window.ub_infinit = {
        post_traitement_dom(sel) { return dom.window.ub_ill_plus_de_book.btn_slide(sel); }
    };
}

// Reponse du serveur simulee
const cartes = Array.from({ length: 3 }, (_, i) =>
  `<div class="ui card illustrateur newitem_hide" id="user_book${i}" data-user="book${i}"></div>`).join('\n');

$.getJSON = (url, data) => {
    const d = $.Deferred();
    setTimeout(() => d.resolve({ html: cartes, count: 3, fin: true }), 10);
    return d.promise();
};

// Charger le script tel quel
dom.window.console.warn = () => {};   // avertissements attendus en mode degrade

const code = fs.readFileSync('public/js/ubdf-infinite.js', 'utf8');
new dom.window.Function('jQuery', code)($);

// Declencher le chargement
$(dom.window).trigger('scroll');

setTimeout(() => {
    const total = $('#accueil_portfolio .ui.card').length;
    const cachees = $('#accueil_portfolio .ui.card.newitem_hide').length;
    const visibles = total === 3 && cachees === 0;

    // Quel que soit l'etat du front 2018, les cartes doivent s'afficher.
    let ok = visibles;
    let detail = `${total} carte(s), ${cachees} encore invisible(s)`;

    // Front 2018 complet : le clic doit avoir ete pose sur chaque carte.
    if (scenario === 'complet') {
        ok = ok && active.length === 3;
        detail += `, ${active.length} activee(s)`;
    }

    console.log(`[${scenario}] ${ok ? 'OK' : 'ECHEC'} — ${detail}`);
    process.exit(ok ? 0 : 1);
}, 900);
