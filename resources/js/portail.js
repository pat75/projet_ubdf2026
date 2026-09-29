import Alpine from 'alpinejs';
import apparition from './portail/apparition';
import cartes from './portail/cartes';
import connexion from './portail/connexion';
import contact from './portail/contact';
import controles from './portail/controles';
import cookies from './portail/cookies';
import entete from './portail/entete';
import inscription from './portail/inscription';
import memoCompte from './portail/memo-compte';
import modales from './portail/modales';
import newsletter from './portail/newsletter';
import recherche from './portail/recherche';
import visionneuse from './portail/visionneuse';

/*
 * Front du portail (Ultra-book, Dustfolio). Chaque module remplace une
 * partie du JavaScript jQuery de 2019 (public/html_pages_v2018/_/js2019),
 * au fil de _doc/17_remplacement_jquery.md.
 */
[apparition, cartes, connexion, contact, controles, cookies, entete, inscription, memoCompte, modales, newsletter, recherche, visionneuse].forEach((module) => Alpine.plugin(module));

window.Alpine = Alpine;
Alpine.start();
