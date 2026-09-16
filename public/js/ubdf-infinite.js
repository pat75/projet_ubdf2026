/**
 * Defilement infini du portail Ultra-book.
 *
 * Reproduit le comportement du front 2018 : les cartes suivantes sont
 * demandees au serveur, inserees masquees (classe « newitem_hide »), puis
 * revelees une par une avec 40 ms d'ecart. La transition elle-meme est
 * portee par le CSS existant (opacite 0, translation de -30px, 0,3 s).
 */
(function ($) {
    'use strict';

    var conf = window.ubdf || {};

    if (!conf.category) {
        return; // L'accueil affiche des blocs par metier, sans defilement.
    }

    var $cards = $('#accueil_portfolio');
    var $loader = $('.bloc_portfolios .loader');
    var $fin = $('.bloc_portfolios .result_end');

    if (!$cards.length) {
        return;
    }

    var page = 0;
    var enCours = false;
    var termine = false;

    /**
     * Active une carte fraichement inseree.
     *
     * Le front 2018 n'attache l'ouverture en pleine page qu'au chargement du
     * document, sur les cartes deja presentes. Une carte arrivee par
     * defilement doit donc etre activee explicitement, exactement comme le
     * fait le legacy apres chaque insertion :
     * ub_infinit.post_traitement_dom() appelle btn_slide(), qui pose le
     * gestionnaire de clic ouvrant la lightbox.
     *
     * Sans cet appel, les premieres cartes s'ouvrent et les suivantes non.
     */
    function activer($carte, essai) {
        var selecteur = '#user_' + $carte.data('user');

        if (window.ub_infinit && typeof ub_infinit.post_traitement_dom === 'function') {
            ub_infinit.post_traitement_dom(selecteur);

            return;
        }

        /*
         * js_core_cards.js est charge de facon asynchrone par LABjs : sur un
         * defilement tres rapide, il peut ne pas etre encore la. On reessaie
         * brievement plutot que d'abandonner la carte, qui resterait alors
         * muette au clic.
         */
        essai = essai || 0;

        if (essai < 20) {
            setTimeout(function () {
                activer($carte, essai + 1);
            }, 150);
        }

        // ub_ill_plus_de_book.stats_book() n'est volontairement pas appele :
        // il envoie un « action=add » vers https://www.extra-book.com, qui
        // est le serveur de statistiques de production. Le comptage des vues
        // sera reimplemente cote Laravel (table visit_stats).
    }

    /** Revele les cartes une a une, pour l'effet de cascade. */
    function reveler($nouvelles) {
        $nouvelles.each(function (index) {
            var $carte = $(this);

            // L'activation ne depend pas de l'animation : on l'applique
            // tout de suite, pour qu'un clic pendant le fondu fonctionne.
            activer($carte);

            setTimeout(function () {
                $carte.removeClass('newitem_hide');

                if ($.fn.dimmer) {
                    $carte.dimmer({
                        selector: { dimmable: '.dimmable', dimmer: '.ui.dimmer' },
                        on: 'hover'
                    });
                }
            }, index * 40);
        });
    }

    function charger() {
        if (enCours || termine) {
            return;
        }

        enCours = true;
        page += 1;
        $loader.addClass('active');

        $.getJSON('/cartes/' + conf.category + '/' + page, { selection: conf.selection || 'sel' })
            .done(function (reponse) {
                if (reponse.count > 0) {
                    var $nouvelles = $(reponse.html);
                    $('#position_card_last').before($nouvelles);
                    reveler($nouvelles.filter('.ui.card'));
                }

                if (reponse.fin) {
                    termine = true;
                    $fin.show();
                }
            })
            .fail(function () {
                // Une page ratee ne doit pas bloquer les suivantes.
                page -= 1;
            })
            .always(function () {
                enCours = false;
                $loader.removeClass('active');
            });
    }

    /** Declenche le chargement a l'approche du bas de page. */
    var attente = null;

    $(window).on('scroll.ubdf', function () {
        if (attente) {
            return;
        }

        attente = setTimeout(function () {
            attente = null;

            var restant = $(document).height() - ($(window).scrollTop() + $(window).height());

            if (restant < 600) {
                charger();
            }
        }, 120);
    });
})(jQuery);
