


$(document).ready(function () {

    console.log('load---> js_core');




    /* init /start 2018
     -------------------------------------------------------------- */

    // console
    console.log("######################## js_core");
    console.log(page_type);
    console.log(page_domaine);
    console.log("########################");


    // recherche
    ubdf_recherche.init();

    // init
    ub_fn.init();



    // show book - via API
    book.book_api_show();


    // show stats book+action - bloc_portfolios
    book.book_static_show( '.ptf_index_static' );

    // send stats - bloc_portfolios
    ub_ill_plus_de_book.stats_book ( '.ptf_index_static' );


    // show book+action -  bloc_ultrabook
    if ( page_type == 'home' ) {
        book.book_static_show( '.ptf_index_static_ultrabook' );
        // send stats - bloc_portfolios
        ub_ill_plus_de_book.stats_book ( '.ptf_index_static_ultrabook' );
    }


    // ub_menu
    ub_menu.init();




    // menu light
    if ( page_type == 'message' )   book.menu_lightfixed(true); // menu version light

    // domaine & accueil
    if (
        (
            page_type == 'domaine' /*||
            page_type == 'accueil' ||
            page_type == 'home'*/
        )
        &&  book.book_api_show_no_used
    ) {


        // domaines
        // domaine url default
        //url_data = book.url_domaine('illustrateur');
        var domaine = ( page_domaine ? page_domaine.replace(/ /g,"_") : 'illustrateur');

        // domaine menu=light
        //if ( page_domaine ) book.menu_lightfixed(true); // menu version light

        // type url
        var type_url = book.url_domaine(domaine)

        // make_url_save
        var data_url = book.make_url(type_url);

        // stock_url_save
        book.stock_url_query_save(data_url);

        // show book
        book.show_book(data_url);

    }



    if (
            page_type == 'home'
        ) {
        // show slide
        $('.bloc_slide .swiper-container').addClass('opacity_on');
        $('.ui.active.loader').removeClass('active');
    }



    ub_infinit.init();

    ubdf_accueil.init();









    // test des url avec ouverture automatique #book
    // url_hash
    url_hash_bookid = ub_fn.url_hash_get();


    if ( url_hash_bookid == 'create-book' ) {
        // open create book
        Alpine.store('modale').ouvrir('creerbook');
    }
    else
        if ( url_hash_bookid ) {

        // open book
        console.log('############### url avec ouverture automatique #book '+url_hash_bookid);
        //console.log( book.data_url );


        // type url
        var type_url_ = book.url_hash( url_hash_bookid );

        // make_url_save
        book.data_url = book.make_url(type_url_);


        // show book
        book.show_book(
            book.data_url,
            function() {
                // auto open
                $('#user_' + url_hash_bookid).trigger('click');

                book.show_end(false);

            }
        );

        //console.log( url_data.book_id );
    }






    // motscles
    if (    page_type == 'user' && 	type_action == 'user_pref_form___'   ) {

        book.motscles_add();

        console.log('book.motscles_add');

    }


    // WP page
    if (
        (
            page_type == 'wp'
        )
        &&  book.book_api_show_no_used
    ) {

        ubdf_wp.init();
    }



    // fm2
    if ( page_type == 'user') {
        fm2.init();
    }


    // func autres
    //


    // page memobook

    ub_memobook.init();

    if (page_type == 'memobook' &&  book.book_api_show_no_used) {

        $('.infinite .cards').removeClass('five').addClass('four');

        ub_memobook.front_page_memobook();

        book.menu_lightfixed(true);
    }



    // single book
    //
    if ( page_type == 'book_single' ) {

        ub_ill_plus_de_book.open_single_book_slide();

    }


    // creer un book
    //$('.btn_modal_creerbook').trigger('click');




});

