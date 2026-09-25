/*
 * Pont entre le JavaScript jQuery de 2019 et les composants Alpine du
 * portail (resources/js/portail), le temps du remplacement de jQuery
 * (_doc/17_remplacement_jquery.md).
 *
 * Charge apres Semantic UI, il remplace $.fn.modal : les appels existants
 * ($('.ui.modal.modal_connection').modal('show'), 'hide', 'hide all',
 * 'is active', reglages onShow / onHidden) pilotent desormais
 * $store.modale. A retirer quand plus aucun script n'appelle .modal().
 */
(function ($) {
    var magasin = function () { return window.Alpine && window.Alpine.store('modale'); };

    $.fn.modal = function (commande) {
        var nom = this.first().attr('data-modale');
        var store = magasin();

        if (!store) {
            return this;
        }

        if (typeof commande === 'object' && nom) {
            var el = this.get(0);
            store.reglages(nom, {
                apresOuverture: commande.onShow ? function () { commande.onShow.call(el, function () {}); } : undefined,
                apresFermeture: commande.onHidden ? function () { commande.onHidden.call(el); } : undefined
            });
            return this;
        }

        switch (commande) {
            case 'show':
                if (nom) store.ouvrir(nom);
                return this;
            case 'hide':
            case 'hide all':
                store.fermer();
                return this;
            case 'is active':
                return nom ? store.ouverte === nom : false;
            default:
                return this;
        }
    };
})(jQuery);
