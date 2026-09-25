{{-- <head> du portail 2018, repris tel quel. Les balises variables sont
     pilotees par la section @yield('meta'). --}}
<head id="ultra-book">

<meta charset="UTF-8">
<meta http-equiv="Content-Language" content="fr_FR" />
<meta http-equiv="Cache-control"    content="public">
<meta name="viewport" 				content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">


    <title>@yield('title', $marque->titre())</title>
    <meta name="Description" 			content="@yield('description', $marque->description())"/>



<meta name="Keywords" 				content="{{ $marque->nom }},book freelance,portfolio gratuit,book gratuit,création de portfolio,art graphique,portfolio creatif,book de creatifs,book illustrateur,book graphiste,book webdesign,book freelance,book directeurs artistique,créer un book,créer son book"/>
<meta name="application-name" 	    content="{{ $marque->nom }}" />

{{-- og:locale attend la forme POSIX complete (fr_FR), pas le code court. --}}
<meta property='og:locale' 		    content='{{ App\Support\Langue::posix(app()->getLocale()) }}'/>
<meta property='og:type' 			content='website'/>
<meta property='og:title' 			content='@yield('title', $marque->titre())'/>
<meta property='og:url' 			content='{{ url()->current() }}'/>
<meta property='og:site_name'		content='{{ $marque->nom }}'/>
<meta property='og:description' 	content='{{ $marque->description() }}'/>
<meta property='og:image' 			content='https://www.ultra-book.com/img_front/favicon/android-icon-192x192.png'/>

<meta name="twitter:card" 			content="summary" />
<meta name="twitter:site" 			content="&#64;ultra_book" />
<meta name="twitter:title" 		    content="@yield('title', $marque->titre())" />
<meta name="twitter:description"    content="{{ $marque->description() }}"/>
<meta name="twitter:url" 			content="{{ url()->current() }}" />
<meta name="twitter:image" 		    content="https://www.ultra-book.com/img_front/favicon/android-icon-192x192.png" />
<meta name="twitter:creator" 		content="&#64;ultra_book" />


<link href="https://plus.google.com/b/113618166875483060799/+Ultrabook01" rel="publisher" />
<meta name="p:domain_verify" 		content="3141b3250ec1ce665aa24814628e365b"/>


<meta http-equiv="X-UA-Compatible" 	content="IE=edge,chrome=1">
<meta name="viewport" 				content="width=device-width,initial-scale=1">

<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<link rel="apple-touch-icon" sizes="57x57" href="/img_front/favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/img_front/favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/img_front/favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/img_front/favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/img_front/favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/img_front/favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/img_front/favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/img_front/favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/img_front/favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="/img_front/favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/img_front/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/img_front/favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/img_front/favicon/favicon-16x16.png">
<link rel="manifest" href="/img_front/favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/img_front/favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">




<!-- SementicUI #2018-->
<meta name="viewport"   content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">


<link rel="stylesheet"  href="/html_pages_v2018/_/lib/Semantic-UI-CSS-master2.3.1/semantic.min.css">
<link rel="stylesheet"  href="/html_pages_v2018/_/lib/responsive-semantic-ui.min.css">
<style>
    body { background-color: #ebebeb!important;}
</style>


<!-- slides -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.3.3/css/swiper.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.15/jquery.bxslider.min.css"/>
<!-- slider -->
<link rel="stylesheet" href="/html_pages_v2018/_/js/swipebox-master/src/css/swipebox.min.css">

<!-- Icones ubdf #icomoon -->
<link rel="stylesheet" href="/html_pages_v2018/_/font_icon/style.min.css">

<!-- Fontes |Open+Sans|Raleway:300,700|Playfair+Display:400,700,900 -->
<link rel="stylesheet"	href="https://fonts.googleapis.com/css?family=Lato:300,400,700|Source+Sans+Pro:200,300,400,500,600,700">




<!-- commun-->





<!--

	CSS core
	All Less 2019

-->

<link rel="stylesheet" href="/html_pages_v2018/_/css2019/core.css?v=1783005157">









<script type="text/javascript" src="/js_jquery/LABjs-203/LAB.min.js?v=1.0"></script>

<script type="text/javascript">

	/* ub_gal global var js */
	var book_addtohome_l =      false;

	var page_type =         'home',
		page_domaine =      '',
		type_action =       '___',
		layoutType =        'classic',
		conf_view =         '{{ $marque->code }}',
		user_admin_js =      false,

		ext_min =           '',
		lang =              '{{ app()->getLocale() }}',
		user_admin_lang =   '{{ app()->getLocale() }}',
		user_connect =      true,
		reCAPTCHA_key_public  =      @js(config('services.recaptcha.key')),
		user_formule =      false	;




	// function global - ne pas mettre var ensuite
	//
	var ub_motscles_search,
		ub_recherche,
		ub_ill_plus_de_book,
		fm_validator,
		fm_ajax;


	/* stats live + archives  sep 2016 */
		var url_stats_archives = 'https://stats.ultraportfolio.info/';
    var url_stats_archives = 'https://www.ultraportfolio.org/stats/';
	


	// prefix en https
	var url_prefix = /^http:/.test(document.location) ? 'http' : 'https';




	var conf_metier = [],
		conf_statut = []
		;

	// domaines
	//
	conf_metier[0]  = 'Illustration'; 
conf_metier[1]  = 'Illustration jeunesse'; 
conf_metier[2]  = 'Graphisme'; 
conf_metier[3]  = 'Direction artistique'; 
conf_metier[4]  = 'Digital & développement'; 
conf_metier[5]  = 'Art'; 
conf_metier[6]  = 'Photographie'; 
conf_metier[7]  = 'Design objet'; 
conf_metier[8]  = 'Architecture'; 
conf_metier[9]  = 'Web-design'; 
conf_metier[10]  = 'Scénographie'; 
conf_metier[11]  = 'Stylisme'; 
conf_metier[12]  = 'Autre'; 

	// metiers
	//
	conf_statut[0]  = 'A définir'; 
conf_statut[1]  = 'Maison des artistes'; 
conf_statut[2]  = 'Auto-entrepreneur'; 
conf_statut[3]  = 'Salarié'; 
conf_statut[4]  = 'Étudiant'; 
conf_statut[5]  = 'Eurl'; 
conf_statut[6]  = 'Sarl/Sas'; 
conf_statut[7]  = 'Freelance'; 
conf_statut[8]  = 'Autre'; 


	// js translate 2019
	//
	
/* translate inscription/login  _._ ne passe pas */


var __ = {
		__ : {

		'Doit contenir plus de {ruleValue} caracteres' :    'Doit contenir plus de {ruleValue} caractères',
		'Indiquer votre nom' :                              'Indiquer votre nom',
		'Votre nom de book/identifiant doit contenir plus de  {ruleValue} caracteres' :  'Votre nom de book/identifiant doit contenir plus de  {ruleValue} caractères',
		'Caracteres incorrecte' :                           'Caractéres incorrecte',
		'Ce nom existe deja' :                              'Ce nom existe déja',
		'Selectionner un metier ou domaine' :               'Sélectionner un métier ou domaine',
		'Votre mot de passe doit contenir plus de  {ruleValue} caracteres':    'Votre mot de passe doit contenir plus de  {ruleValue} caractères',
		'Vous devez accepter les conditions d’utilisation' : 'Vous devez accepter les conditions d’utilisation',

		'Il ne s’agit pas d’un mail' :                       'Il ne s’agit pas d’un mail',
		'Votre mail doit contenir plus de  {ruleValue} caracteres' : 'Votre mail doit contenir plus de  {ruleValue} caractères',
		'Indiquer votre mail' :                              'Indiquer votre mail',                            /* Please enter a mail */
		'Le montant maximum est inférieur au montant minimum':'Le montant maximum est inférieur au montant minimum',
		'Indiquer une valeur':                               'Indiquer une valeur',                            /* Please enter a value */
		'Indiquez votre identifiant (pas votre mail)' :      'Indiquez votre identifiant (pas votre mail)',    /* Your ID, not your mail please */
		'Champ vide' :                                       'Champ vide',
        'Visuel affiché sur le mini-book' :                  'Visuel affiché sur le mini-book'


		}

};






var ub_msg_core = {

	// domaine UB
	//
	ub_url_http:            url_prefix + '://ubdf2020ssl.localhost:4433',	//url_prefix+'://www.ubdf.com,
	ub_url_http_dom:        'ubdf2020ssl.localhost:4433', 							//'ubdf.com,
	ub_url_prefix:          url_prefix,

	// domaine stats
	//
	ub_url_http_stats:      url_prefix + '://www.extra-book.com',



	msg_supp_memo_book:     'Retirer du Memo-book',
	msg_pas_de_resulat:     'Pas de resultat',
	msg_mon_portfolio:      'Mon portfolio',
	msg_portfolio_complet:  'Portfolio complet',
	msg_memo_message:       'Mémoriser ce message, pour un autre créatif',
	msg_mots_cles:          'Mots clés',
	msg_nom:                'Nom',
	msg_nouveaute:          'nouveauté',
	msg_identifiant_mail:   '<strong>Attention</strong>, indiquer bien<br/>votre <strong>identifiant</strong><br/>( et non pas votre mail...)',
	msg_slide:              '',
	//msg_loading:            '<span style="color:orange">Chargement...</span>',
	//msg_end_loading:        '<div id="fin_selection">Fin de la sélection</div>',
	msg_memo_add_portfolio: '<span class="ub_icone selection_plus tooltip_tipsy" original-title="Ajouter à ma selection"></span>',
	msg_memo_supp_portfolio: 'Effacer de ma sélection',
	msg_book_suivant:       'book suivant',
	msg_book_localisation:  'Localisation',
	msg_book_dejaselection: '<span class="ub_icone selection_fait tooltip_tipsy" original-title="Déja dans ma selection"></span>',
	msg_memo_partage:       'Partage',
	//msg_pasderesultat:      '<h3>Pas de résultat...</h3>Essayez d’enlever une option de recherche',
	msg_input_default:      'Nom, Prénom ou pseudo',
	msg_contacter:          'Contacter',


	// intro JS
	//
	msg_intro_voila:            'Voila! c’est fini.',
	msg_intro_suivant:          'Suivant',
	msg_intro_prec:             'Préc.',
	msg_intro_quitter:          'Quitter',

	// recherche
	msg_3_carteres:             'Indiquez au moins 3 caratères...',
	msg_option_recherche:       'Essayez d’enlever une option de recherche',
	msg_type_recherche_motscles:'Mots clés',
	msg_type_recherche_nom:     'Nom',
	msg_type_recherche_globale: 'Globale',
	msg_type_recherche_in:      'dans',
};





/* ub_gal editeur options des boutons du haut*/

var ub_gal_options = {

			txt_gal_ajouterunegalerie:	false, 	txt_gal_ajouteruneimage: 	    false, 	txt_gal_ajouterunepage:		false, 		var_null: true

	,
	txt_Modifier: 'Modifier',
	txt_Valider: 'valider',
	txt_Annuler: 'Annuler',
	txt_Voir: 'Voir',
	txt_nouvelle_image: 'Nouvelle image',
	txt_nouvelle_gal: 'Nouveau portfolio',
	txt_nouvelle_gal2: 'Nouvelle rubrique',
	txt_eff_img_ok: '',
	txt_eff_img: 'Supprimer l’image',
	txt_gall_add_ok: '',
	txt_gall_del_ok: '',
	txt_gall_edit_ok: ''

	,
	txt_nouvelle_page: 'Nouvelle page'

	,
	txt_quotadepasse: 'Quota d’image dépassé',
	txt_quotadepasse_rub: 'Quota de création de rubrique dépassé',
	txt_abrev_gal: 'Ptf.',
	txt_abrev_gal2: 'Rub.'

	,
	txt_gal_ajouterunerubrique: 'Ajouter une rubrique',
	txt_gal_ajouterunegalerie: 'Ajouter un portfolio',
	txt_gal_ajouteruneimage: 'Ajouter une image',
	txt_gal_ajouterdesimages: 'Ajouter des images',
	txt_gal_ajouterunepage: 'Ajouter une page',
	txt_gal_fichiertroplourd: 'Fichier trop lourd.',
	txt_gal_minimum: 'Vous devez créer au moins un portfolio...'

	,
	txt_img_edit_ok: '',
	txt_miseajour_ok: '',
	txt_enregistrement_ok: '',
	txt_size_nb: 'Le nombre d’images et le poids total correspond à la totalité des images présentes sur votre book - Vous pouvez modifier ce nombre à partir du menu ”Ma formule” '

	,
	txt_notification_ie: 'L’administration des books n’est pas optimisé pour Internet Explorer - Vous devriez plutôt utiliser Firefox, Safari ou Chrome'

	,
	txt_enregistrement_effectue: 'Enregistrement effectué... ',

	// fileuploader.js
	txt_drag_drop:      'Glisser/Deposer une image',
	txt_Telecharger:    'Télécharger'


};









var ub_gal = {

	 txt_Modifier:              'Modifier'
	,txt_Valider:               'valider'
	,txt_Annuler:               'Annuler'
	,txt_Voir:                  'Voir'
	,txt_nouvelle_image:        'Nouvelle image'
	,txt_nouvelle_gal:          'Nouveau portfolio'
	,txt_nouvelle_gal2:         'Nouvelle rubrique'
	,txt_eff_img_ok:            ''
	,txt_eff_img:               'Supprimer l’image'
	,txt_gall_add_ok:		    ''
	,txt_gall_del_ok:		    ''
	,txt_gall_edit_ok:	        ''

	,txt_nouvelle_page:     	'Nouvelle page'

	,txt_quotadepasse:     		'Quota d’image dépassé'
	,txt_quotadepasse_rub:   	'Quota de création de rubrique dépassé'
	,txt_abrev_gal : 			'Ptf.'
	,txt_abrev_gal2 : 			'Rub.'

	,txt_gal_ajouterunerubrique:'Ajouter une rubrique'
	,txt_gal_ajouterunegalerie:	'Ajouter un portfolio'
	,txt_gal_ajouteruneimage: 	'Ajouter une image'
	,txt_gal_ajouterdesimages: 	'Ajouter des images'
	,txt_gal_ajouterunepage:	'Ajouter une page'
	,txt_gal_fichiertroplourd:	'Fichier trop lourd.'
	,txt_gal_minimum:			'Vous devez créer au moins un portfolio...'

	,txt_img_edit_ok:			''
	,txt_miseajour_ok:			''
	,txt_enregistrement_ok:		''
	,txt_size_nb:				'Le nombre d’images et le poids total correspond à la totalité des images présentes sur votre book - Vous pouvez modifier ce nombre à partir du menu \"Ma formule\" '

	,txt_notification_ie:		'L’administration des books n’est pas optimisé pour Internet Explorer - Vous devriez plutôt utiliser Firefox, Safari ou Chrome'

	// nov 2017
	,txt_gal_supp:				'Supprimer la galerie'

};






	/*
	 * Chargement du front 2019. Seuls les scripts reellement executes par
	 * le portail restent : page_type vaut toujours 'home', user_admin_js
	 * false et type_action '___' ; l'administration integree au book et la
	 * messagerie sont servies par l'espace creatif (Livewire).
	 * Retrait progressif de jQuery : voir _doc/17_remplacement_jquery.md.
	 */
	the_LAB = $LAB
		.setOptions({BasePath: "/html_pages_v2018/_/", UseCachePreload: true})
		.script("js_cdn/jquery-1.12.4.min.js")
		.script("js_cdn/jquery-migrate-1.4.1.min.js")
		.script("lib/Semantic-UI-CSS-master2.3.1/semantic.min.js")
		.wait()
		// remplace $.fn.modal de Semantic par les modales Alpine
		.script("js2019/pont_alpine.js")
		.script("https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.3.3/js/swiper.min.js")
		// cookie, JSON, Handlebars, tipsy, easing, scrollTo, Magnific
		.script("js2019/js_allplug2018.js?v=1594989160")
		.wait()
		.script("js2019/js_core_inscription"+ext_min+".js")
		.script("js2019/js_core_pages"+ext_min+".js")
		.script("js2019/js_core_cards"+ext_min+".js")
		.script("js2019/js_core_function"+ext_min+".js")
		.wait()
		.script("js2019/js_core"+ext_min+".js");

</script>






<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-464814-3"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-464814-3');

</script>




