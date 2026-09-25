
$(document).ready(function () {

    console.log('load---> js_core_inscription + mdp forget + login');




    /* =inscription 2019
     -------------------------------------------------------------- */

    // rules
    //
    // special rule

    /*
    // recaptcha_validation_rule
    //
    $.fn.form.settings.rules.recaptcha_validation_rule = function() {
        if ( inscription.recaptcha_validation ) return true;
        return false;
    };
    */

    // domaine exist
    //
    $.fn.form.settings.rules.domaine_existe = function(value) {
        // Your validation condition goes here

        inscription.check_dom(value);

        if ( inscription.check  ) return true;
        return false;
    };


    // licence
    //
    $.fn.form.settings.rules.checked_ = function(value) {
        // Your validation condition goes here
        //console.log( ( inscription.form_validate.form('get value', 'us_licence').length > 0)  );
        // console.log('valide '+ $('.us_licence').addClass('checked').length );

        // pas de validation pour le volet n1
        if ( inscription.volet_active == 1  ) return true;


        return ( inscription.form_validate.form('get value', 'us_licence').length > 0);

       // return false;

    };







    // ########################################### LoginIn
    //
    login = {

        name:               ''

        ,url : 		        '/ubaction__user_open'
        ,form_validate :    $('#login_form')
        ,login_opened :     false


        // init

        , init: function () {

            inscription.recaptcha_load_init_v3();

            // modal
            $('.btn_connection').on('click', function() {

                login.login_show();

                // validate Mdp forgeit
                mdp_forget.init();

                if ( ! login.login_opened ) { // stop multi opened

                    grecaptcha.execute(reCAPTCHA_key_public, {action: 'validate_captcha'}).then(function(token) {
                       $('input[name=g-recaptcha-response]').val(token);
                    });

                    login.login_rules();
                    login.login_validate_submit();

                    login.login_opened = true;


                }

            });

        }

        // login_validate_submit 1/2

        , login_validate_submit: function () {

            $('.valider_submit_login').on('click', function(e) {

                e.stopPropagation();
                e.preventDefault();

                // console.log('submit login');

                if(  login.form_validate.form('is valid') ) {

                    //console.log('submit############### login');
                    login.form_validate.submit();

                }


            });
        }


        // Show Login

        , login_show: function () {

            ub_fn.UI_Close_all_modal();
            $('.ui.modal.modal_connection').modal('show');

        }

        // validate Login

        , login_rules: function ($el) {

        // validate
        this.form_validate
            .form({
                inline: true,
                on: 'change',
                debug: true,
                delay: true,
                duration: 50,

                fields: {

                    login: {
                        identifier: 'login',
                        rules: [
                            {
                                type:       'empty',
                                prompt:     __.__['Indiquer une valeur']
                            },
                            {
                                type   :    'doesntContain[@]',
                                prompt :    __.__['Indiquez votre identifiant (pas votre mail)']
                            }
                        ]
                    },

                    /*
                    pass: {
                        identifier: 'pass',
                        rules: [
                            {
                                type: 'empty',
                                prompt:     __.__['Indiquer une valeur']
                            },
                            {
                                type: 'minLength[4]',
                                prompt:     __.__['Doit contenir plus de {ruleValue} caractères']
                            }
                        ]
                    },
                    */


                }
            });
    }


};



    // ########################################### Mdp forget
    //
    mdp_forget = {
        name:               ''
        ,$el :              ''

        ,url : 		        '/inscription'
        ,form_validate :     $('#mdp_form')

        , mdp_forget_opened :     false

        // init

        , init: function () {

            // modal
            $('#btn_mdp_forget').on('click', function() {

                mdp_forget.mdp_show();

                if ( ! mdp_forget.mdp_forget_opened) { // stop multi opened

                    mdp_forget.mdp_rules();
                    mdp_forget.mdp_validate_submit();

                    mdp_forget.mdp_backtologin();
                    mdp_forget.mdp_back();

                    mdp_forget.mdp_forget_opened = true;
                }

            });

            // test trigger
            //$('#btn_mdp_forget').trigger('click');

        }

        // Show Mdp forget

        , mdp_show: function () {

            //console.log('Mdp forget')

            $('#segment_connect').addClass('hidden');
            $('#segment_mdp').removeClass('hidden');

        }

        // retour form to login 1->0
        //
        , mdp_backtologin: function () {
            $('.btn_back_mdptologin').on('click', function(e) {

                e.stopPropagation();
                e.preventDefault();

                $('#segment_mdp').fadeOut('fast', function () {

                    $('#segment_connect').removeClass('hidden');
                    $(this).addClass('hidden').fadeIn();

                });

            });
        }


        // retour form mdp where error 2->1
        //
        , mdp_back: function () {
            $('.btn_back_mdp').on('click', function(e) {

                    e.stopPropagation();
                    e.preventDefault();

                    $('#segment_mdp').fadeIn('fast', function () {

                            $('#segment_mdp_showOk')
                            .addClass('hidden')
                            .find('.message')
                            .addClass('hidden')
                            .find('.header')
                            .text('')
                        ;


                    });

            });
        }


        // validate & submit  Mdp forget   1/3

        , mdp_validate_submit: function () {

            $('.valider_submit_mdp').on('click', function(e) {

                e.stopPropagation();
                e.preventDefault();

                console.log('submit');

                if(  mdp_forget.form_validate.form('is valid') ) {

                    //console.log('submit############### mdp');

                    mdp_forget.mdp_validate_submit_send();

                }
            });

        }




        // validate submit send ajax  Mdp forget 2/3
        //
        , mdp_validate_submit_send: function () {

            allFields = mdp_forget.form_validate.form('get values')

            //console.log( allFields );

            $('#segment_loader_mdp').addClass('active');

            grecaptcha.execute(reCAPTCHA_key_public, {action: 'validate_captcha'}).then(function(token) {
                //console.log('-----> ####### grecaptcha.execute RELOAD');
                $('input[name=g-recaptcha-response]').val(token);
            });

            console.log('ajax send');

            $.ajax({
                url:        mdp_forget.url,
                type :      "POST",
                data :      allFields,
                dataType:   "json",

                success: function(retour_data) {

                    console.log('ajax sucess');

                    $('#segment_mdp').fadeOut('fast', function () {
                        $('#segment_loader_mdp').removeClass('active');

                        // error
                        if ( retour_data.error)
                        {
                            mdp_forget.validate_submit_error(retour_data);

                        } else
                        // ok - no error
                        {
                            mdp_forget.validate_submit_show_ok(retour_data);
                        }

                    });

                }

            });

        }


        // show error   3/3
        //
        , validate_submit_error: function(data) {
            console.log( data);

            $('#segment_mdp_showOk')
                .removeClass('hidden')
                .find('.negative.message')
                .removeClass('hidden')
                .find('.header')
                .text(data.error_msg)
            ;

        }


        // show OK  3/3
        //
        , validate_submit_show_ok: function(data) {
            //console.log( data);

            $info_message = $('#segment_mdp_showOk')
                .removeClass('hidden')
                .find('.info.message')
                .removeClass('hidden')
                ;

            $info_message
                .find('p')
                .html( data.msg )
                ;

            $info_message
                .find('.list')
                .html(data.list_mail)
                ;

            $info_message
                .find('.header')
                .html(data.header)
            ;
        }



        // validate Mdp forgeit - Mdp rules
        //
        , mdp_rules: function ($el) {

            // validate
            this.form_validate
                .form({
                    inline: true,
                    on: 'change',
                    debug: true,
                    //verbose :   true,
                    delay: true,
                    duration: 100,

                    fields: {

                        us_mail_mdp: {
                            identifier: 'us_mail',
                            rules: [
                                {
                                    type: 'empty',
                                    prompt: __.__['Indiquer votre mail']
                                },
                                {
                                    type: 'minLength[5]',
                                    prompt: __.__['Votre mail doit contenir plus de  {ruleValue} caractères']
                                }
                            ]
                        },

                    }
                });
        }

    };



    // ########################################### modal subscribe
    //
    inscription = {

        name:               ''
        ,$el :              ''

        ,url : 		        '/fm_ajax'              // input+combo
        ,url_dom_check:     '/inscription'  // checkbox

        ,form_validate :            $('#inscription_classic')
        ,form_validate_google :     $('#inscription_google')

        ,check_last_value : ''
        ,check_ajax_lock :  ''

        ,volet_active :     1

        ,url_valided_inscription :   false

        ,animation_makebook_end : false

        ,inscription_opened :     false
        //,modal_close_callback : function(){}


        // init
        //
        , init: function () {

            this.init_modal();


        }


        // init valided
        //
        , init_valided: function () {

            this.rules();
            this.validate_volet();
            this.validate_submit();

            $('.us_licence').checkbox();

            //console.log('inscription_classic')

        }

        //

        , init_modal: function () {

            $('.btn_modal_creerbook, .btn_modal_creerbook_mdl').on('click', function (event) {

                event.stopPropagation();
                event.preventDefault();

                $('.ui.modal').modal('hide all');

                $('.ui.modal.modal_creerbook').modal('show');

                if ( ! inscription.inscription_opened ) {

                    inscription.init_valided();

                    $('#inscription_classic .dropdown').dropdown();

                    inscription.inscription_opened = true;
                }

                // reload recaptcha_reloadforce_v3
                setInterval(function(){
                    inscription.recaptcha_reloadforce_v3();
                }, 150000);

            });

        }




        // show form
        //
        ,user_add_google_form: function (data) {

            $('#inscription_segment')
                .addClass('hidden');

            $('#inscription_segment_google_form_after')
                .removeClass('hidden')
                .find('.dropdown').dropdown();


            this.rules_auth();

            /*
            // test default data
            data = {};
            data.google_data.email =           'pat75.2010@gmail.com';
            data.google_data.displayName =     'Patrice Tardif';
            data.google_data.photoURL =        'https://lh5.googleusercontent.com/-pJ5ZzWANqg8/AAAAAAAAAAI/AAAAAAAAK2g/kUZrRmrWjxU/photo.jpg?sz=200';
            */

           // console.log( data );
            var data_ = data;//JSON.parse(data);
            //console.log( data_ )

            var $data_google = $('.data_google');

            $data_google.find('.data_displayName').text( data_.google_data.displayName );
            $data_google.find('.data_photo_url').attr('src', data_.google_data.photoURL );
            $data_google.find('.data_email').text( data_.google_data.email );


        }





        // validate submit send ajax classique/google
        //
        ,validate_submit_send_google: function ( allFields ) {

            //console.log( allFields );

            $.ajax({
                url:        inscription.url_dom_check,
                type :      "POST",
                data :      allFields,
                dataType:   "json",
                success: function(retour_data) {
                    //console.log('######## retour '+ retour_data);

                    $('#inscription_segment_loader').addClass('active');

                    $('#inscription_segment_presentation').transition('stop').transition('fade out', function(){


                            //retour_data = {'error':false};
                            // retour_data = {'error':true, 'error_msg':['identifiant incorrect','other'] };

                            // error
                            if ( retour_data.error)
                            {
                                inscription.validate_submit_error(retour_data.error_msg);

                                // recaptcha_reload_v3
                                inscription.recaptcha_reloadforce_v3();

                            } else
                            // ok - no error
                            {

                                inscription.url_valided_inscription = url_prefix+'://'+retour_data.url_domaine+'/'+retour_data.url_action;

                                $('#inscription_segment_').transition('stop').transition('fade out', function(){
                                     inscription.validate_submit_presentation(retour_data);

                                });

                            }

                    });

                }

            });

        }




        // validate submit social

        ,validate_submit_google: function () {

            $('.valider_submit').on('click', function(e) {

                e.stopPropagation();
                e.preventDefault();
                //console.log('submit');

                $('#display_error_segment').transition('stop').transition('fade out').html('')

                if( inscription.form_validate_google.form('is valid') ) {

                    //console.log('submit############### google');

                    allFields = inscription.form_validate_google.form('get values');

                    inscription.validate_submit_send_google(allFields);

                }
            });

        }









        // show form user_add_process_form social
        //
        // for google, fb, linkedin
        //
        ,user_add_process_form: function (type,data) {

            $('#inscription_segment')
                .addClass('hidden');

            $('#inscription_segment_google_form_after')
                .removeClass('hidden')
                .find('.dropdown').dropdown();


            this.rules_auth();


            //console.log( data );
            var data_ = data;//JSON.parse(data);
            //console.log( data_ )
            var $data_google = $('.data_google');

            $('#icon_to_change').addClass(type); // modif icon

            switch ( type ) {

                case 'google':
                    var data_process = data_.google_data;
                    break

                case 'linkedin':
                    var data_process = data_.linkedin_data;
                    break

                case 'facebook':
                    var data_process = data_.facebook_data;
                    break

            }

            // process
            $data_google.find('.data_displayName').text(data_process.displayName);
            $data_google.find('.data_photo_url').attr('src', data_process.photoURL);
            $data_google.find('.data_email').text(data_process.email);
            $data_google.find('.data_type_connection').text( type );

            // modify hidden
            $('#inscription_google input[name="form_id"] ').val('form_adduser_'+type);


        }








        // recaptcha v3 load & init
        //
        , recaptcha_reload_v3: function () {
            grecaptcha.ready(function() {
                grecaptcha.execute(reCAPTCHA_key_public, {action: 'validate_captcha'}).then(function(token) {
                    //console.log('-----> ####### recaptcha_reload_v3');
                    $('input[name=g-recaptcha-response]').val(token);
                });
            });
        }

        , recaptcha_reloadforce_v3: function () {

                if ( typeof (grecaptcha) != "undefined" ) {
                    //console.log( 'recaptcha_reloadforce_v3' );
                    grecaptcha.execute(reCAPTCHA_key_public, {action: 'validate_captcha'}).then(function (token) {
                        //console.log('-----> ####### recaptcha_reloadforce_v3');
                        $('input[name=g-recaptcha-response]').val(token);
                    });
                } else {
                    //console.log( 'inscription.recaptcha_load_init_v3' );
                    inscription.recaptcha_load_init_v3();
                }
        }

        , recaptcha_load_init_v3: function () {

                $.getScript( "https://www.google.com/recaptcha/api.js?render="+reCAPTCHA_key_public)
                .done(function( script, textStatus ) {
                    //console.log( textStatus );
                    inscription.recaptcha_reload_v3();
                })
                .fail(function( jqxhr, settings, exception ) {
                        console.log( "Triggered ajaxError handler load grecaptcha." );
                });

            //console.log('-----> recaptcha_load_init_v3');
        }








        // make 100%

        , make_progress_canvas: function () {
            return new Promise(function (resolve, reject) {
                var ctx = document.getElementById('make_progress_canvas').getContext('2d');
                var al = 0;
                var start = 4.72;
                var cw = ctx.canvas.width;
                var ch = ctx.canvas.height;
                var diff;

                function progressSim() {
                    diff = ((al / 100) * Math.PI * 2 * 10).toFixed(2);

                    ctx.clearRect(0, 0, cw, ch);
                    ctx.lineWidth = 24;
                    ctx.fillStyle = "#00b5ad";
                    ctx.strokeStyle = "#00b5ad";
                    ctx.textAlign = "center";
                    ctx.font = "14px 'Source Sans Pro' ";

                    ctx.fillText(al+'%', cw*.5-10, ch*.5+4, cw);
                    ctx.beginPath();
                    ctx.arc(140, 75, 54, start, diff/10+start, false);
                    ctx.stroke();

                    if (al >= 100) {
                        clearTimeout(sim);
                        resolve(true)
                    }
                    al++;
                }

                var sim = setInterval(progressSim, 50);
            });
        }


        // check dom
        // appeller par $.fn.form.settings.rules.domaine_existe

        , go_to_url_valided_inscription: function () {


            if (inscription.url_valided_inscription) {
                //alert('ok')
                //location.reload();
                //console.log('## close');
                window.location.href = inscription.url_valided_inscription;
            }
        }

        // check domain
        // appeller par $.fn.form.settings.rules.domaine_existe

        , check_dom: function (value) {


            if (   inscription.check_last_value == value) return;
            if (   inscription.check_ajax_lock == 'lock') return;
            if (  value.length <= 3) return;


            inscription.check_ajax_lock = 'lock';
            $.ajax({
                //async : false,
                url:    inscription.url_dom_check,
                type :  "GET",
                data :  {
                    action : 'loginexist',
                    us_login : value
                },
                //dataType: "json",

                success: function(data) {

                    inscription.check_last_value = value; //inscription.check_new_value;
                    inscription.check_ajax_lock = '';

                    inscription.check =  (data == 'true')? true : false;

                    // revalidation à la fin du retour ajax
                    $('#inscription_classic').form('validate field','us_login');
                    //console.log('######## revalidate us_Login'+ inscription.check +' '+value);

                    $('#show_id_connection').text(value);
                }

            });

        }



        // validate google

        , rules_auth: function ($el) {


            // validate
            this.form_validate_google
                .form({
                    inline:     true,
                    on:         'change',
                    debug:      true,
                    //verbose :   true,
                    delay:      true,
                    duration:   100,

                    fields: {
                        us_login: {
                            identifier: 'us_login',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Indiquer votre nom']
                                },
                                {
                                    type   : 'minLength[4]',
                                    prompt : __.__['Votre nom de book/identifiant doit contenir plus de  {ruleValue} caractères']
                                },

                                {
                                    type   : 'regExp[/^[a-z0-9-]{3,32}$/]',
                                    prompt : __.__['Caractéres incorrecte']
                                },

                                {
                                    type   : 'domaine_existe',  // voir > $.fn.form.settings.rules.domaine_existe
                                    prompt : __.__['Ce nom existe déja']
                                }
                            ]

                        },

                        us_type : {
                            identifier: 'us_type',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Sélectionner un métier ou domaine']
                                }
                            ]
                        },

                        us_licence: {
                            identifier: 'us_licence',
                            rules: [
                                {
                                    type   : 'checked_',
                                    prompt : __.__['Vous devez accepter les conditions d’utilisation']
                                }
                            ]
                        }

                    }
                });



        }


        // validate

        , rules: function ($el) {


            // validate
            this.form_validate
                .form({
                    inline:     true,
                    on:         'change',
                    debug:      true,
                    delay:      true,
                    duration:   100,

/*
                    onSuccess : function(){
                        //post to controller
                        console.log(' ############################ onSuccess ')
                        inscription.validate_submit_send();
                    },
*/

                    fields: {
                        us_login: {
                            identifier: 'us_login',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Indiquer votre nom']
                                },
                                {
                                    type   : 'minLength[4]',
                                    prompt : __.__['Votre nom de book/identifiant doit contenir plus de  {ruleValue} caractères']
                                },

                                {
                                    type   : 'regExp[/^[a-z0-9-]{3,32}$/]',
                                    prompt : __.__['Caractéres incorrecte']
                                },

                                {
                                    type   : 'domaine_existe',  // voir > $.fn.form.settings.rules.domaine_existe
                                    prompt : __.__['Ce nom existe déja']
                                }
                            ]
                        },

                        us_type : {
                            identifier: 'us_type',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Sélectionner un métier ou domaine']
                                }
                            ]
                        },

                        us_pass: {
                            identifier: 'us_pass',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Indiquer votre nom']
                                },
                                {
                                    type   : 'minLength[2]',
                                    prompt : __.__['Votre mot de passe doit contenir plus de  {ruleValue} caractères'] //'Your password must be at least {ruleValue} characters'
                                },
                                {
                                    type   : 'regExp',
                                    value  : /^[-a-zA-Z0-9!@#$%*_=+]{3,24}$/i,
                                    prompt : __.__['Votre mot de pass doit comporter des chiffres, des lettres et contenir au minimu 6 caratéres ( exepté :iIoO ) ']
                                }
                            ]
                        },

                        us_licence: {
                            identifier: 'us_licence',
                            rules: [
                                {
                                    type   : 'checked_',
                                    prompt : __.__['Vous devez accepter les conditions d’utilisation'] // You must agree to the terms and conditions'
                                }
                            ]
                        },

                        us_nom: {
                            identifier: 'us_nom',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Indiquer votre nom']
                                },
                                {
                                    type   : 'minLength[2]',
                                    prompt : __.__['Doit contenir plus de {ruleValue} caractères']
                                }
                            ]
                        },

                        us_mail: {
                            identifier: 'us_mail',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : __.__['Indiquer votre mail']
                                },
                                {
                                    type   : 'minLength[5]',
                                    prompt : __.__['Votre mail doit contenir plus de  {ruleValue} caractères']
                                },
                                {
                                    type   : 'contains[@]',
                                    prompt : __.__['Il ne s’agit pas d’un mail']
                                }
                            ]
                        },

                    }
                });

        }



        // validate submit show ok - end 1
        //
        , validate_submit_presentation: function (retour_data) {

            // inscription_segment

            $('#inscription_segment_loader').transition('stop').transition('fade out', function(){

                $('.ten.wide').removeClass('ten wide').addClass('sixteen wide');

                $('.inscription_segment_bravo').addClass('hidden')
                $('#inscription_segment_presentation').addClass('hidden')

                    $('#inscription_segment_validation').transition('stop').transition('vertical flip in', function() {

                        // progress make book

                        inscription.make_progress_canvas()
                            .then(function () {
                                inscription.validate_submit_presentation_link();
                            })
                            .catch(function (err) {
                                console.error('Erreur !');
                                inscription.validate_submit_presentation_link();
                            });

                    });

            })

        }

        // validate_submit_presentation_link - end 2
        //
        , validate_submit_presentation_link: function () {



            $('#make_progress_canvas').transition('stop').transition('vertical flip out',function(){

                $('.inscription_segment_bravo').transition('stop').transition('vertical flip in');

                $('.ui.modal.modal_creerbook')
                    .modal({
                        onHidden: function () {
                            inscription.go_to_url_valided_inscription();
                        }
                    })
                ;

                $('.btn_acceder_espace').on('click',function(){

                    $('.ui.modal.modal_creerbook').modal('hide');

                })

            });

        }


        // validate submit show error

        , validate_submit_error: function (data) {

            var msg_error = '';

            data.forEach(function(val){
                msg_error += '<p>'+ val +'</p>';
            });


            $('#inscription_segment_loader').transition('fade out', function() {

                    $('#display_error_segment').removeClass('hidden').transition('fade in', function(){
                        $(this).find('.error.message').append( msg_error )
                    });


                    $('.return_first')
                        .removeClass('hidden')
                        .on('click',function(){

                            inscription.recaptcha_reloadforce_v3();

                            $('#inscription_segment_google_form_after').addClass('hidden');

                            $('#display_error_segment').transition('fade out', function(){
                                $(this).addClass('hidden').find('.error.message').html('');
                                $('#inscription_segment').transition('fade in', function(){
                                    $('#inscription_segment').removeClass('hidden');
                                })
                            })
                    })

            })

        }


        // validate submit send ajax
        //
        , validate_submit_send: function () {

            allFields = inscription.form_validate.form('get values')

            /*
            // test form
            allFields = {
                'action':           "form",
                'form_action':      "form_valide",
                'form_id':          "form_adduser",
                'g-recaptcha-response':     "03AF6jDqW5NxTBZJ11UTPYgcaTR2uSK2OubFTLpQ2WKhN8SDb19u",
                'recaptcha':        "on",
                'us_licence':       "on",
                'us_login':         "pat-test2019--"+new Date().getTime(),
                'us_mail':          "patrice2016@tardif.fr",
                'us_pass':          "94363694"
            };
            */
            //console.log(allFields['g-recaptcha-response'] );
            //allFields.us_login = "pat-test2019--"+new Date().getTime();
            //console.log( allFields );
            //$('#inscription_segment').transition('stop').transition('fade out','2s');

            $.ajax({
                url:        inscription.url_dom_check,
                type :      "POST",
                data :      allFields,
                dataType:   "json",

                success: function(retour_data) {
                    //console.log('######## retour '+ retour_data);

                    $('#inscription_segment').transition('stop').transition('fade out', function(){
                        $('#inscription_segment_loader').addClass('active');

                            //retour_data = {'error':false};
                            // retour_data = {'error':true, 'error_msg':['identifiant incorrect','other'] };

                            // error
                            if ( retour_data.error)
                            {
                                inscription.validate_submit_error(retour_data.error_msg);

                            } else
                            // ok - no error
                            {

                                inscription.url_valided_inscription = url_prefix+'://'+retour_data.url_domaine+'/'+retour_data.url_action;

                                inscription.validate_submit_presentation(retour_data);

                            }


                    });

                }

            });


        }


        // validate submit
        //
        , validate_submit: function () {


            $('.valider_submit').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                inscription.form_validate.form('validate field', 'us_mail');
                inscription.form_validate.form('validate field', 'us_licence');
                inscription.form_validate.form('validate field', 'us_pass');
                inscription.form_validate.form('validate field', 'us_nom');

               // console.log('submit');

                if( inscription.form_validate.form('is valid') ) {

                    e.stopPropagation();
                    e.preventDefault();

                    //$('.valider_submit').transition('fade','0.8s')
                    //   console.log('submit ############### form is valid ');

                    inscription.validate_submit_send();

                }
            });

        }



        // validate volet
        //
        , validate_volet: function () {


            //suivant
            $('.valider_volet_1').on('click',function(e) {
                e.stopPropagation();
                e.preventDefault();

                // revalidate
                inscription.form_validate.form('validate field', 'us_login');
                inscription.form_validate.form('validate field', 'us_type');

                var isvalide_us_login =     inscription.form_validate.form('is valid', 'us_login')
                var isvalide_us_type =      inscription.form_validate.form('is valid', 'us_type')

                //console.log('validate_volet_1' + isvalide_us_login);

                if ( isvalide_us_login &&  isvalide_us_type ) {

                    //console.log('validate_volet_1' + isvalide_us_login);

                    $('.volet_2').addClass('hidden');
                    $('.volet_1')
                        .transition(
                            'vertical flip out', 600,
                            function () {
                                $('.volet_2').transition('stop').transition('vertical flip in');
                                $('#inscription_segment_social_connect').addClass('hidden');
                                inscription.volet_active = 2;

                                // recaptcha_reload_v3
                                inscription.recaptcha_reloadforce_v3();

                            })
                    ;

                }

            })

            // btn back

            $('.valider_volet_2').on('click',function(e) {
                e.stopPropagation();
                e.preventDefault();

                $('.volet_1').addClass('hidden');
                $('.volet_2')
                    .transition('vertical flip out', 600, function() {
                        $('.volet_1').transition('stop').transition('vertical flip in');
                        $('#inscription_segment_social_connect').removeClass('hidden');
                        inscription.volet_active = 1;
                    })
                ;
            })

        }

    };



});

