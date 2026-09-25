



/* =Handlebars helper
 -------------------------------------------------------------- */

Swag.registerHelpers(Handlebars);

Handlebars.registerHelper('toLowerCase', function (value) {
    return (value && typeof value === 'string') ? value.toLowerCase() : '';
});

Handlebars.registerHelper('toUrl', function (value) {
    return (value && typeof value === 'string') ? value.toLowerCase().replace(/ /g, '_').replace(/é/g, 'e') : '';
});

Handlebars.registerHelper('toTraduction', function (value) {
    var nom = (value && typeof value === 'string') ? value.replace(/_/g, ' ') : '';
    return nom.charAt(0).toUpperCase() + nom.substring(1).toLowerCase();
});

Handlebars.registerHelper('splitQuote', function (string) {
    if (string.length == 2) // '[]'
    {
        var result = '';
    } else {
        var result = '<div>' + string.replace(/,/g, '</div><div>') + '</div>';
    }
    return new Handlebars.SafeString(result);
});

// nettoayge et mise en forme mcles
Handlebars.registerHelper('mcles', function (string) {

    if (string == '' || string == undefined) return '';

    //console.log(string);
    string = (string && typeof string === 'string') ? string.toLowerCase().replace(/&amp;quot;|&amp;amp;quot;|\[&amp;amp;quot;|\[&amp;quot;|;,&quot|&amp;|&quot|amp;quot;|&amp;quot;|,quot;/g, '') : '';

    if (string.length == 2) // '[]'
    {
        var result = '';
    } else {
        var result = '<div class="ui label">' + string.replace(/,/g, '</div><div  class="ui label">') + '</div>';
    }
    return new Handlebars.SafeString(result);
});


// set var
Handlebars.registerHelper('setVar',function(name, value, context ){
    this[name] = value;
});




// trouver la tagname
//
// http://www.kryzalid.net/blogue/2010/11/12/jquery-comment-trouver-le-type-de-balise-dun-element/
// http://docs.jquery.com/Plugins/Authoring
//
$.fn.tagName = function() {
    return this.get(0).tagName.toLowerCase();
};





// Capital
//
String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
};





// Tiny jQuery Plugin check el exit
// by Chris Goodchild
//
// Usage
/*
 $('div.test').exists(function() {
 this.append('<p>I exist!</p>');
 });	*/
//
$.fn.exists = function(callback) {
    var args = [].slice.call(arguments, 1);
    if (this.length) {
        callback.call(this, args);
    }
    return this;
};












$(document).ready(function () {

    console.log('load---> js_core_function');


    /* MENU - 2015 - 2019
     -------------------------------------------------------------- */

    ub_menu = {

        tmp: '',
        timeout3: null,

        init: function () {
            //console.log("fn ub_menu - init");

            // partage FB&TW
            ub_menu.menu_partage_show();


            // menu
            ub_menu.menu_toggle();			//nov2017

            ub_menu.menu_closebox();		//nov2017


            ub_menu.menu_formules(); 		//nov2017

            ub_menu.menu_aff_stats_book();


            // nb msg
            ub_plugin_front.nb_contact_message_menu_g();


            ub_menu.recherche_menu_top_init();

            //$("#search-menu").trigger('click');

        },


        // recherche_menu_top
        recherche_menu_top_init: function () {

            $("#search-menu").on('click', function () {
                // console.log('open');
                $('#bloc_rechercher_top_menu_modal')
                    .modal('show');

                $('#bloc_rechercher_top_menu_modal .close').unbind().on('click', function () {
                    $('#bloc_rechercher_top_menu_modal')
                        .modal('hide');
                })

                // change domain to all
                $('.bloc_titre h2').text('');
                $('.link_rechercher_change_domain')
                    .dropdown('set selected', 'tous')
                ;


            });

        },


        /* =submit formules -> /ubaction__user_pref_formule
         --------------------------------------------------------------*/
        menu_formules: function () {
            $('div.ub_fml_12mois, div.ub_fml_6mois').click(function (event) {
                $(this).find('form').submit();
            });
        },


        // test email
        validateEmail: function (email) {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        },

        /* =menu toggle #nov2017
         open/close   menu avec class toggle
         = voir page formules

         -------------------------------------------------------------- */
        menu_toggle: function () {

            $('.toggle .toggle_title').click(function () {
                $(this).parent().toggleClass("open");
            });

        },


        /* =menu closebox #nov2017
         open/close   menu avec class toggle
         = voir page menu droite

         -------------------------------------------------------------- */
        menu_closebox: function () {

            $('.closebox').addClass('load');

            // memo close/open
            if ($.cookie('closebox') == 'true') {
                $('.closebox').addClass('close');
            }

            // action close/open
            $('.closebox .close_btn ').click(function () {

                $box = $(this).parent();

                $box.toggleClass("close");

                // memo close/open
                if ($box.hasClass("close")) {
                    $.cookie('closebox', 'true', {expires: 60});
                } else {
                    $.cookie('closebox', 'false', {expires: 60});
                }

            });

        },


        /* =partage FB
         -------------------------------------------------------------- */
        //
        menu_partage_show: function () {

            $('#ub_btn_partage_fb,   #menu_contant_partage_fb .ub_icone.close').click(function (event) {
                event.preventDefault();
                $('#menu_contant_partage_fb').toggleClass("show");
                ub_menu.menu_partage_anim_icon($('div#bloc_menu_contant_partage_fb .fonticon-uniF051'));
            });


            $('#ub_btn_partage_tw,   #menu_contant_partage_tw .ub_icone.close').click(function (event) {
                event.preventDefault();
                $('#menu_contant_partage_tw').toggleClass("show");
                ub_menu.menu_partage_anim_icon($('div#bloc_menu_contant_partage_tw .fonticon-uniF057'));
            });
        },

        // animation icon
        menu_partage_anim_icon: function ($ele) {
            if ($ele.hasClass('anim_on')) return false;
            var $anim_ele = $ele.addClass('anim_on');
            TweenMax.to($anim_ele, 1.6, {
                scaleX: 0.8, scaleY: 0.8, force3D: true, yoyo: true, repeat: -1, ease: Power1.easeInOut
            });
            return true;
        },


        menu_aff_stats_book: function () {

            $.getJSON('/cache_js/data_stats.json', {_: new Date().getTime()})
                .fail(function (jqxhr, textStatus, error) {
                    var err = textStatus + ", " + error;
                    console.log("Request Failed: " + err);
                })
                .done(function (data) {
                    //console.log(data);

                    // modif les stats du menu
                    $('#ub_stats_nb_book, #ub_stats_nb_book2').text(data.menu_stats.nb_book);
                    $('#ub_stats_nb_sel, #ub_stats_nb_sel2').text(data.menu_stats.nb_selection);
                    $('#ub_stats_nb_img').text(data.menu_stats.nb_visuel);
                    $('#ub_stats_nb_gal').text(data.menu_stats.nb_galerie);


                    // modif les exemple du menu
                    var html = '';
                    var data_exemple = data.menu_book_exemple;

                    var data_url = $('#ub_exemple_book').data('url');

                    for (i = 0; i < 5; i++) {
                        select = Math.floor(Math.random() * data_exemple.length);
                        var choix = data_exemple[select];
                        data_exemple.splice(select, 1); // supp l'ele select

                        // ubdf
                        // replace_var = '/'+choix.url+'/i';
                        choix.url = choix.url.replace(/ultra-book(.com|.net|.org)/i, data_url);

                        html = html + '<a class="fade ' + choix.type.toUpperCase() + ' " href="' + choix.url + '" target="_blank"><i class="ub_icone ouvrir "></i>' + choix.nom + '<span> - ' + choix.type + '</span></a>';
                        //console.log( "---" + select + "---" + choix.nom );
                    }
                    ;

                    $('#ub_exemple_book').html(html);


                });

        }

    };


    /* function communes 2015
     -------------------------------------------------------------- */
    ub_fn = {


        init: function () {

            /*
             this.menu_responsive();

             this.menu_tooltip();

             this.compteur_de_mot();
             */


            this.UI_Lightbox_init();


        },


        /* =Semantic UI Close all modal
         -------------------------------------------------------------- */

        UI_Remove_all_modal: function () {
            // Delete any modals hanging around
            $('.ui.modal').each(function () {
                $(this).remove();
            });
        },

        UI_Close_all_modal: function () {
            // Delete any modals hanging around
            $('.ui.modal').each(function () {
                $(this).modal('hide');
            });
        },

        /* =Semantic UI Lightbox
         -------------------------------------------------------------- */

        UI_Lightbox_init: function () {

            $('.UI_Lightbox').click(function () {
                var image = $(this).children('img').attr('src');
                ub_fn.UI_Lightbox_create(image);
            })

        },

        UI_Lightbox_create: function (image) {

            ub_fn.UI_Remove_all_modal();

            var html_close = '<div class="btn_close outbox"><div></div></div>';

            // create and show
            $('body').append('<div class="ui basic modal"><div class="content">' + html_close + '<img class="ui centered image" src="' + image + '" /></div></div>');
            $('.ui.basic.modal').modal('show');

        },


        /* =user identifier
         --------------------------------------------------------------

        // test email
        validateEmail: function (email) {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        },

        menu_identifier: function () {

            // test si email
            $('#identifiant').change(function () {
                $el = $(this);
                if (ub_fn.validateEmail($el.val().trim())) {
                    console.log('id is a mail');

                    var $prompt = $el.parent().next('.prompt');

                    $prompt.addClass('show').fadeIn()
                        .delay(4600).fadeOut(1800, function () {
                        $prompt.removeClass('show')
                        $el.val('');
                    });

                }
            });
        },
         */

        /* url =hash
         -------------------------------------------------------------- */

        // get hash
        url_hash_get: function () {
            if (window.location.hash) {
                return window.location.hash.substring(1); //Puts hash in variable, and removes the # character
            } else {
                return false; // No hash found
            }
        },

        // add hash
        url_hash_add: function (hash) {
            if (history.pushState) {
                history.pushState(null, null, '#' + hash);
            } else {
                location.hash = '#' + hash;
            }
        },

        // del hash
        url_hash_del: function () {
            if (history.pushState) {
                history.pushState(null, null, ' ');
            } else {
                location.hash = ' ';
            }
        },


        // open book a partir du hash
        url_hash_openBook: function (hash) {

            var tmp_hash = this.url_hash_get();

            //console.log('url_hash_openBook: function > '+tmp_hash);


        },


        /* tooltip
         -------------------------------------------------------------- */

        menu_tooltip: function () {
            $('.tooltip_tipsy').tipsy({title: 'title', fade: true, offset: 3});
        },


        /* compteur de mot
         -------------------------------------------------------------- */
        compteur_de_mot: function () {

            $('.count_input').each(function () {

                // init max
                var max = $(this).next('span').text();//alert(max);
                $(this).next('span').data('max', max);
                ub_fn.compteur_de_mot_count($(this));
                $(this).keyup(function () {
                    ub_fn.compteur_de_mot_count($(this));
                });

            });
        },

        compteur_de_mot_count: function (src) {
            var txtVal = src.val();
            var dest = src.next('span');
            var max = dest.data('max');
            var chars = txtVal.length;
            if (chars > max) {
                dest.css({'color': 'red'});
            } else {
                dest.css({'color': '#444'});
            }
            dest.html(chars + ' / ' + max);
        }

    };


    /* =fn =format nombre
     -------------------------------------------------------------- */

    var ub_fn_nombre = {

        init: function () {
            //console.log('nb');
            $('.ub_format_sep').each(function () {
                $(this).html(
                    ub_fn_nombre.ub_format($(this).text(), '.')
                );
            });
        },


        ub_format: function (nStr, sep) {
            //console.log( 'nb -> ' + nStr );
            nStr += '';
            x = nStr.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + sep + '$2');
            }
            return x1 + x2;
        }

    };


    /* =fn =cursor
    -------------------------------------------------------------- */
/*
    var ub_fn_cursor = {

        init: function () {

            $circleCursor = $('.cursor');

            if (page_type == 'cms_user' || page_type == 'user') {
                $circleCursor.remove();
                return true;
            }

            //$circleCursor.hide();
            $(window).on('mousemove', this.handleMouseMove);


            $('.cursor_effect').on('mouseenter', this.zoomCursor);
            $('.cursor_effect').on('mouseleave', this.defaultCursor);
        },

        handleMouseMove: function (event) {
            //var x = event.pageX;
            //var y = event.pageY;
            var x = event.clientX;
            var y = event.clientY;
            $circleCursor.animate({
                left: x,
                top: y
            }, 2).show();
        },

        // simple zoom
        zoomCursor: function (e) {
            $($circleCursor).addClass('scale-up-center');
            $($circleCursor).removeClass('scale-down-center');
        },

        // default
        defaultCursor: function (e) {
            $($circleCursor).addClass('scale-down-center');
            $($circleCursor).removeClass('scale-up-center');
        }


    };

    // cursor init
    ub_fn_cursor.init();
*/


    /*
    var ub_fn_cursor2 = {


        cursor_move: false,

        init:  function () {

        console.log('ub_fn_cursor 2');



        $circleCursor = $('#cursor_follower');

        if ($('.btn_connection').is(":hidden")) {

            $circleCursor.hide();
            $('html').addClass('cursor_classique');

            return;
        }


        // v2
        ub_fn_cursor2.cursor_();


        },

        cursor_: function () {

        $('.cursor_effect').on('mouseenter', function () {
            $('#cursor_follower').addClass('scale-up-center');
        });
        $('.cursor_effect').on('mouseleave', function () {
            $('#cursor_follower').removeClass('scale-up-center');
        });


        (function () {
            var follower, init, mouseX, mouseY, positionElement, printout, timer;

            follower = document.getElementById('cursor_follower');

            mouseX = function (event) {
                return event.pageX;
            };

            mouseY = function (event) {
                return event.pageY;
            };

            positionElement = function (event) {

                var mouse;
                mouse = {
                    x: mouseX(event),
                    y: mouseY(event)
                };

                follower.style.top = mouse.y + 'px';
                return follower.style.left = mouse.x + 'px';
            };

            timer = false;

            window.onmousemove = init = function (event) {
                var _event;
                _event = event;
                return timer = setTimeout(function () {
                    return positionElement(_event);
                }, 2);
            };

        }).call(this);


    }
}

    ub_fn_cursor2.init();
    */











    /* infinit 2018
     -------------------------------------------------------------- */

    // scrool book+ aff #2018
    ub_infinit = {

        page_num: 0,


        init: function () {

            book.infinite();

            // aff stats
            //
            setTimeout(function () {
                // ub_ill_plus_de_book.aff_stats();
            }, 110);


        },


        /*
         // chargement des portfolios
         load_portfolios: function () {

         console.log('load_portfolios /page_suite')

         if (ub_infinit.stock_url_query_exist() ) {
         url_data.page_type = '/page_suite'
         }

         ub_infinit.GenerateItems( url_data );

         },
         */



        /*
         // url make
         url_recherche : function () {
         url_data = {
         page_type:      '/recherche',
         page_domaine:   '',
         page_num:       ub_infinit.page_num,
         book_auto_open: false,
         page_url:       '/rechercher_submit'
         }
         console.log('url recherche')
         return url_data;
         },

         // url make
         url_hash : function () {
         url_data = {
         page_type:      '/pseudo_hash',
         page_domaine:   '',
         page_num:       0,
         book_auto_open: true,
         book_id:        'ste',
         page_url:       '/rechercher_submit'
         }
         console.log('url hash')
         return url_data;
         },

         // url domaine
         url_domaine : function () {
         url_data = {
         page_type:      '/accueil',
         page_domaine:   'illustrateur',
         page_num:       ub_infinit.page_num,
         auto_open:      false
         }
         console.log('url domaine')
         return url_data;
         },
         */


        // ouverture automatique ds book via url#book
        auto_open_book: function () {
            var tmp_hash = ub_fn.url_hash_get();

            if (tmp_hash) {
                console.log(tmp_hash);
                // open book
                $('#user_' + tmp_hash).trigger('click');
            }
        },


        // supp contenu
        clean_portfolio: function () {

            $('#accueil_portfolio').html('<div id="position_card_last"></div>');
            $('.result_end').addClass('show');
        },

        // post traitement dom aprés aff des book 2019
        post_traitement_dom: function (selecteur) {


            // slider
            ub_ill_plus_de_book.btn_slide(selecteur);


           // console.log('post_traitement_dom > end ' + selecteur);


        },


        // readaptation des données ill et image
        readaptation: function (cont_retour) {

            //var path_taille_image = '/img_ptf_medium';
            //var path_taille_image = '/img_';
            // /users_2/g/a/gaeldezothez/img_ptf_medium/nouvelle_image__629333.jpg
            // /users/gaellemallet/images/ed65b508d2a888.jpg

            //var phpthumb = '/phpthumb_last/phpThumb.php?src=';
            //console.log(cont_retour);

            for (var i = 0, j = cont_retour.length; i < j; i++) {

                cont_retour[i].id_user = cont_retour[i].us_dir;
                cont_retour[i].us_prenom_nom = cont_retour[i].us_prenom + ' ' + cont_retour[i].us_nom;

                /*
                 cont_retour[i].us_vign  = 			cont_retour[i].us_path+'/'+cont_retour[i].us_pf_img_vignette;
                 */
                cont_retour[i].us_vign = cont_retour[i].us_pf_img_vignette;
                //console.log(cont_retour[i].us_vign);

                // bio 2016
                if (cont_retour[i].us_pf_img_photo_bio != '') {
                    cont_retour[i].img_bio = cont_retour[i].us_path + '/cms_pref/' + cont_retour[i].us_pf_img_photo_bio;
                    //console.log(cont_retour[i].img_bio);
                }

                // us_formule
                if (cont_retour[i].us_formule == 0) cont_retour[i].us_formule = '';

                // stats_st_cles -> http://www.extra-book.com
                if (page_type == 'memobook') {
                    cont_retour[i].stats_champ = 'st_memo';
                } else {
                    cont_retour[i].stats_champ = 'st_minibook';
                }

                //console.log(' us_path = '+cont_retour[i].us_path );

                var img_book = [];

                if (cont_retour[i].img) $.each(cont_retour[i].img, function (key, val) {
                    //console.log(key+ ' = '+val );
                    if (val) $.each(val, function (k, gal) {

                        if (k == 0) {
                            //console.log(k+ ' = '+gal.img_prim_350x190+ ' = '+gal.img_prim_350x190 );
                            img_book.push({
                                img_fichier: gal.img_prim_350x190, // _360x360
                                img_alt: gal.img_titre,
                                //img_fichier_mobile:gal.img_prim_350x190
                            });
                        } else {
                            //console.log(k+ ' = '+gal.img_sec_91x91 );
                            img_book.push({
                                img_fichier: gal.img_sec_91x91,
                                img_alt: gal.img_titre
                            });
                        }

                        //console.log(k+ ' = '+cont_retour[i].us_path+gal.img_fichier );
                        //img_book.push({img_fichier:cont_retour[i].us_path+path_taille_image+'/'+gal.img_fichier, img_alt:gal.img_titre});

                    });
                });
                cont_retour[i].img_book = img_book;


                // filtre domaine
                //if ($( '#nav_metiers_filtre ul li a[href=#'+ cont_retour[i].us_type.toLowerCase() +'] ').parent().hasClass('selected') ) cont_retour[i].us_filtre = 'hide';

            }
            ;


            // last book
            if (!cont_retour.length)        return true;
            if (cont_retour[i - 1].us_last)    return true;

            return false;

        },


    };



});
