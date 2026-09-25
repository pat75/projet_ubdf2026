import Alpine from 'alpinejs';
import apparition from './portail/apparition';
import connexion from './portail/connexion';
import controles from './portail/controles';
import cookies from './portail/cookies';
import inscription from './portail/inscription';
import modales from './portail/modales';
import newsletter from './portail/newsletter';

/*
 * Front du portail (Ultra-book, Dustfolio). Chaque module remplace une
 * partie du JavaScript jQuery de 2019 (public/html_pages_v2018/_/js2019),
 * au fil de _doc/17_remplacement_jquery.md.
 */
[apparition, connexion, controles, cookies, inscription, modales, newsletter].forEach((module) => Alpine.plugin(module));

window.Alpine = Alpine;
Alpine.start();
