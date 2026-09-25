/**
 * Created by pat on jeudi25/avril/19.
 */
console.log('fn_msg_admin->init');


var fn_msg_admin = {


    $col_left:          $('#col_left'),
    $col_right:         $('#col_right'),

    select_msg_id :     false,
    select_case :       0,
    select_case_first : 1,

    type :              'work_C_buy',

    nav_prev :          false,
    nav_next :          false,
    nav_change :        true,

    nb_by_page :        6,
    max_case_visible :  4,


    msg_open_refresh :  false,




    init: function () {


        console.log('msg_admin ->  init || type_action ' + type_action )

        // if client & msg

        if ( type_action == 'message' ) {

            if ( array_data.get_result.msgs ) {

                this.msg_open_client(array_data);

                /*
                $('[data-make-sticky-to]').sticky({
                    context: $('[data-make-sticky-to]').data('makeStickyTo'),
                    pushing: true,
                    offset: 50
                    // onStick: function() { alert('onStick')}
                })
                */

            } else {
                //alert('ok')
                this.msg_open_client_no_msg();
            }

        } else {


        // if user & msg
            var this_ = this;
            this.menu_make().then(function(is_doing) {
                // if make menu is_doing
                if (is_doing) {
                    this_.loader_make(this_.$col_right);

                    //console.log('type>'+this.type)
                    //this.nav_change = true;
                    this_.list_make();
                }
            });
        }
    },





    // bad client token
    msg_open_client_no_msg: function () {

        var the_Template = Handlebars.compile( $("#tpl_msg_client_no_msg").html() );
        var the_tpl =  the_Template();
        $('.reply_form').remove();
        $('#msg_client').append( the_tpl );

    },

    // open msg client
    msg_open_client: function (array_data) {

        var this_ = this;

        var data_ = array_data.get_result;

        $.each(data_.msgs, function(index, item) {
            data_.msgs[index].allreadyshow = "false";
        });

        //console.log(data_)
        // tpl menu
        var the_Template = Handlebars.compile( $("#tpl_msg_open").html() );
        var the_tpl =  the_Template( data_ );
        $('#msg_client').append( the_tpl );
        $('#msg_client .msg_open').transition('fade in');
        $('.cards .reply_form').remove();


        // show client
        var client = data_.msgs[data_.msgs.length - 1];
        //console.log(data_.msgs);
        console.log(client);

        // if msg close/deleted
        if (client.action_request =="trash" ) {
            var the_Template = Handlebars.compile( $("#tpl_msg_deleted").html() );
            var the_tpl =  the_Template();
            $('#msg_client').before( the_tpl );
        }


        // client show
        var the_Template = Handlebars.compile( $("#tpl_client_show").html() );
        var the_tpl =  the_Template( client );
        $('#client_show').append( the_tpl );

        // show request
        var the_Template = Handlebars.compile( $("#tpl_client_request").html() );
        client.from = data_.from;
        var the_tpl =  the_Template( client );
        $('#client_request').append( the_tpl );


        // origin format reply
        if ( data_.from == 'user') {
            $('.reply_form .card').removeClass('ui right pointing card input').addClass('ui left pointing card input is_my_msg');
        }

        // show reply
        if ( client.action_request != "trash" ) {
            $('.reply_form').transition("fade in");
        }

        // action submit reply client
        this.msg_open_reply_client_submit(client.id, client.action_request, client.user_book, data_.from , verif);


        book.book_api_show();

    },



    // open msg ajax
    msg_open_reply_client_submit: function (id_parent, action_request, user_book, from, verif) {

        //var this_ = this;

        $('.btn_reply_client_send').on('click', function(e) {

            e.stopPropagation();
            e.preventDefault();

            var $el_ = $(this);
            var msg_ = $el_.parent().parent().find('textarea[name="us_reply"]').val();
/*
            var data =       {
                action :    'msg_reply_client',
                id_parent:  id_parent,
                us_id:      user_book,
                type :      action_request,
                token :     token,
                selector :  selector,
                msg :       msg_
            };

            console.log(data);
*/
            var request = $.ajax({
                url:        "/intermediate_send",
                type:       "POST",
                data:       {
                    action :    'msg_client_reply_externe',
                    id_parent:  id_parent,
                    us_id:      user_book,
                    type :      action_request,
                    token :     token,
                    selector :  selector,
                    from :      from,
                    verif:      verif,
                    msg :       msg_
                },
                dataType:   "json"
            });

            request.done(function( retour_data ) {

                if ( retour_data.error)
                {
                    console.log('error')
                } else
                // ok - no error
                {
                   //var data_ = retour_data.get_result;

                    //console.log('id_ ' + id_parent)
                    //is is_my_msg

                    var is_my_msg = ( $('.cards.reply_form .card').hasClass('is_my_msg')) ?  "true" : "false";
                    //console.log( is_my_msg )


                    var dat_ = {
                        "msgs": [
                            {
                                "id": "",
                                "id_parent": id_parent,
                                "is_my_msg": is_my_msg,
                                "name": "",
                                "compagny": "",
                                "email": "",
                                "phone": "",
                                "ip": "",
                                "date": "Maintenant",
                                "msg": msg_,
                                "visuel": "",
                                "readed": "false",
                                "hide": "true"
                            }
                        ]
                    };

                    // tpl menu
                    var the_Template = Handlebars.compile( $("#tpl_msg_open").html() );
                    var the_tpl = the_Template(dat_);
                    $('.reply_form').after(the_tpl)

                    $('.reply_form').next().transition('fade in').find('.card')
                        .transition({
                            animation: 'pulse',
                            duration: '1000'
                        })
                        .transition({
                            animation: 'glow',
                            duration: '600'
                        })

                }

            });


            request.fail(function( jqXHR, textStatus ) {
                alert( "Request failed: " + textStatus );
            });

        });


    },






    // open msg
    msg_open: function (id) {

        //console.log('open '+id+'   this.select_msg_id '+this.select_msg_id);
        var this_ = this;
        //var msg_content = '';

        // if one msg open
        if ( this.select_msg_id  ) {
            this.msg_close( this.select_msg_id );
        }


        // show arrow & click arrow
        $('#msg_'+id+' .btn_msg_close')
            .transition('show')
            //.unbind('click')
            .on('click',function(){
                // close msg + arrow
                var id_to_close = $(this).parent().parent().data('msg_id');
                this_.msg_close( id_to_close );
            });
        $('#msg_'+id+' .btn_msg_open').transition('hide');


        // mark open
        $('#msg_'+id).addClass('open')
        $('#msg_'+id).find('.non_lu').transition('hide');

        // show refresh
        $('#msg_'+id+' .btn_msg_refresh').transition('show');
        $('.btn_msg_refresh').popup({position:'top center'});

        // new seleted msg id
        this.select_msg_id = id;






        // test if msg already exist
        var  $msg_open_new = $('#msg_'+id+' .cards');
        if ( $msg_open_new.length != 0 ) {
            $msg_open_new.transition('fade in');
            console.log('test if msg already exist '+id)
            return true;
        }


        // nb read countdown if not allready doing
        if ( $('#msg_'+id+' .non_lu').length != 0 ) this.list_nb_down();


        // ajax
        //msg_content.id = id;

        this.msg_open_ajax(id);


        return;
    },

    // open msg ajax
    msg_open_ajax: function (id) {

        var this_ = this;

        // clean
        $('#msg_'+id+' .cards').remove();

        var request = $.ajax({
            url:        "/intermediate_get",
            type:       "POST",
            data:       {
                key :       id,
                action :    'msg_open'
            },
            dataType:   "json"
        });

        request.done(function( retour_data ) {

            //alert('ok')

            if ( ! retour_data ) {
                console.log('msg_open_ajax: réponse vide ou non-JSON');
                return;
            }

            if ( retour_data.error)
            {
                console.log('error')
            } else
            // ok - no error
            {
                var data_ = retour_data.get_result;

                if ( ! data_ || ! data_.msgs ) {
                    console.log('msg_open_ajax: aucun message trouvé pour cet id');
                    return;
                }

                //data_.msgs[0].readed = '';
                //data_.msgs[1].readed = 'A';


                // tpl menu
                // Handlebars.templates = {};
                var the_Template = Handlebars.compile( $("#tpl_msg_open").html() );
                var the_tpl =  the_Template( data_ );
                $('#msg_'+id).append( the_tpl );
                $('#msg_'+id+' .msg_open').transition('fade in');

                // if action is a refresh
                if (fn_msg_admin.msg_open_refresh) {
                    fn_msg_admin.msg_open_refresh =false;
                    this_.$col_right.find('.loading_segment').transition('fade out');
                    //return;
                }

                // show _msg_reply_form
                var $msg_client_first = $('#msg_'+id+' .cards').first(); //.find('.description'); //.css('background-color','red');

                var the_Template = Handlebars.compile( $("#tpl_msg_reply_form").html() );
                var the_tpl =  the_Template( data_ );
                $msg_client_first.before(the_tpl)

                // $('.card').first().css('background','red');

                // action submit reply
                this_.msg_open_reply_submit(id);

            }
        });


        request.fail(function( jqXHR, textStatus ) {
            alert( "Request failed: " + textStatus );
        });


    },

    // btn trash+open
    list_action_titre: function ( $el ) {
        $el.find( '.mail_titre' )
            .unbind('click')
            .on('click', function(){
                var id = $(this).parent().data('msg_id');

                if ( $(this).parent().hasClass('open') ) {
                    // close
                    fn_msg_admin.msg_close( id );
                } else {
                    // open
                    fn_msg_admin.msg_open( id );
                }
            });
    },


    get_type_actif: function () {
        return $('.menu_type .active').data('type');
    },



    // open msg ajax
    msg_open_reply_submit: function (id) {
        //var id_ =   id;
        var this_ = this;
        //console.log('id'+id)

        $('#msg_'+id+' .btn_reply_send').on('click', function(e) {

            e.stopPropagation();
            e.preventDefault();

            // find parent msg
            var id_ = $(this).parents('.item').data('msg_id');
            //$(this).parents('.item').css( "background-color", "red" );

            var $el_ = $(this);

            var msg_ = $el_.parent().parent().find('textarea[name="us_reply"]').val();
            var type_ = this_.get_type_actif();

            var request = $.ajax({
                url:        "/intermediate_get",
                type:       "POST",
                data:       {
                    key :       id_,
                    action :    'msg_reply',
                    type :      type_,
                    msg :       msg_
                },
                dataType:   "json"
            });

            request.done(function( retour_data ) {

                if ( retour_data.error)
                {
                    console.log('error')
                } else
                // ok - no error
                {
                    var data_ = retour_data.get_result;

                    console.log('id_ ' + id_)
                    var dat_ = {
                        "msgs": [
                            {
                                "id": "",
                                "id_parent": id_,
                                "is_my_msg": "true",
                                "name": "",
                                "compagny": "",
                                "email": "",
                                "phone": "",
                                "ip": "",
                                "date": "Maintenant",
                                "msg": msg_ ,
                                "visuel": "",
                                "readed": "false",
                                "hide": "true"
                            }
                        ]
                    };



                    // tpl menu
                    var the_Template = Handlebars.compile( $("#tpl_msg_open").html() );
                    var the_tpl = the_Template(dat_);
                    $('#msg_' + id_ + ' .reply_form').after(the_tpl)
                    //$('#msg_' + id_ + ' .reply_form').next().transition('fade in');


                    $('#msg_' + id_ + ' .reply_form').next().transition('fade in').find('.card')
                        .transition({
                            animation: 'pulse',
                            duration: '1000'
                        })
                        .transition({
                            animation: 'glow',
                            duration: '600'
                        })



                }

            });


            request.fail(function( jqXHR, textStatus ) {
                alert( "Request failed: " + textStatus );
            });

        });


    },

    // list
    list_make: function ( type, key ) {


        if ( ! type )   type = this.type;
        if ( ! key )    key = 0;

        if (key == 0)   fn_msg_admin.select_case = 0;

        this.type = type;
        var this_ = this;


        this.$col_right.find('.list_segment').transition('stop').transition('fade out');

        var request_list = $.ajax({
            url:        "/intermediate_get",
            type:       "POST",
            data:       {
                key :       key,
                action :    'msg_list',
                type :      type,
                nb_by_page : fn_msg_admin.nb_by_page
            },
            dataType:   "json"
        });

        request_list.done(function( retour_data ) {

            //alert('ok')

            if ( ! retour_data ) {
                console.log('list_make: réponse vide ou non-JSON');
                return;
            }

            if ( retour_data.error)
            {
                console.log('error')
            } else
            // ok - no error
            {

                var data_ = retour_data.get_result;

                // tpl menu
                this_.$col_right.find('.list_segment').remove();

                if ( !  data_.msg ) {

                    // msg empty
                   // bottom menu remove
                   $('.menu_case').remove();

                    // msg - nothing
                    var ub_theTemplate_header = Handlebars.compile($("#tpl_msg_list_nothing").html());
                    var the_tpl = ub_theTemplate_header();
                    this_.$col_right.prepend(the_tpl);
                    this_.$col_right.find('.list_segment').transition('stop').transition('fade in');

                } else {

                    console.log( data_)

                    // date fr
                    data_ = this_.msg_date_fr( data_);

                    // nettoyage avant insertion (évite doublons)
                    this_.$col_right.find('.spam_alert_banner').remove();
                    this_.$col_right.find('.list_segment').remove();

                    // msg ok
                    var ub_theTemplate_header = Handlebars.compile($("#tpl_msg_list").html());
                    var the_tpl = ub_theTemplate_header(data_);
                    this_.$col_right.prepend(the_tpl);

                    //this_.$col_right.find('.loading_segment').transition('fade out', function() {

                    this_.$col_right.find('.list_segment').transition('stop').transition('fade in', function () {

                        if (fn_msg_admin.nav_change) {
                            this_.nav_bottom_make(fn_msg_admin.select_case);
                        }

                        this_.list_action();

                        // handler bouton suppression SPAM
                        if ( data_.has_spam ) {
                            this_.$col_right.find('.btn_trash_spam').off('click').on('click', function(e) {
                                e.preventDefault();
                                if ( confirm( 'Supprimer définitivement tous les messages SPAM [ALERTE] ?' ) ) {
                                    fn_msg_admin.msg_trash_spam_action();
                                }
                            });
                        } else {
                            this_.$col_right.find('.spam_alert_banner').remove();
                        }

                        //console.log('list make')

                    });
                    //});
                }

            }

        });

        request_list.fail(function( jqXHR, textStatus ) {
            alert( "Request failed: " + textStatus );
        });

        //});

    },


    msg_date_fr: function (data_) {
        // date fr
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour:'numeric', minute:'numeric' };

        var lang_ = lang+'-'+lang; // "fr-FR"
        data_.msg.forEach(function(element,kk) {
            //console.log(element.date+kk);
            var today  =  new Date( element.date );
            data_.msg[kk].date = today.toLocaleDateString(lang_, options);
        });
        return data_;
    },

    // trash msg
    msg_trash: function (id) {

        var request_trash = $.ajax({
            url:        "/intermediate_get",
            type:       "POST",
            data:       {
                key :       id,
                type :      this.type,
                action :    'msg_trash'
            },
            dataType:   "json"
        });

        request_trash.done(function( retour_data ) {

            //alert('ok')

            if ( retour_data.error)
            {
                console.log('error')
            } else
            // ok - no error
            {

                $("#msg_"+id).transition('fade out',function(){
                    $(this).remove();
                })

            }
        });

        request_trash.fail(function( jqXHR, textStatus ) {
            alert( "Request failed: " + textStatus );
        });
    },

    // un-trash msg
    msg_untrash: function (id) {

        var request_trash = $.ajax({
            url:        "/intermediate_get",
            type:       "POST",
            data:       {
                key :       id,
                action :    'msg_untrash'
            },
            dataType:   "json"
        });

        request_trash.done(function( retour_data ) {

            //alert('ok')

            if ( retour_data.error)
            {
                console.log('error')
            } else
            // ok - no error
            {

                $("#msg_"+id).transition('fade out',function(){
                    $(this).remove();
                })

            }
        });

        request_trash.fail(function( jqXHR, textStatus ) {
            alert( "Request failed: " + textStatus );
        });
    },



    // close msg
    list_nb_down: function () {
        // find type
        var $the_item = $('.menu .item.active');

        var nb = $the_item.find('.nb_noread').text();
        nb = nb - 1
        if (nb<=0){
            $the_item.find('.nb_noread').hide();
        } else {
            $the_item.find('.nb_noread').text(nb);
        }
        $.cookie('us_msg_unreaded', nb);

    },


    // close msg
    msg_close: function (id) {
        console.log('close '+id);
        var $select_msg = $('#msg_'+id);
        $select_msg.find('.cards').transition('hide');
        $select_msg.find('.btn_msg_close, .btn_msg_refresh').transition('hide');
        $select_msg.find('.btn_msg_open').transition('show');
        $select_msg.find('.mail.icon').removeClass('mail').addClass('envelope open outline');

        // mark close
        $('#msg_'+id).removeClass('open')

    },


    // btn trash+open
    list_action: function () {

        var this_ = this;

        var $list = this.$col_right.find('.list .item');
        $list.each(function( index ) {

            // open action
            $( this ).find( '.btn_msg_open' ).on('click', function(){
                var id = $(this).parent().parent().data('msg_id')
                fn_msg_admin.msg_open( id );
            });

            // refresh action
            $( this ).find( '.btn_msg_refresh' ).on('click', function(event){
                event.preventDefault();
                event.stopPropagation();
                var id = $(this).parent().parent().data('msg_id');
                this_.$col_right.find('.loading_segment').transition('fade in');
                fn_msg_admin.msg_open_refresh = true;
                fn_msg_admin.msg_open_ajax(id);
            });



            fn_msg_admin.list_action_titre( $( this ) );

            if ( this_.type != 'trash') {

                $(this).find('.btn_msg_undeleted').transition('hide');

                $(this).find('.btn_msg_deleted').on('click', function () {
                    var id = $(this).parent().parent().data('msg_id')
                    fn_msg_admin.msg_trash(id);
                });
                $('.btn_msg_deleted').popup({
                    position:'top center'});
                /*
                $('.btn_msg_deleted').popup({
                    position:'top center',
                    delay: {
                        show:   10,
                        hide:   1000
                        },
                    boundary :      'div',
                    distanceAway :  8
                });
                */
            } else {
                $(this).find('.btn_msg_undeleted').on('click', function () {
                    var id = $(this).parent().parent().data('msg_id')
                    fn_msg_admin.msg_untrash(id);
                });



                $(this).find('.btn_msg_deleted').transition('hide');
            }
            //console.log( index + ": " + $( this ).text() );
        });

    },




    // menu
    menu_make: function () {


        var deferred = $.Deferred();

        //var data_ = msg_nb;

        var this_ = this;

        this_.loader_make(this_.$col_left);

        var request_menu = $.ajax({
            url:        "/intermediate_get",
            type:       "POST",
            data:       {
                action :    'msg_nb',
            },
            dataType:   "json"
        });


        request_menu.done(function( retour_data ) {

            //alert('ok')

            if ( retour_data.error)
            {
                console.log('error')
            }
            else
            // ok - no error
            {

                this_.loader_make(this_.$col_right);

                var data_ = retour_data.get_result ;
                //console.log( data_)


                // make msg empty

                if ( data_.nb.length == 0) {
                    // tpl menu
                    var ub_theTemplate_header = Handlebars.compile($("#tpl_mybook").html());
                    var the_tpl = ub_theTemplate_header();
                    this_.$col_left.append(the_tpl);

                    var ub_theTemplate_header = Handlebars.compile($("#tpl_msg_no_msg").html());
                    var the_tpl = ub_theTemplate_header();
                    this_.$col_right.append(the_tpl);


                    // no margin left col
                    this_.$col_left.css({'padding':0, 'margin-left':'10px'});
                    this_.$col_right.css({'margin-left':'-20px'});

                    book.book_api_show();

                    deferred.resolve(false);

                } else {

                // make menu

                    // tpl menu
                    var ub_theTemplate_header = Handlebars.compile($("#tpl_msg_menu").html());
                    var the_tpl = ub_theTemplate_header(data_);
                    this_.$col_left.append(the_tpl);

                    $('.menu_type  .btn_type:eq(0)').addClass('active');

                    // default type
                    fn_msg_admin.type = $('.menu_type  .btn_type:eq(0)').data('type');
                    console.log('type>>>' + fn_msg_admin.type)

                    // action menu type
                    $('.menu_type  .btn_type').on('click', function (e) {

                        //e.stopPropagation();
                        //e.preventDefault();

                        $('.menu_type > .btn_type').removeClass('active');
                        $(this).addClass('active');

                        fn_msg_admin.type = $(this).data('type');
                        fn_msg_admin.select_case_first = 1;
                        fn_msg_admin.nav_change = true;

                        this_.list_make(fn_msg_admin.type, 0);
                    });

                    deferred.resolve(true);
                }


            }

            return;
        });


        request_menu.fail(function( jqXHR, textStatus ) {
            alert( "Request failed: " + textStatus );
        });

        return deferred.promise();
    },



    // nav_bottom get_nb_case_show
    nav_bottom_get_nb_case_show: function ( select_case, nb_total ) {
        var nb_case_total = Math.ceil( nb_total / fn_msg_admin.nb_by_page ) ;
        var nb_case_sold =  nb_case_total - select_case ;

        fn_msg_admin.nav_prev = false;
        fn_msg_admin.nav_next = false;

        if ( nb_case_sold > fn_msg_admin.max_case_visible ) {
            fn_msg_admin.nav_next = true;
            var nb_case_show = fn_msg_admin.max_case_visible;
        } else {
            var nb_case_show = nb_case_sold;
        }


        if ( select_case >=  fn_msg_admin.max_case_visible ) {
            fn_msg_admin.nav_prev = true;
        }


        nb_case_show --;

        /*
         console.log(
         'nb_case_sold '+nb_case_sold
         + ' | select_case '+select_case
         + ' | max_case_visible ' +fn_msg_admin.max_case_visible
         + ' | nb_total '+nb_total+' | nb_case_total '+nb_case_total
         + ' | nb_case_show '+nb_case_show
         + ' | nav_next '+fn_msg_admin.nav_next
         );
         */
        return nb_case_show
    },

    // nav_bottom
    nav_bottom_make: function ( select_case ) {

        $('.menu_case').remove();

        //var select_case = 8;
        // find nb total on active menu
        var nb_total = $('.menu_type .active').data('nb_total');

        var nb_case_show = this.nav_bottom_get_nb_case_show(select_case, nb_total);

        var data_  =    [];
        data_.cases  =  [];

        for (var i = 0; i <=  nb_case_show ; i++) {
            data_.cases.push( { text:select_case+i+1 , key: select_case+i } );
        }


        //console.log( 'nav_bottom_make: function' );



        // tpl tpl_msg_nav_bottom
        var the_Template = Handlebars.compile($("#tpl_msg_nav_bottom").html());
        var the_tpl = the_Template(data_);

        this.$col_right.append(the_tpl);
        this.$col_right.find('.menu_case').transition('stop').transition('fade in', function() {
            fn_msg_admin.nav_bottom_action();
            fn_msg_admin.nav_bottom_pointing( fn_msg_admin.select_case );
        });


    },


    // nav_bottom pointing
    nav_bottom_action: function ( select_case ) {

        $('.menu_case > .btn_case').each( function(){
            $(this).unbind('click').on('click', function(){
                var click_case_id =     $(this).data('case_id');
                fn_msg_admin.nav_bottom_pointing( click_case_id );
            })
        })

    },


    // nav_bottom pointing
    nav_bottom_pointing: function ( select_case ) {

        // a faire afficher par bloc

        $('.btn_case').removeClass('active');
        $('.btn_case').eq( 0 ).addClass('active');

        if ( fn_msg_admin.nav_next ) {
            $('.btn_case_next')
                .transition('show')
                .unbind()
                .on('click', function(){

                    fn_msg_admin.select_case = fn_msg_admin.select_case_first * fn_msg_admin.max_case_visible ;
                    fn_msg_admin.select_case_first ++;
                    fn_msg_admin.nav_change = true;

                    //console.log( 'fn_msg_admin.select_case '+fn_msg_admin.select_case + ' | fn_msg_admin.select_case_first'+fn_msg_admin.select_case_first );

                    fn_msg_admin.list_make( fn_msg_admin.type, fn_msg_admin.select_case );
                })
        }

        if ( fn_msg_admin.nav_prev ) {
            $('.btn_case_prev')
                .transition('show')
                .unbind()
                .on('click', function(){

                    fn_msg_admin.select_case = ( ( fn_msg_admin.select_case_first - 2 ) * fn_msg_admin.max_case_visible  ) ;
                    fn_msg_admin.select_case_first --;
                    fn_msg_admin.nav_change = true;

                    //console.log( 'fn_msg_admin.select_case '+fn_msg_admin.select_case + ' | fn_msg_admin.select_case_first'+fn_msg_admin.select_case_first );

                    fn_msg_admin.list_make( fn_msg_admin.type, fn_msg_admin.select_case );
                });

        }

        // normal link
        $('.menu_case .btn_case')
            .unbind()
            .on('click', function(){
                fn_msg_admin.select_case = $(this).data('case_id');
                console.log('fn_msg_admin.type '+fn_msg_admin.type+ '| fn_msg_admin.select_case '+fn_msg_admin.select_case)

                $('.menu_case .btn_case.active').removeClass('active');
                $(this).addClass('active');

                fn_msg_admin.nav_change = false;
                fn_msg_admin.list_make( fn_msg_admin.type, fn_msg_admin.select_case )
            });


        //console.log( 'nb_case '+nb_case+'  - msg_list_nb.max_case '+msg_list_nb.max_case );
    },



    // trash all spam [ ALERTE ] messages
    msg_trash_spam_action: function () {

        var this_ = this;
        var type_ = this.get_type_actif();

        var request = $.ajax({
            url:        '/intermediate_get',
            type:       'POST',
            data:       {
                action :    'msg_trash_spam',
                type :      type_
            },
            dataType:   'json'
        });

        request.done(function( retour_data ) {
            if ( ! retour_data.error ) {
                // supprime visuellement tous les items [ ALERTE ] de la liste
                this_.$col_right.find('.item').each(function() {
                    if ( $(this).find('.mail_titre').text().indexOf('[ ALERTE ]') !== -1 ) {
                        $(this).transition('fade out', function(){ $(this).remove(); });
                    }
                });
                // masque le bandeau
                this_.$col_right.find('.spam_alert_banner').transition('fade out', function(){ $(this).remove(); });
            }
        });

        request.fail(function( jqXHR, textStatus ) {
            alert( 'Request failed: ' + textStatus );
        });
    },

    // loader
    loader_make: function ($dom_insert) {
        // tpl loader
        //$dom_insert.find('.loading_segment').remove();

        var ub_theTemplate_header = Handlebars.compile($("#tpl_msg_loader").html());
        var the_tpl = ub_theTemplate_header();
        $dom_insert.append(the_tpl);

    }

};
