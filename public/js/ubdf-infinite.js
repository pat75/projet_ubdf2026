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

    /** Revele les cartes une a une, pour l'effet de cascade. */
    function reveler($nouvelles) {
        $nouvelles.each(function (index) {
            var $carte = $(this);

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
