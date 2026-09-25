// ###########################################
//



$(document).ready(function () {

    $.fn.editable.defaults.mode = 'inline';

    $.fn.editableform.template = '\
        <form class="ui form form-inline editableform form_validate">\
            <div class="control-group">\
            <div class="field">\
                <div class="editable-input"></div>\
                <div class="editable-buttons"></div>\
                <div class="editable-error-block"></div>\
            </div>\
            </div>\
        </form>\
        ';


    $.fn.editableform.buttons = '\
        <button class="ui primary icon button editable-submit">\
            <i class="check icon"></i>\
        </button>\
        <button class="ui icon button editable-cancel">\
            <i class="cancel icon inverted"></i>\
        </button>\
        ';


    $.fn.editableform.loading = '<div class="ui active inline loader"></div>';


    function callback_checkbox(id) {alert('ok')}


    console.log('load---> js_core_fm2');


    /* =fm_v2  dec2018
     -------------------------------------------------------------- */

    fm2 = {

         name:          ''
        ,$el :          ''

        ,url : 		    '/fm_ajax'              // input+combo
        ,url_checkbox:  '/front/ajax_2010.php'  // checkbox

        ,fm_token :     $('#ubdf_token').data('token')



        // init
        //
        , init: function () {

            if ( $('#fm2_stop').length ) return;


            this.menu();

            this.fm_input();
            this.fm_checkbox();
            this.fm_combo();
            this.fm_submit();

            this.fm_icon_edit();

            $('.UItooltip')
                .popup()
            ;

            //
            // ckeditor actived from header
            //this.fm_edit_zone();

        }







/*
        // edit cke 2014
        ,fm_edit_zone: function() {


            $('.cont_cke_edit').each(function() {


                //fm2.fm_edit_add_all_btn( $(this) ); // add all btn

                var el_id = $(this).attr('id');								// id de l'activateur
                var el_deleted = $(this).children('.cont_cke_deleted'); 	// del
                var el_editable = $(this).children('.cont_cke_edit_bloc'); 	// edit zone
                var el_editable_id = $(this).data('cke_text_id'); 			// id de la db   ub_cke_text


                //fm2.fm_edit_edit_btn( $(this), el_editable );   // btn edition
                //fm2.fm_edit_deleted_action( el_deleted,el_id ); // deleted

                console.log( el_id+' '+ el_editable )

                fm2.fm_edit_action_cke( el_id, el_editable );       // edit


                $(this).addClass('border');
            });
        }

        // fn edit ajouter tous les boutons
        ,fm_edit_add_all_btn: function($el_) {
            $el_.append('\
				<div class="cont_cke_edit_ico infobulles" original-title="'+fm2.txt_bt_edit_cont+'"></div>\
				<div class="cont_cke_deleted infobulles" original-title="'+fm2.txt_bt_sup_cont+'"></div>\
				');
        }



        // fn cke edit
        ,fm_edit_action_cke: function(el_id, el_editable) {
            // http://ckeditor.com/forums/CKEditor/Ckeditor-4-inline-toolbar-options-not-working
            // http://jsfiddle.net/paulftw/H2szq/
            // http://stackoverflow.com/questions/3799317/how-can-i-get-content-of-ckeditor-using-jquery
           // console.log( el_id );
           // alert('ok')

            var editor = $(el_editable).ckeditor({
                language : 'fr',
                uiColor : '#AADC6E',
                // <span style="font-family:abel"></p><link href="https://fonts.googleapis.com/css?family=Abel" rel="stylesheet" type="text/css" />	</div>
                //entities : true,
                //htmlEncodeOutput : false,
                allowedContent : true,
                //extraPlugins : 'ckeditor-gwf-plugin,colorbutton',
                toolbar :
                    [
                        { name: 'basicstyles', items : [ 'Bold','Italic','Underline','-' , 'HorizontalRule', 'SpecialChar' ] },
                        { name: 'links', items: [ 'Link', 'Unlink' ] },
                        { name: 'colors', items: [ 'TextColor', 'BGColor', 'Font', 'FontSize'  ] },
                        { name: 'align', items : [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] }
                        //{ name: 'tools', items : [ 'About' ] }
                    ],

                font_names : 'Arial/Arial, Helvetica,Georgia/Georgia, serif;Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;Times New Roman/Times New Roman, Times, serif;Verdana/Verdana, Geneva, sans-serif;GoogleWebFonts;',


                on: {
                    /*
                    blur: function( event ) {
                        var data = event.editor.getData();
                        var dataID =  event.editor.container.getId();
                        //console.log(dataID+'ok'+data);// Do sth with your data...

                        jQuery.ajax({
                            type: "POST",
                            url: url_ajax_usadmin_editxt,
                            data: { dom_data: data, dom_id: dataID, us_key: data_us_key, book_mdl: ub_page_mdl  }
                        })
                            .done(function (data, textStatus, jqXHR) {
                                //console.log("Your content was successfully saved. [" + jqXHR.responseText + "]");
                                $('#'+dataID).parent('.cont_cke_edit').addClass('border');
                            })
                            .fail(function (jqXHR, textStatus, errorThrown) {
                                console.log("Error saving content. [" + jqXHR.responseText + "]");
                            });

                        // btn edit Aff
                        //console.log(dataID);
                        $('#'+dataID).parent('.cont_cke_edit').children('.cont_cke_edit_ico').fadeIn();

                    },

                    focus: function( event ) {
                        //alert('ok')
                        // btn edit Eff
                        var dataID =  event.editor.container.getId();
                        $('#'+dataID).parent('.cont_cke_edit').children('.cont_cke_edit_ico').fadeOut();
                        $('#'+dataID).parent('.cont_cke_edit').removeClass('border');
                    }
                    */
/*
                }

            });

        }

        // fn cke edit - btn
        ,fm_edit_edit_btn: function($el_, el_editable) {

            $el_.children('.cont_cke_edit_ico')
                .click(function(){
                    // auto fire CKE
                    el_editable.focus();
                });
        }




        // fn cke del - dialogue
        ,fm_edit_deleted_action: function(el_deleted) {

            var el_id = $(el_deleted).parent('.cont_cke_edit').children('.cont_cke_edit_bloc').attr('id');

            $(el_deleted)
                .html('<div class="deleted-button"></div>')
                .click(function(){

                    // si +
                    if ( $(el_deleted).children().hasClass('add-button') ) {
                        // aff x
                        $(el_deleted).parent('.cont_cke_edit').children('.cont_cke_edit_bloc').html('<br/>').fadeIn();
                        $(el_deleted).children().removeClass('add-button').addClass('deleted-button');

                        $(el_deleted).attr('original-title', fm2.txt_bt_sup_cont); //legende

                        $(el_deleted).parents('.cont_cke_edit').removeClass('no_border');

                        // auto fire CKE
                        $('#'+el_id).focus();

                    }

                    else {
                        // si X

                        swal({
                                title: 					fm2.txt_supprimer, //"Supprimer ?",
                                text: 					fm2.txt_contenuefface, //"Le contenu sera effacé",
                                // type: 				"info",
                                showCancelButton: 		true,
                                icone_css:				"icon-warning", // modif ub 2014
                                cancelButtonText:		fm2.txt_annuler, //"Annuler",
                                confirmButtonColor: 	"#DD6B55",
                                confirmButtonText: 		fm2.txt_supprimer_ok //"Supprimer !"
                            },
                            function(){
                                fm2.edit_deleted_action_del(el_deleted,el_id);

                                // btn edit
                                $(el_deleted).parent('.cont_cke_edit').children('.cont_cke_edit_ico').fadeOut();

                                // sup contenu
                                $(el_deleted).parent('.cont_cke_edit').children('.cont_cke_edit_bloc').html('<br/>').fadeOut();

                            });


                    }
                });

        }


        // fn cke del
        ,fm_edit_deleted_action_del: function(el_deleted,el_id) {

            // console.log('ok'+ el_id );
            // requet ajax pour effacer le contenu
            $.ajax({
                url:  url_ajax_usadmin_editxt,
                data:  { 	action: 		'deleted',
                    us_key: 		data_us_key,
                    book_mdl:       ub_page_mdl,
                    dom_id: 		el_id
                },
                type:  "POST",
                cache: false,
                //dataType: 'jsonp', // Notice! JSONP <-- P (lowercase)

                success:function(jsondata) {
                    //console.log(jsondata);

                    // hide cke
                    $(el_deleted).prev('.cont_cke_edit_bloc').html('').fadeOut();
                    // bouton aff +
                    $(el_deleted).children().removeClass('deleted-button').addClass('add-button');

                    // legende
                    $(el_deleted).attr('original-title', fm2.txt_bt_add_cont);

                    // no-border
                    $(el_deleted).parents('.cont_cke_edit').addClass('no_border');

                },
                error:function() {
                    alert("Error - reconnectez-vous !");
                }
            });

        }
        // edit cke 2014 - fin
*/




































        // validate
        //
        , fm2_validate: function ($el) {

            //console.log( $el );

            var $form_validate = $('.form_validate');
            $('.form_validate input').attr('name','form_input_validate');//.css('color','blue')

            // parse et recompose les régles
            var valided_rules = $el.data('validedcont');
            valided_rules = valided_rules.split(',');
            var valided_rules_ = [];
            $.each(valided_rules, function( index, value ) {
                valided_rules_.push({ type   : value  })
            });
            console.log(valided_rules_);
            /*
             console.log(valided_rules_);
             [
             { type   : 'minLength[10]'  },
             { type   : 'maxLength[12]'  },
             { type   : 'empty'  }
             ]
             */


            // validate
            $form_validate
                .form({
                    inline: true,
                    on: 'change',
                    //debug: true,
                    //verbose : true,
                    fields: {
                        form_input_validate: {
                            identifier: 'form_input_validate',
                            rules: valided_rules_
                        },
                    }
                });

        }


        // input
        // textarea
        //
        , fm_input: function () {


            $('.xeditable').each(function () {

                var $el = $(this);
                //$el.css('border-color','red');
                //console.log('fm2_input '+$el.data('pk'));

                // cacher les password

                fm2.fm_pass_hide($el);


                // init form
                $el.editable({

                    emptyclass:     'placeholder',
                    savenochange :  true,

                    url: fm2.url,
                    ajaxOptions: {
                        dataType: 'json'
                    },
                    //type: $el.data('type'), // text|password|textarea

                    params: function(params) {
                        //originally params contain pk, name and value
                        params = {
                            action:	'fm-ajax'
                            ,fm_field: params.pk
                            ,fm_value: params.value
                            ,fm_token: fm2.fm_token
                        };
                        //console.log(params);
                        return params;
                    },

                    validate: function(value) {
                        if(  ! $('.form_validate').form('is valid', 'form_input_validate')) {
                           return " ";
                        }
                    },

                    success: function (response, newValue) {
                        console.log('success')
                        if (response.status == 'error') {
                            response = {error:'true',error_msg:'failed response.status'};
                        }
                        fm2.ajax_retour(response)
                    },

                    error: function(response, newValue) {
                        console.log('error')
                        response = {error:'true',error_msg:response.status};
                        fm2.ajax_retour(response)
                    },


                });

                // show icon
                $el.after('<i class="edit icon xeditable_action"></i>');

                //hide form
                $el.on('hidden', function (e, reason) {

                    fm2.fm_pass_hide( $(this) );

                    $('.fm2_field_active')
                        .removeClass('fm2_field_active')

                    if (reason === 'save' || reason === 'cancel') {
                        //auto-open next editable
                        $(this).parent().parent().parent().next().find('.editable').editable('show');
                    }
                    $(this).next().fadeIn();
                });

                // show form
                $el.on('shown', function (e, editable) {

                    fm2.fm_pass_show($el,editable);

                    fm2.fm2_validate($el);

                    $el.parent().addClass('fm2_field_active');

                    $el.next().next().fadeOut();


                });

            })

        }




        // password show/hide
        //
        , fm_pass_hide: function ($el_) {
            if (!$el_.data('password')) return

            // in form or in text
            var pass_input = $el_.editable('getValue');
            pass_input = pass_input[Object.keys(pass_input)[0]];// first item
            if (!pass_input) pass_input = $el_.text().trim();
            //console.log('fm_pass_hide  >>>>>>>> ' + pass_input);

            var pass_ = pass_input.replace(/./g, "•");

            $el_
                .data('defaultValue', pass_input)
                .text(pass_)
                .css('opacity', '1');

        }
        , fm_pass_show: function ($el_, editable) {
            if ( ! $el_.data('password') ) return

            var pass = $el_.data('defaultValue');
            console.log('fm_pass_show defaultValue '+pass+' ')
            editable.input.$input.val(pass);

        }




        // icon edit
        //
        , fm_icon_edit: function () {
            $('.xeditable_action').click(function (e) {
                e.stopPropagation();
                console.log('action')
                $(this).prev('.xeditable').editable("show");
            });

        }



        // retour ajax response
        //
        , ajax_retour: function (json) {
            //alert('ok');


            // all OK
            if (!json.error || json.error=='false') {
                fm2.valided_icon(true);
                return true;
            }

            // error
            console.log(json.error_msg)
            fm2.valided_icon(false,json.error_msg);

        }


        // retour ajax icon true/fase
        //
        , valided_icon: function (etat,msg) {

            if (msg==undefined) msg = '';

            var icon_etat =  (etat)?'check icon green':'exclamation icon red';
            $('.fm2_field_active').find('i.fm2_validated_active').remove();
            $('.fm2_field_active')
                .removeClass('fm2_field_active')
                .append('<i class="'+icon_etat+' fm2_validated_active"></i>'+msg)
                .find('.fm2_validated_active')
                .delay(600)
                .fadeOut(1800, function () {
                    $(this).remove();
                })
            ;
        }



        // checkbox
        //
        , fm_checkbox: function () {
            $(".ui.toggle.checkbox:not(.us_licence)").each(function () {

                fm2.$el = $(this);

                var $checkbox = fm2.$el.find("input");
                var pk =        fm2.$el.data('pk');

                fm2.$el.checkbox({
                    onChange: function () {
                        var value = $checkbox.prop("checked")
                        //console.log("onChange!" + value + ' ' + pk + ' ');

                        $(this).parent().parent().addClass('fm2_field_active');
                        fm2.send_combo_checkbox( pk, value, 'checkbox');

                        // optinos
                        if ( pk == 'us_pf_diff_dispo' ) fm2.fm_checkbox_us_pf_diff_dispo(value);

                    }
                })
                ;
            });
        }






            // submit -> send as checkbox
        //
        , fm_submit: function () {
            $(".fm2-ajax-submit").each(function () {

                fm2.$el = $(this).find('button');

                var pk = fm2.$el.data('pk');
                var val = fm2.$el.data('val');

                // options
                if (pk == 'us_formule_ask_date') fm2.fm_option_us_formule_ask_date_init(fm2.$el, val );


                    fm2.$el.on('click submit', function (event) {

                        event.preventDefault();

                        if ( fm2.$el.hasClass('disabled') ) return false;

                        //console.log("Submit!" + pk + ' ');

                        $(this).parent().addClass('fm2_field_active');
                        fm2.send_combo_checkbox(pk, true, 'checkbox');

                        // options
                        if (pk == 'us_formule_ask_date') fm2.fm_option_us_formule_ask_date($(this));

                    })

                ;
            });
        }






// option =======================


        // option - checkbox

        , fm_checkbox_us_pf_diff_dispo: function (checker) {

            if (checker) {
                $('.dispo-false').hide();
                $('.dispo-true').show();
            } else {
                $('.dispo-true').hide();
                $('.dispo-false').show();
            }
        }

        // option - submit
        ,fm_option_us_formule_ask_date: function (el) {

            el.addClass('disabled').transition('hide');
            el.parent().find('.ask_last').hide();
            el.parent().find('.ask_send').removeClass('hide');

        }

        // option - submit init
        ,fm_option_us_formule_ask_date_init: function (el,isoFormatDateString) {

            var nb_day_to_renew =  30;
            //var nb_day_to_renew =  1;

            //  calculate the number of days between two dates

            var dateParts = isoFormatDateString.split("-"); // mysql date
            var jsDate = new Date(dateParts[0], dateParts[1] - 1 , dateParts[2].substr(0,2) );

            var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
            var firstDate = new Date(jsDate.getFullYear(), jsDate.getMonth(), jsDate.getDate());
            var secondDate = new Date();
            secondDate.setDate(secondDate.getDate() - nb_day_to_renew ); // add 30 days

            var diffDays = fm2.fm_option_us_formule_ask_date_diff(firstDate, secondDate);

            //console.log(diffDays)
            //console.log(jsDate)

            if ( diffDays > 0 ) {
                el.parent().find('.ask_last .nb_day').append(diffDays);
                el.addClass('disabled').transition('hide');
            } else {
                //el.parent().find('.ask_now').removeClass('hide');
                el.parent().find('.ask_last').hide();
            }


        }

        ,fm_option_us_formule_ask_date_diff:function (date1, date2) {
            var datediff = date1.getTime() - date2.getTime(); //store the getTime diff - or +
            return Math.round((datediff / (24*60*60*1000))); //Convert values to -/+ days and return value
        }



// option ======================= end











        // combo
        //
        , fm_combo: function () {
            $(".fm2-ajax-combo .ui.dropdown").each(function () {

                fm2.$el = $(this);

                var pk = fm2.$el.data('pk');

                fm2.$el.dropdown({
                    onChange: function (value, text, $selectedItem) {
                        console.log("onChange!" + value + ' ' + text + ' ' + pk);
                        $selectedItem.parent().parent().parent().addClass('fm2_field_active');
                        fm2.send_combo_checkbox( pk, value, 'combo');
                    }
                })
                ;
            });
        }


        // send ajax combo
        //
        , send_combo_checkbox: function( fm_field, fm_value, type ) {

            //fm2.$el.children('.fm-ajax-loader').addClass('show');  // loader

            var url =       (type=='checkbox') ?  fm2.url_checkbox : fm2.url;

            if (type=='checkbox') {
                var params =  {
                    action:   'pref_ckeckbox_ajax'
                    ,combo_champ: fm_field
                    ,combo_val: fm_value
                    //,fm_token: fm2.fm_token
                };
            } else {
                var params =  {
                    action:   'fm-ajax'
                    ,fm_field: fm_field
                    ,fm_value: fm_value
                    ,fm_token: fm2.fm_token
                };
            }
            //   console.log(fm_field+' '+fm_value+' '+fm2.fm_token+' '+url);

            var req = $.post(
                url,
                params,
                true,
                "json"
            );


            // success
            //
            req.success(function( data ,response ){
                console.log('success')
                if (response.status == 'error') {
                    response = {error:'true',error_msg:'failed response.status'};
                }

                fm2.ajax_retour(response)

            });

            // error
            //
            req.fail(function( data ,msg ) {
                //fm_ajax.has_error( data, $el );
            });

        }




        // loader wait, ok, error
        //
        /*
        , loader: function (val) {
            $loader = $('#loader');
            switch (val) {
                case 'wait':
                    $loader.append('<div class="ui active inline loader"></div>');
                    break;
                case 'ok':
                    $loader.append('<i class="ui icon check circle"></i>');
                    break;
                case 'error':
                    $loader.append('<i class="ui icon exclamation circle"></i>');
                    break;
            }
        }
        */


        // menu
        //
        , menu: function () {


            // open/close	 + fleches

            $(".form_label:not(.open)").nextUntil(".form_label, .ub_form_input, .form_label_groupe, button, br").hide();

            $(".form_label:not(.Noactive)")
                .toggle(
                    function(){
                        $(this).nextUntil(".form_label, .ub_form_input, .form_label_groupe  ").show()
                       $(this).find(".form_label_fleches").removeClass("right").addClass("down");
                        $(this).addClass("open");
                    }
                    ,function(){
                        $(this).nextUntil(".form_label, .ub_form_input, .form_label_groupe  ").hide();
                        $(this).find(".form_label_fleches").removeClass("down").addClass("right");
                        $(this).removeClass("open");
                    })
                .prepend("<i class=\"chevron right icon form_label_fleches\"></i>")
                .css("cursor","pointer")
                .filter(".open")
                .find(".form_label_fleches")
                .removeClass("right")
                .addClass("down");



            /*

            // nettoyage des menus auto
            $(".form_label_simple").each(function (k, e) {
                if (k != 2)    $(this).show().unbind();
            });

            $(".form_label").each(function () {
                $(this).unbind().find(".fonticon-uniF006").removeClass("fonticon-uniF006").addClass("fonticon-uniF004");
            });

            */
        }
};




});
