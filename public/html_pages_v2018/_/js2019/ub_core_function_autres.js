/**
 * Created by pat on lundi06/mars/17.
 */



console.log('ub_core_function_autres');



/* aide - 2014
 -------------------------------------------------------------- */

var ub_aide = {

    aide_id:        '#aide_intro_' + type_action,
    aide_id_cook:   'aide_intro_' + type_action,

    init: function () {

        //console.log(this.aide_id+' Aide  '+this.aide_id_cook);

        if (type_action == 'user_open___' || type_action == '_projet__portfolio') {
            // action - qu'une fois localStorage/cookie
            if (typeof localStorage != 'undefined') {
                var ub_aide_accueil = (localStorage.getItem(this.aide_id_cook) ? false : true);
            } else {
                var ub_aide_accueil = ($.cookie(this.aide_id_cook) ? false : true);
            }


            if (ub_aide_accueil) {
                setTimeout(function(){
                    // desac aide auto
                    //  ub_aide.intro_start();
                }, 1600); // pour FF qui a besoin des dom avant
            }

        }


        //action menu
        $("#aide_menuprincipal").click(function (event) {
            ub_aide.intro_start();
        });

    },


    intro_start: function () {

        //console.log(' intro_start: function ' +ub_msg_core.msg_intro_suivant );

        if (!$('#ub_user_menu').length) return false; // non connecté

        // test localstorage
        if (type_action == 'user_open___' || type_action == '_projet__portfolio') {
            localStorage.setItem(this.aide_id_cook, true);
            $.cookie(this.aide_id_cook, true);
        }

        /*
        // retour menu top
        $('#ub_nav_sec_bloc').css({
            'border-bottom': 'none',
            'width': '100%',
            'z-index': 10,
            'position': 'relative'
        });
        $('#ub_nav_sec_bloc ul.nav_sec li a.selected').css({'background-position': '20px 32px'});	// fleche

        // retour menu right
        $('#ub_user_menu').css({'margin-top': '30px'});
        */

        if (type_action == '_projet__portfolio')    aide_id = "#bloc_cms_user";
        else                                        aide_id = "#aide_intro_user_bookaccueil___";

        //overlay
        $('body').append('<div id="introjs-overlay_accueil" class="introjs-overlay" style="top: 0;bottom: 0; left: 0;right: 0;position: fixed;opacity: 0.8;"></div>');



        var intro = introJs(aide_id);
        intro.setOptions({
            overlayOpacity: 0,
            nextLabel: ub_msg_core.msg_intro_suivant,
            prevLabel: ub_msg_core.msg_intro_prec,
            skipLabel: ub_msg_core.msg_intro_quitter,
            doneLabel: ub_msg_core.msg_intro_voila,
            tooltipPosition: 'auto'
        });

        intro.start();



        intro.oncomplete(function () {
            $('#introjs-overlay_accueil').remove();
        });

        intro.onexit(function () {
            $('#introjs-overlay_accueil').remove();
        });
    }


};


/* =fn =format nombre
 -------------------------------------------------------------- */

var ub_fn_nombre = {

    init: function () {
        //console.log('nb');
        $('.ub_format_sep').each( function() {
            $(this).html(
                ub_fn_nombre.ub_format( $(this).text() , '.' )
            );
        });
    },


    ub_format: function (nStr,sep)
    {
        //console.log( 'nb -> ' + nStr );
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) { x1 = x1.replace(rgx, '$1' + sep + '$2'); }
        return x1 + x2;
    }

};




/*  =newsletter
 -------------------------------------------------------------- */

var ub_newsletter = {

    init: function () {
        //console.log('ub_newsletter init');

        this.add();

        if (page_type == "newsletter") {
            this.btn_hover();
            this.btn_ajax();
            this.aff_newsletter_now();
        }

    },

    // add mail
    add: function () {
        //console.log("form.ub_form_newsletter");
        /* newsletter inscription */

        $("form.form_newsletter_2018").unbind().submit( function(event) {
            event.preventDefault();

            //console.log('form.ub_form_newsletter submit');
            var inputs = [];
            $(':input', this).each(function() {
                inputs.push(this.name + '=' + encodeURI(this.value));
            });

            //console.log(inputs);
            //var $NewRetour = $(this).find(".newsletter_retour");
            var $NewRetour = $(this).next();
            var $NewForm = $(this);

            $.ajax({
                dataType: "json",
                type: "GET",
                data: inputs.join('&'),
                url: $(this).attr("action"),
                success: function(retour){
                    //$NewForm.fadeOut();
                    //$(".newsletter_retour")
                    if (retour.error) {
                        $NewRetour.stop().fadeOut(50).empty().append('<span style="color:red">'+retour.message+'</span>').fadeIn(100).delay(5000).fadeOut(2000);
                    } else {
                        $NewRetour.stop().fadeOut(50).empty().append('<span style="color:lightgreen">'+retour.message+'</span>').fadeIn(100).delay(5000).fadeOut(2000);
                        $.cookie('logiciel_ub_newsletter_inscription', 'true',  { expires: 90 });
                    }

                }
            });

        });
    },

    // hover sur les dates
    btn_hover: function () {

        $( '#newsletter_archive ul.annees li').click( function() {
            $('ul.mois:visible').parent().removeClass('selected');
            $('ul.mois:visible').hide();
            $(this).find('ul').fadeIn('slow');
            $(this).addClass('selected');
            //alert('ok');					
        });
    },


    // action ajax load newsletter
    btn_ajax: function () {
        $( '#newsletter_archive ul.annees li ul.mois li a').each(function() {
            var link = $(this).attr('href');
            $(this).click( function(event) {
                event.preventDefault();
                // alert(link);
                // ajax
                //$( "#newsletter_contenu" ).load( link );
                $("#newsletter_contenu_frame").attr("src",  link );

            });
        });
    },

    aff_newsletter_now: function() {
        // aff news
        $("#newsletter_contenu_frame").attr("src",  newsletter_now );
        // selected nav
        $('#news_'+newsletter_now_annee).addClass('selected').find('ul').fadeIn('slow');
    }

};




/* =plugin front all
 -------------------------------------------------------------- */

var ub_plugin_front = {


    // iPhone/iPad URL bar hides itself on pageload
    // ne fonctionne pas avec addEventListener
    iphone: function() {
        if (navigator.userAgent.indexOf('iPhone') != -1) {
            setTimeout( window.scrollTo(0, 0), 0);
        }
    },


    nb_contact_message_menu_g: function() {
        // nb message de contact -> menu gauche
        var ub_contact_msg_nb = $.cookie('us_msg_unreaded');
        if (ub_contact_msg_nb > 0 && type_action != 'user_message___') {
            $('ul.ub_nav_user li:nth-child(4)  a').append('<div style="margin-left:10px;" class="ui mini purple left pointing label">' + ub_contact_msg_nb + '</div>');
        }
    },

    /*
    menu_langue: function() {
        $('.lang_select_combo').on('change', function () {
            var url = $(this).val(); // get selected value
            if (url) window.location = url; // redirect
            return false;
        });
    }
   */


};

