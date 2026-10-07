import motcles from './motcles.json';

/*
 * Formulaires de recherche du portail (.form_rechercher2018 : accueil,
 * barre mobile, fenetre de recherche), ex-ubdf_recherche et
 * book.submitForm de js_core_pages.js.
 *
 * Le JavaScript de 2019 reecrivait l'accueil en ajax ; le formulaire
 * aboutit desormais a la page /recherche, rendue par le serveur, qu'il
 * visait deja sans JavaScript. Saisie libre : recherche par nom
 * (type_recherche=pseudo). Suggestion choisie : recherche par mots-cles
 * (mcles), « domaine,mot-cle » comme le legacy.
 *
 * Les suggestions viennent de motcles.json (ex-tpl_conf_msg/
 * motcles_data_front_fr_en.json) : domaines et mots-cles, et alias
 * (« illustrateur » -> illustration).
 */
const MAX_RESULTATS = 38;
const MIN_BASE = 3;
// Ecart entre le dernier caractere saisi et le bouton ✕.
const ECART_VIDER = 20;
const GROUPE_BASE = 'mots-clés';

function suggestions(langue) {
    const domaines = motcles[langue] ?? motcles.fr;
    const liste = [];

    for (const [domaine, mots] of Object.entries(domaines)) {
        const alias = motcles.alias.find((a) => a.title === domaine)?.alias.split('|')[0];
        // etiquette/couleur : affichage en label (x-portail.resultats-recherche).
        liste.push({ domaine, titre: domaine, description: `catégorie ${domaine}`, alias, valeur: domaine,
            etiquette: domaine, couleur: domaine });
        for (const mot of mots) {
            liste.push({ domaine, titre: `${domaine} ${mot}`, description: mot, valeur: `${domaine},${mot}`,
                etiquette: mot, couleur: domaine });
        }
    }

    return liste;
}

/* « illustrateurs » -> « illustration » : le domaine dont un alias correspond. */
function domaineDeLAlias(requete) {
    return motcles.alias.find((a) => new RegExp(a.alias, 'i').test(requete) || a.title === requete)?.title;
}

export default function recherche(Alpine) {
    // Options communes aux formulaires (popup .popup_rechercher_options).
    Alpine.store('optionsRecherche', { selection: false, abonnes: false });

    Alpine.data('recherche', () => ({
        requete: '',

        /*
         * Le ✕ se recale a chaque nouvelle recherche (requete, voir
         * x-effect), mais aussi quand la mise en page bouge sans que le texte
         * change : polices chargees, fenetre redimensionnee, focus.
         */
        init() {
            // $nextTick : les x-ref des enfants ne sont pas encore enregistres.
            this.$nextTick(() => {
                if (!this.$refs.vider) return;
                const recaler = () => requestAnimationFrame(() => this.placerVider());
                document.fonts?.ready.then(recaler);
                window.addEventListener('resize', recaler, { passive: true });
                this.$refs.champ.addEventListener('focus', recaler);
                this.$refs.champ.addEventListener('keyup', recaler);
            });
        },
        resultats: [],
        erreurVide: false,
        liste: suggestions(document.documentElement.lang === 'en' ? 'en' : 'fr'),

        chercher() {
            const saisie = this.requete.trim();
            if (!saisie) {
                this.numero++;
                this.resultats = this.motsBase = this.catalogue = [];
                return;
            }
            const requete = domaineDeLAlias(saisie) ?? saisie;
            const debut = new RegExp(`^${requete.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`, 'i');
            this.catalogue = this.liste
                .filter((s) => debut.test(s.titre) || debut.test(s.description))
                .slice(0, MAX_RESULTATS);
            this.resultats = [...this.motsBase, ...this.catalogue];
            this.chercherEnBase(saisie);
        },

        /*
         * Des 3 caracteres : les mots-cles saisis par les createurs
         * (GET /recherche/suggestions), en tete de la liste deroulante.
         * Une reponse arrivee apres une frappe plus recente est ignoree.
         */
        catalogue: [],
        motsBase: [],
        numero: 0,

        async chercherEnBase(saisie) {
            const numero = ++this.numero;
            if (saisie.length < MIN_BASE) {
                this.motsBase = [];
                this.resultats = this.catalogue;
                return;
            }
            try {
                const reponse = await fetch(`/recherche/suggestions?q=${encodeURIComponent(saisie)}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!reponse.ok || numero !== this.numero) return;
                this.motsBase = (await reponse.json()).map(({ mot, total }) => ({
                    domaine: GROUPE_BASE,
                    titre: mot,
                    description: `${total} portfolio${total > 1 ? 's' : ''}`,
                    valeur: mot,
                    etiquette: mot,
                    couleur: null,
                }));
                this.resultats = [...this.motsBase, ...this.catalogue];
            } catch {
                // Sans reseau, les suggestions du catalogue suffisent.
            }
        },

        /*
         * Bouton ✕ (bloc-recherche) : ECART_VIDER (20 px) apres le dernier caractere saisi.
         * La largeur du texte est mesuree avec la police du champ ; le
         * bouton ne deborde jamais du bord droit du champ.
         */
        placerVider() {
            const { champ, vider } = this.$refs;
            if (!champ || !vider) return;
            const style = getComputedStyle(champ);
            const mesure = (this.mesure ??= document.createElement('canvas').getContext('2d'));
            mesure.font = `${style.fontStyle} ${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
            const texte = mesure.measureText(champ.value).width;

            const debut = champ.offsetLeft + parseFloat(style.paddingLeft) + parseFloat(style.borderLeftWidth);
            const maximum = champ.offsetLeft + champ.offsetWidth - vider.offsetWidth - 8;
            vider.style.left = `${Math.min(debut + texte - champ.scrollLeft + ECART_VIDER, maximum)}px`;

            // Verticalement : centre sur la ligne de texte (zone de contenu,
            // hors padding — le libelle flottant decale le texte vers le bas).
            const hautContenu = champ.offsetTop + parseFloat(style.borderTopWidth) + parseFloat(style.paddingTop);
            const hauteurContenu = champ.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom);
            vider.style.top = `${hautContenu + hauteurContenu / 2}px`;
        },

        /* Bouton ✕ : champ vide, suggestions fermees, curseur rendu au champ. */
        vider(formulaire) {
            this.numero++;
            this.requete = '';
            this.resultats = this.motsBase = this.catalogue = [];
            formulaire.querySelector('[name=q]').focus();
        },

        choisir(suggestion) {
            // Par `requete` (x-model) et non par le DOM : le ✕ suit le texte.
            this.requete = suggestion.valeur;
            this.$el.closest('form').querySelector('[name=q]').value = suggestion.valeur;
            this.envoyer(this.$el.closest('form'), 'mcles');
        },

        envoyer(formulaire, type = 'mcles') {
            const q = formulaire.querySelector('[name=q]');
            if (!q.value.trim()) {
                this.erreurVide = true;
                setTimeout(() => (this.erreurVide = false), 2000);
                return;
            }

            formulaire.querySelector('[name=type_recherche]').value = type;
            const options = Alpine.store('optionsRecherche');
            for (const [nom, actif] of [['flt_sel', options.selection], ['flt_pro', options.abonnes]]) {
                formulaire.querySelector(`input[type=hidden][name=${nom}]`)?.remove();
                if (actif) {
                    const champ = Object.assign(document.createElement('input'), { type: 'hidden', name: nom, value: 'true' });
                    formulaire.appendChild(champ);
                }
            }
            if (formulaire.hasAttribute('data-ajax')) {
                this.resultats = [];
                this.envoyerAjax(formulaire);
            } else {
                formulaire.submit();
            }
        },

        /*
         * Page /search : les resultats s'affichent sous le bloc sans
         * recharger. L'URL suit la requete (partageable, bouton retour), et
         * le defilement infini repart sur les nouveaux criteres.
         */
        async envoyerAjax(formulaire) {
            const zone = document.getElementById('resultats_recherche');
            const url = new URL(formulaire.action, window.location.origin);
            url.search = new URLSearchParams(new FormData(formulaire)).toString();

            zone.setAttribute('aria-busy', 'true');
            // Loader Semantic UI a la place des anciens resultats, le temps de la requete.
            zone.innerHTML = '<div class="ui container" style="padding: 60px 0"><div class="ui active centered inline large loader"></div></div>';
            try {
                const reponse = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!reponse.ok) throw new Error(reponse.status);
                const donnees = await reponse.json();

                window.ubdf = { ...window.ubdf, ...donnees.ubdf };
                zone.innerHTML = donnees.html; // HTML rendu par le serveur (front.partials.resultats-recherche)
                history.replaceState(null, '', url);
                this.$nextTick(() => this.placerVider());
                zone.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch {
                formulaire.submit();
            } finally {
                zone.removeAttribute('aria-busy');
            }
        },
    }));
}
