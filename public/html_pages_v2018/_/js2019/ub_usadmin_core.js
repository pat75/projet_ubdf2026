// UB

// Ads Brave
//
var brave = {

    ub_brave_cookie: {},
    ub_brave_first: true,

    init: function () {
        //console.log('brave->init '+this.intro_is_already_show());


        this.is_brave().then(function (isBrave) {

            brave.cookie_brave();

            //brave.show_brave_ads();

            //console.log('brave-> then '+isBrave);
            if (isBrave && !brave.cookie_brave_is_used()) {

                // Yes is brave !
                if (brave.intro_is_already_show() && brave.timing_to_show_ads_thank()) {

                    brave.show_brave_ads_thank();

                    // test barve code promo
                    brave.is_code_promo_test().then(function (isPromoReady_now) {
                        $('.brave_ads_thank_codepromo').transition('fade in', 1500);
                        $('.gifs')
                            .transition('set looping')
                            .transition('pulse', '2500ms');
                        $('.brave_ads_thank').addClass('brave_ads_thank_showcode');
                        $('.brave_actived_codepromo').on('click', function () {
                            brave.code_promo_activate();
                        })
                    });


                }

            } else {

                // is not brave...
                if (brave.intro_is_already_show() && brave.timing_to_show_ads()) {
                    brave.show_brave_ads();
                }

            }

            //alert( data );
        });


    }

    // test if Brave
    , timing_to_show_ads: function () {
        //console.log(brave.ub_brave_cookie.number_show )
        //console.log(brave.ub_brave_cookie.number_show % 4 )

        if (brave.ub_brave_cookie.number_show % 4 == 0 && brave.ub_brave_cookie.number_show < 30) {
            return true
        } else {
            return false
        }
    }

    , timing_to_show_ads_thank: function () {
        //console.log(brave.ub_brave_cookie.number_show )
        //console.log(brave.ub_brave_cookie.number_show % 4 )

        return true

        if (brave.ub_brave_cookie.number_show_thank % 4 == 0 && brave.ub_brave_cookie.number_show_thank < 10) {
            return true
        } else {
            return false
        }

    }
    // test if Brave
    , is_brave: function () {

        var deferred = new $.Deferred();

        var url_ = 'https://api.duckduckgo.com/?q=useragent&format=json';

        $.getJSON(url_, function (data) {
            var isBrave = data['Answer'].includes('Brave');
            //console.log('isBrave > '+isBrave);
            brave.isBrave_now = isBrave;
            deferred.resolve(isBrave)
        });

        return deferred.promise();
    }

    // test if code promo not used
    , is_code_promo_test: function () {

        var deferred = new $.Deferred();

        var url_ = '/app/active_brave_promo/test';

        $.getJSON(url_, function (data) {
            var isPromoReady_now = data['action'].includes('activeBravePromo_ready');
            console.log(data);
            deferred.resolve(isPromoReady_now)
        });

        return deferred.promise();
    }

    // activate code promo
    , code_promo_activate: function () {

        var url_ = ub_msg_core.ub_url_http + '/app/active_brave_promo/active';

        $.getJSON(url_, function (data) {
            var isPromo_actived = data['action'].includes('activeBravePromo');
            //console.log(data);

            if (isPromo_actived) {

                $('.brave_ads.brave_ads_thank_showcode h5').transition('fade out', 100);
                $('.brave_noactivate').transition('fade out', 100, function () {
                    $('.brave_activated').transition('fade in', 1500);
                });

                $('.gifs')
                    .transition('remove looping')
                //.transition('set allowRepeats')
                //.transition('pulse','500ms');

                if (true) {
                    // cookie
                    var ub_brave_cookie = $.cookie('ubdf_brave');
                    ub_brave_cookie = JSON.parse(ub_brave_cookie);
                    ub_brave_cookie.brave_promo_actived = true;
                    $.cookie('ubdf_brave', JSON.stringify(ub_brave_cookie), {expires: 100});
                }

            }

        });


    }


    // test if promo brave is used
    , cookie_brave_is_used: function () {
        var ub_brave_cookie = $.cookie('ubdf_brave');
        ub_brave_cookie = JSON.parse(ub_brave_cookie);
        return ub_brave_cookie.brave_promo_actived;
    }



    // save brave cookies
    , cookie_brave: function () {

        //$.cookie('ub_brave','');
        var ub_brave_cookie = $.cookie('ubdf_brave');


        if (!ub_brave_cookie) {

            var ub_brave_cookie = {
                first_date: brave.date_now(),
                number_show: 1,
                number_show_thank: 0,
                brave_now: false,
                brave_promo_actived: false
            };

        } else {

            ub_brave_cookie = JSON.parse(ub_brave_cookie);
            //console.log(brave.isBrave_now)

            if (brave.isBrave_now) {

                // is brave.
                // save brave now
                if (!ub_brave_cookie.brave_now) {
                    ub_brave_cookie.brave_now = true;
                    ub_brave_cookie.brave_now_date = brave.date_now();
                } else {
                    // not enought brave
                    ub_brave_cookie.number_show_thank++;
                    //console.log('add number_show_thank')
                }

            } else {

                // not brave !
                ub_brave_cookie.number_show++;
                //console.log('add number_show')

            }
        }

        brave.ub_brave_cookie = ub_brave_cookie;

        //console.log( ub_brave_cookie )

        $.cookie('ubdf_brave', JSON.stringify(ub_brave_cookie), {expires: 100});

        //brave.setCookie('ubdf_brave', JSON.stringify(ub_brave_cookie), 100 );
        //$.cookie('ubdf_brave', JSON.stringify(ub_brave_cookie), { expires: 10, path:'/' });


        //alert('brave')

    }


    , show_brave_ads: function () {


        //console.log( brave.ub_brave_cookie.number_show );

        $('.brave_ads_notbrave').transition('fade', 500);


        $('.btn_brave_info').popup({
            popup: '.brave_popup.popup',
            position: 'left center',
            target: '.brave_ads_content',
            distanceAway: 20
        })
        ;

    }

    , show_brave_ads_thank: function () {
        $('.brave_ads_thank').transition('fade', 500);
    }



    // other
    , date_now: function () {
        var the_date = new Date()
        return the_date.getDate() + "/" + (the_date.getMonth() + 1) + "/" + the_date.getFullYear();
    }


    // test intro
    , intro_is_already_show: function () {

        var aide_id_cook = 'aide_intro__projet__portfolio';

        if (typeof localStorage != 'undefined') {
            var ub_aide_accueil_ = (localStorage.getItem(aide_id_cook) ? true : false);
        } else {
            var ub_aide_accueil_ = ($.cookie(aide_id_cook) ? true : false);
        }
        //console.log( ub_aide_accueil_ )
        return ub_aide_accueil_;
    }


};


// form ajax -oct 2017 -mai 2018
//



var fm_ajax = {

    btn_edit: 'edit'
    , url: '/fm_ajax'


    , init: function () {

        console.log('fm_ajax->init');


        // fm-ajax
        //
        /*
		 <div class="fm-ajax">
		 <label for="us_pf_nom">Titre<span class="fm-help tooltip_tipsy fonticon-uni2753" original-title="Le titre de votre portfolio apparaît dans le haut de la page du navigateur"></span></label>
		 <input class="fm-field fm-input_auto_expand valid" name="us_pf_nom" value="aaaabfffbbbccc" placeholder="Contenu" data-count_input_max="60" style="width: 105px;">
		 <button type="button" class="fm-edit"><div class="fonticon-edit"></div></button>
		 <button type="button" class="fm-ajax-submit show"><span class="fonticon-check"></span></button>
		 <button type="button" class="fm-ajax-cancel show"><span class="fonticon-x"></span></button>
		 <button type="button" class="fm-ajax-loader"><img class="loader" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Loader..."></button>
		 <div class="input_count" style="color: unset;">14 / 60 max.</div>
		 </div>
		 */


        // combo 2018
        //

        $('.ui.dropdown').dropdown();

        $('.fm-ajax-combo select').on('change', function (e) {
            var $optionSelected = $("option:selected", this);
            var valueSelected = this.value;
            //alert(this.name + ' '+valueSelected+ ' '+$optionSelected.text() );

            var $el = $(this).parent();

            fm_ajax.send_combo($el, this.name, $optionSelected.text());

        });


        // input + textarea 2018
        //
        $(".fm-ajax").each(function () {

            var $el = $(this);

            $el.children('.fm-edit, .fm-field').click(function (event) {


                event.preventDefault();
                event.stopPropagation();

                var $el_fm_ajax = $(this).parent();
                var $el_field = $el_fm_ajax.children('.fm-field');

                // old data pour le cancel
                $el_field.data('old', $el_field.val());

                // si other edit exist && not selfclick
                if ($('.fm-ajax').hasClass('edit') && !$el_fm_ajax.hasClass('edit')) {
                    //console.log('trigger click');
                    $('.fm-ajax.edit .fm-ajax-submit').trigger("click");
                }


                $el_fm_ajax.addClass('edit');


                // active le cancel
                $el_fm_ajax.children('.fm-ajax-cancel').unbind().click(function (event) {

                    //console.log('cancel');

                    var $el_fm_ajax = $(this).parent();
                    var $el_field = $el_fm_ajax.children('.fm-field');

                    // tooltipper sur trouve dans la form_class.php
                    //
                    if ($el_field.hasClass("tooltipstered")) {
                        $el_field.removeClass('error').addClass('valid').tooltipster('close');
                    }

                    //if ( ! $el_fm_ajax.hasClass('edit')) {console.log('cancel noedit'); return true;}

                    $el_fm_ajax.children('.fm-ajax-submit').unbind(); // evite le trigger

                    $el_fm_ajax.removeClass('edit');
                    $el_field.val($el_field.data('old')).trigger('input');

                });

                // active validation ajax
                $el_fm_ajax.children('.fm-ajax-submit').unbind().click(function (event) {

                    var $el_fm_ajax = $(this).parent();
                    var $el_field = $el_fm_ajax.children('.fm-field');

                    //console.log( fm_validator.element( $el_field ) );

                    // validation form
                    if (typeof fm_validator != "undefined") {
                        if (!fm_validator.element($el_field)) {
                            // cancel
                            return false;
                        }
                    }

                    // pas de changement
                    if ($el_field.data('old') == $el_field.val()) {
                        // cancel
                        $el_fm_ajax.children('.fm-ajax-submit').unbind(); // evite le trigger
                        $el_fm_ajax.removeClass('edit');
                        return false;
                    }


                    //console.log( fm_validator.element( $el_field ) );
                    //console.log('submit'+$el_field.data('old')+' '+$el_field.val());

                    $el_fm_ajax.children('.fm-ajax-submit, .fm-ajax-cancel').removeClass('show');
                    $el_fm_ajax.children('.fm-ajax-loader').addClass('show');  // loader

                    // send
                    //console.log('submit-send');
                    fm_ajax.send_form($el_fm_ajax);


                });

                // focus off => focusout = bug


            });

        });


    },


    // valided indicator
    //
    valided: function ($el_fm_ajax) {

        //console.log('valided all '+ $el_fm_ajax.html());


        // input
        var $el_fm_valid =
            $el_fm_ajax
                .find('.fm-edit')
                .after('<i class="fm-valided check icon"></i>'); // <div class="fm-valided fonticon-uni2713"></div>


        $el_fm_valid.next('.fm-valided')
            .addClass('show')
            .delay(4500)
            .queue(function () {
                $(this).removeClass("show").dequeue();
            }).queue(function () {
            $(this).remove().dequeue();
        });

    },


    // send via ajax
    //
    send_form_motcles: function (fm_value, fm_field) {

        var fm_token = $('#ubdf_token').data('token');

        $('#rechercher_loader').addClass('show');

        var req = $.post(
            fm_ajax.url,
            {
                action: 'fm-ajax'
                , fm_field: fm_field
                , fm_value: fm_value
                , fm_token: fm_token
            },
            true,
            "json"
        );

        // success
        //
        req.success(function (data, msg) {
            //console.log(data);
            //console.log('msg : '+msg);
            $('#rechercher_loader').removeClass('show');

            //$el.children('.fm-ajax-loader').removeClass('show');  // loader

            //fm_ajax.has_error( data, $el );

        });


    },


    // send ajax combo
    //
    send_combo: function ($el, fm_field, fm_value) {

        var fm_token = $('#ubdf_token').data('token');
        //console.log(fm_field+' '+fm_value+' '+fm_token);

        $el.children('.fm-ajax-loader').addClass('show');  // loader

        var req = $.post(
            fm_ajax.url,
            {
                action: 'fm-ajax'
                , fm_field: fm_field
                , fm_value: fm_value
                , fm_token: fm_token
            },
            true,
            "json"
        );

        // success
        //
        req.success(function (data, msg) {
            //console.log(data);
            //console.log('msg : '+msg);

            setTimeout(function () {
                $el.children('.fm-ajax-loader').removeClass('show');
            }, 300);// loader

            fm_ajax.valided($el.parent());

            fm_ajax.has_error(data, $el);

        });

        // error
        //
        req.fail(function (data, msg) {
            fm_ajax.has_error(data, $el);
        });

    },


    // send ajax input+textarea
    //
    send_form: function ($el) {

        //console.log('send_form: function ');

        // input et textearea
        var fm_field = $el.children('.fm-field').attr('name');
        var fm_value = $el.children('.fm-field').val();
        var fm_token = $('#ubdf_token').data('token');
        //console.log(fm_field+' '+fm_value+' '+fm_token);

        var req = $.post(
            fm_ajax.url,
            {
                action: 'fm-ajax'
                , fm_field: fm_field
                , fm_value: fm_value
                , fm_token: fm_token
            },
            true,
            "json"
        );

        // success
        //
        req.success(function (data, msg) {
            //console.log(data);
            //console.log('msg : '+msg);

            setTimeout(function () {
                $el.children('.fm-field').text(fm_value);
                $el.children('button').addClass('show');
                $el.children('.fm-ajax-loader').removeClass('show');
                $el.removeClass('edit');
            }, 300);// loader

            fm_ajax.valided($el);

            fm_ajax.has_error(data, $el);

        });

        // error
        //
        req.fail(function (data, msg) {
            fm_ajax.has_error(data, $el);
        });

    },


    // has_error ?
    //
    has_error: function (data, $el) {
        if (data.error == undefined) return false;
        if (data.error == 'true') {
            $el.append('<span class="fm-error">' + data.error_msg + '</span>').find('.fm-error').delay(2000).fadeOut(1000, function () {
                $('.fm-error').remove();
            })
        }
    },

    // input_auto_expand
    //
    input_auto_expand: function () {

        $.fn.textWidth = function (text, font) {
            if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);

            $.fn.textWidth.fakeEl
                .text(text || this.val() || this.text() || this.attr('placeholder'))
                .css('font', font || this.css('font'));

            var fakeEl_width = $.fn.textWidth.fakeEl.width();
            if (fakeEl_width < 50) fakeEl_width = 50;
            return fakeEl_width + 35;
        };


        $('.fm-input_auto_expand').on('input', function () {
            var $el = $(this);

            // count_input
            var count_input = $(this).val().length;
            var count_input_max = $el.data('count_input_max');
            var $count_input = $el.parent().children('.input_count');

            if (count_input_max) {
                if (count_input > count_input_max) {
                    $count_input.css('color', 'red');
                    var val_bloc = $el.val().substring(0, count_input_max);
                    $el.val(val_bloc);
                    count_input--;
                } else {
                    $count_input.css('color', 'unset');
                }
                $count_input.html(count_input + ' / ' + count_input_max + ' max.');
            }


            var padding = 10; //Works as a minimum width
            // textarea / input
            //
            if ($el.is('textarea')) {
                // cols
                // la ligne la plus longue
                var max_ligne = $el.val().split(/[\r\n]/g);
                max_ligne.sort(function (a, b) {
                    return b.length - a.length;
                });
                // width
                var valWidth = ($.fn.textWidth(max_ligne[0]) + padding);
                $el.css('width', valWidth + 'px');


                // rows
                if ($el.val().match(/[\r\n]/g)) {
                    var nb_retour_chario = $el.val().match(/[\r\n]/g).length;
                } else {
                    var nb_retour_chario = 0;
                }
                var nbCaractere = $el.val().length - nb_retour_chario;
                nb_retour_chario += Math.ceil(nbCaractere / $el.attr('cols'));
                $el.attr('rows', nb_retour_chario);
            } else {

                // input
                // auto expand witdh
                var valWidth = ($(this).textWidth() + padding);
                //if (valWidth<100) valWidth=100;
                $el.css('width', valWidth + 'px');
                //
                //console.log(valWidth);
            }
        }).trigger('input');

    }


};

//console.log('ub_usadmin.js')

//fm_ajax.init();


// auto exapnde textarea & input
// add class .fm-input_auto_expand
//
//fm_ajax.input_auto_expand();


// Quota
// nov 2017
//
// var result = quota.count( 'img', 1, 100 ); // type | +1 ou -1 | poids
// console.log( result )
// click > if ( quota.count( 'img', 1, 100 ) ) return
// le poids n'est pas utilise car au final il ne sera jamais supérieur à 500x2Mo soit 1Go
//


// ptf & rub utilise le même quota (12 ou 24)
//
var quota = {

    el: {

        // porftolio
        //
        img: {
            nb: 0,             // nb
            poids: 0,           	// poids
            max_nb: 24,            // max nb
            max_poids: 12000,          // max poids
            div_count: '.nb_img',      // dom count
            div_btn: '#btn_add_one_image, #ub_drop_zone'  // dom bouton
        },

        ptf: {
            nb: 0,
            poids: false,
            max_nb: 3,
            div_count: '#nb_ptf',
            div_btn: '#btn_add_portfolio'
        },

        // news
        //
        page: {
            nb: 0,             // nb
            poids: false,           // poids
            max_nb: 24,            // max nb
            div_count: '#count_pag',      // dom count
            div_btn: '#btn_add_page'  // dom bouton
        },

        rub: {
            nb: 0,
            poids: false,
            max_nb: 6,
            div_count: '#nb_rub',
            div_btn: '#btn_add_rubrique'
        }

    },

    init: function () {

        $quota_init = $('#quota_init');

        // news
        //

        // rub
        this.el.rub.max_nb = ub_gal.txt_gal_nb_quota;
        this.el.rub.nb = ub_gal.txt_gal_nb;

        this.count('rub', 0, 0);
        this.count('page', 0, 0);

        // page
        this.el.page.nb = $quota_init.data('page_nb');
        this.el.page.max_nb = $quota_init.data('page_nb_max');

        // portfolio
        //

        // ptf
        this.el.ptf.max_nb = ub_gal.txt_gal_nb_quota;
        this.el.ptf.nb = ub_gal.txt_gal_nb;

        // img
        this.el.img.nb = $quota_init.data('img_nb') - 1;
        this.el.img.max_nb = $quota_init.data('img_nb_max');
        this.el.img.poids = $quota_init.data('img_poids');
        this.el.img.poids_max = $quota_init.data('img_poids_max');

        this.count('img', 0, 0);
        this.count('ptf', 0, 0);


    },

    count: function (type, val, poids) {

        // console.log(type+' '+$('#gal_base ul.gal_n0').length);

        // compte uniqueemnt pour la rub accueil
        if (type_action == '_projet__accueil' && type == 'rub') {
            if ($('#gal_base ul.gal_n0').length > (0 - val))
                $('#btn_add_rubrique').hide()
            else
                $('#btn_add_rubrique').show();
            return true;
        }


        this.type = type;
        this.poids = poids;

        this.nb = quota.el[this.type].nb + val;
        if (this.nb < 0) this.nb = 0;
        this.max_nb = quota.el[this.type].max_nb;
        this.div_count = quota.el[this.type].div_count;
        this.div_btn = quota.el[this.type].div_btn;

        if (poids) {
            this.poids = quota.el[this.type].poids + poids;
            if (this.poids < 0) this.poids = 0;
        }

        // aff quota
        //console.log( this.type+ ' => quota max > '+this.max_nb+'  quota > '+this.nb+'  poids total> '+this.poids+'  poids file > '+poids );

        // test quota depasse
        if (this.nb > this.max_nb) {

            // aff quota full
            $(this.div_btn).addClass('disable');
            if (this.type == 'img') $('#quota_full_msg').addClass('show');
            $(this.div_count).animate({fontSize: '24px'}, "slow", function () {
                $(this).delay(1500).animate({fontSize: '9px'}, "slow");
            });


            // aff
            $(this.div_count).text((this.nb - 1) + '/' + this.max_nb);
            // save for negatif val
            if (val < 0) quota.el[this.type].nb = this.nb;
            return false;
        }

        // Maj count val + aff
        if (val < 0) {
            $('#quota_full_msg').removeClass('show');
            $(this.div_btn).removeClass('disable');
        }

        // derniere image
        if (this.nb == this.max_nb) {
            // aff quota full
            $(this.div_btn).addClass('disable');
            if (this.type == 'img') $('#quota_full_msg').addClass('show');
            $(this.div_count).animate({fontSize: '24px'}, "slow", function () {
                $(this).delay(1500).animate({fontSize: '9px'}, "slow");
            });
        }

        // aff
        $(this.div_count).text(this.nb + '/' + this.max_nb);
        quota.el[this.type].nb = this.nb;

        return true;

    }
};


// gestionnaire de page+ptf
//
var app_redactor;

var ub_gal = {

    name: 'ub_gal'

    /*
         ,txt_Modifier:          'Modifier'
         ,txt_Valider:           'valider'
         ,txt_Annuler:           'Annuler'
         ,txt_Voir:              'Voir'
         ,txt_nouvelle_image:    'Nouvelle image'
         ,txt_nouvelle_gal:      'Nouveau portfolio'
         ,txt_nouvelle_gal2:     'Nouvelle rubrique'
         ,txt_eff_img_ok:        ''
         ,txt_eff_img:           'Supprimer l’image'
         ,txt_gall_add_ok:		  ''
         ,txt_gall_del_ok:		  ''
         ,txt_gall_edit_ok:	  ''

         ,txt_nouvelle_page:     		'Nouvelle page'

         ,txt_quotadepasse:     		'Quota d’image dépassé'
         ,txt_quotadepasse_rub:   		'Quota de création de rubrique dépassé'
         ,txt_abrev_gal : 				'Ptf.'
         ,txt_abrev_gal2 : 			'Rub.'

         ,txt_gal_ajouterunerubrique:	'Ajouter une rubrique'
         ,txt_gal_ajouterunegalerie:	'Ajouter un portfolio'
         ,txt_gal_ajouteruneimage: 	'Ajouter une image'
         ,txt_gal_ajouterdesimages: 	'Ajouter des images'
         ,txt_gal_ajouterunepage:		'Ajouter une page'
         ,txt_gal_fichiertroplourd:	'Fichier trop lourd.'
         ,txt_gal_minimum:				'Vous devez créer au moins un portfolio...'

         ,txt_img_edit_ok:				''
         ,txt_miseajour_ok:			''
         ,txt_enregistrement_ok:		''
         ,txt_size_nb:					'Le nombre d’images et le poids total correspond à la totalité des images présentes sur votre book - Vous pouvez modifier ce nombre à partir du menu \"Ma formule\" '

         ,txt_notification_ie:			'L’administration des books n’est pas optimisé pour Internet Explorer - Vous devriez plutôt utiliser Firefox, Safari ou Chrome'

         // nov 2017
         ,txt_gal_supp:				'Supprimer la galerie'

         */


    , url_gal_action: '/front/ajax_2011_usadmin.php'


    , tipsy_conf: {
        gravity: 's',
        offset: 4,
        title: 'title',
        fade: 1,
        opacity: 0.99
    }

    , init: function () {

        //var type_action = 		'_projet__portfolio';
        //console.log('type_action> '+type_action)

        // quota 2017nov
        //
        quota.init();


        this_ub_gal = this;


        // supp cursor effect
        //
        $('#cursor_follower').hide();
        $('html').css('cursor', 'unset');



        // gal =modif 2014
        //

        $("li.gal_box").each(function () {


            // gal openclose
            this_ub_gal.gal_openclose($(this));

            // pour le quota
            ub_gal.init_nb_gal++;


            // gal titre edit
            var $ub_gal_titre = $(this).children('.ub_gal_titre');
            this_ub_gal.gal_titre_edit($ub_gal_titre);


            // gal eff
            var $gal_del = $(this).children(".gal_del");
            this_ub_gal.gal_eff($gal_del);

            // gal coul
            var $coul = $(this).children(".gal_coul").children("input.colorSelector");
            this_ub_gal.gal_coul($coul);


            // img
            // img openclose
            var $detail_imgpage = $(this).find("ul.gal_n1 li");
            this_ub_gal.gal_img_openclose($detail_imgpage);   // img openclose

            // img eff top
            var $gal_img_del = $detail_imgpage.find(".gal_img_del_top");
            this_ub_gal.gal_img_eff_top($gal_img_del);


        });


        // img =modif 2014
        //

        $(".filename_detail").each(function () {

            if (type_action == '_projet__portfolio') {

                // img edit
                var $ub_img_edit = $(this).find(".ub_img_edit");
                this_ub_gal.gal_img_edit($ub_img_edit);

                // img upload
                var $img_file_ajax2 = $(this).find(".img_file_ajax2");
                this_ub_gal.gal_img_upload($img_file_ajax2);

            } else {

                //console.log('ok');

                // page titre edit
                var $ub_img_edit = $(this).find(".ub_img_edit");
                this_ub_gal.gal_img_edit($ub_img_edit);

                // page
                var $ub_page_edithtml = $(this).find(".ub_page_edithtml");
                //this_ub_gal.gal_page_list_init($ub_page_edithtml);         // img edit

                this_ub_gal.gal_page_list_init_redactor($ub_page_edithtml);         // img edit


                // quota 2017nov
                //
                quota.count('page', 1, 0);
            }

        });


        // bouton ajout gal
        // ptf / rub
        //

        this.gal_ajout_init();


        // portfolio page
        //
        if (type_action == '_projet__portfolio') {
            if ((typeof navigator.vendor != 'undefined') && navigator.vendor.toLowerCase().indexOf('apple_____desac') == -1) {
                // ne fonctionne pas sur Safari
                this.gal_img_multiajout_init(); 	// bouton ajout img multi
            } else {
                this.gal_img_ajout_init();  		// bouton ajout img
            }
        }

        // news page || accueil
        //


        if (type_action == '_projet__news' || type_action == '_projet__accueil') {
            // bouton ajout page
            this.gal_page_ajout_init();
        }


        $('#gal_base').fadeTo('slow', 200);	// aff contenu


        ub_gal.sort_galn0();

        ub_gal.sort_galn1();


        // infobulle
        $(".infobulles").tipsy(ub_gal.tipsy_conf);


        // intermediate
        // ub_gal.intermediate_init();


        // navigateur  IE
        //
        if ($.browser.msie && !$.cookie('ub_notification_1')) {
            $.jGrowl(ub_gal.txt_notification_ie, {life: 12000, header: 'Notification'});
            $.cookie('ub_notification_1', true, {expires: 3600});
        }

        // show_minibook_seleted init
        //
        ub_gal.show_minibook_seleted();


    }





    // show mini book select 2020
    //
    , show_minibook_seleted: function () {


        if (type_action != '_projet__portfolio') return;

        var icon = '<i class="clone outline icon "></i>';
        var nb_show = (user_formule) ? 24 : 4;


        $('.gal_n0 .filename').each(function (i) {

            $(this).find('.clone.outline.icon').remove();

            if (i < nb_show) {
                $(this).prepend(icon)
                    .find('.clone.outline.icon').popup({
                    content: __.__['Visuel affiché sur le mini-book'],
                    position: 'top left',
                    distanceAway: 14
                });
            }
        });


    }




    // when click on image
    // intermediate check
    // intermediate rules

    , intermediate_init_onclick: function ($img_id) {

        // buy
        $img_id.find('.intermediate_action_buy').each(function () {
            var $intermediate_buy = $(this).parent().next();

            $(this).checkbox({
                onChecked: function () {
                    $intermediate_buy.transition('fade in');
                    ub_gal.intermediate_Unchecked_buy($intermediate_buy);
                },
                onUnchecked: function () {
                    $intermediate_buy.transition('fade out')
                    ub_gal.intermediate_Unchecked_buy($intermediate_buy);
                }
            });

        });


        // sim
        $img_id.find('.intermediate_action_sim').each(function () {
            var $intermediate_sim = $(this).parent().next();

            $(this).checkbox({
                onChecked: function () {
                    $intermediate_sim.transition('fade in');
                    ub_gal.intermediate_Unchecked_sim($intermediate_sim);
                },
                onUnchecked: function () {
                    $intermediate_sim.transition('fade out');
                    ub_gal.intermediate_Unchecked_sim($intermediate_sim);
                }
            });

        });


        // rules
        ub_gal.rules_intermediate($img_id);


    }


    //re-valided for show error msg

    , intermediate_form_revalidate: function ($el) {

        // validate
        $el.find('.intermediate_sim').form('validate field', 'img_offre_similaire_min') //re-valided for show error msg
        // , 'img_offre_similaire_max'
        $el.find('.intermediate_buy').form('validate field', 'img_offre_vente') //re-valided for show error msg


    }


    // dispatch & show when click

    , intermediate_buy_dispatch: function ($img_id) {

        var img_offre_vente = $img_id.find('input[name="img_offre_vente"]').val();
        //console.log(img_offre_vente);

        //if (img_offre_vente=='') return false;

        if (img_offre_vente === undefined || img_offre_vente == '') return false;

        var img_offre_vente_obj = JSON.parse(img_offre_vente);
        //$img_id.find('input[name="img_offre_vente"]').val(      img_offre_vente_obj.buy );
        $img_id.find('textarea[name="img_desc_vente"]').val(img_offre_vente_obj.buy_desc.replace(/(\\n)/g, "\n").replace(/(\\t)/g, ""));

        //alert('ok')

        if (img_offre_vente_obj.buy_actif) {
            $img_id.find('.intermediate_action_buy').trigger('click');
        }


    }

    // dispatch & show when click

    , intermediate_sim_dispatch: function ($img_id) {

        var img_offre_similaire = $img_id.find('input[name="img_offre_similaire"]').val();

        if (img_offre_similaire === undefined || img_offre_similaire == '') return false;

        var img_offre_similaire_obj = JSON.parse(img_offre_similaire);
        //$img_id.find('input[name="img_offre_similaire_max"]').val(      img_offre_similaire_obj.sim_max );
        //$img_id.find('input[name="img_offre_similaire_min"]').val(      img_offre_similaire_obj.sim_min );
        $img_id.find('textarea[name="img_desc_sim"]').val(img_offre_similaire_obj.sim_desc.replace(/(\\n)/g, "\n").replace(/(\\t)/g, ""));

        //console.log(img_offre_similaire_obj);

        if (img_offre_similaire_obj.sim_actif) {
            $img_id.find('.intermediate_action_sim').trigger('click'); // open if checkbox is true
        }

    }


    // validation for ajax send

    , intermediate_buy_valided: function (ref) {

        var $el = $('#' + ref);

        //console.log($el.find('.intermediate_buy').form('is valid'));
        if (!$el.find('.intermediate_buy').form('is valid')) return false;

        //var img_offre_vente =  	$el.find('input[name="img_offre_vente"]').val();
        var img_desc_vente = $el.find('textarea[name="img_desc_vente"]').val();
        img_desc_vente = img_desc_vente.replace(/['"]+/g, '');

        var buy_actif = ($el.find('.intermediate_action_buy ').hasClass('checked') ? true : false);

        var form_validate_intermediate_buy = {
            buy_actif: buy_actif,
            //buy : 			img_offre_vente,
            buy_desc: img_desc_vente
        };

        var form_validate_intermediate_buy_ = JSON.stringify(form_validate_intermediate_buy);

        //console.log( ref+ ' - '+img_offre_vente+ ' - '+img_desc_vente);
        //console.log( form_validate_intermediate_buy_ );

        return form_validate_intermediate_buy_;
    }


    // validation for ajax send

    , intermediate_sim_valided: function (ref) {

        var $el = $('#' + ref);
        //console.log($el.find('.intermediate_sim').form('is valid'));
        if (!$el.find('.intermediate_sim').form('is valid')) return false;

        //var img_offre_similaire_min =  	$el.find('input[name="img_offre_similaire_min"]').val();
        //var img_offre_similaire_max =  	$el.find('input[name="img_offre_similaire_max"]').val();
        var img_desc_sim = $el.find('textarea[name="img_desc_sim"]').val();
        img_desc_sim = img_desc_sim.replace(/['"]+/g, '');

        var sim_actif = ($el.find('.intermediate_action_sim ').hasClass('checked') ? true : false);

        var form_validate_intermediate_sim = {
            sim_actif: sim_actif,
            //sim_min: 	img_offre_similaire_min,
            //sim_max: 	img_offre_similaire_max,
            sim_desc: img_desc_sim,
        }

        var form_validate_intermediate_sim_ = JSON.stringify(form_validate_intermediate_sim);

        //console.log( form_validate_intermediate_sim_ );

        return form_validate_intermediate_sim_;
    }


    // Unchecked sim

    , intermediate_Unchecked_sim: function ($el) {
        //console.log('Unchecked_sim');
        // trigger .save
        // alert('ok')
        //$el.find('input[name="img_offre_similaire_min"]').trigger('dblclick');

        //console.log( $el.find('textarea[name="img_desc_sim"]').css('border','1px solid red') )

        $el.find('textarea[name="img_desc_sim"]').trigger('dblclick'); // go to fn gal_img_edit()

    }

    // Unchecked buy

    , intermediate_Unchecked_buy: function ($el) {


        // trigger .save
        //console.log('Unchecked_sim');
        //console.log( $el.find('textarea[name="img_desc_vente"]').val() );


        //$el.find('input[name="img_offre_vente"]').trigger('dblclick');
        $el.find('textarea[name="img_desc_vente"]').trigger('dblclick');  // go to fn gal_img_edit()
    }



    // intermediate rules

    , rules_intermediate: function ($el) {

        /*
		$.fn.form.settings.rules.sim_min_max = function(value_min) {
			//console.log(   '#####'   )
			//console.log(    )
			var sim_max = Number($(this.context).find('input[name="img_offre_similaire_max"]' ).val() );
			var sim_min = Number($(this.context).find('input[name="img_offre_similaire_min"]' ).val() );
			//console.log(  sim_max+' <= '+sim_min  )

			if ( sim_max=='' || sim_min=='' ) return true;
			if ( sim_min > sim_max ) return false;
			return true;

		};
		*/


        // validate
        $el.find('.intermediate_sim')
            .form({
                inline: true,
                on: 'change',
                //debug:      true,
                delay: true,
                duration: 200,

                fields: {
                    /*
					img_offre_similaire_min: {
						identifier: 'img_offre_similaire_min',
						rules: [
							{
								type   : 'regExp[/^[0-9]{0,5}$/]',
								prompt : __.__['Caractéres incorrecte']
							},
							{
								type   : 'sim_min_max',
								prompt : __.__['Le montant maximum est inférieur au montant minimum']

							}
						]
					},
					img_offre_similaire_max: {
						identifier: 'img_offre_similaire_max',
						rules: [
							{
								type   : 'regExp[/^[0-9]{0,5}$/]',
								prompt : __.__['Caractéres incorrecte']
							},
							{
								type   : 'sim_min_max',
								prompt : __.__['Le montant maximum est inférieur au montant minimum']

							}
						]
					}
					*/
                    img_desc_sim: {
                        identifier: 'img_desc_sim',
                        rules: [
                            {
                                type: 'maxLength[300]',
                                prompt: __.__['Caractéres incorrecte']
                            },
                        ]
                    }
                }

            })


        $el.find('.intermediate_buy')
            .form({
                inline: true,
                on: 'change',
                //debug:      true,
                delay: true,
                duration: 100,

                fields: {
                    img_desc_vente: {
                        identifier: 'img_desc_vente',
                        rules: [
                            {
                                type: 'maxLength[300]',
                                prompt: __.__['Caractéres incorrecte']
                            },
                        ]
                    }
                    /*
					img_offre_vente: {
						identifier: 'img_offre_vente',
						rules: [
							{
								type   : 'regExp[/^[0-9]{0,5}$/]',
								prompt : __.__['Caractéres incorrecte']
							},
						]
					},*/
                }

            })


    }















    //
    // sort
    //
    //

    , sort_galn0: function () {
        // sotable
        //
        // n0
        //
        $("#gal_base").sortable({
            connectWith: '.gal_n0',
            handle: '.gal_move',
            opacity: 0.4,
            update: function () {
                //var order = $('.gal_n0').serializelist();
                //var order = $("#gal_base").sortable( "serialize", "" );
                //var order = $("#gal_base").serializelist({attributes:['id'], is_child: true});

                var order = $('ul.gal_n0').serializelist_ub2({attributes: ['id'], child: false});
                //$("#info2").html("--->" + order['rub_ord']);
                //$('#info1').html(order);

                $('#gal_msg_waite').show();

                url = ub_gal.url_gal_action;

                /* ajax */
                var req = $.post(
                    url,
                    {
                        action: 'rub_ord'
                        , gal_ord: order['rub_ord']
                        , erreur: 'false'
                    },
                    true,
                    "json"
                );

                /* ajax error */
                req.error(function (data, msg) {
                    this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
                });

                /* ajax ok */
                req.success(function (data, msg) {
                    //alert('ok2 success '+data);
                    id = data.img_id;
                    // data ok
                    this_ub_gal.gal_msg_info(ub_gal.txt_miseajour_ok, 'green');  // + ' (id:'+id+')'

                });

                ub_gal.show_minibook_seleted();
                //alert('ok 2');
            }
        });

    }

    /* sort dasn les rub */

    , sort_galn1: function () {
        //
        // n1
        //

        var sort = $("ul.gal_n1").sortable({

            connectWith: 'ul.gal_n1',
            handle: '.gal_move_img',
            opacity: 0.5,


            start: function (event, ui) {
                sort.tmp = true;
            },

            // intra rub
            stop: function (event, ui) {
                if (sort.tmp) {
                    //alert ("stop "+ sort.tmp + '  '+$(ui.sender).parent().parent().find('h3').attr("id"));

                    var rub_drag_id = $(ui.item).parent().parent().find('h3.ub_gal_titre').attr("id");
                    var rub_drag_id_start = $(ui.sender).parent().parent().find('h3.ub_gal_titre').attr("id");
                    var order2 = $('ul.gal_n1').serializelist_ub2({
                        attributes: ['id'],
                        child: true,
                        rub_a: rub_drag_id,
                        rub_b: rub_drag_id_start
                    });


                    //console.log('intra rub     ');
                    //console.log('***  ' + order2[1]['id']  + ' ::: ' + order2[1]['val'] );


                    url = ub_gal.url_gal_action;
                    $('#gal_msg_waite').show();
                    // ajax
                    var req = $.post(
                        url,
                        {
                            action: 'img_ord'
                            , rub_a: order2[1]['id']
                            , rub_b: false
                            , img_ord_a: order2[1]['val']
                            , img_ord_b: false
                            , img_drag_id: false
                            , img_drag_rubid: false
                            , erreur: 'false'
                        },
                        true,
                        "json"
                    );

                    /* ajax ok */
                    req.success(function (data, msg) {
                        //alert('ok2 success '+data);
                        id = data.img_id;
                        // data ok
                        this_ub_gal.gal_msg_info(ub_gal.txt_miseajour_ok, 'green');  // + ' (id:'+id+')'


                    });

                    ub_gal.show_minibook_seleted();
                    //alert('ok 3');

                }
                sort.tmp = true;


            },


            // inter rub
            receive: function (event, ui) {
                sort.tmp = false;

                var img_drag_id = $(ui.item).attr("id");
                var rub_drag_id = $(ui.item).parent().parent().find('h3.ub_gal_titre').attr("id");
                var rub_drag_id_start = $(ui.sender).parent().parent().find('h3.ub_gal_titre').attr("id");

                var order2 = $('ul.gal_n1').serializelist_ub2({
                    attributes: ['id'],
                    child: true,
                    rub_a: rub_drag_id,
                    rub_b: rub_drag_id_start
                });

                /*
                 console.log(  img_drag_id + ' --> newRUB ' + rub_drag_id + ' _________ oldRUB:' + rub_drag_id_start );
                 console.log('inter rub     ' +img_drag_id +' idrub='+ order2[2]['id']);
                 console.log('***  ' + order2[1]['id']  + ' ::: ' + order2[1]['val'] );
                 console.log('***  ' + order2[2]['id']  + ' ::: ' + order2[2]['val'] );
				*/


                //alert( "receive " + $(ui.item).attr("id") + '-- ' +sort.tmp  )
                $('#gal_msg_waite').show();
                url = ub_gal.url_gal_action;

                // ajax
                var req = $.post(
                    url,
                    {
                        action: 'img_ord'
                        , rub_a: order2[1]['id']
                        , rub_b: order2[2]['id']
                        , img_ord_a: order2[1]['val']
                        , img_ord_b: order2[2]['val']
                        , img_drag_id: img_drag_id
                        , img_drag_rubid: rub_drag_id
                        , erreur: 'false'
                    },
                    true,
                    "json"
                );

                /* ajax ok */
                req.success(function (data, msg) {
                    //alert('ok2 success '+data);
                    id = data.img_id;
                    // data ok
                    this_ub_gal.gal_msg_info(ub_gal.txt_miseajour_ok, 'green'); //+ ' (id:'+id+')'
                });

            }

        });


    }




    //
    // gal
    //
    //

    /* gal couleur */

    , gal_coul: function ($el_) {

        var name_target = $el_.attr("name");
        var rel_target = $el_.attr("rel");

        $el_.miniColors({
            change: function (hex, rgb) {

                var gal_id = $(this).parents('ul').attr("id");
                //console.log('change coul'+hex+ ' == '+gal_id + ' == '+  ub_gal.url_gal_action);
                ub_gal.gal_coul_ajax(gal_id, hex);

            }
        });

    }


    /* gal couleur - suite ajax */

    , gal_coul_ajax: function (gal_id, coul) {

        $('#gal_msg_waite').show();

        url = ub_gal.url_gal_action;

        /* ajax */
        var req = $.post(
            url,
            {
                action: 'rub_mod'
                , erreur: 'false'
                , gal_id: gal_id
                , gal_champ: 'rub_coul'
                , gal_val: coul
            },
            true,
            "json"
        );

        /* ajax error */
        req.error(function (data, msg) {
            this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
        });

        /* ajax ok */
        req.success(function (data, msg) {
            // data ok
            this_ub_gal.gal_msg_info(ub_gal.txt_gall_edit_ok, 'green');  //+ 'id ' + gal_id.attr("id")
        });
    }




    /* gal titre edit */

    , gal_titre_edit: function (gal_id) {
        // alert(gal_id.attr("id"));


        gal_id.editableText({
            newlinesEnabled: false,
            changeEvent: 'dblclick',
            txt_valider: ub_gal.txt_Valider
        })
            .dblclick(function () {

                $('#gal_msg_waite').show();

                url = ub_gal.url_gal_action;

                /* ajax */
                var req = $.post(
                    url,
                    {
                        action: 'rub_mod'
                        , erreur: 'false'
                        , gal_id: gal_id.attr("id")
                        , gal_champ: 'rub_nom'
                        , gal_val: $(this).text()
                    },
                    true,
                    "json"
                );

                /* ajax error */
                req.error(function (data, msg) {
                    this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
                });

                /* ajax ok */
                req.success(function (data, msg) {
                    // data ok
                    this_ub_gal.gal_msg_info(ub_gal.txt_gall_edit_ok, 'green');  //+ 'id ' + gal_id.attr("id")
                });

            });

    }



    /* gal ouverture fermeture
	*
	* */

    , gal_openclose: function (gal_id) {

        gal_id.prepend('<span class="icon fonticon-uniF004 ub_gal_open"></span>');


        gal_id.find(".ub_gal_open").toggle(function () {
            $(this).removeClass("fonticon-uniF004").addClass("fonticon-uniF006");
            $(this).nextAll("ul.gal_n1").hide();

        }, function () {
            $(this).removeClass("fonticon-uniF006").addClass("fonticon-uniF004");
            $(this).nextAll("ul.gal_n1").show();
        });

    }





    /* img ouverture fermeture
	*
	* */

    , gal_img_openclose: function (img_id) {
        //console.log('img_id>'+img_id)

        img_id.find(".ub_gal_action_open").click(function () {

            //console.log('click')
            $img_id = $(this).parent();

            if (!$img_id.hasClass('open')) {

                $img_id.children('.ub_gal_img_open').removeClass("fonticon-uniF006").addClass("fonticon-uniF004");
                $img_id.addClass("open");

                // intermediate check
                // intermediate rules
                ub_gal.intermediate_init_onclick($img_id);

                // intermediate dispatch & show
                ub_gal.intermediate_buy_dispatch($img_id);
                ub_gal.intermediate_sim_dispatch($img_id);

                //re-valided when open
                // ub_gal.intermediate_form_revalidate($img_id);


            } else {
                $img_id.children('.ub_gal_img_open').removeClass("fonticon-uniF004").addClass("fonticon-uniF006");
                $img_id.removeClass("open");
            }

        });

    }













    /* gal ajout */

    , gal_ajout_init: function () {

        this_ub_gal = this;


        // #ub_gal_bouton_add,

        // add portfolio
        //
        $("#btn_add_portfolio, #btn_add_rubrique").click(function (event) {

            event.preventDefault();
            event.stopPropagation();

            url = ub_gal.url_gal_action;
            //console.log('add gal')

            // quota nov 2017
            //
            var el = (type_action == '_projet__portfolio') ? 'ptf' : 'rub';
            if (!quota.count(el, 1, 0)) {
                //console.log('quota atteint');
                return;
            }

            /* si valeur de ub_gal.txt_gal_ajouterunepage = false alors => gal_id_categorie=portfolio */
            if (ub_gal.txt_gal_ajouterunepage) {
                var tmp_txt_gal_new = ub_gal.txt_nouvelle_gal2;
            } else {
                var tmp_txt_gal_new = ub_gal.txt_nouvelle_gal;
            }

            /* ajax */
            var req = $.post(
                url,
                {
                    action: 'rub_add'
                    , erreur: 'false'
                    , gal_nom: tmp_txt_gal_new
                    , gal_id_parent: 1
                    , gal_id_categorie: ub_gal.gal_id_categorie
                },
                true,
                "json"
            );

            /* ajax error */
            req.error(function (data, msg) {
                this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
            });

            /* ajax ok */
            req.success(function (data, msg) {


                $('#txt_gal_minimum').fadeOut();

                //var id=Math.floor(Math.random()*101)+10;
                id = data.rub_id;


                var data = [
                    {ub_gal_id: id, ub_gal_titre: tmp_txt_gal_new},
                ];

                $("#ub_gal_tmpl").tmpl(data).prependTo("#gal_base");    // aff tmpl


                this_ub_gal.gal_openclose($("#" + id + " .gal_box"));         // openclose

                this_ub_gal.gal_titre_edit($("#" + id + " .ub_gal_titre"));   // titre edit

                this_ub_gal.gal_eff($("#" + id + " .gal_del"));               // eff

                // $("#"+id).sortable( "refresh" );                            // sortable

                //this_ub_gal.gal_deplier( $("#"+id+" .ub_page_bouton_deplier") );         // gal deplier
                //this_ub_gal.gal_replier( $("#"+id+" .ub_page_bouton_replier") );        // gal replier

                // sort dans la gal
                ub_gal.sort_galn1();

                // infobulle
                $("#" + id + " .infobulles").tipsy(ub_gal.tipsy_conf);

                // data ok
                this_ub_gal.gal_msg_info(ub_gal.txt_gall_add_ok);//+ ' (id:'+id+')'


            });


        });

    }



    /* gal eff */

    , gal_eff: function (gal_id) {

        //console.log(gal_id);

        gal_id.click(function (event) {

            event.preventDefault();
            event.stopPropagation();

            var ref_gal = $(this).parentsUntil('ul').parent();

            var $btn_supp = ref_gal.find('.gal_del');


            /* si on trouve des images ds la galerie #2018*/
            if (ref_gal.find('ul.gal_n1 > li').length > 0) {

                var popupaction_msg = $btn_supp.popup({
                    popup: $('.popup_gal_eff_msg.popup'),
                    position: 'top center',
                    on: 'click',
                    closable: false,
                    onShow: function () {
                        //var popup = this;
                        //console.log('ok');
                    }
                }).popup('show');

                $('.popup_gal_eff_msg.popup .cancel').click(function () {
                    popupaction_msg.popup('hide');
                });

                return false;
            }


            ref_gal.addClass('del_gal');
            $('#cboxOverlay').addClass('show_for_eff');


            // https://codepen.io/anon/pen/aOPRXK
            // 2018

            var popupaction = $btn_supp.popup({
                popup: $('.popup_gal_eff.popup'),
                position: 'top center',
                on: 'click',
                closable: false,
                onHide: function () {
                    $('#cboxOverlay').removeClass('show_for_eff');
                    ref_gal.removeClass('del_gal');
                }
            }).popup('show');

            $('.popup_gal_eff.popup .approve').unbind('click').click(function () {

                $('#cboxOverlay').removeClass('show_for_eff');
                ref_gal.removeClass('del_gal');

                url = ub_gal.url_gal_action;
                gal_id = ref_gal.attr('id');

                var req = $.post(
                    url,
                    {
                        action: 'rub_del'
                        , erreur: 'false'
                        , gal_id: gal_id
                    },
                    true,
                    "json"
                );

                req.error(function (data, msg) {
                    this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
                });

                req.success(function (data, msg) {
                    // data ok
                    this_ub_gal.gal_msg_info(ub_gal.txt_gall_del_ok);    // + ' (id:'+gal_id+')'
                    ref_gal.fadeOut("slow", function () { /* $(this).remove(); */
                    });   // eff

                    // quota nov 2017
                    //
                    var el = (type_action == '_projet__portfolio') ? 'ptf' : 'rub';
                    quota.count(el, -1, 0);
                });

            });

            $('.popup_gal_eff.popup .cancel').unbind('click').click(function () {
                popupaction.popup('hide').popup('destroy');
                $('#cboxOverlay').removeClass('show_for_eff');
                ref_gal.removeClass('del_gal');
            });

            return false;

        });


    }




    //
    // page with redactor dec 2020
    //
    //

    /* initialisation de l'editeur - redactor
      *
      * */
    , gal_page_list_init_redactor: function (img_id) {


        var infobulles_actived = [];
        var redactor_edit_to_save_id = '';

        var user_path_script = '/front/';
        var user_name_script = 'upload_json.json.php';
        //console.log (user_path_script+);


        //var user_path_image ='/users_2/a/d/adolie/';
        //var user_formule = true;
        //var user_formule = false;

        // add pat
        // in redactor.js


        app_redactor = $R('.redactor', {

            lang: 'fr',

            plugins: ['video', 'filemanager', 'imagemanager', 'fontfamily', 'fontcolor', 'fontsize', 'alignment',  'imageposition', 'textexpander', 'fullscreen','textia'],
            // 'fullscreen',



            textexpander: [
                ['lorem', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.']
            ],

            //limiter: 1000,
            fontfamily: ['Montserrat', 'HKGrotesk', 'Dosis', "\'Lato\'", 'Arial', 'Verdana', 'Times'],

            fileUpload: user_path_script + user_name_script + '?action=upload_file',
            fileManagerJson: user_path_script + user_name_script + '?action=list_file',
            fileManagerDele: user_path_script + user_name_script + '?action=deleted_file',

            imageUpload: user_path_script + user_name_script + '?action=upload',
            imageManagerJson: user_path_script + user_name_script + '?action=list',
            imageManagerDele: user_path_script + user_name_script + '?action=deleted',

            imageResizable: true,
            imagePosition: true,


            buttons: ['format', 'bold', 'alignment', 'fontfamily', 'fontcolor', 'fontsize', 'image', 'file', 'link', 'html','textia'],
            // 'ul', 'line', 'italic',

            clickToEdit: true,
            clickToSave: {title: 'Save', icon: '<i class="inverted white icon check"></i>'},
            clickToCancel: {title: 'Cancel', icon: '<i class="inverted white close icon"></i>'},




            toolbarFixedTopOffset: 40,
            toolbarContext: true,

            styles: false,


            showSource: true,
            breakline: true,

            cleanInlineOnEnter: true,


            callbacks: {

                start: function () {
                    console.log('started redactor');
                },



                blur: function (e) {
                     //console.log('blur');


                    //this.close()
                    // fn save

                    var html = $('#'+redactor_edit_to_save_id+' #img_html').html();

                    //console.log(html);
                    ub_gal.redactor_clickSave( html , redactor_edit_to_save_id);

                    $('.redactor_button_editor_bloc').fadeIn(20);

                    // personnalisation toolbar
                    // bt formule
                    infobulles_actived[redactor_edit_to_save_id] = false;
                    //console.log(infobulles_actived)

                },


                focus: function (e) {


                    $('.redactor-focus').prev('.redactor_button_editor_bloc').fadeOut(10);

                    //$('.redactor-focus').css('border','1px solid orange');


                    // id
                    var id = $('.redactor-focus').parentsUntil('li').parent().attr("id");
                    $('.redactor-focus').attr('id', id);


                    redactor_edit_to_save_id = id;


                    // personnalisation toolbar
                    // bt formule
                    if ( ! infobulles_actived[id] ) {


                                infobulles_actived[id] = true;
                                //console.log(infobulles_actived)


                               $redactor_focus_toolbar =  $('.redactor-focus .redactor-toolbar');


                                // version premium
                                //
                                if ( ! user_formule ) {

                                    var $btn_redactor_premium = $('<i>').attr('class', 'tiny icon lock ');

                                    $redactor_focus_toolbar.find('.re-video, .re-fontfamily, .re-fontcolor, .re-fontsize, .re-file')
                                        .addClass('btn_redactor_premium infobulles')
                                        .append($btn_redactor_premium);


                                    var $btn_redactor_premium_div = $('<div>')
                                        .addClass('redactor_btn_desactive infobulles')

                                    var tmp = ' - Réservé aux formules premium';


                                    // video
                                    var $btn_redactor_premium_div_video = $btn_redactor_premium_div
                                        .attr('original-title', 'Intégration vidéo' + tmp)
                                    $redactor_focus_toolbar.find(".re-video").wrap($btn_redactor_premium_div_video);


                                    // fontfamily
                                    var $btn_redactor_premium_div_fontfamily = $btn_redactor_premium_div
                                        .attr('original-title', 'Fonte choix' + tmp)
                                    $redactor_focus_toolbar.find(".re-fontfamily").wrap($btn_redactor_premium_div_fontfamily);


                                    // font color
                                    var $btn_redactor_premium_div_fontcolor = $btn_redactor_premium_div
                                        .attr('original-title', 'Couleur de texte' + tmp)
                                    $redactor_focus_toolbar.find(".re-fontcolor").wrap($btn_redactor_premium_div_fontcolor);


                                    // font size
                                    var $btn_redactor_premium_div_fontsize = $btn_redactor_premium_div
                                        .attr('original-title', 'Fonte taille' + tmp)
                                    $redactor_focus_toolbar.find(".re-fontsize").wrap($btn_redactor_premium_div_fontsize);


                                    // PDF
                                    var $btn_redactor_premium_div_pdf = $btn_redactor_premium_div
                                        .attr('original-title', 'Import PDF' + tmp)
                                    $redactor_focus_toolbar.find(".re-file").wrap($btn_redactor_premium_div_pdf);


                                    // PDF
                                    var $btn_redactor_premium_div_pdf = $btn_redactor_premium_div
                                        .attr('original-title', 'correction texte IA' + tmp)
                                    $redactor_focus_toolbar.find(".re-textia").wrap($btn_redactor_premium_div_pdf);



                                    $(".infobulles").tipsy(ub_gal.tipsy_conf);


                                } else {

                                    var $btn_redactor_premium = $('<i>').attr('class', 'tiny black icon lock open ');

                                    $redactor_focus_toolbar.find('.re-video, .re-fontfamily, .re-fontcolor, .re-fontsize, .re-file')
                                        .css('background-color', '#484848')
                                        //.css('padding-right', '5px')
                                        //.append($btn_redactor_premium);
                                    $redactor_focus_toolbar.find('.re-textia')
                                        .css('background-color', '#0aafab')
                                }




                            }



                },


                clickSave: function (html) {
                    //console.log('save', html);

                    //var id = $('.redactor-focus').attr("id");
                    //console.log(id + ' id art -> ' + ub_gal.url_gal_action);

                    //$R('#'+redactor_edit_to_save_id+' #img_html', 'plugin.fullscreen.close');
                    //app_redactor.api('plugin.fullscreen.toggle');
/*
                    if ( $('body').has('redactor-body-fullscreen') ) {
                        $R(' #img_html', 'plugin.fullscreen.toggle');
                        //app_redactor.api('plugin.fullscreen.toggle');
                    }
*/

                    // fn save
                    ub_gal.redactor_clickSave(html, redactor_edit_to_save_id);

                },


                click: function (e) {
                    //console.log('click');
                    //console.log(e)
                    //$('.redactor-toolbar').show();
                    //console.log( e.html() ); //css('border','1px solid red');
                    //css('border','1px solid red');

                },


                clickCancel: function (html) {
                    //console.log('cancel', html);

                },


            }

        });







    }


    // save content redactor
    //
    , redactor_clickSave: function (html, id) {

        //console.log('save', id);
        //console.log(html);


        /*
        // if fullscreen
        if ($('.redactor-body-fullscreen').length > 0) {
			$( ".redactor-body-fullscreen .re-fullscreen" ).trigger( "click" );
        }
        */


		// loader ON
        $('.redactor-focus')
            .append('<div class="ui active loader"></div>')
            .find('.redactor')
            .css('opacity', '0.2');


        var data_ = {
            action: 'img_mod_txt',
            erreur: false,
            img_id: id,
            img_champ: 'img_html',
            img_val: html
        };



        var req = $.ajax({
            url: ub_gal.url_gal_action,
            type: 'post',
            data: data_
        });

        /* ajax error */
        req.error(function (data, msg) {
            console.log('Erreur d’enregistrement...' + msg + data);
        });

        /* ajax ok */
        req.success(function (data, msg) {
            //console.log('ok')

            setTimeout(function () {

                // loader off
                $('.redactor')
                    .css('opacity', '1');

                $('.redactor-box .loader')
                    .transition('fade out', function () {
                        $(this).remove();
                    });

                /*
                if ($('.redactor-body-fullscreen').length > 0) {
                    //app_redactor.plugin.fullscreen.close();
                    $R('.redactor', 'plugin.fullscreen.close');
                }*/
                //$('.redactor_no_saved').removeClass('redactor_no_saved');

            }, 1200);


        });


    }


    , gal_page_ajout_init: function () {

        this_ub_gal = this;
        // .ub_page_bouton_add

        $("#btn_add_page").click(function (event) {

            event.preventDefault();
            event.stopPropagation();

            /* si on trouve au moins une galerie*/
            if ($('#gal_base > ul.gal_n0').length == 0) {
                $("#dialog:ui-dialog").dialog("destroy");
                $("#ub_gal_nonpresente_msg").dialog({
                    modal: true,
                    buttons: {
                        Ok: function () {
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            }

            // quota 2017nov
            //
            if (!quota.count('page', 1, 0)) {
                return false;
            }

            //var id = Math.floor(Math.random()*100001)+10;
            //id = "image_"+id;

            url = ub_gal.url_gal_action;

            // premiere gal parent
            gal_id_parent = $('#gal_base > ul.gal_n0').attr('id');

            /* ajax */
            var req = $.post(
                url,
                {
                    action: 'pag_add'
                    , img_nom: ub_gal.txt_nouvelle_page
                    , gal_id_parent: gal_id_parent
                    , erreur: 'false'
                },
                true,
                "json"
            );

            /* ajax error */
            req.error(function (data, msg) {
                this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
            });

            /* ajax ok */
            req.success(function (data, msg) {

                id = data.img_id;
                img_date_crea = data.img_date_crea;


                var data = [
                    {
                        ub_img_id: id,
                        ub_img_titre: ub_gal.txt_nouvelle_page,
                        ub_img_fichier: "default.jpg",
                        ub_img_type: "image/jpeg",
                        ub_img_date: img_date_crea
                    },
                ];

                var gal = "#gal_base ul.gal_n0:first-child li.gal_box ul.gal_n1";

                $("#ub_page_tmpl").tmpl(data).prependTo(gal);                // aff tmpl
                this_ub_gal.gal_msg_info(ub_gal.txt_enregistrement_effectue);

                this_ub_gal.gal_img_openclose($("li#" + id));             // openclose
                //this_ub_gal.gal_img_open( $("li#"+id) );                        // open
                this_ub_gal.gal_img_edit($("#" + id + " .ub_img_edit"));          // titre edit
                this_ub_gal.gal_img_eff_top($("#" + id + " .gal_img_del_top"));           // eff



                //console.log('a terminer');

                // page init redactor
                var $ub_page_edithtml = $("li#" + id + " div.ub_page_edithtml");


                //console.log("li#"+id+" .ub_page_edithtml")
                //$("li#"+id+" .redactor").append('<i className="redactor_button_editor_bloc bordered inverted black icon edit outline bordered_rounded"></i>');

                // edition button
                $('<i class="redactor_button_editor_bloc bordered inverted black icon edit outline bordered_rounded"></i>')
                    .css('display', 'block')
                    .insertBefore($ub_page_edithtml);


                this_ub_gal.gal_page_list_init_redactor($ub_page_edithtml);         // img edit


                // infobulle
                $("#" + id + " .infobulles").tipsy(ub_gal.tipsy_conf);


            });

        });

    }






    //
    // img
    //
    //


    /* img ajout multi
      *
      * */
    , gal_img_multiajout_init: function () {


        $("#ub_drop_zone").addClass('show');

        this_ub_gal = this;
        Dropzone.autoDiscover = false;

        // si on est bien sur la page portfolio
        if (ub_gal_options.gal_id_categorie != 2) return true;

        //console.log('ub_img_bouton_add_multi')

        //var var_action =        'img_upload';
        var url = ub_gal.url_gal_action;

        $("#ub_drop_zone").show();

        // init
        // http://www.dropzonejs.com/#listen_to_events
        //
        myDropzone = new Dropzone('#ub_drop_zone', {
            url: url,
            previewsContainer: "#ub_drop_zone_preview",
            acceptedFiles: "image/*,application/pdf,application/swf",
            maxThumbnailFilesize: 1,
            paramName: "qqfile",
            accept: function (file, done) {

                // quota 2017 nov
                if (!quota.count('img', 1, 0)) {
                    done("quota full");
                    //console.log("quota full");
                    //previewsContainer.removeFile(file);
                    return false;
                } else {
                    //console.log("quota ok");
                    done();
                }

            }

        });


        // event
        myDropzone.on("sending", function (file, xhr, formData) {

            /* si on trouve au moins une galerie*/
            if ($('#gal_base > ul.gal_n0').length == 0) {
                $("#dialog:ui-dialog").dialog("destroy");
                $("#ub_gal_nonpresente_msg").dialog({
                    modal: true,
                    buttons: {
                        Ok: function () {
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            }


            // premiere gal parent
            var gal_id_parent = $('#gal_base ul.gal_n0:first-child').attr('id');

            //console.log('gal_img_multiajout_init ===>'+gal_id_parent);
            //console.log(file);

            formData.append("action", "img_add_and_upload"); // Append all the additional input data of your form here!
            formData.append("gal_id_parent", gal_id_parent);
            formData.append("img_nom", file.name);
            // premiere gal parent
            //gal_id_parent = $('#gal_base > ul.gal_n0').attr('id');
            //console.log(gal_id_parent);

            $('#gal_msg_waite').show();

        });

        /*
			myDropzone.on("addedfile", function(file) {
			    console.log("Added file.");
			});
			*/


        myDropzone.on("success", function (file, response) {

            /*
			    {"erreur":false,"quota":null,"img_id":{"img_id":"639766","quota":false,"img_size_total":6263,"date":"03-09-2014 18:09:16","erreur":false,"uploadDirectory":"\/users_2\/p\/a\/patrice\/img_\/","add_and_upload":true}}			    */

            var response = jQuery.parseJSON(response);
            //console.log(response);

            if (response.erreur) {

                $('#ub_drop_zone')
                    .before('<div class="ub_drop_zone_erreur">' + ub_gal.txt_gal_fichiertroplourd + '</div>')
                    .prev().delay(800).fadeOut(800, function () {
                    $(this).remove();
                });

            } else {

                // $('#log').append("complete "+file.name+"<br/>");
                ub_gal.gal_img_multiajout_crea_blocimg(file, response);

                ub_gal.show_minibook_seleted();
                //alert('ok 2');
            }


        });

        myDropzone.on("complete", function (file) {
            myDropzone.removeFile(file);
            $('#gal_msg_waite').stop().fadeOut('slow');
        });

    }

    /* img ajout multi - suite 2014
      *
      * */
    , gal_img_multiajout_crea_blocimg: function (file, response) {

        //console.log(response);
        //console.log(file);

        // quota ok on aff l'img
        // size nb
        // this_ub_gal.gal_msg_size( false, 1);

        //alert('ok2 success '+data);
        id = response.img_id;

        var data = [
            {
                ub_img_id: id,
                ub_img_titre: file.name,

                ub_img_path: response.uploadDirectory,
                ub_img_fichier: response.img_file,
                ub_img_type: response.img_type,
                ub_img_size: response.img_size,
                ub_img_date: new Date()
            },
        ];

        // console.log(data);


        var gal = "#gal_base ul.gal_n0:first-child li.gal_box ul.gal_n1";

        $("#ub_img_tmpl").tmpl(data).prependTo(gal);                // aff tmpl

        // info
        this_ub_gal.gal_msg_info(ub_gal.txt_enregistrement_ok, 'green');

        this_ub_gal.gal_img_openclose($("li#" + id));             		// openclose
        //this_ub_gal.gal_img_open( $("li#"+id) );                        // open
        this_ub_gal.gal_img_edit($("#" + id + " .ub_img_edit"));          // titre edit
        this_ub_gal.gal_img_eff_top($("#" + id + " .gal_img_del_top"));   // eff
        this_ub_gal.gal_img_upload($("#" + id + " .img_file_ajax2"));     // img upload

        // infobulle
        $("#" + id + " .infobulles").tipsy(ub_gal.tipsy_conf);

        // poids
        //this_ub_gal.gal_msg_size (response.total_img_size,false);

        // quota 2017 nov
        quota.count('img', 0, response.total_img_size);


        // sortable
        var sort = $("ul.gal_n1").sortable({

            connectWith: 'ul.gal_n1',
            handle: '.gal_move_img',
            opacity: 0.5,


            start: function (event, ui) {
                sort.tmp = true;
            },

            // intra rub
            stop: function (event, ui) {
                if (sort.tmp) {
                    //alert ("stop "+ sort.tmp + '  '+$(ui.sender).parent().parent().find('h3').attr("id"));

                    var rub_drag_id = $(ui.item).parent().parent().find('h3.ub_gal_titre').attr("id");
                    var rub_drag_id_start = $(ui.sender).parent().parent().find('h3.ub_gal_titre').attr("id");
                    var order2 = $('ul.gal_n1').serializelist_ub2({
                        attributes: ['id'],
                        child: true,
                        rub_a: rub_drag_id,
                        rub_b: rub_drag_id_start
                    });

                    //console.log('<br/>***     ' + order2[1]['id']  + '::: ' + order2[1]['val'] );


                    url = ub_gal.url_gal_action;

                    // ajax
                    var req = $.post(
                        url,
                        {
                            action: 'img_ord'
                            , rub_a: order2[1]['id']
                            , rub_b: false
                            , img_ord_a: order2[1]['val']
                            , img_ord_b: false
                            , img_drag_id: false
                            , img_drag_rubid: false
                            , erreur: 'false'
                        },
                        true,
                        "json"
                    );

                    ub_gal.show_minibook_seleted();
                    //alert('ok 3');

                }
                sort.tmp = true;
            }


        });


        // intermediate check
        // intermediate rules
        ub_gal.intermediate_init_onclick($('#' + id));


    }


    /* img ajout - One
      *
      * */
    , gal_img_ajout_init: function () {

        this_ub_gal = this;

        $("#btn_add_one_image").addClass('show');

        $("#btn_add_one_image").click(function (event) {

            event.preventDefault();
            event.stopPropagation();

            /* si on trouve au moins une galerie*/
            if ($('#gal_base > ul.gal_n0').length == 0) {
                $("#dialog:ui-dialog").dialog("destroy");
                $("#ub_gal_nonpresente_msg").dialog({
                    modal: true,
                    buttons: {
                        Ok: function () {
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            }

            url = ub_gal.url_gal_action;

            // premiere gal parent
            gal_id_parent = $('#gal_base > ul.gal_n0').attr('id');
            //alert(gal_id_parent);

            $('#gal_msg_waite').show();

            /* ajax */
            var req = $.post(
                url,
                {
                    action: 'img_add'
                    , img_nom: ub_gal.txt_nouvelle_image
                    , gal_id_parent: gal_id_parent
                    , erreur: 'false'

                },
                true,
                "json"
            );

            /* ajax error */
            req.error(function (data, msg) {
                this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
            });

            /* ajax ok */
            req.success(function (data, msg) {

                //console.log('add > '+data.total_img_size)  ;

                if (!quota.count('img', 1, 0)) {

                    //console.log('add quota full');

                } else {

                    // quota ok on aff l'img
                    // size nb
                    //this_ub_gal.gal_msg_size( false, 1);
                    //quota = data.quota;

                    id = data.img_id;
                    img_date_crea = new Date(); //data.img_date_crea;

                    var data = [
                        {
                            ub_img_id: id,
                            ub_img_titre: ub_gal.txt_nouvelle_image,
                            ub_img_fichier: false,
                            ub_img_type: " ",
                            ub_img_size: "0",
                            ub_img_date: img_date_crea
                        },
                    ];

                    var gal = "#gal_base ul.gal_n0:first-child li.gal_box ul.gal_n1";

                    $("#ub_img_tmpl").tmpl(data).prependTo(gal);                // aff tmpl

                    // info
                    this_ub_gal.gal_msg_info(ub_gal.txt_enregistrement_ok, 'green');

                    this_ub_gal.gal_img_openclose($("li#" + id));             // openclose
                    //this_ub_gal.gal_img_open($("li#" + id));                        // open
                    this_ub_gal.gal_img_edit($("#" + id + " .ub_img_edit"));          // titre edit
                    this_ub_gal.gal_img_eff_top($("#" + id + " .gal_img_del_top"));           // eff
                    this_ub_gal.gal_img_upload($("#" + id + " .img_file_ajax2"));     // img upload

                    // infobulle
                    $("#" + id + " .infobulles").tipsy(ub_gal.tipsy_conf);


                    // sortable
                    var sort = $("ul.gal_n1").sortable({

                        connectWith: 'ul.gal_n1',
                        handle: '.gal_move_img',
                        opacity: 0.5,


                        start: function (event, ui) {
                            sort.tmp = true;
                        },

                        // intra rub
                        stop: function (event, ui) {
                            if (sort.tmp) {
                                //alert ("stop "+ sort.tmp + '  '+$(ui.sender).parent().parent().find('h3').attr("id"));

                                var rub_drag_id = $(ui.item).parent().parent().find('h3.ub_gal_titre').attr("id");
                                var rub_drag_id_start = $(ui.sender).parent().parent().find('h3.ub_gal_titre').attr("id");
                                var order2 = $('ul.gal_n1').serializelist_ub2({
                                    attributes: ['id'],
                                    child: true,
                                    rub_a: rub_drag_id,
                                    rub_b: rub_drag_id_start
                                });

                                //$("#info2").html('<br/>intra rub' );
                                //$("#info3").html('<br/>***     ' + order2[1]['id']  + '::: ' + order2[1]['val'] );


                                url = ub_gal.url_gal_action;

                                // ajax
                                var req = $.post(
                                    url,
                                    {
                                        action: 'img_ord'
                                        , rub_a: order2[1]['id']
                                        , rub_b: false
                                        , img_ord_a: order2[1]['val']
                                        , img_ord_b: false
                                        , img_drag_id: false
                                        , img_drag_rubid: false
                                        , erreur: 'false'
                                    },
                                    true,
                                    "json"
                                );


                            }
                            sort.tmp = true;
                        }


                    });
                    //}
                    //$('#msg_info').show().html('Enregistrement effectué...').effect("highlight", function() {$(this).delay(1000).fadeOut('slow');}, 4000);
                    //this_ub_gal.gal_msg_info('Enregistrement effectué... ');

                }

            });

        });

    }


    /* gal img eff all default image  - 2017
	 * \/ultra-book_default_1200x1200\.gif$
	  * */
    , gal_img_deleted_alldefault: function () {

        //console.log('gal_img_deleted_alldefault');

        $('.gal_img_min').each(function () {

            if ($(this).attr('src').match(/\/ultra-book_default_1200x1200.gif$/)) {

                //console.log($(this).attr('src'));

                var tmp_ref_img = $(this).parents('li').attr('id');

                $('#' + tmp_ref_img).addClass('del_img').fadeOut("slow", function () {
                    $(this).remove();
                });


            }

        });


    }




    /* gal img eff top - 2015 */

    , gal_img_eff_top: function (gal_id) {

        this_ub_gal = this;

        // click del
        //
        gal_id.click(function () {

            var ref_img = $(this).parent();
            var img_id = ref_img.attr('id');

            ref_img.addClass('del_img'); //css('border-color', 'red');

            var gal_id = ref_img.parents('ul.gal_n0').attr('id');

            var $btn_supp = ref_img.find('.gal_img_del_top');

            // 2018

            $('#cboxOverlay').addClass('show_for_eff');

            // popup del
            var popupaction_img = $btn_supp.popup({
                popup: $('.popup_img_eff.popup'),
                position: 'top center',
                on: 'click',
                closable: false,
                preserve: true,
                onHide: function () {
                    $('#cboxOverlay').removeClass('show_for_eff');
                    ref_img.removeClass('del_img');
                }
            }).popup('show');

            // approuver del
            $('.popup_img_eff.popup .approve').unbind("click").click(function () {


                popupaction_img.popup('hide');

                // move popup to #ub_book_bloc_base
                $(".popup_img_eff.popup").each(function () {
                    var item = $(this);
                    item.insertAfter('#ub_book_bloc_base');
                });


                $('#cboxOverlay').removeClass('show_for_eff');
                ref_img.removeClass('del_img');

                ref_img.fadeOut("slow", function () {

                    $(this).remove();

                    // deleted_alldefault
                    // console.log( ref_img );

                    var tmp_img_file = ref_img.find('.gal_img_nor').attr('src');
                    //console.log( tmp_img_file );
                    //var matches = tmp_img_file.match(/\/ultra-book_default_1200x1200\.gif$/);
                    //console.log( matches );
                    if (type_action == '_projet__portfolio') {
                        if (tmp_img_file.match(/\/ultra-book_default_1200x1200.gif$/)) {
                            ub_gal.gal_img_deleted_alldefault();
                        }
                    }

                    // reordination
                    ub_gal.gal_img_reordination(gal_id, img_id);

                });   // eff

                // ajax
                url = ub_gal.url_gal_action;
                $('#gal_msg_waite').show();

                var req = $.post(
                    url,
                    {
                        action: 'img_del',
                        img_id: img_id,
                        img_file: $('li #' + img_id).find('.descfile_name').text(),
                        erreur: 'false'
                    },
                    true,
                    "json"
                );

                // ajax error
                req.error(function (data, msg) {
                    this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
                });

                // ajax ok
                req.success(function (data, msg) {
                    // data
                    this_ub_gal.gal_msg_info(ub_gal.txt_eff_img_ok, 'green'); // + ' (id:'+img_id+')'

                    // size nb
                    if (type_action == '_projet__portfolio') {
                        if (!/^ultra-book_default_/.test($('li #' + img_id).find('.descfile_name').text())) {
                            //this_ub_gal.gal_msg_size( data.total_img_size, -1 );
                            quota.count('img', -1, data.total_img_size);
                            //console.log('supp' + data.total_img_size);
                        }

                    } else {
                        quota.count('page', -1, 0);
                    }


                });

            });

            // cancel del
            $('.popup_img_eff.popup .cancel').unbind("click").click(function () {
                popupaction_img.popup('hide');
                $('#cboxOverlay').removeClass('show_for_eff');
                ref_img.removeClass('del_img');
            });

            return false;

        });

    }







    /* gal img reordination */

    , gal_img_reordination: function (gal_id) {

        var order2 = $('ul.gal_n1').serializelist_ub2({attributes: ['id'], child: true, rub_a: gal_id, rub_b: ''});

        //console.log ('gal_img_reordination '+gal_id);
        //console.log(order2);

        url = ub_gal.url_gal_action;

        // ajax
        var req = $.post(
            url,
            {
                action: 'img_ord'
                , rub_a: order2[1]['id']
                , rub_b: false
                , img_ord_a: order2[1]['val']
                , img_ord_b: false
                , img_drag_id: false
                , img_drag_rubid: false
                , erreur: 'false'
            },
            true,
            "json"
        );


        ub_gal.show_minibook_seleted();
        //alert('ok 6');

    }





    //
    // data  erreur
    //
    //
    , gal_msg_erreur: function (msg) {

        $('#gal_msg_info_suite').css({'color': 'red'});

        $('#gal_msg_waite').stop().fadeOut('slow', function () {
            $('#gal_msg_error').stop().show().delay(800).fadeOut(1400);
            if (msg) $('#gal_msg_info_suite').show().html(msg).delay(8000).fadeOut();
        });

    }


    //
    // data  info
    //
    //
    , gal_msg_info: function (msg, coul) {

        //this.gal_msg_erreur(data);
        if (coul) $('#gal_msg_info, #gal_msg_info_suite').css({'color': coul});

        $('#gal_msg_waite').stop().fadeOut('slow', function () {
            $('#gal_msg_info').stop().show().delay(800).fadeOut(1400);
            if (msg) $('#gal_msg_info_suite').show().html(msg).delay(4000).fadeOut();
        });

    }





    /* img/page edit */

    // Create save buttons
    //$(".ub_img_edit").parentsUntil('li').parent().css('background-color', 'red');

    , gal_img_edit: function (img_id) {

        //alert('gal_img_edit');


        // editableText
        // -> js_jquery/js_2011/jquery.editableText.js


        img_id
            .editableText({
                newlinesEnabled: true,
                changeEvent: 'dblclick', //
                txt_valider: ub_gal.txt_Valider
            })

            //.one("submit",  function(e) {
            .dblclick(function (e) {
                e.preventDefault();  //prevent form from submitting

                var ref_img = $(this).parentsUntil('li').parent().attr("id");
                var newValue = $(this).val();
                var ub_champ = $(this).attr("name");


                //	alert ($(this).tagName()+' --- '+ub_champ+' --- Ref img '+ref_img+' --- '+newValue);
                //alert ('open');

                if (ub_champ == 'img_titre') {
                    $('li#' + ref_img + ' .filename').text(newValue); // second titre

                    // modif auto du titre alt
                    var img_option = false;
                    if ($(this).parentsUntil('li').find('#img_titre_alt').val() == ub_gal.txt_nouvelle_image) {
                        $(this).parentsUntil('li').find('#img_titre_alt').val(newValue);
                        var img_option = true;
                    }

                }


                //console.log('------------>dblclick');
                //console.log(ub_champ);


                // intermidiate sim- modify before send in ajax
                if (ub_champ.match(/img_offre_similaire_min|img_offre_similaire_max|img_desc_sim|img_desc_sim_actif/g)) {
                    newValue = ub_gal.intermediate_sim_valided(ref_img);
                    ub_champ = 'img_offre_similaire';

                    if (!newValue) return false; // form in-valid

                }


                // intermidiate buy - modify before send in ajax
                if (ub_champ.match(/img_offre_vente|img_desc_vente/g)) {
                    newValue = ub_gal.intermediate_buy_valided(ref_img);
                    ub_champ = 'img_offre_vente';

                    if (!newValue) return false; // form in-valid
                }


                var url = ub_gal.url_gal_action;

                $('#gal_msg_waite').show();

                /* ajax */
                var req = $.post(
                    url,
                    {
                        action: 'img_mod_txt'
                        , erreur: 'false'
                        , img_id: ref_img
                        , img_champ: ub_champ
                        , img_val: newValue
                        , img_option: img_option
                    },
                    true,
                    "json"
                );


                /* ajax error */
                req.error(function (data, msg) {
                    this_ub_gal.gal_msg_erreur('Erreur d’enregistrement...' + msg + data);
                });

                /* ajax ok */
                req.success(function (data, msg) {
                    //console.log('okokokook')
                    // data ok
                    this_ub_gal.gal_msg_info(ub_gal.txt_img_edit_ok, 'green'); // + 'id ' + ref_img
                });


                //return false;
            });
    }


    //$(this).css("backgroundColor", "red");


    /* img upload */

    , gal_img_upload: function (img_id) {


        // crea de $('.img_file_ajax')
        // voir edit
        //
        img_id.hover(function () {
                $(this).find('.bouton_modif').show();
                $(this).find('.bouton_zoom').show();
                $(this).find('img').css({opacity: 0.5});
            },
            function () {
                $(this).find('.bouton_modif').hide();
                $(this).find('.bouton_zoom').hide();
                $(this).find('img').css({opacity: 0.99});
            }
        );


        // bouton
        //var obj_id  = img_id.attr('rel');
        var ref_img = img_id.parentsUntil('li').parent().attr("id");
        var obj_id = ref_img;


        // adaptation du bloc a la taille de l'img
        if (img_id.find('img').width() > 0) {
            img_id.css({
                'width': img_id.find('img').width(),
                'height': 'auto' /*img_id.find('img').height()*/
            });
        }


        // bt upload
        $("<div/>", {
                'class': 'drop-zone bouton_drop-zone',
                'id': ref_img,
                'text': 'Upload'
            }
        ).appendTo(img_id);


        /*bt modifier*/
        $("<div/>", {
                'class': 'bouton_modif',
                'html': ub_gal.txt_Modifier,
                'click': function () {

                    var drop_zone_bloc = $('#' + ref_img + ' .drop-zone');
                    var var_action = 'img_upload';

                    // click bouton annuler
                    if ($(this).html() == ub_gal.txt_Annuler) {
                        $(this).html(ub_gal.txt_Modifier);
                        drop_zone_bloc.hide();
                        return false;
                    }

                    // transfore bouton en annuler
                    $(this).html(ub_gal.txt_Annuler);

                    // upload

                    drop_zone_bloc.show();
                    //var tmp =         obj_id.split("-");
                    //var var_action =  tmp[0];
                    //var var_id =      tmp[1];


                    // upload js
                    //
                    //var img_titre = 	$(this).parentsUntil('.filename').find('.filename').html();
                    var img_titre = $(this).parent().parent().parent().find('.filename').text();
                    var url = ub_gal.url_gal_action;

                    createUploader2(drop_zone_bloc, url, var_action, img_titre, ref_img, $(this));

                }
            }
        ).appendTo(img_id);


        /*bt zoom*/
        $("<div/>", {
                'class': 'bouton_zoom',
                'html': ub_gal.txt_Voir,
                'click': function () {
                    var img = $('li#' + ref_img).find('img.gal_img_min').attr('alt') + '?' + new Date() * Math.random();


                    // envoyer le zoom  flash ou image
                    if ($('li#' + ref_img).find('span.descfile_type').text() == 'flash/swf') {
                        /*
							// flash
						var html = '<div id="fbvideo"></div>';
						$.facebox(html);
						$('#fbvideo').flash({
							swf: img,
							width: 800,
							height: 580
							});
					*/
                    } else {
                        // img
                        /*
						$.facebox({
						  image:img
						  });
						*/
                        ub_fn.UI_Lightbox_create(img);
                    }
                }


            }
        ).appendTo(img_id);


    }


    , show: function (id) {
        $('#info2').html('AAAAA' + id);
        this.effacer(id, 14);

    } // eo show()


};


// create upload
//
//

var createUploader2 = function (ele, url, action, img_titre, img_id, bouton_modif) {


    var el = $(ele)[0];

    //alert(el+' - '+img_id);


    var img_small = $('li #' + img_id).find('img.gal_img_min'); //.css("border", "1px solid red");
    var img_norm = $('li #' + img_id).find('img.gal_img_nor'); //.css("border", "1px solid green");
    var bouton_dropzone = $('li #' + img_id).find('.drop-zone'); //.css("border", "1px solid pink");


    var uploader = new qq.FileUploader({
            element: el,
            multiple: false,
            action: url,
            params: {
                img_titre: img_titre,
                img_id: img_id,
                action: action
            },


            txt_drag_drop: ub_gal.txt_drag_drop,
            txt_Telecharger: ub_gal_options.Telecharger,


            onEnter: function () {
                var bouton_dropzone = $('li #' + img_id).find('.drop-zone');
                $bouton_dropzone.css('background', 'green');
                //$(ele).css('background', 'green');
            },


            onSubmit: function (id, fileName) {
                //var bouton_dropzone = $('#'+img_id+' .drop-zone')
                //bouton_dropzone.parent().find('img').addClass('loader').attr('src','/form_class/valums-file-uploader/client/loader.gif');
                //img_norm.addClass('loader').attr('src','/form_class/valums-file-uploader/client/loader.gif');
            },


            onComplete: function (id, fileName, responseJSON) {

                if (responseJSON.success) {

                    var img_small = $('li #' + img_id).find('img.gal_img_min');
                    var img_norm = $('li #' + img_id).find('img.gal_img_nor');
                    var bouton_dropzone = $('li #' + img_id).find('.drop-zone');
                    var descfile_date = $('li #' + img_id).find('span.descfile_date');
                    var descfile_name = $('li #' + img_id).find('span.descfile_name');
                    var descfile_type = $('li #' + img_id).find('span.descfile_type');
                    var descfile_size = $('li #' + img_id).find('span.descfile_size');
                    var descfile_alt = $('li #' + img_id).find('img.gal_img_min');
                    bouton_dropzone.hide();

                    //var img_path =  $('#'+img_id).find('img').attr('src');
                    // responseJSON.uploadDirectory +


                    // maj image + specificité

                    if (responseJSON.ext == 'swf') {
                        var img_new = '/2011_user_admin_img/ub_adm_default_norm_flash.jpg';
                        var img_new_s = '/2011_user_admin_img/ub_adm_default_small_flash.jpg';
                        img_small.removeClass('loader').attr('src', img_new_s);
                        img_norm.removeClass('loader').attr('src', img_new);
                        descfile_alt.attr('alt', responseJSON.uploadDirectory + responseJSON.fileName + '?' + new Date() * Math.random()); // img alt pour le zoom

                    } else {
                        var img_new = responseJSON.uploadDirectory + responseJSON.fileName + '?' + new Date() * Math.random();
                        img_small.removeClass('loader').attr('src', img_new);
                        img_norm.removeClass('loader').attr('src', img_new);
                        descfile_alt.attr('alt', img_new); // img alt pour le zoom
                    }



                    img_norm.show('slow');


                    descfile_name.text(responseJSON.fileName);
                    descfile_type.text((responseJSON.ext == 'swf' ? 'flash/' + responseJSON.ext : 'image/' + responseJSON.ext));
                    descfile_date.text(responseJSON.date);
                    var size = Math.round(responseJSON.size / 1024);
                    descfile_size.text(size);

                    // size nb
                    //this_ub_gal.gal_msg_size( responseJSON.img_size_total, responseJSON.img_nb );
                    // alert (responseJSON.quota);
                    //console.log(responseJSON.img_size_total+' '+ responseJSON.img_nb )


                    //alert (img_new);
                    bouton_modif.html('Modifier');

                    // list file show fade eff
                    bouton_dropzone.find('ul.qq-upload-list').show(100).delay(2000).fadeOut(900, function () {
                        $(this).html('');
                    });


                } else {

                    // no success
                    var bouton_dropzone = $('li #' + img_id).find('.drop-zone');
                    bouton_dropzone.find('ul.qq-upload-list').show(100).delay(3000).fadeOut(1200, function () {
                        $(this).html('');
                    });
                }
                //alert (responseJSON.action+' ---> '+fileName)
                //alert (dump(responseJSON, 10)+' <br> ---> '+fileName);


            },
            debug: false
        }
    );

};


