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

function suggestions(langue) {
    const domaines = motcles[langue] ?? motcles.fr;
    const liste = [];

    for (const [domaine, mots] of Object.entries(domaines)) {
        const alias = motcles.alias.find((a) => a.title === domaine)?.alias.split('|')[0];
        liste.push({ domaine, titre: domaine, description: `catégorie ${domaine}`, alias, valeur: domaine });
        for (const mot of mots) {
            liste.push({ domaine, titre: `${domaine} ${mot}`, description: mot, valeur: `${domaine},${mot}` });
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
        resultats: [],
        erreurVide: false,
        liste: suggestions(document.documentElement.lang === 'en' ? 'en' : 'fr'),

        get groupes() {
            const groupes = {};
            for (const r of this.resultats) {
                (groupes[r.domaine] ??= []).push(r);
            }
            return Object.entries(groupes);
        },

        chercher() {
            const saisie = this.requete.trim();
            if (!saisie) {
                this.resultats = [];
                return;
            }
            const requete = domaineDeLAlias(saisie) ?? saisie;
            const debut = new RegExp(`^${requete.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`, 'i');
            this.resultats = this.liste
                .filter((s) => debut.test(s.titre) || debut.test(s.description))
                .slice(0, MAX_RESULTATS);
        },

        choisir(suggestion) {
            this.$el.closest('form').querySelector('[name=q]').value = suggestion.valeur;
            this.envoyer(this.$el.closest('form'), 'mcles');
        },

        envoyer(formulaire, type = 'pseudo') {
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
            formulaire.submit();
        },
    }));
}
