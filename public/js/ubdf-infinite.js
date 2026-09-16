/**
 * Defilement infini du portail Ultra-book.
 *
 * Reproduit le comportement du front 2018 : les cartes suivantes sont
 * demandees au serveur, inserees masquees (classe « newitem_hide »), puis
 * revelees une par une avec 40 ms d'ecart. La transition elle-meme est
 * portee par le CSS existant (opacite 0, translation de -30px, 0,3 s).
 *
 * jQuery est charge par LABjs de facon ASYNCHRONE : ce fichier ne peut pas
 * en dependre a son execution. Il attend donc sa disponibilite avant de
 * demarrer — sans cela il levait une ReferenceError et le defilement ne
 * fonctionnait pas du tout.
 */
(function () {
    'use strict';

    function demarrer($) {
        var conf = window.ubdf || {};

        if (!conf.category) {
            return; // L'accueil affiche des blocs par metier, sans defilement.
        }

        var $cards = $('#accueil_portfolio');

        if (!$cards.length) {
            return;
        }

        var $loader = $('.bloc_portfolios .loader');
        var $fin = $('.bloc_portfolios .result_end');

        /** Distance au bas de page qui declenche le chargement suivant. */
        var SEUIL = 600;

        var page = 0;
        var enCours = false;
        var termine = false;

        /**
         * Active une carte fraichement inseree.
         *
         * Le front 2018 n'attache l'ouverture en pleine page qu'au chargement
         * du document, sur les cartes deja presentes. Une carte arrivee par
         * defilement doit donc etre activee explicitement, comme le fait le
         * legacy apres chaque insertion : ub_infinit.post_traitement_dom()
         * appelle btn_slide(), qui pose le gestionnaire de clic.
         *
         * Les objets du front 2018 sont toujours lus sur « window » : ce
         * module est en mode strict, et une reference nue leverait une
         * ReferenceError tant que js_core_cards.js n'est pas charge.
         */
        function activer($carte, essai) {
            var infinit = window.ub_infinit;

            if (infinit && typeof infinit.post_traitement_dom === 'function') {
                infinit.post_traitement_dom('#user_' + $carte.data('user'));

                return;
            }

            essai = essai || 0;

            if (essai < 20) {
                setTimeout(function () {
                    activer($carte, essai + 1);
                }, 150);
            }

            // ub_ill_plus_de_book.stats_book() n'est volontairement pas
            // appele : il emet un « action=add » vers extra-book.com, le
            // serveur de statistiques de production. Le comptage des vues
            // sera reimplemente cote Laravel (table visit_stats).
        }

        /** Revele les cartes une a une, pour l'effet de cascade. */
        function reveler($nouvelles) {
            $nouvelles.each(function (index) {
                var $carte = $(this);

                /*
                 * L'apparition passe en premier et n'est jamais conditionnee
                 * par le reste : une carte doit s'afficher meme si le
                 * JavaScript du front 2018 est indisponible.
                 */
                setTimeout(function () {
                    $carte.removeClass('newitem_hide');

                    try {
                        if ($.fn.dimmer) {
                            $carte.dimmer({
                                selector: { dimmable: '.dimmable', dimmer: '.ui.dimmer' },
                                on: 'hover'
                            });
                        }
                    } catch (e) {
                        // Le survol degrade ne justifie pas de masquer la carte.
                    }
                }, index * 40);

                try {
                    activer($carte);
                } catch (e) {
                    if (window.console) {
                        console.warn('ubdf : activation de la carte impossible', e);
                    }
                }
            });
        }

        function restant() {
            return $(document).height() - ($(window).scrollTop() + $(window).height());
        }

        /**
         * Charge une page si le bas approche.
         *
         * Rappelee apres chaque insertion : quand les cartes ajoutees ne
         * suffisent pas a rallonger la page au-dela du seuil, aucun nouvel
         * evenement de defilement n'est emis et le chargement s'arreterait la.
         */
        function verifier() {
            if (!termine && !enCours && restant() < SEUIL) {
                charger();
            }
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

                    // Laisser les cartes s'inserer avant de remesurer la page.
                    setTimeout(verifier, 250);
                });
        }

        var attente = null;

        function planifier() {
            if (attente) {
                return;
            }

            attente = setTimeout(function () {
                attente = null;
                verifier();
            }, 120);
        }

        $(window).on('scroll.ubdf resize.ubdf', planifier);

        // Une page plus courte que la fenetre n'emet aucun defilement.
        $(planifier);
    }

    /*
     * Attend que LABjs ait charge jQuery. Un echec silencieux serait pire
     * qu'une erreur : on renonce au bout de dix secondes en le signalant.
     */
    var essais = 0;

    (function attendreJquery() {
        if (window.jQuery) {
            window.jQuery(function () {
                demarrer(window.jQuery);
            });

            return;
        }

        if (essais++ < 200) {
            setTimeout(attendreJquery, 50);

            return;
        }

        if (window.console) {
            console.warn('ubdf : jQuery indisponible, defilement infini inactif');
        }
    })();
})();
