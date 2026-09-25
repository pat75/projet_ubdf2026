
$(document).ready(function () {

    console.log('load---> js_core_cards');


    //	page scrool infinit
    //

    var ub_theTemplate,
        //page_type,
        page_num,
        ptf_index,
        url_data
        ;

    page_num = 1;
    ptf_index = 0;







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
                //ub_ill_plus_de_book.aff_stats();
            }, 110);



        },


/*
        // ouverture automatique ds book via url#book
        auto_open_book: function () {
            var tmp_hash = ub_fn.url_hash_get();

            if (tmp_hash) {
                console.log(tmp_hash);
                // open book
                $('#user_' + tmp_hash).trigger('click');
            }
        },
*/

        // supp contenu
        clean_portfolio: function () {

            $('#accueil_portfolio').html('<div id="position_card_last"></div>');
            $('.result_end').addClass('show');
        },

        // post traitement dom aprés aff des book
        post_traitement_dom: function (selecteur) {

            // action memobook btn add
            //ub_memobook.btn_add(".iscrool_newitem");
            //$(".iscrool_newitem .memobook_add").css({'border':'1px solid red'});




            // slider
            ub_ill_plus_de_book.btn_slide(selecteur);




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
                        } /*else {
                            //console.log(k+ ' = '+gal.img_sec_91x91 );
                            img_book.push({
                                img_fichier: gal.img_sec_91x91,
                                img_alt: gal.img_titre
                            });
                        }*/

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
            if (!cont_retour.length)            return true;
            if (cont_retour[i - 1].us_last)    return true;

            return false;

        },


    };




    /*  =btn plus d book pour rechercher et accueil
     -------------------------------------------------------------- */


    ub_ill_plus_de_book = {

        name: ''
        , msg_pas_de_resulat:       'noresult' //ub_msg_core.msg_pas_de_resulat
        , modifaff:                 false
        , msg_portfolio_complet:    'complet' //ub_msg_core.msg_portfolio_complet
        , msg_pas_de_resultat :     'pas de résultat'




        // init                                     #2018
        //
        , init: function () {

            //this.stats_book('');


        }


        // open single book - aff                   #2020
        //
        , open_single_book_slide: function () {

            // chargement des images
            var img =       [];
            var tmp_img =   '';
            var url =       window.location.hostname;

            //console.log(slider_data);
            var slider_data_nb_total = slider_data.book_img.length;

            // vignette
            var book_info_recup_img_vign = $('.circular.image').attr('src');

            // tpl
            var data = {
                "user_detail": user_detail,
                "user_id": id_user,
                "book_info_recup_img_vign": book_info_recup_img_vign,
                "slider_data_nb_total": slider_data_nb_total,
                //"msg_book_suivant": ub_msg_core.msg_book_suivant,
                //"book_id_next": book_id_next,
                //"us_pf_css": motcles,
                "user_book_url":  url_prefix + '://' + id_user + window.location.hostname.replace("www", "")
            };


            /*
            // mots cles - reformat 2019
            if ( motcles != '' &&  motcles != undefined ) {
                if (motcles.match(/^(\[&#34;)/)) {
                    //console.log('ok');
                    motcles = motcles.replace(/(\[&#34;|&#34;\])/g, '');
                    motcles = motcles.replace(/(&#34;,&#34;)/g, ',');

                    $('.motscles').html(motcles);
                }
            }
            */
            $('.motscles').transition('fade out ', 200);

            $('.ub_img_nb_total').text(slider_data_nb_total);


            $.each( slider_data.book_img, function (index, value) {

                if (window.screen.width < 750) {
                    tmp_img = value.fichier.replace(/\/img_\//g, '/img_iph_medium/');
                } else {
                    tmp_img = value.fichier;
                }

                // patch url image UB/ddns
                if (!/url/.test(tmp_img)) {
                    tmp_img = tmp_img.replace('www.ultra-book.com', url)
                }

                img.push({href: tmp_img, title: value.title});

            });


            //console.log(img);

            // lightbox
            $.swipebox(
                img,
                {
                    afterOpen: function () {


                        $('.mfp-container').prepend( $('.mfp-container-html').html() );


                        // infobulles
                        $('.tooltip_tipsy').tipsy({
                            title: 'title',
                            fade: true,
                            offset: 3,
                            background: 'white',
                            color: 'black'
                        });


                        // slide next
                        //
                        $('.mfp-btn_next, .slide.current').click(function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            $.swipebox.next();
                            // pinter on image
                            $('#mfp-book_partage').fadeOut(500, function () {
                                $(this).fadeIn(500);
                            });

                            return false;

                        });

                        // slide prev
                        //
                        $('.mfp-btn_prev').click(function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            $.swipebox.prev();
                            // pinter on image
                            $('#mfp-book_partage').fadeOut(500, function () {
                                $(this).fadeIn(500);
                            });

                            return false;
                        });

                        // intermediate 2019
                        ub_ill_plus_de_book.intermediate_btn(id_user, data);

                        // link Portfolio complet
                        $('a.link_ultra-book_url').bind('touchend click', function (event) {
                            event.preventDefault();
                            event.stopPropagation();
                            ga('send', 'pageview', '/book_open_' + id_user); //stats GA
                            //console.log('new win');
                            var win = window.open($(this).attr('href') + '/portfolio', 'Ultra-book ' + id_user, '');
                            win.focus();
                        });

                        // memobook
                        ub_memobook.btn_add('#book_open');

                        //if (page_type == 'memobook')    ub_memobook.btn_del_popup("#mfp-book_add_selection");

                        // stats
                        if (id_user)    ga('send', 'pageview', '/book_singlepage_' + id_user); //stats GA

                        // statslive - after open
                        ub_ill_plus_de_book.statlive(id_user, img);

                        // intermediate
                        $('.mfp-container-html').remove();
                        ub_ill_plus_de_book.inter_maj(0, slider_data);



                    },

                    beforeOpen: function () {
                       $('#swipebox-overlay').remove();
                    },

                    afterClose: function () {
                        //if ( anu_type == '' ) { anu_type =  'accueil'; }
                        window.location = '/'+anu_type;
                    },


                    hideBarsOnMobile: false,
                    hideBarsDelay: true,
                    loopAtEnd: true,

                    nextSlide: function () {  	// statslive
                        ub_ill_plus_de_book.statlive(id_user, img);

                        // index
                        tmp = $('#swipebox-slider .slide').index($('#swipebox-slider .slide.current'));
                        $('.ub_img_nb strong').html(tmp+1);

                        // intermediate
                        ub_ill_plus_de_book.inter_maj(tmp, slider_data);

                    },

                    prevSlide: function () {  	// statslive
                        ub_ill_plus_de_book.statlive(id_user, img);

                        // index
                        tmp = $('#swipebox-slider .slide').index($('#swipebox-slider .slide.current'));
                        $('.ub_img_nb strong').text(tmp+1);

                        // intermediate
                        ub_ill_plus_de_book.inter_maj(tmp, slider_data);

                    }

                }
            );





        }












        // open book - aff                          #2018
        //
        , btn_slide: function (selecteur) {

            //console.log('#### -> ' + selecteur);

            // bouton
            // selecteur+' .slider_link, +'.card'

            $(selecteur).unbind('click').click(function (event) {

                //console.log('Click on -> ' + selecteur);
                //$(this).removeClass('ub_accueil_ill_link_action');
                event.preventDefault();
                event.stopPropagation();

                // recup info book
                var $this_el = $(this);

                // user
                var id_user =       $this_el.data('user');
                var user_detail =   $this_el.data('user_detail'); // us_dispo
                var slider_data =   $this_el.data('slider');
                var motcles =       $this_el.data('motcles');


                // vignette
                var book_info_recup_img_vign = $this_el.find('.avatar').attr('src');

                if (book_info_recup_img_vign) {
                    // supp tiny to small from filename
                    const regex = /vignette_home_(.*)_tiny(\.jpg|png|gif)/gm;
                    const subst = 'vignette_home_$1_small$2';
                    // The substituted value will be contained in the result variable
                    book_info_recup_img_vign = book_info_recup_img_vign.replace(regex, subst);
                }

                // stats like+view
                var book_data_stats = {
                    like: $this_el.find('.stats_sel').html(),
                    view: $this_el.find('.stats_vue').html(),
                    html: $this_el.find('.created').html()
                };


                // next
                var book_id_next = $this_el.next().attr('id');

                //console.log(slider_data);

                var slider_data_nb_total = slider_data.book_img.length;

                // dispo
                //user_detail.book_dispo = 'true';
                //console.log(user_detail.book_dispo);

                //console.log(book_id_next);
                //book_id_next = false;

                // console.log( 'motcles '+motcles );
                // mots cles - reformat 2019
                if ( motcles != '' &&  motcles != undefined ) {
                    if (motcles.match(/^(\[&#34;)/)) {
                        //console.log('ok');
                        motcles = motcles.replace(/(\[&#34;|&#34;\])/g, '');
                        motcles = motcles.replace(/(&#34;,&#34;)/g, ',');
                    }
                }


                // tpl
                var data = {
                    "user_detail": user_detail,
                    "user_id": id_user,
                    "book_info_recup_img_vign": book_info_recup_img_vign,
                    "slider_data_nb_total": slider_data_nb_total,
                    "msg_book_suivant": ub_msg_core.msg_book_suivant,
                    "book_id_next": book_id_next,
                    "us_pf_css": motcles,
                    "user_book_url":  url_prefix + '://' + id_user + window.location.hostname.replace("www", "")
                };




                //console.log(user_detail);
                //console.log(data);

                var ub_theTemplate = Handlebars.compile($("#tpl_book_open").html());
                var book_info_tpl = ub_theTemplate(data);



                // chargement des images
                var img = [];
                var tmp_img = '';
                var url = window.location.hostname;


                $.each(slider_data.book_img, function (index, value) {

                    if (window.screen.width < 750) {
                        tmp_img = value.fichier.replace(/\/img_\//g, '/img_iph_medium/');
                    } else {
                        tmp_img = value.fichier;
                    }

                    // patch url image UB/ddns

                    if (!/url/.test(tmp_img)) {
                        tmp_img = tmp_img.replace('www.ultra-book.com', url)
                    }


                    img.push({href: tmp_img, title: value.title});

                });


                // url hash
                ub_fn.url_hash_add(id_user);

                //console.log(img);
                //console.log(book_info_tpl);

                // lightbox
                $.swipebox(
                    img,
                    {
                        afterOpen: function () {

                            $('.mfp-container').prepend(book_info_tpl);


                            // infobulles
                            $('.tooltip_tipsy').tipsy({
                                title: 'title',
                                fade: true,
                                offset: 3,
                                background: 'white',
                                color: 'black'
                            });


                            $('.mfp-book_info_cont').fadeIn('slow');// fade


                            ////ub_ill_plus_de_book.cursor_droite_gauche();


                            // slide next
                            //
                            $('.mfp-btn_next, .slide.current').click(function (event) {
                                event.preventDefault();
                                event.stopPropagation();

                                $.swipebox.next();
                                // pinter on image
                                //ub_ill_plus_de_book.clone_pinter_on_slideimage();
                                $('#mfp-book_partage').fadeOut(500, function () {
                                    $(this).fadeIn(500);
                                });

                                return false;

                            });

                            // slide prev
                            //
                            $('.mfp-btn_prev').click(function (event) {
                                event.preventDefault();
                                event.stopPropagation();

                                $.swipebox.prev();
                                // pinter on image
                                //ub_ill_plus_de_book.clone_pinter_on_slideimage();
                                $('#mfp-book_partage').fadeOut(500, function () {
                                    $(this).fadeIn(500);
                                });

                                return false;
                            });


                            // contact - desactive mai2019
                            //
                            //ub_ill_plus_de_book.contact_btn(id_user, data);

                            // intermediate 2019
                            ub_ill_plus_de_book.intermediate_btn(id_user, data);



                            // link Portfolio complet
                            $('a.link_ultra-book_url').bind('touchend click', function (event) {
                                event.preventDefault();
                                event.stopPropagation();
                                ga('send', 'pageview', '/book_open_' + id_user); //stats GA
                                //console.log('new win');
                                var win = window.open($(this).attr('href') + '/portfolio', 'Ultra-book ' + id_user, '');
                                win.focus();
                            });


                            // memobook
                            ub_memobook.btn_add('#book_open');
                            //if (page_type == 'memobook')    ub_memobook.btn_del_popup("#mfp-book_add_selection");



                            // stats
                            // deja en place a l'aff mini-book
                            // console.log( 'STAST + 1 #user_'+id_user );
                            //ub_ill_plus_de_book.stats_book_partage: function (stats_st_champ, id_user, stats_st_cles) {
                            if (id_user)    ga('send', 'pageview', '/book_' + id_user); //stats GA


                            // bouton book suivant
                            $('#mfp-book_suivant').bind('touchend click', function (event) {
                                event.preventDefault();
                                event.stopPropagation();
                                // next
                                //console.log( book_id_next+'  '+id_user );

                                if (book_id_next) {

                                    $('#mfp-book_suivant_lien').fadeOut('slow', function () {

                                        $.swipebox.close();

                                        $('#' + book_id_next + ' a').trigger('click');
                                        $('#swipebox-overlay').removeClass('anim');	//pas d'anim book suivant
                                        $(this).fadeIn('slow');
                                    });

                                } else {

                                    console.log('plus de book');

                                }

                            });


                            // statslive - after open
                            ub_ill_plus_de_book.statlive(id_user, img);

                            // bouton top hide
                            //$('.btn_top').removeClass('show');

                            // intermediate
                            ub_ill_plus_de_book.inter_maj(0, slider_data);


                        },

                        beforeOpen: function () {
                            $('#swipebox-overlay').remove();
                        },



                        afterClose: function () {
                            //ub_scrollPagination.init();
                            ub_fn.url_hash_del();
                        },
                        hideBarsOnMobile: false,
                        hideBarsDelay: true,
                        loopAtEnd: true,
                        nextSlide: function () {  	// statslive
                            ub_ill_plus_de_book.statlive(id_user, img);

                            // index
                            tmp = $('#swipebox-slider .slide').index($('#swipebox-slider .slide.current'));
                            $('.ub_img_nb strong').html(tmp+1);

                            // intermediate
                            ub_ill_plus_de_book.inter_maj(tmp, slider_data);

                        },
                        prevSlide: function () {  	// statslive
                            ub_ill_plus_de_book.statlive(id_user, img);

                            // index
                            tmp = $('#swipebox-slider .slide').index($('#swipebox-slider .slide.current'));
                            $('.ub_img_nb strong').text(tmp+1);

                            // intermediate
                            ub_ill_plus_de_book.inter_maj(tmp, slider_data);

                        }

                    }
                );


                // popup
                $('.popup_link').popup({});


            });

        }







        // intermediate

        , inter_maj: function (tmp, slider_data) {

            //console.log(slider_data.book_img[tmp])


            $('#msg_send').transition('show');
            $('#work_buy').transition('hide').next('br').show();
            $('#work_similary').transition('hide').next('br').show();


            if(typeof(slider_data.book_img)!=="undefined") {

                if (typeof slider_data.book_img[tmp]['img_offre_vente'] !== 'undefined') {

                    if (slider_data.book_img[tmp]['img_offre_vente']['buy_actif']) {
                        //$('.img_offre_vente').text( slider_data.book_img[tmp]['img_offre_vente']['buy'] )
                        //console.log('->'+slider_data.book_img[tmp]['img_offre_vente']['buy_desc'])

                        $('#work_buy').attr('data-content', slider_data.book_img[tmp]['img_offre_vente']['buy_desc'].replace(/\\n/gi, ' '))

                        $('#work_buy').transition('fade in ', 600);


                    } else {

                        $('.img_offre_vente').text('');
                        $('#work_buy').next('br').hide();

                    }

                }

                if (typeof slider_data.book_img[tmp]['img_offre_similaire'] !== 'undefined') {

                    if (slider_data.book_img[tmp]['img_offre_similaire']['sim_actif']) {
                        //$('.img_offre_similaire_min').text( slider_data.book_img[tmp]['img_offre_similaire']['sim_min'] )
                        //$('.img_offre_similaire_max').text( slider_data.book_img[tmp]['img_offre_similaire']['sim_max'] )

                        $('#work_similary').attr('data-content', slider_data.book_img[tmp]['img_offre_similaire']['sim_desc'].replace(/\\n/gi, ' '));

                        $('#work_similary').transition('fade in ', 600);
                    } else {
                        $('#work_similary').next('br').hide();
                        $('.img_offre_similaire_min, .img_offre_similaire_max').text('');
                    }

                }
            }


        }

        ,modale_link_info: function () {

            $('.sub_title_info').transition('hide'); // close default
            $('.sub_title').click(function() {
                    $this_info = $(this).next('.sub_title_info');
                    if ( ! $this_info.hasClass('visible') )     $('.sub_title_info').transition('hide'); // close all
                    $this_info.transition('scale');
                });



        }

        // intermediate form contact sur les books               #2019

        , intermediate_btn: function (id_user, data) {

            //console.log('intermediate_btn: function');
            //console.log(data);

            var data_ = data;

            $('#work_similary, #work_buy, #msg_send').bind('touchend click', function (event ) {
                event.preventDefault();
                event.stopPropagation();


                var $modal = $('.ui.modal.modal.modal_content_ajax_intermediate');

                // var url = '/contact_show__' + id_user;
                // console.log('open contact'+id_user);
                // console.log('modal ' + url);
                // console.log(data_);

                // header visuel
                data_.book_visuel_selection =    $('#swipebox-slider > div.slide.current img').attr('src');
                if ( data_.book_visuel_selection == undefined) {
                    data_.book_visuel_selection =    $('#swipebox-slider > div.slide:first img').attr('src');
                }

                // type sim/buy
                var type_demande = $(this).attr('id')

                // detail request
                if ( type_demande == 'work_similary') {
                    data_.type_demande =        'work_B_similary';
                    data_.mf_request_detail =   $('#work_similary').data('content') + ' (' + $('#work_similary .img_offre_similaire_min').text() + ' - ' + $('#work_similary .img_offre_similaire_max').text() + ')'
                }
                else if ( type_demande == 'work_buy') {
                    data_.type_demande =        'work_C_buy';
                    data_.mf_request_detail =   $('#work_similary').data('content') + ' ' + $('#work_similary .img_offre_vente').text();
                }
                else  {
                    data_.type_demande =        'work_A_contact';
                    data_.mf_request_detail =   '';
                }


                //console.log(data_);


                // tpl header
                var ub_theTemplate_header = Handlebars.compile($("#tpl_bloc_modal_content_ajax_intermediate_header_creatif").html());
                var book_info_tpl = ub_theTemplate_header(data_);
                $modal.find('.segment_header').html(book_info_tpl);

                // tpl form
                var ub_theTemplate_header = Handlebars.compile($("#tpl_bloc_modal_content_ajax_intermediate_form_creatif").html());
                var book_info_tpl = ub_theTemplate_header(data_);
                $modal.find('.segment_content').html(book_info_tpl);

                // form options
                $( '.moreinfo_btn' ).click(function() {
                    $('.moreinfo').toggle();
                });

                // link info
                ub_ill_plus_de_book.modale_link_info();

                // form rules + submit
                ub_ill_plus_de_book.rules_inter();
                ub_ill_plus_de_book.validate_submit_inter();

                // captcha image
                ub_ill_plus_de_book.captcha_reload();


                $modal
                    .modal({
                        onShow: function (callback) {

                            var $inter = $('.modal_content_ajax_intermediate');

                            $inter.find('.loading_segment').transition('fade out', function() {
                                $inter.find('.segment_header, .segment_content').transition('fade in', function() {

                                });
                            });

                        }

                    }).modal('show')

            });

            //setTimeout(function(){ $('#work_similary').trigger('click'); }, 500);


        }




        ,validate_submit_inter: function () {

            $('.valider_submit_inter').on('click', function(e) {

                e.stopPropagation();
                e.preventDefault();
                //console.log('submit');

                ub_ill_plus_de_book.form_validate_inter.form('validate field', 'us_mail');
                ub_ill_plus_de_book.form_validate_inter.form('validate field', 'us_nom_prenom');
                ub_ill_plus_de_book.form_validate_inter.form('validate field', 'us_message');




                if( ub_ill_plus_de_book.form_validate_inter.form('is valid') ) {

                    console.log('submit############### inter');

                    allFields = ub_ill_plus_de_book.form_validate_inter.form('get values');

                    ub_ill_plus_de_book.validate_submit_send_inter(allFields);

                }
            });

        }

        // validate inter

        , url_intermediate :        '/intermediate_send'
        , form_validate_inter :     '' // load after make tpl


        , validate_submit_send_inter :  function (allFields){

            console.log(allFields);

            // test
            //$('input[name=g-recaptcha-response').val('##');


            $.ajax({
                url:        ub_ill_plus_de_book.url_intermediate,
                type :      "POST",
                data :      allFields,
                dataType:   "json",

                success: function(retour_data) {
                    //console.log('######## retour '+ retour_data);

                    $('#segment_intermediate_form').transition('stop').transition('fade out', function(){
                        $('.loading_segment').transition('stop').transition('fade in', function() {
                            // {"error":false,"error_list":[],"action":"work_similary","savedb_result":true}

                            // error
                            if (retour_data.error) {
                                ub_ill_plus_de_book.validate_submit_error(retour_data);

                            } else
                            // ok - no error
                            {
                                ub_ill_plus_de_book.validate_submit_presentation(retour_data);

                            }
                        });
                    });

                }

            });

        }

        // show validation msg
        , validate_submit_presentation: function (retour_data) {

            $('.loading_segment').transition('stop').transition('fade out', function(){
                $('#segment_intermediate_reponse').transition('stop').transition('fade in')
            });

        }

        // show validation msg error
        , validate_submit_error: function (retour_data) {

            // erreur captcha seule : revenir au formulaire avec message inline
            if (retour_data['error_list'] && retour_data['error_list']['captcha_answer']) {
                ub_ill_plus_de_book.captcha_reload();
                $('.loading_segment').transition('stop').transition('fade out', function() {
                    $('#segment_intermediate_form').transition('stop').transition('fade in');
                    $('#captcha_error_msg').show();
                });
                return;
            }

            var error_list = '';
            //retour_data = {"error":true,"error_list":{"g-recaptcha-response":["recaptcha"]},"savedb_result":false};

            $.each(retour_data['error_list'], function(el) {
                error_list += retour_data['error_list'][el]+ '<br/>';
            });

            $('#segment_intermediate_reponse').transition('hide');
            $('#segment_intermediate_reponse_error p.error_list').html(error_list);

            $('.loading_segment').transition('stop').transition('fade out', function(){
                $('#segment_intermediate_reponse_error').transition('stop').transition('fade in', function(){

                    $('.btn_back_form').on('click', function(e) {
                        e.stopPropagation();
                        e.preventDefault();

                        // captcha reload
                        ub_ill_plus_de_book.captcha_reload();

                        $('#segment_intermediate_reponse_error').transition('stop').transition('fade out', function() {
                            $('#segment_intermediate_form').transition('stop').transition('fade in');
                        });
                    })

                });
            });

        }


        // captcha image load/reload
        , captcha_reload: function () {
            var ts = new Date().getTime();
            $('#captcha_img').attr('src', '/captcha_img?' + ts);
            $('#captcha_answer_input').val('');
            $('#captcha_error_msg').hide();
            // clic sur l'image ou l'icône reload
            $('#captcha_img, #captcha_reload_btn').off('click.captcha').on('click.captcha', function () {
                ub_ill_plus_de_book.captcha_reload();
            });
        }

        // inter rules
        , rules_inter: function () {
            

            this.form_validate_inter = $('#intermediate_form');

            // validate
            this.form_validate_inter
                .form({
                    inline:     true,
                    on:         'change',
                    debug:      true,
                    verbose :   true,
                    delay:      true,
                    duration:   100,

                    fields: {

                        /*
                        us_login: {
                            identifier: 'us_login',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Champ vide']
                                },
                                {
                                    type   : 'minLength[4]',
                                    prompt : __.__['Votre nom de book/identifiant doit contenir plus de  {ruleValue} caractères']
                                },
                                {
                                    type   : 'regExp[/^[a-z0-9_-]{3,24}$/]',
                                    prompt : __.__['Caractéres incorrecte']
                                }
                            ]

                        },
                        */

                        us_message : {
                            identifier: 'us_message',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Champ vide']
                                }
                            ]
                        },

                        us_mail : {
                            identifier: 'us_mail',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Champ vide']
                                },
                                {
                                    type   : 'contains[@]',
                                    prompt : __.__['Il ne s’agit pas d’un mail']
                                }
                            ]
                        },


                        us_nom_prenom : {
                            identifier: 'us_nom_prenom',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Champ vide']
                                }
                            ]
                        },


                        captcha_answer : {
                            identifier: 'captcha_answer',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : 'Recopiez le code'
                                },
                                {
                                    type   : 'minLength[4]',
                                    prompt : 'Code incomplet'
                                }
                            ]
                        },

                        us_tel : {
                            identifier: 'us_tel',
                            optional   : true,
                            rules: [
                                {
                                    type   : 'regExp[/^[0-9-+]{6,24}$/]',
                                    prompt : __.__['Caractéres incorrecte']
                                }
                            ]
                        }
                        /*
                        us_societe : {
                            identifier: 'us_societe',
                            optional   : true,
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Champ vide']
                                }
                            ]
                        }
                        */
                    }
                });



        }


        // stats books - #Aout2019
        //
        , stats_book: function (selecteur) {

            //return true;

            /*
            <div class="us_stats_book memob_data hide" data-us_login="bazil"
            data-us_="us_stats_st_cles" data-stats_st_cles="37fcd7879061b1bb8b8a50e836de21af"
            data-src="https://www.extra-book.com/2012_stats/st_action.php"></div>
             */

            setTimeout(function () {
                $(selecteur + ' .us_stats_book').each(function () {

                    ub_msg_core.ub_url_http_stats = 'https://www.extra-book.com';
                    var url = ub_msg_core.ub_url_http_stats + '/2012_stats/st_action.php';

                    //$(this).parent().css('border','1px solid red');


                    $.ajax({
                        type: "GET",
                        url: url,
                        data: {
                            'action':       'add',
                            'st_champ':     'st_minibook',
                            'us_login':     $(this).data('us_login'),
                            'st_cles':      $(this).data('stats_st_cles'),
                            'r': 1
                        },
                        dataType: 'jsonp',
                        jsonp: 'jsoncallback',
                        success: function (data_retour) {
                            console.log('stats '+data_retour );
                        },
                        error: function (resultat, statut, erreur) {
                            //console.log('error'+resultat+ ' ====== '+statut+ ' ============ '+erreur);
                        }
                    });

                });
                //console.log( '______init stats' );
            }, 520);

        }



        // show all book's stats    view & like                   #2018 #Aout2019
        // cont_retour = selector
        //
        , aff_stats: function (cont_retour) {

            var us_login = [];

            ub_msg_core.ub_url_http_stats = 'https://www.extra-book.com';


            $(cont_retour).each(function () {
                var tmp_id_user = $(this).data('user'); //.replace(/user_/g, '');
                us_login.push(tmp_id_user);
                //console.log(tmp_id_user);
            });




            // ele vide
            if (typeof us_login == 'undefined' || us_login.length == 0)  return false;


            var lien = ub_msg_core.ub_url_http_stats + '/2012_stats/st_action.php';


            $.ajax({
                type: "GET",
                async: true,
                url: lien,
                data: {
                    'action': 'stats_aff_book',
                    'st_champs': 'st_minibook, st_memo',
                    'us_login': JSON.stringify(us_login),
                    'st_cles': ''
                },

                dataType: 'jsonp',
                jsonp: 'jsoncallback',

                success: function (data_retour) {

                    //console.log('us_login'+data_retour );

                    for (var i = 0; i < us_login.length; i++) {
                        // console.log('stats aff  '+us_login[i]+' === '+data_retour[i].st_minibook);
                        // memo du book
                        if (data_retour[i].st_memo < 2) {
                            $('#user_' + us_login[i]).find('.stats_sel').parent().remove();
                        } else {
                            $('#user_' + us_login[i]).find('.stats_sel').html(data_retour[i].st_memo).fadeIn('slow');
                        }

                        // vue du book
                        var tmp_nb = data_retour[i].st_book;
                        if (typeof(tmp_nb) != 'undefined') {
                            var tmp_nb_lg = tmp_nb.length;
                            if (tmp_nb_lg > 3) tmp_nb = '<strong>' + tmp_nb.substr(0, tmp_nb_lg - 3) + ',' + tmp_nb.substr(tmp_nb_lg - 4, 1) + '</strong> k';
                        } else {
                            tmp_nb = '';
                        }

                        $('#user_' + us_login[i]).find('.stats_vue').html(tmp_nb).fadeIn('slow');

                    }

                },
                error: function (resultat, statut, erreur) {
                    console.log('error' + resultat + ' ====== ' + statut + ' ====== ' + erreur);
                }
            });


        }


        // stats live 25juil - 2016
        //

        , statlive: function (id_user, img_all) {

            //return true;

            // index
            var swipebox_index = $('#swipebox-slider .slide').index($('#swipebox-slider .slide.current'));
            //console.log(swipebox_index);

            // visiteur
            var statlive_visiteur = $('#ub_user_ident_btn').text().replace(/\r?\n|\r|\t/g, '');
            if (statlive_visiteur.length == 0 || statlive_visiteur == 'Connexion') {
                statlive_visiteur = 'anonyme__' + Math.floor((Math.random() * 10000) + 1);
            }

            var statlive_img_url = (typeof(img_all[swipebox_index]) == 'undefined') ? '' : img_all[swipebox_index].href;
            //var statlive_img_url = img_all[swipebox_index].href;


            var statlive_imd_id = statlive_img_url.substring(statlive_img_url.lastIndexOf('/') + 1);
            statlive_imd_id = statlive_imd_id.replace(/\.([a-zA-Z]+)$/, '');


            var vignette = $('#mfp-book_info .mfp-ill_desc .mfp-ill_vign img').attr('src');
            //vignette = vignette.replace("/phpthumb_master/phpThumb.php?src=/",'');
            vignette = ub_msg_core.ub_url_http + vignette;

            /*
             console.log({
             'us_id': 			id_user,
             'us_vignette': 		vignette ,
             'us_nom_prenom': 	$('#mfp-book_info .mfp-book_info_cont .mfp-ill_desc h3').text(),
             'img_id': 			id_user + '_' + statlive_imd_id,
             'img_url': 			statlive_img_url,
             'visiteur': 		statlive_visiteur,
             'type_book':		'mini-book or book'
             });
             */

            /* desac en dev
             socket.emit('stats', {
             'us_id': 			id_user,
             'us_vignette': 		vignette,
             'us_nom_prenom': 	$('#mfp-book_info .mfp-book_info_cont .mfp-ill_desc h3').text().replace(/\r?\n|\r|\t/g, ''),
             'us_type':			$('#user_'+id_user).data('us_type'),
             'img_id': 			id_user + '_' + statlive_imd_id,
             'img_url': 			statlive_img_url,
             'visiteur': 		statlive_visiteur,
             'type_book':		'mini-book'
             });
             */
            /*
             if (Math.floor((Math.random() * 20) + 1) > 14) {
             socket.emit('stats', {
             'us_id': 			id_user,
             'us_vignette': 		vignette,
             'us_nom_prenom': 	$('#mfp-book_info .mfp-book_info_cont .mfp-ill_desc h3').text().replace(/\r?\n|\r|\t/g, ''),
             'img_id': 			id_user + '_' + statlive_imd_id,
             'img_url': 			statlive_img_url,
             'visiteur': 		statlive_visiteur,
             'type_book':		'book'
             });
             }
             */

        }



    };



    /* =memo-book 2014
     -------------------------------------------------------------- */

    ub_memobook = {

        name: ''
        , msg_supp_memo_book: ub_msg_core.msg_supp_memo_book
        , msg_vide_memo_book: 'selection vide'


        // init
        //
        , init: function () {

            //console.log('mb init 1');
            if (!this.storage_exist) return false;


            // Safari, in Private Browsing Mode, looks like it supports localStorage but all calls to setItem
            // throw QuotaExceededError. We're going to detect this and just silently drop any calls to setItem
            // to avoid the entire page breaking, without having to do a check at each usage of Storage.
            if (typeof localStorage === 'object') {
                try {
                    localStorage.setItem('localStorage', 1);
                    localStorage.removeItem('localStorage');
                } catch (e) {
                    Storage.prototype._setItem = Storage.prototype.setItem;
                    Storage.prototype.setItem = function () {
                    };
                    return false;
                    //alert('Your web browser does not support storing settings locally. In Safari, the most common cause of this is using "Private Browsing Mode". Some settings may not save or some features may not work properly for you.');
                }
            }


            this.create_storage(); 		// si n'existe pas
            this.maj_nb(); 				// compteur
            this.btn_add('');			// bouton add
            //console.log('mb init 2');

            $('.memo_nb').removeClass('hidden');

        }

        // active les boutons filtre #2018
        , filtre_btn: function () {
            $('.memo_domaine .filtre').on('click', function () {

                $(this).toggleClass('selected');

                var the_filtre = $(this).data('filtre');

                $('.card.' + the_filtre)
                    .transition('vertical flip')
                ;
            })
        }

        // aff les books enregistres #2018
        //
        , memobook_filtre: []

        , front_page_memobook: function () {

            this.maj_nb();

            memobooks_list = {
                date: '',
                user: '',

                lists: [{
                    list_name: 'Ma liste 2018',
                    list_dommaine: 'Illustration',
                    list_data: '#christinecircosta,#michelboucher,#antoinecogne,#camilleandre,#ste,#kloe79,#ludivinemartin,#perodessin'
                }]

            };


            // readaptation old data memo
            var books_all = ub_memobook.list();
            console.log(books_all)
            var list_data = '';
            $.each(books_all, function (key, val) {
                list_data += ',#' + val.id_user;
            });
            list_data = list_data.substr(1);
            console.log(list_data);

            // synchro
            memobooks_list.lists[0].list_data = list_data;


            var url_data = {
                page_type: '/pseudo_hash',
                page_domaine: '',
                page_num: 0,
                book_auto_open: false,
                book_id: '',
                page_url: '/rechercher_submit'
            }
            // make_url_save
            var data_url = book.make_url(url_data);

            //console.log(data_url);

            var list_book = ',' + memobooks_list.lists[0].list_data;
            list_book = list_book.split(',#');
            list_book.shift();

            console.log(list_book);

            if ( list_book.length == 0 ) {
                //console.log('end');
                // modif_bloc: function (tpl_id, tpl_data, dom_id, callback)

                book.modif_bloc(
                    "#tpl_memobook_vide",
                    { message:  ub_memobook.msg_vide_memo_book },
                    '#bloc_memobook'
                    ,
                    function () {
                        $('.vide').removeClass('hide');
                        //console.log('end')
                    }
                );

            }


            var the_end = list_book.length;
            $.each(list_book, function (key, val) {

                // query -> id book
                data_url.tmp_data.q = val;

                // show book
                book.show_book(data_url,
                    // callback to add filtre
                    function (cont_retour) {

                        if( typeof cont_retour !=  'object' ) return
                            ;

                        //if ( typeof us_type !== 'undefined') {

                        if ( typeof us_type === 'undefined')  us_type = 'Illustrateur';

                            filtre = cont_retour[0].us_type.toLowerCase().replace(' ', '_');
                            if (ub_memobook.memobook_filtre.indexOf(filtre) == -1) {
                                ub_memobook.memobook_filtre.push(filtre);
                                filtre_title = filtre.replace('_', ' ')
                                $('.memo_domaine').append('<a><li class="filtre" data-filtre="' + filtre + '"><div class="nuancier coul_' + filtre + ' "></div>' + filtre_title + '</li></a>')
                            }
                        //}

                            // active les btn filtre (fin de tous les retours ajax)
                            if (the_end == (key + 1)) {
                                ub_memobook.filtre_btn();
                                ub_memobook.btn_del();
                            }

                        //console.log(the_end +'=='+ (key + 1) )
                    }
                );

            })


            //console.log(ub_memobook.memobook_filtre);


        }


        // create storage vide
        //
        , create_storage: function () {

            if (!localStorage["books"]) {
                var books = [];
                localStorage["books"] = JSON.stringify(books);
                //alert ('ok-create_storage');
            }
            return true;
        }




        // maj nb books
        //
        , maj_nb: function () {
            var nb = ub_memobook.nb();
            if (nb > 0) {
                $('.memo_nb').text(nb).show();
                $("#books_abscent").hide();
            } else {
                $('.memo_nb').hide();
                $("#books_abscent").show();
            }
        }

        // changement du douton add par deleted (deja selectionné)
        ,btn_change_for_deled: function (sel) {


            $(".tipsy").remove();

            $(sel)
                .transition({
                    animation  : 'scale',
                    duration   : '500ms',
                    onComplete : function() {

                        $(sel)
                            .removeClass('button')
                            .addClass('no_button')
                            .html('<i class="heart red icon"></i>')
                            .transition('horizontal flip', '500ms')

                        /*
                        $(sel)
                            .html('<i class="heart outline icon"></i><i class="trash alternate icon"></i>')
                            .transition('horizontal flip', '500ms')


                        $('.tooltip_tipsy').tipsy({
                            title: 'title',
                            fade: true,
                            offset: 3,
                            background: 'white',
                            color: 'black'
                        });
*/

                    }
                })
            ;

        }

        // bouton add #2018
        , btn_add: function (selecteur) {

            var book_array = ub_memobook.list();

            $(selecteur + ' .memobook_add').each(function () {

                var user = $(this).data('user');
                //console.log( user+ '  >>>>>>>>>>> '+ub_memobook.exist(user, book_array) );

                if (ub_memobook.exist(user, book_array) == -1) {	// ne pas aff bouton si deja enregistre

                    $(this).bind('touchend click', function () {
                        //$(this).click(function() {
                        ub_memobook.add(user);
                        ub_memobook.maj_nb();

                        //$('a[href="/memobook"] > i').animate({  fontSize: "0.4em"	},500 ).animate({  fontSize: "1.4em"},600);
                        ub_memobook.btn_change_for_deled (this);
                    });
                } else {

                    // deja selectionne
                    ub_memobook.btn_change_for_deled (this);
                }
            });
        }

        // bouton del popup
        //	 aff uniqueemnt pour la page memobook

/*
         , btn_del_popup: function (selecteur) {
         $(selecteur + ' .memobook_del').each(function () {

         var user = $(this).data('user');

         $(this).bind('touchend click', function () {
         //console.log('del '+user);
         ub_memobook.del(user);
         $('#user_' + user).parent().fadeOut('slow').remove();
         ub_memobook.maj_nb();
         ub_memobook.btn_filtre_memobook(ub_memobook.list());
         $(this).hide('slow');


         setTimeout(function () {
           //  $container.isotope('layout');
         }, 250);


         });
         });

         }
*/

        // bouton del #2018
        //
        , btn_del: function () {


            $('.card .image.dimmable').append('\
                <div class="ui red right corner label">\
                <i class="trash alternate outline icon"></i>\
                </div>\
            ')


            $('.card .corner').bind('touchend click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                var user = $(this).parent().parent().data('user');

                console.log('del ' + user);

                $('#user_' + user).transition({
                    animation: 'vertical flip',
                    onComplete: function () {
                        $(this).remove();
                    }
                });

                ub_memobook.del(user);
                ub_memobook.maj_nb();

            });


        }


        // recup el class="memob_data"
        //
        , recup_data: function (id_user) {

            var memob_data = {};
            var tag;
            var key;
            var img_book = [];
            var img_book_mobile = [];
            //alert(id_user);

            // key id_user+dir
            memob_data.id_user = id_user;
            memob_data.us_dir = id_user;
            memob_data.us_memob_date = new Date();
            memob_data.us_type = $('#user_' + id_user).data('us_type');

            // serialise slider
            slider = $('#user_' + id_user).data('slider').book_img;
            memob_data.slider = JSON.stringify(slider);
            //memob_data.us_slider = 			$.param( us_slider );
            //console.log( memob_data.slider );


            // tous les el memob_data
            $('#user_' + id_user + ' .memob_data').each(function (index, value) {

                tag = $(this).tagName();
                //console.log(index + ':' + value + ':' + tag);

                switch (tag) {
                    case 'img':
                        val = $(this).attr("src");
                        val_alt = $(this).attr("alt");
                        //val = val.replace(/&zc=1&w=91&h=91&q=75/g,"");
                        //console.log(val+' = ');

                        break;

                    case 'h3':
                    case 'p':
                    case 'div':
                    case 'span':
                        val = $(this).text().replace(/^t+|^\s+|\s+$/g, '');	// netoyage tab+espace debut et fin
                        //console.log(val+' = 2');
                        break;

                    case 'a':
                        val = $(this).attr("href");
                        break;
                }

                key = $(this).data('us_'); // key ="us_nomvar"
                //console.log( key+' = '+val );

                // cles speciales
                if (key == 'us_stats_st_cles') {
                    memob_data['stats_st_cles'] = $(this).data('stats_st_cles');//alert($(this).data('stats_st_cles'));
                }
                else if (key == 'img_book_img_fichier') {
                    img_book.push({img_fichier: val, img_titre_alt: val_alt}); // array image
                }
                else if (key == 'img_book_img_fichier_mobile') {
                    img_book_mobile.push({img_fichier: val, img_titre_alt: val_alt}); // array image
                }
                else if (key == 'us_selection_date') {
                    memob_data['us_affhome'] = "true"; // pour afficher la date de selection
                    memob_data['us_selection_date'] = val;
                }
                else if (key == 'us_geo') {
                    memob_data[key] = $(this).data('lat') + ',' + $(this).data('lng'); // geo lat,lnt
                    memob_data['us_lat'] = $(this).data('lat');
                    memob_data['us_lng'] = $(this).data('lng');
                }
                else {
                    memob_data[key] = val; // load objet cle normal
                }

            });

            memob_data.img_book = img_book; 			// array image
            memob_data.img_book_mobile = img_book_mobile;	// array image

            //console.log(memob_data);
            return memob_data;
        }

        // search if exist el
        //
        , exist: function (el, book_array) {
            //var index = book_array.indexOf(el);
            //var index = findIndex_multi(el,book_array);
            //return (index > -1)?index:false;

            var result = -1;
            for (var i = 0, j = book_array.length; i < j; i++) {
                //console.log(book_array[i].id_user+' === '+el+' === '+i);
                if (book_array[i].id_user == el) {
                    return i;
                }
            }
            return result;
        }
        // del book
        //
        , del: function (user) {

            var all_books = this.list();
            var index = ub_memobook.exist(user, all_books);

            //console.log(all_books);
            //console.log(all_books[index].id_user+' === '+user+' === '+index);

            if (index > -1) {
                var removed = all_books.splice(index, 1);
                localStorage["books"] = JSON.stringify(all_books);
                return true;
            }

            return false;
        }

        // storage exist
        //
        , storage_exist: function () {
            return (typeof localStorage != 'undefined') ? true : false;
        }

        // nb book
        //
        , nb: function () {
            return ub_memobook.list().length;
        }

        // list book
        //
        , list: function () {

            try {
                return JSON.parse(localStorage["books"]);
            }
            catch (error) {
                return false;
            }


        }


        // add book
        //
        , add: function (e) {

            var book_data = this.recup_data(e);
            var all_books = this.list();

            all_books.push(book_data);
            localStorage["books"] = JSON.stringify(all_books);


            //alert(ub_memobook.list() );
            //ub_ill_affbook.aff_books({ books : ub_memobook.list() });

            // stats
            var lien = ub_msg_core.ub_url_http_stats + '/2012_stats/st_action.php';

            $.ajax({
                type: "GET",

                url: lien,
                data: {
                    'action': 'add',
                    'st_champ': 'st_memo',
                    'us_login': book_data.id_user,
                    'st_cles': book_data['stats_st_cles'],
                    'r': 1
                },

                dataType: 'jsonp',
                jsonp: 'jsoncallback',

                success: function (data_retour) {
                    console.log('retour st_memo' + data_retour + ' ' + book_data.id_user);
                },
                error: function (resultat, statut, erreur) {
                    console.log('error' + resultat + ' ====== ' + statut + ' ====== ' + erreur);
                }
            });
            //alert("http://www.extra-book.com/2012_stats/st_action.php?action=add&st_champ=memo_add&us_login="+book_data.id_user+"&st_cles="+book_data['stats_st_cles']+"&r=1");

            return true;
        }


    };



});
