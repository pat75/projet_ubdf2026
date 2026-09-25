

$(document).ready(function () {

    console.log('load---> js_core_pages');



    var page_num
        ;




    /* func de la page accueil et domaines
     -------------------------------------------------------------- */
    // En-tete, menus, fenetres et retour en haut : resources/js/portail
    // (entete.js, modales.js). Reste ici les options de recherche.
    ubdf_accueil = {

        init: function () {
            book.submit_option_btn();
        },
    };


    /* func de la page WP
     -------------------------------------------------------------- */
    ubdf_wp = {

        init: function () {
            this.popup();
            this.other();
        },

        // http://whoisryosuke.com/blog/2018/shortcode-semantic-ui-lightbox/

        popup: function () {

            $('.colorbox_zoom').each(function(){
                $(this).on('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    $('#modal_temp').remove();

                    var image = $(this).children('img').attr('src');
                    $('body').append('<div class="ui long modal" id="modal_temp"><div class="content"><img src="'+image+'" width="100%" /></div></div>');
                    $('.ui.long.modal')
                        .remove()
                        .modal('show');

                })
            });

        },


        other: function () {

            // menu selected
            var url = location.pathname;
            console.log(url)
            if (url=="/doc/") return true;

            $('ul#principale li a').each(function () {
                var link = $(this).attr('href');
                if (link.match(url)) {
                    $(this).addClass('selected').prepend('<i class="fonticon-uniF006 fonticon_b18 "></i>')
                }
                ;
            });

            // chapitre
            //

            if (url=="/doc/questions-frequentes-2") {

                $('#wp_cont h4').each(function () {

                    $(this).addClass('close').nextUntil('h4').hide();

                    $(this)
                        .toggle(function () {
                            $(this).removeClass('close').nextUntil('h4').show();
                        }, function () {
                            $(this).addClass('close').nextUntil('h4').hide();
                        });

                });
            }
        }





},






    /* func book 2018
     -------------------------------------------------------------- */
    // func sur les books #2018
    book = {

        data_url: {},

        // $loader:        $('.infinite .loader'),
        //$result_end:    $('.infinite .result_end'),
        page_num: 0,
        //
        infinite_ready: false,
        //

        // tpl book card
        ub_theTemplate: Handlebars.compile($("#tpl_book").html()),

        // exclusivite des api sur la pagetype domaine
        book_api_show_no_used: true,


        // url make
        url_recherche: function () {
            url_data = {
                page_type: '/recherche',
                page_domaine: '',
                page_num: book.page_num,
                book_auto_open: false,
                page_url: '/rechercher_submit'
            }
            console.log('url recherche')
            return url_data;
        },

        // url make
        url_hash: function (id) {
            url_data = {
                page_type: '/pseudo_hash',
                page_domaine: '',
                page_num: 0,
                book_auto_open: true,
                book_id: id,
                page_url: '/rechercher_submit'
            }
            console.log('############### url hash >'+id)
            return url_data;
        },

        // menu version light fixed
        menu_lightfixed: function (yes) {
            if (yes) {
                $('#menu-top-fixed').addClass('light light_permanent');
            } else {
                $('#menu-top-fixed').removeClass('light light_permanent');
            }
        },


        // url domaine
        url_domaine: function (dom) {

            url_data = {
                page_type: '/accueil',
                page_domaine: dom, //'illustrateur',
                page_num: book.page_num,
                auto_open: false
            }

            console.log('url domaine ' + dom)
            return url_data;
        },

        // infinit init

        infinite: function () {

            $('.infinite')
                .visibility({
                    once: false,
                    // update size when new content loads
                    observeChanges: true,
                    // load content on bottom edge visible
                    onBottomVisible: function () {

                        console.log('infinit 1')

                        if (!book.infinite_ready) return;

                        console.log('infinit 2')


                        // page suite
                        var data_url = book.infinit_page_suite();
                        //console.log(data_url);

                        // aff book
                        book.show_book(data_url);


                    }
                })
            ;

        },

        // page de suite pour infinit scrool
        infinit_page_suite: function () {

            // page suite
            var data_url = book.stock_url_query_load();

            // +1
            book.page_num++;
            data_url.tmp_data.suite = book.page_num;

            // url refonte pour accueil
            if (data_url.page_type == "/accueil" ) {
                data_url.url = data_url.page_type + '__sel__' + data_url.tmp_data.anu_type + '__' + (book.page_num + 1);
            }

            // stock_url_save
            book.stock_url_query_save(data_url);

            //console.log('book.page_num >>>>>>>>>> ' + book.page_num);

            return data_url;
        },


        // construct url
        make_url: function (url_data) {

            //console.log('book make_url: function > ' + url_data.page_type);

            var error = false;


            switch (url_data.page_type) {

                // page suite  des recherches
                case '/page_suite':
                    //console.log('make_url /page_suite')
                    //
                    tmp = ub_infinit.stock_url_query_load()
                    url = tmp[0];
                    tmp_data = tmp[1];

                    break

                // page accueil + metier
                case '/home':
                case '/home_domaine':
                case '/accueil':
                    //console.log('make_url /accueil')
                    // url
                    url_data.page_num++;

                    var tmp_data = {
                        'anu_type':     url_data.page_domaine,
                        'page_type':    url_data.page_type,
                        'suite':        url_data.page_num
                    };

                    var url = url_data.page_type + '__sel__' + url_data.page_domaine + '__' + url_data.page_num;
                    break


                // rehercher hash
                case '/pseudo_hash':
                    console.log('make_url /pseudo_hash')
                    // url hash
                    var tmp_data = {
                        'anu_type': 'tous',
                        'flt_sel': false,
                        'flt_pro': false,
                        'page_type': 'book_rechercher_ajax',
                        'recherche': 'pseudo_hash',
                        'suite':    url_data.page_num,
                        'q':        url_data.book_id
                    };

                    var url = url_data.page_url;
                    break


                case '/recherche':
                    console.log('make_url /recherche')
                    // url

                    var query_input = $(".rech2018").find("input[name=q]").val();

                    var tmp_data = {
                        'anu_type': $(".rech2018 input[name=page_domaine]").val(),
                        'flt_sel': $("#bloc_menu_contant_metiers .flt_sel").hasClass('checked'),
                        'flt_pro': $("#bloc_menu_contant_metiers .flt_pro").hasClass('checked'),
                        'page_type': 'book_rechercher_ajax',
                        'recherche': $(".rech2018 input[name=type_recherche]").val(),
                        'suite': url_data.page_num,
                        'q': query_input    //'illustration,3D;drawing 3d'
                    };

                    // patch recherche motcles
                    if (tmp_data.recherche == 'mcles') {

                        if (tmp_data.anu_type == '') {
                            tmp_data.anu_type = 'tous';
                        }

                        tmp_data.q = ubdf_recherche.motcles_query;


                        // version EN/FR
                        console.log('query version EN/FR >>>>>>>>>>> ' + ubdf_recherche.motcles_query)
                        //console.log(ubdf_recherche.index_fr_en);

                        if (lang == 'fr') {

                            var found = ubdf_recherche.index_fr_en.find(function (element) {
                                //if (element.fr == tmp_data.q)console.log(element.fr + ' ' + tmp_data.q);
                                return element.fr == tmp_data.q;
                            });

                            var o = Object(found);
                            //console.log( o.en );
                            q_suite = o.en;
                        } else {

                            var found = ubdf_recherche.index_fr_en.find(function (element) {
                                return element.en == tmp_data.q;
                            });

                            var o = Object(found);
                            //console.log( o.fr );
                            q_suite = o.fr;
                        }
                        // query add en
                        tmp_data.q += ';' + q_suite;
                    }

                    // query
                    //console.log(tmp_data)
                    var url = url_data.page_url;

                    if (tmp_data.q == '') {
                        error = true;
                    }


                    break

            }


            var data_url = {
                page_type: url_data.page_type,
                url: url,
                tmp_data: tmp_data,
                error: error,
                query_input: query_input
            };

            //console.log(data_url)

            return data_url;


        },

        // recharge les input de recherherchelors du changement de page
        update_input_search: function (data_url) {

            $(".rech2018 input[name=type_recherche]").val(data_url.tmp_data.recherche)

            var tmp_q = data_url.tmp_data.q.split(";");
            tmp_q = tmp_q[0].replace(",", " ");
            $('.rech2018 input[name="q"]').val(tmp_q); // data_url.query_input

        },


        // stock data url+query
        stock_url_dom: 'body',
        //
        stock_url_query_save: function (tmp_data) {

            console.log('stock_url_save')

            // save
            $(book.stock_url_dom).data('search_data', JSON.stringify(tmp_data));


        },
        // stock url + query in infinite - load
        stock_url_query_load: function () {
            console.log('stock_url_query_load');
            if (tmp = $(book.stock_url_dom).data('search_data')) {
                tmp_data = JSON.parse(tmp);
                return tmp_data;
            }
            return false;
        },
        // stock url + query in infinite - exist ?
        stock_url_query_exist: function () {
            url = $(book.stock_url_dom).data('search_data');
            if (url == undefined) return false;
            return true;
        },
        // stock url + query in infinite - deleted #lors d'une nouvelle recherche
        stock_url_query_deleted: function () {
            $(book.stock_url_dom)
                .data('search_data', '');
        },

        // debug
        tmp_callback: function () {
            console.log('############callback tmp_aff_book')
        },

        // clean dom book lors d'une nouvelle recherche
        clear_book: function (callback) {
            $('.cards')
                .fadeOut('fast',
                    function () {
                        $(this)
                            .html('<div id="position_card_last"></div>')
                            .fadeIn('fast', function () {

                                //$('.infinite .loader').addClass('active');
                                //book.$loader.addClass('active');
                                book.show_loader(true);

                                book.infinite();

                                console.log('clear book > end');
                                if (callback) callback();

                            });
                    });
        },


        // modif bloc avec tpl + transition
        modif_bloc: function (tpl_id, tpl_data, dom_id, callback) {


            var tmp_ub_theTemplate = Handlebars.compile($(tpl_id).html());
            var tmp_html = tmp_ub_theTemplate(tpl_data);


            $(dom_id)
                .hide()
                .html(tmp_html)
                .transition('hide')
                .transition({
                    animation: 'horizontal flip',
                    duration: '900ms',
                    onComplete: function () {

                        if (callback) callback();
                    }
                })
            ;
        },

        // modif page tpl
        modif_page: function (callback) {


            // menu version light
            $('#menu-top-fixed').addClass('light');

            // change type page
            $('.bloc_base_contant').addClass('bloc_base_domaine');


            var ub_theTemplate_page_ptf = Handlebars.compile($("#tpl_bloc_portfolios").html());
            var html = ub_theTemplate_page_ptf({});

            $('#bloc_home_content').addClass('light'); // menu du haut en light

            $('.bloc_base_contant')
                .fadeOut('fast',
                    function () {

                        $(this)
                            .html(html)
                            .fadeIn('fast', function () {

                                // autocompletion
                                ubdf_recherche.rechercher_accueil_autocompletion();

                                book.infinite();

                                book.submit_btn();

                                book.submit_option_btn();

                                console.log('modif_template_recherche > end');
                                if (callback) callback();

                            });

                    });

            page_type = "domaine";


        },


        // aff bloc titre portfolios
        modif_bloc_titre: function (tmp_data) {

            console.log('modif_bloc_titre function');
            // bloc_titre_recherche
            //var terme_recherche =  $(".rech2018 input[name=q]").val();
            // bloc_titre_recherche
            var terme_recherche = tmp_data.q.split(';');
            terme_recherche = terme_recherche[0];

            if (tmp_data.anu_type == 'tous') {
                var terme_domaine = ub_msg_core.msg_type_recherche_globale;//' globale';
            } else {
                var terme_domaine = ub_msg_core.msg_type_recherche_in+' ' + tmp_data.anu_type;
            }

            var bloc_titre_recherche_theTemplate = Handlebars.compile($("#tpl_bloc_titre_recherche").html());

            if (tmp_data.recherche == 'mcles') {
                //type_recherche = "Mots clés";
                type_recherche =  ub_msg_core.msg_type_recherche_motscles;
            } else {
                //type_recherche = "Nom";
                type_recherche =  ub_msg_core.msg_type_recherche_nom;
            }

            var bloc_titre_recherche_tpl = bloc_titre_recherche_theTemplate({
                type_recherche: type_recherche,
                terme_recherche: terme_recherche,
                domaine: terme_domaine,
                flt_pro: tmp_data.flt_pro,
                flt_sel: tmp_data.flt_sel
            });

            $('.bloc_portfolios .bloc_titre').html(bloc_titre_recherche_tpl);


        },

        // tpl book
        _renderItem: function (cont_retour) {
            var html = book.ub_theTemplate({books: cont_retour});
            return html;
        },

        // affiche le loader
        show_loader: function (yes) {

            if (yes) {
                $('.loader').addClass('active');
            } else {
                $('.loader').removeClass('active');
            }

        },

        // affiche end result
        show_end: function (yes) {

            console.log('show_end: function '+yes);

            if (yes) {
                $('.result_end').addClass('show');
            } else {
                $('.result_end').removeClass('show');
            }

        },

        // test end result
        test_end: function () {
            return $('.result_end').hasClass('show');
        },

        // show bookfrom data_url
        show_book: function (data_url, callback) {


            //console.log('###########show_book: function ');
            //console.log(data_url);

            book.infinite_ready = false;

            if (book.test_end()) return;


            $('.result_message').html('');

            book.show_loader(true);


            // ajax
            //
            var jqxhr = $.ajax({
                type: 'GET',
                url: data_url.url,
                dataType: 'json',
                data: data_url.tmp_data,
            })


            // completed
            //
            jqxhr.always(function (cont_retour) {

                // readaptation
                var last_book = ub_infinit.readaptation(cont_retour);
                var delay = 0;
                var tmp_length = cont_retour.length;
                var newitems = '';

                //console.log(cont_retour);
                // load off
                //book.$loader.removeClass('active');
                //$('.infinite .loader').removeClass('active');
                book.show_loader(false);

                // fin des resultats + pas de resulats
                if (cont_retour[0] == null) {
                    console.log('GenerateItems > cont_retour[0] == null _____fin resultat 2');

                    if (book.page_num == 0 && page_type != 'memobook' ) {

                        console.log('GenerateItems > cont_retour[0] == null _____fin resultat 2');
                        // pas de resultat
                        // modif_bloc: function (tpl_id, tpl_data, dom_id, callback)
                        book.show_end(false);

                        book.modif_bloc(
                            "#tpl_memobook_vide",
                            {message: ub_ill_plus_de_book.msg_pas_de_resultat},
                            '.result_message',
                            function () {
                                $('.vide').removeClass('hide');
                                //console.log('end')
                            }
                        );


                    } else {
                        //book.$result_end.addClass('show');
                        //book.show_end(true);

                        if ( ! book.show_end_hide) book.show_end(true);
                    }


                }


                // resultats partiel
                if (tmp_length < 6) {
                    //console.log(tmp_length)
                    //book.$result_end.addClass('show');
                    //console.log('######## end '+ data_url.page_type);
                    //alert('end')
                    if ( ! book.show_end_hide) book.show_end(true);

                }

                // resultats
                try {

                    for (var i = 0; i < tmp_length; i++) {

                        var tmp_id_user = cont_retour[i].id_user;

                        if ($('#user_' + tmp_id_user).length) {
                            //console.log('doublon ' + cont_retour[i].id_user);
                        }
                        else {

                            // tpl
                            cont_retour[i].ptf_index = book.page_num;
                            //ptf_index++;
                            //console.log(newitems)
                            //newitems.push(ub_infinit._renderItem(cont_retour[i]));

                            newitems += book._renderItem(cont_retour[i]);

                        }
                    }
                }

                finally {

                    try {

                        // add html cards
                        $('#position_card_last').before(newitems);

                        //console.log(newitems);
                    }
                    finally {

                        // book.$infinite_loader.removeClass('active');

                        selecteur = '.ptf_index_' + book.page_num;
                        $cards = $(selecteur);

                        //console.log( book.page_num );
                        var delay = 0;

                        $cards.each(function (index, val) {

                            var id_user = $cards.eq(index).data('user');
                            //console.log('#user_' + id_user);

                            // post traitement
                            ub_infinit.post_traitement_dom('#user_' + id_user);


                            // fadeIn on cards
                            //
                            setTimeout(function () {

                                $cards.eq(index)
                                    .removeClass('newitem_hide')
                                    .dimmer({
                                        selector: {
                                            dimmable:   '.dimmable',
                                            dimmer:     '.ui.dimmer'
                                        },
                                        on: 'hover'
                                    });



                            }, delay);

                            delay += 40;

                        });


                        //setTimeout( function () {book.infinite_ready = true;} ,1000 );

                        book.infinite_ready = true;

                        //console.log('######## end '+ data_url.page_type);
                        //alert('book.infinite_ready')


                        // recup stats + aff
                        setTimeout(function () {
                            ub_ill_plus_de_book.aff_stats(selecteur);
                        }, 510 );

                        //console.log(selecteur);




                        // open book auto
                        /*
                        if (typeof url_data != 'undefined') {
                            if (url_data.book_auto_open)  {
                                //console.log('// open book auto > ON')
                                ub_infinit.auto_open_book();
                            }
                        }
                        */

                        //console.log('// open book auto'+url_data);
                        //console.log(url_data);
                    }


                }


            });








            // retour du data par callback
            if (callback) {
                jqxhr.always(function (cont_retour) {
                    // callback
                    callback(cont_retour);
                });
            }

        },










        // test if popup search is active
        submitForm_if_popup: function () {

            var is_popup_search_open =            $('#bloc_rechercher_top_menu_modal').modal('is active')
            if (is_popup_search_open) {

                if ( ubdf_recherche.motcles_query ) {
                    var q_popup_val = ubdf_recherche.motcles_query.replace(",", " "); // auto complete
                } else {
                    // transfert var
                    var q_popup_val = $('#bloc_rechercher_top_menu_modal .rech2018 input[name="q"]').val();
                }

                $('.rech2018 input[name="q"]').val(q_popup_val);
                //console.log(q_popup_val)

                //var q_popup_dom = $('#bloc_rechercher_top_menu_modal .rech2018 input[name="page_domaine"]').val();
                //$('.rech2018 input[name="page_domaine"]').val('tous');
                //$(".link_rechercher_change_domain input[name=page_domaine]").val('tous');

                // close popup
                $('#bloc_rechercher_top_menu_modal').modal('hide');
            }


        },



        // validation de recherche
        submitForm: function () {

            // test if popup search is active
            book.submitForm_if_popup();


            // si pas de valeur
            if ($('.rech2018 input[name="q"]').val() == '') {

                $('.error_prompt').addClass('show').delay(2000).queue(function (next) {
                    $(this).removeClass('show');
                    next();
                });

                return;
            }


            // supp end
            //book.$result_end.removeClass('show');
            book.show_end(false);


            //console.clear()
            //console.log('######################################');

            book.page_num = 0;

            book.infinite_ready = false;

            // type url
            var type_url = book.url_recherche()

            // make_url_save
            var data_url = book.make_url(type_url);

            book.data_url = data_url;
            //console.log(data_url)


            // stock_url_save
            book.stock_url_query_save(data_url);

            // modif template
            if (
                page_type == 'accueil' ||
                page_type == 'memobook'||
                page_type == 'home' ||
                page_type == 'user' ||
                page_type ==  'cms_user'

            ) {

                // page de accueil -> page recherche
                book.modif_page(
                    function () { // <- callback

                        // show book
                        book.show_book(data_url);

                        // modif titre recherche
                        book.modif_bloc_titre(data_url.tmp_data);

                        // rechagement des input
                        book.update_input_search(data_url);

                    }
                );


            } else {

                //console.log('no modif tpl');

                // show book
                book.clear_book(
                    function () {  // <- callback

                        // show book
                        book.show_book(data_url);

                        // modif titre recherche
                        book.modif_bloc_titre(data_url.tmp_data);


                    });

            }


        },








        // submit menu top
        submit_btn: function (query_input) {

            $('.rech2018 .submit_rechercher,  .rech2018 .submit_rechercher_accueil,  #bloc_rechercher_top_menu_modal button[type="submit"]  ').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                book.submitForm();

            });


            // charge tmp_url.query_input
            if (query_input) {
                $('.rech2018 input[name="q"]').val(query_input);
            }

        },


        // submit option Popup+checkbox
        submit_option_btn: function () {

            $('.link_rechercher_options')
                .popup({
                    popup:          '.popup_rechercher_options',
                    position:       'bottom center',
                    hoverable: true,
                    //on:             'click',
                    transition:     'scale',
                    duration:       120,
                    offset:         -98,
                    distanceAway:   10,
                    delay: {
                        show: 10,
                        hide: 120
                    },
                    onShow: function () {


                    },
                    onHide: function () {
                        /*
                         if ( $( ".popup_rechercher_options input[name=flt_sel]" ).is(':checked')  ||
                         $( ".popup_rechercher_options input[name=flt_pro]" ).is(':checked') )
                         {
                         $('.link_rechercher_options').toggleClass('show_on') ;
                         }
                         */
                    },

                }); //.popup('show')
            ;

            // options checkbox

            $('.popup_rechercher_options .checkbox')
                .checkbox({
                    onChange: function () {
                        console.log('change')

                        // new submit
                        book.submitForm();

                        if (
                            $("#bloc_menu_contant_metiers .flt_sel").hasClass('checked')
                            ||
                            $("#bloc_menu_contant_metiers .flt_pro").hasClass('checked')
                        ) {

                            $('.link_rechercher_options').addClass('show_on');

                        } else {
                            $('.link_rechercher_options').removeClass('show_on');
                        }

                    }
                })
            ;


            // Popup rechercher link_change_domaine

            $('.ui.dropdown.link_rechercher_change_domain')
                .dropdown({
                    on: 'hover',
                    duration: 70,
                    delay: {
                        show: 10,
                        hide: 200
                    },
                    onChange: function (value, text, $selectedItem) {
                        // custom action

                        // change bg color on sheach domain
                        bg_color = $selectedItem.find('.nuancier').css('background-color');
                        //console.log(bg_color);
                        $('.rech2018 .rechercher_change_domain').css('background-color', bg_color)
                    }

                })
            ;

            // couleur active pour les domaines
            if (page_type == 'domaine') {
                bg_color = $('.rechercher_change_domain .menu .active.selected .nuancier').css('background-color');
                $('.rech2018 .rechercher_change_domain').css('background-color', bg_color)
            }


        },


        // show book via Static

        book_static_show: function ( _select  ) {

            // bloc_ultrabook
            $cards = $(  _select );

            //console.log( book.page_num );

            var the_select_ = _select;

            var delay = 0;

            $cards.each(function (index, val) {

                var id_user = $cards.eq(index).data('user');

                //$( the_select_ + '#user_' + id_user ).css({'border':'1px solid red'});
                //console.log(the_select_ + '#user_' + id_user);

                // post traitement
                ub_infinit.post_traitement_dom(   _select + '#user_' + id_user);


                // fadeIn on cards
                //
                setTimeout(function () {

                    $cards.eq(index)
                        .removeClass('newitem_hide')
                        .dimmer({
                            selector: {
                                dimmable: '.dimmable',
                                dimmer: '.ui.dimmer'
                            },
                            on: 'hover'
                        });


                    // recup stats + aff
                    //ub_ill_plus_de_book.aff_stats();
                    //ub_scrollPagination.aff_stats();

                }, delay);
                delay += 40;

            });


            setTimeout(function () {
                ub_ill_plus_de_book.aff_stats(_select );
            }, 510 );

        },


        // show book via API   <div class="book_api_show" data-domaine="illustration"></div>

        book_api_show: function () {

            $('.book_api_show').each(function (el) {

                $el = $(this);
                var domaine =   $el.data('domaine');
                var infinite = ( $el.data('infinite') == true ? 'infinite' : '' );
                var dom_id =    $el.attr('id');
                var book_id =   $el.data('book_id');

                // book by id

                if ( book_id ) {

                    book.book_api_show_no_used = false;

                    //console.log('fn:book_api_show   show one book >'+book_id);
                    // type url
                    //var type_url = book.url_recherche()

                    // make_url_save
                    //var data_url = book.make_url(type_url);

                    //alert('ok'+book_id);


                    // add book_id
                   var data_url = {
                        error: false,
                        query_input: "",
                        page_type: "/pseudo_hash",
                        url: "/rechercher_submit",
                        tmp_data: {
                            anu_type:   "tous",
                            flt_pro:    false,
                            flt_sel:    false,
                            page_type:  "book_rechercher_ajax",
                            q:          book_id,
                            recherche:  "pseudo_hash",
                            suite:      0
                        }
                    };

                    //alert ('ok')
                    //console.log(data_url)

                    // show book
                    book.modif_bloc(
                        '#tpl_bloc_portfolios_seul',
                        {infinite: infinite},
                        '#' + dom_id,
                        function () {
                            book.show_end_hide = true;
                            book.show_book(data_url);
                            //alert('end')
                        }
                    );

                } else {

                    // book by domain

                    //console.log('domaine > ' + domaine + infinite)

                    book.book_api_show_no_used = false;


                    // modif bloc avec tpl + transition
                    book.modif_bloc(
                        '#tpl_bloc_portfolios_seul',
                        {infinite: infinite},
                        '#' + dom_id,
                        function () {

                            // domaine & url
                            // type url
                            var type_url = book.url_domaine(domaine)
                            // make_url_save
                            var data_url = book.make_url(type_url);
                            // stock_url_save
                            book.stock_url_query_save(data_url);
                            // show book
                            book.show_book(data_url);

                            // infinit active le chargement des book+show
                            if (infinite == 'infinite') {
                                //console.log('ok1');
                                ub_infinit.init();
                            } else {
                                //console.log('ok2');
                                book.show_loader(false);
                                book.show_end(true);
                            }
                        }
                    );
                }

            })
        },











        // admin - add tags
        motscles_nb:        0,
        motscles_nb_max:    10,
        motscles_quota :    true,
        motscles_array:     [],

        motscles_nb_normal:     3,
        motscles_nb_formule:    12,

        // init nb motcles
        motscles_nb_init: function () {

            $('.form_label:eq(3)').trigger('click');

            var formule = $(".user_formule.formule_ub").length;
            //var formule = false;
            book.motscles_nb_max =  ( formule ) ? book.motscles_nb_formule : book.motscles_nb_normal;
            if (formule){
                $('#motscles_nb_formule').removeClass('hide');
            } else {
                $('#motscles_nb_normal').removeClass('hide');
            }



        },

        motscles_add: function () {

            var content_data = [];

            console.log('fn motscles_add');
            // chargement mots cles
            $.getJSON('/html_pages_v2018/tpl_conf_msg/motcles_data_front_fr_en.json',
                function (data_all) {

               // console.log(data_all);
               // console.log('========================== ubdf_recherche init json loaded')

                // fr/en
                //lang = 'en';

                if ( lang == 'fr' ) {
                    var data = data_all.content_motscles;
                } else {
                    var data = data_all.content_motscles_en;
                }


                var items = [],
                    index_ = 0
                    ;
                //console.log( data );

                $.each(data, function (key, val) {

                    //console.log( key,val );
                    var cat = key;
                    var mots_cles = val;//.split(",");

                    var last = mots_cles.length;

                    $.each(mots_cles, function (kk, mot_cles_) {

                        description = '\
                            <a class="result">\
                                 <div class="content">\
                                 <div class="title">' + mot_cles_ +'</div>\
                                 <div class="description">' + cat + '<span class="fonticon-arrow-right icon"></span>' + mot_cles_ +' <i class="plus square icon"></i></div>\
                                 </div>\
                            </a>'
                            ;

                        // data pour dropdown
                        content_data.push({
                            'description':  description,
                            'data-value':   cat + ',' + mot_cles_,
                            'text':         mot_cles_,  //cat + '<span class=\'fonticon-arrow-right icon\'></span>' + mot_cles_,
                            'category':     cat
                        });

                    });


                });


                book.motscles_add_dropdown(content_data);


            }
            ).fail(function( jqxhr, textStatus, error ) {
                var err = textStatus + ", " + error;
                console.log( "Request Failed: " + err );
            });

            //console.log('========================== end')
            // https://semantic-ui.com/modules/dropdown.html#/settings
            // http://api.semantic-ui.com/tags/cat
            // {"success":true,"results":[{"name":"Cat","value":"cat"},{"name":"Caterpillar","value":"caterpillar"}]}


        },

        motscles_count: function (nb,nb_max) {
            if (nb >= nb_max ) {
                $('#motscles_nb').html(' (<span style="color:red">'+nb + '</span>/' + nb_max +')');
            } else {
                $('#motscles_nb').html(' ('+nb + '/' + nb_max +')');
            }
        },


        // dropdown mot cles + modif template dropdown Semantic UI
        motscles_add_dropdown: function (content_data) {

            //console.log(content_data);

            // modifcation de la Templates Semantic pour gérer les cats
            $.fn.dropdown.settings.templates = {

                // generates dropdown from select values
                dropdown: function(select) {
                    var
                        placeholder = select.placeholder || false,
                        values      = select.values || {},
                        html        = ''
                        ;
                    html +=  '<i class="dropdown icon"></i>';
                    if(select.placeholder) {
                        html += '<div class="default text">' + placeholder + '</div>';
                    }
                    else {
                        html += '<div class="text"></div>';
                    }
                    html += '<div class="menu">';
                    $.each(select.values, function(index, option) {
                        html += (option.disabled)
                            ? '<div class="disabled item" data-value="' + option.value + '">' + option.name + '</div>'
                            : '<div class="item" data-value="' + option.value + '">' + option.name + '</div>'
                        ;
                    });
                    html += '</div>';
                    return html;
                },

                // generates just menu from select
                menu: function(response, fields) {

                    var
                        values = response[fields.values] || {},
                        html   = ''
                        ;

                    //console.log(values);
                    var cat_ = false;

                    $.each(values, function(index, option) {
                        var
                            maybeText = (option[fields.text])
                                ? 'data-text="' + option[fields.text] + '"'
                                : '',
                            maybeDisabled = (option[fields.disabled])
                                ? 'disabled '
                                : ''
                            ;

                        // les cat
                        var cat = option.category;
                        if (cat_ != cat) {
                            // on ferme
                            if (cat_) {
                                html +=     '</div>\
                                             </div>'
                                            ;
                            }
                            // on ouvre
                            html +=     '<div class="category cat_' + cat + ' coul_' + cat + '">\
                                         <div class="name">' + cat + '</div>\
                                         <div class="results">\
                                        ';
                            cat_ = cat;
                        }

                        // les item
                        html += '<div class="'+ maybeDisabled +'item" data-value="' + option[fields.value] + '"' + maybeText + '>';
                        html +=   option[fields.name];
                        html += '</div>';


                    });
                    return html;
                },


                // generates label for multiselect
                label: function(value, text) {
                    var value_ = value.replace(',','<span class="fonticon-arrow-right icon"></span>');
                    return value_ + '<i class="delete icon"></i>';
                },


                // generates messages like "No results"
                message: function(message) {
                    return message;
                },

                // generates user addition to selection menu
                addition: function(choice) {
                    return choice;
                }

            };



            console.log('fn dropdown 1');
            // aff dropdown
            //
            setTimeout(function(){
                console.log('fn dropdown 2');
                // dropdown
                $('.motscles_add .ui.category.search.dropdown').dropdown({

                    //allowAdditions: true,
                    filterRemoteData: true,
                    //allowCategorySelection: true,
                    //fullTextSearch: true,
                    maxSelections: 	 book.motscles_nb_max,

                    fields: {
                        name:   "description",
                        value:  "data-value",
                        text:   "text"
                    },


                    apiSettings: {
                        mockResponse: {
                            success: true,
                            results: content_data
                        }
                    },


                    onChange: function (value, text, $choice) {
                       // console.log(value, text)
                    },

                    onShow: function (value) {

                        //console.log(value);
                        //$('.ui.category.search.fluid.dropdown')
                        //    .dropdown( 'add optionValue',  {'name':'option1', 'text':'option1', 'value':'architecture,aménagement espace'} );
                            //.append('<a class="ui label transition visible" data-value="architecture,aménagement espace">architecture<span class="fonticon-arrow-right icon"></span>aménagement espace<i class="delete icon"></i></a>');

                        console.log("_________ onShow")

                    },


                    onAdd: function (addedValue, addedText, $addedChoice) {
                        if ( book.motscles_nb == book.motscles_nb_max) {

                            setTimeout(function(){  $('.menu > .category').hide(); }, 600);
                        }
                        else  {


                            book.motscles_gestion_add_and_send(addedValue);


                            book.motscles_nb++;
                            //console.log(book.motscles_nb)
                            book.motscles_count(book.motscles_nb, book.motscles_nb_max);
                        }
                    },
                    onRemove: function (removedValue, removedText, $removedChoice) {

                        book.motscles_gestion_del_and_send(removedValue);

                        book.motscles_nb--;
                        //console.log(book.motscles_nb)
                        book.motscles_count(book.motscles_nb, book.motscles_nb_max);
                        $('.menu > .category').show();

                    }


                });

                // ajoute les style pour les cats
                $('.motscles_add  .menu').addClass('results');

                console.log(' motscles_add_dropdown -end ');

                book.motscles_gestion_init();


            }, 400);


        },



        // init, add & del send via ajax
        //
        // ['illustration 3D','illustration presse','architecture Aménagement espace','publishing  tales','art land art']
        // ['illustration',' logo',' jeunesse',' animaux',' écologie',' biodiversité',' conte',' humour',' enfant',' école',' garçon',' fille',' BD',' Lion',' publicité','cuisine',' recette',' scolaire',' salade']


        // stokage DB ["illustration 3D","illustration presse", ...

        // init des tag déja enregistre
        motscles_gestion_init: function () {
            // chargement des motcles deja enregistre
            //

            // init nb
            book.motscles_nb_init();

            // init array
            var value =  $('#us_pf_css').data('value');
            console.log( value );
            var no_array = false;

            if ( Array.isArray(value)  ) {
                console.log( "array" );
                book.motscles_array = value;
            } else {
                console.log( "no array" );
                value =  value.replace(/,''/g, ''); // supp val vide
                value =  value.replace(/'/g, '"');
                book.motscles_array = JSON.parse( value );
                no_array = true;
            }

            //value = "['illustration 3D','illustration presse','architecture Aménagement espace','publishing  tales','art land art']";




            // add drownbox
            var html = '',
                html_v0= '';

            $.each( book.motscles_array, function( key, value ) {

                //var terme = value.split(' ');

                //$('.motscles_add .ui.dropdown').dropdown('refresh').dropdown('set selected', value);
                var terme = value.replace(/ /,'&').split('&');

                console.log(terme[0])

                if ( ! value.trim().match(/ /) || terme[0]=='' ) {
                    html_v0 += '<a class="ui label transition visible btn_supp_motcles" data-value="' + value + '">'
                        + value + '<i class="delete icon"></i></a>'
                } else {
                    html += '<a class="ui label transition visible btn_supp_motcles" data-value="' + value + '">'
                        + terme[0] + '<span class="fonticon-arrow-right icon"></span>' + terme[1] + '<i class="delete icon"></i></a>'
                }

                    book.motscles_nb++;
                    book.motscles_count(book.motscles_nb, book.motscles_nb_max);

            });

            //console.log(html_v0);
            if ( html_v0 != "") {
                $('.motscles_v0').addClass('show').find('.old_tag').append(html_v0);
            }

            $('.motscles_add  .dropdown.icon').after(html);

            book.motscles_gestion_supp();

            console.log('init array')
            //console.log(book.motscles_array)
        },

        // activer la supp des tag déja enregistre
        motscles_gestion_supp: function () {
            $('a.btn_supp_motcles i.delete').on('click',function() {


                $el = $(this).parent('a.btn_supp_motcles');

                motscles_val = $el.data('value');
                book.motscles_gestion_del_and_send(motscles_val );

                $el.fadeOut(400,function(){$(this).remove();});

                book.motscles_nb--;
                book.motscles_count(book.motscles_nb, book.motscles_nb_max);

                //console.log('supp')
            });
        },



        motscles_gestion_add_and_send: function (motscles_val) {

            //console.log(motscles_val);
            //console.log(book.motscles_array)

            motscles_val =  book.motscles_gestion_code(motscles_val);
            book.motscles_array.push(motscles_val);

            // send
            book.motscles_gestion_sendajax(book.motscles_array);

            //console.log('add')
            //console.log(book.motscles_array)

        },

        motscles_gestion_del_and_send: function (motscles_val) {

            // supp l'entre avec la valeur motscles_val
            motscles_val =  book.motscles_gestion_code(motscles_val);
            book.motscles_array.splice(book.motscles_array.indexOf(motscles_val),1)

            // send
            book.motscles_gestion_sendajax(book.motscles_array);

            //console.log('supp')
            //console.log(book.motscles_array)

        },

        // send string via ajax
        motscles_gestion_sendajax: function (motscles_array) {
            //value =  value.replace(/"/g, "'");
            fm_field = 'us_pf_css';
            fm_value = JSON.stringify(motscles_array);
            fm_ajax.send_form_motcles(fm_value, fm_field);
            //console.log('send > '+fm_value);
        },

        motscles_gestion_code: function (motscles_val) {
            return motscles_val.trim().replace(/,/g, ' ');
        },


    },







    /* func recherche de la page accueil et domaines
     -------------------------------------------------------------- */
    ubdf_recherche = {


        content_motscles_en: {},
        content_motscles_fr: {},

        index_fr_en: [],
        motcles_query: '',


        init: function () {

            this.rechercher_accueil();
            // this.debug_rechercher_active();



            this.recherche_motcles_btn();



        },

        // bouton mots cles
        //
        recherche_motcles_btn: function () {

            //alert('ok');

            $('.bloc_last_recherche a.label ').each(function() {
                $(this).on('click',function(){

                    ubdf_recherche.rechercher_type('mcles');

                    var item =  $(this).data('slug');
                    $('.form_rechercher2018 input[name="q"]').val(item);

                    ubdf_recherche.motcles_query = item;
                    book.submitForm();

                })
            })

        },



        debug_rechercher_active: function () {
            // tmp rechercher
            //
            $('#activer_rechercher').on("click", function () {
                $('#menu-top-fixed').toggleClass('recherche');
            });

            $('#menu-top-fixed').toggleClass('recherche');

        },

        // type  de recherche auto pseudo/mcles
        rechercher_type: function (val) {
            // mcles
            // pseudo
            $('.rech2018 input[name="type_recherche"]').val(val);
            //console.log(val);
        },


        // test si il y a un alias du domaine -> retourne domaine
        rechercher_alias: function (content_alias, domaine) {


            domaine_alias = content_alias.find(function (obj) {

                //console.log(  obj.title+' = '+domaine);

                var regexFromMyArray = new RegExp(obj.alias, 'gi');

                if (domaine.match(regexFromMyArray) || obj.title === domaine) {
                    return true;
                }
            });
            if (domaine_alias !== undefined)
                return domaine_alias.title;
            else
                return false
        },

        // test si il y a un alias du domaine => retourne le first alias

        /*
         "content_alias": [
         {
         "title": "illustration",
         "alias": "illustrateurs|illustrateur"
         },

         */
        rechercher_alias_el: function (content_alias, domaine) {

            var domaine_alias_;
            content_alias.find(function (obj) {

                if (obj.title === domaine) {
                    // console.log(obj.alias.split('|').shift());
                    domaine_alias_ = obj.alias.split('|').shift();
                    return;
                }
            });
            //console.log(domaine_alias_)
            if (domaine_alias_ !== undefined)
                return domaine_alias_;
            else
                return;

        },

        // Function to get the nth key from the object
        getByIndex: function (obj, index) {
            return Object.keys(obj)[index];
        },


        // autocompletion champs de recherche
        rechercher_accueil: function () {
            // rechercher 2018

            content_data = [];

            // alias title
            content_alias = [];


            // chargement mots cles
            $.getJSON('/html_pages_v2018/tpl_conf_msg/motcles_data_front_fr_en.json', function (data_all) {

                //console.log(data_all);
                //console.log('========================== ubdf_recherche init json')
                // fr/en
                ubdf_recherche.content_motscles_fr = data_all.content_motscles;
                ubdf_recherche.content_motscles_en = data_all.content_motscles_en;

                //console.log(this.content_motscles_fr);

                // version EN/FR

                if (lang == 'fr') {
                    content_alias = data_all.content_alias;
                    data = ubdf_recherche.content_motscles_fr;
                    data_lang_n2 = ubdf_recherche.content_motscles_en;
                } else {
                    content_alias = data_all.content_alias;
                    data = ubdf_recherche.content_motscles_en;
                    data_lang_n2 = ubdf_recherche.content_motscles_fr;
                }


                var items = [],
                    index_ = 0
                    ;

                $.each(data, function (key, val) {

                    //console.log( key );

                    // alias ? illustration=illustrateur
                    var alias = '';

                    // alias domaine
                    domaine_alias_ = ubdf_recherche.rechercher_alias_el(content_alias, key);

                    //console.log( domaine_alias_  );

                    if (domaine_alias_ !== undefined) {
                        //console.log(item.alias);
                        alias = ' <span class="dom_metier"><span class="fonticon-arrow-right icon"></span>' + domaine_alias_ + '<span>';
                    }

                    // cat + content
                    //
                    content_data.push({
                        category: key,
                        title: key + ' ',
                        description: '<span class="fonticon-arrow-right icon"></span> catégorie ' + key + alias,
                        url: key
                    });


                    // version EN/FR
                    var key_en = ubdf_recherche.getByIndex(data_lang_n2, index_);
                    //   var key_en = ubdf_recherche.getByIndex(ubdf_recherche.content_motscles_fr, index_);

                    index_++;
                    //console.log( key_en );


                    $.each(val, function (keykey, item) {
                        content_data.push({
                            category: key,
                            title: key + ' ' + item,
                            description: item
                        });


                        // version EN
                        // tableau index correspondance FR/EN

                        item_en = data_lang_n2[key_en][keykey]
                        //console.log(key_en+' '+ item_en );
                        if (lang == 'fr') {
                            ubdf_recherche.index_fr_en.push({
                                fr: key + ',' + item,
                                en: key_en + ',' + item_en
                            });
                        } else {
                            ubdf_recherche.index_fr_en.push({
                                fr: key_en + ',' + item_en,
                                en: key + ',' + item
                            });
                        }

                    });

                });

                //console.log(ubdf_recherche.index_fr_en);
                //console.log(content_data); // data pour aff

                ubdf_recherche.rechercher_type('pseudo');

                // autocompletion
                ubdf_recherche.rechercher_accueil_autocompletion();

            });

            // https://semantic-ui.com/modules/search.html#/settings
            // https://semantic-ui.com/behaviors/api.html#/usage


            // submit menu top
            //
            book.submit_btn();

        },


        // autocompletion input rechereche
        rechercher_accueil_autocompletion: function () {

            // moteur de recherche
            //
            $('.rech2018')
                .search({
                    source: content_data,
                    type: 'category',
                    maxResults: 38,
                    searchFields: [
                        'title',
                        'description'
                    ],
                    fullTextSearch: false, //'exact',
                    //cache: false,
                    //minCharacters: 2,
                    //selectFirstResult: true,
                    searchDelay: 50,

                    onResultsAdd: function (html) {
                        /*
                         html += '<div class="category">';
                         html += '<div class="name"></div>';
                         html += '</div>';
                         */
                        return html;
                    },

                    onSelect: function (result, response) {

                        ubdf_recherche.rechercher_type('mcles');

                        //$(".rech2018 input[name=q]").val(result.category + ',' + result.description)

                        ubdf_recherche.motcles_query = result.category + ',' + result.description;
                        console.log('mcles >>>>>>>' + ubdf_recherche.motcles_query)

                        book.submitForm()

                    },

                    /*
                     onResults: function(response) {
                     //console.log( response)
                     },
                     */

                    onSearchQuery: function (query) {

                        console.log('query > ' + query);

                        // alias illustrateurs => illustration
                        domaine_alias_ = ubdf_recherche.rechercher_alias(content_alias, query);
                        if (domaine_alias_) {
                            //console.log('######### domaine alias ' + domaine_alias_)
                            setTimeout(function () {
                                $('.rech2018')
                                    .search('search local', domaine_alias_)
                                ;
                            }, 500)
                        }


                        // restreinde les category suivant un domaine metier
                        domaine = $(".link_rechercher_change_domain input[name=page_domaine]").val();

                        /*
                       if (domaine !== undefined && domaine != 'tous') {

                            $('.rech2018 .results').removeClass('hidden_force')
                            $('.rech2018 .results .category').hide();


                            // alias domaine
                            domaine_alias_ = ubdf_recherche.rechercher_alias(content_alias, domaine);

                            //console.log( 'domaine alias' + domaine_alias_)
                            $domaine_existe = $('.rech2018 .results .cat_' + domaine_alias_)

                            $domaine_existe.show('fast', function () {
                                //console.log('// Animation complete.');
                                $('.rech2018 .results').addClass('hidden_force')
                            });

                            //console.log( $domaine_existe.lenght );

                        }
                        */
                    },

                    /*
                     onResultsClose: function () {
                     //ubdf_recherche.rechercher_type('mcles');
                     },
                     */



                    templates: {
                        message: function message(type, _message) {
                            ubdf_recherche.rechercher_type('pseudo');
                            return '';
                        },

                        category: function category(response, fields) {
                            var
                                html = '',
                                escape = $.fn.search.settings.templates.escape
                                ;
                            if (response[fields.categoryResults] !== undefined) {

                                // each category
                                $.each(response[fields.categoryResults], function (index, category) {
                                    if (category[fields.results] !== undefined && category.results.length > 0) {

                                        html += '<div class="category cat_' + category[fields.categoryName] + '">';

                                        if (category[fields.categoryName] !== undefined) {
                                            html += '<div class="name">' + category[fields.categoryName] + '</div>';
                                        }


                                        // each item inside category
                                        html += '<div class="results">';


                                        $.each(category.results, function (index, result) {
                                            if (result[fields.url]) {
                                                html += '<a class="result txtblanc coul_' + category[fields.categoryName] + '" href="' + result[fields.url] + '">';
                                            }
                                            else {
                                                html += '<a class="result">';
                                            }
                                            if (result[fields.image] !== undefined) {
                                                html += ''
                                                    + '<div class="image">'
                                                    + ' <img src="' + result[fields.image] + '">'
                                                    + '</div>'
                                                ;
                                            }

                                            html += '<div class="content">';

                                            if (result[fields.title] !== undefined) {
                                                html += '<div class="title">' + result[fields.title] + '</div>';
                                            }
                                            if (result[fields.description] !== undefined) {
                                                html += '<div class="description">' + result[fields.description] + '</div>';
                                            }
                                            html += ''
                                                + '</div>'
                                            ;
                                            html += '</a>';
                                        });
                                        html += '</div>';
                                        html += '</div>'
                                        ;
                                    }
                                });
                                if (response[fields.action]) {
                                    html += ''
                                        + '<a href="' + response[fields.action][fields.actionURL] + '" class="action">'
                                        + response[fields.action][fields.actionText]
                                        + '</a>';
                                }
                                return html;
                            }
                            return false;
                        },

                    }


                })
            ;

            var domaine_titre = $('.bloc_titre h2').text();
            console.log('--------------------------- '+domaine_titre);


            $('.link_rechercher_change_domain')
                .dropdown('set selected', domaine_titre)
            ;

        },


        // Accueil -> page recherche    tpl_bloc_portfolios
        modif_template_recherche: function (callback) {

            var ub_theTemplate_ptf = Handlebars.compile($("#tpl_bloc_portfolios").html());
            var html = ub_theTemplate_ptf({});


            $('.bloc_base_contant').fadeOut('fast',
                function () {

                    // menu version light
                    book.menu_lightfixed(true);

                    // change type page
                    $('.bloc_base_contant').addClass('bloc_base_domaine');


                    $(this)
                        .html(html)
                        .fadeIn('fast', function () {

                            // autocompletion
                            ubdf_recherche.rechercher_accueil_autocompletion();

                            book.infinite();

                            //console.trace();

                            ub_infinit.GenerateItems(ub_infinit.url_recherche());

                        });

                });

        },


    };


// a faire


    // create sidebar and attach to menu open - mobile menu
    //
    //$(".ui.sidebar").sidebar("attach events", ".toc.item");


    // stats live 25juil- 2016
    //
    // http://localhost:5000/stats
    // http://localhost:5000/book
    // $ cd /Users/pat/Sites_2013/_projet_ub2014/___ub_developpement/st4_sockets
    // $ gulp
    //
    //
    //var socket = io( url_stats_archives );


});
