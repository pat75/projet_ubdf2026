{{-- Porte depuis 2011_html_pages_v2/grid2015/_ultrabook__header.tlp.php (_outils/porter_gabarits.py) --}}
<!doctype html>
<html lang="fr-FR" class="no-js">
	<head>
		<meta charset="UTF-8">
		<title><?=ucfirst($b->cont_page_titre);?></title>

		
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		<meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">
		
		<meta name="keywords" content="Ultra-book, creation de book, <?=str_replace(array("[&quot;","&quot;]","&quot;,&quot;"), array("","",","),  $b->cont_page_key);?>"/>
		<meta name="description" content="book <?=$b->cont_page_meta?> <?=(($b->page_type=='accueil')?$b->gal_cont['img'][0]['img_titre_alt']:'');?> <?=(($b->page_type=='news')?$b->gal_cont['img'][0]['img_titre_alt']:'');?>" />

		<!-- icon -->
		<link rel="shortcut icon" href="<?=$b->icone;?>"/>
		<link rel="apple-touch-icon" href="<?=$b->icone_iphone;?>"/>
		<link rel="apple-touch-icon" sizes="72x72" href="<?=$b->icone_ipad;?>" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />


				
		<!--Font -->	
		
		<link href='https://fonts.googleapis.com/css?family=Dosis:400,500,700|Concert+One|Belleza|Belgrano|Quantico|Vollkorn|Codystar|Oleo+Script|Averia+Sans+Libre|Ubuntu+Mono|Economica|Dosis|Ruda|Signika|Oswald|Amatic+SC|Jockey+One|Philosopher|Duru+Sans|Rationale|Medula+One|Sansita+One|Patua+One|Ubuntu+Condensed|Open+Sans' rel='stylesheet' type='text/css'>
		<!--[if IE]>
		<link href='https://fonts.googleapis.com/css?family=Concert+One' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Belleza' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Belgrano' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Quantico' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Vollkorn' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Codystar' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Oleo+Script' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Ubuntu+Condensed' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Averia+Sans+Libre' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Ubuntu+Mono' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Economica' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Dosis' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Ruda' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Signika' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Duru+Sans' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Rationale' rel='stylesheet' type='text/css'>
		<![endif]-->


		<!-- Icones -->
		<link rel="stylesheet" href="/2012_web/grid2015/__/css/fontello-8503d1b6/css/ub-grid-icons.css">


	
		<!-- js+css UB2015 -->	
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/reset.css" />
		<link rel="stylesheet" href="/html_pages_v2018/_/font/mfglabs-iconset-master/css/mfglabs_iconset.css">
	
	
		<!--Grid 2015 CSS-->
		
		<link rel='stylesheet' id='normalize-css'  href='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/css/normalize-min.css?ver=1.0' media='all' />
		<link rel='stylesheet' id='fotorama-css'  href='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/js/fotorama/fotorama.css?ver=4.6.2' media='all' />
		<link rel='stylesheet' id='ub-grid-css'  href='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/css/ub-grid-style.css?ver=1.4' media='all' />
		
		<link rel='stylesheet' id='ub-grid-css'  href='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/js/malihu_scrollbar/jquery.mCustomScrollbar.css' media='all' />
		
		<style type="text/css"><?php grid2015__affiche_custom_css($data_coulBook, $data_coulBGBook); ?></style>

		
		
		
		
		<!-- jQuery-->
		<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js?ver=1.10.1'></script>
		<!-- modernizr  -->
		<script type='text/javascript' src='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/js/modernizr/modernizr-2.7.1.min.js?ver=2.7.1'></script>


		<!-- Grid 2015 JS 
		<script type='text/javascript' src='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/js/iscroll/iscroll.js'></script>	-->	
		<script type='text/javascript' src='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/js/fotorama/fotorama.min.js?ver=4.6.2.1'></script>
		<script type='text/javascript' src='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/js/isotop/isotope.pkgd.min.js?ver=2.0.1'></script>

		<script type='text/javascript' src='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/js/ub-grid-plugin.min.js'></script>
		<script type='text/javascript' src='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/__/js/ub-grid.min.js?ver=1.2'></script>
		
		
		
		<style>
				#page_socle #barrer2014_r {
              	display:none;
          	}

		</style>

		<!-- 		patch_vertical_css_loader -->
		<meta name="patch_vertical_css_loader" content="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/patch_vertical.min.css" />



<?php 
//
// si en mode admin ou pas
//
$url= request()->getHost();
$matches=array();
if (preg_match("/(?:http:\/\/)?(?:www\.)?([a-z0-9\.\-_]+)\.[a-z0-9\-_]{2,}\.[a-z]{2,4}(?:.*)/i", $url, $matches)) {
      $subdomains=explode('.', $matches[1]);
}
//print_r($matches);
//echo request()->query('pr');

if (  ( (request()->cookie('us_pr') !== null) || request()->query('pr')=='true' ) &&   request()->cookie('us_pr_login') == $subdomains[0] && request()->query('pr') != 'public'  ) {	
	\App\Services\Book\Gabarit::ignorer("us_pr", true, time()+3600);	
	\App\Services\Book\Gabarit::ignorer("us_pr_login", $subdomains[0], time()+3600);	
	$b->connection_admin_book = false; /* PORTAGE : edition en phase 5, voir _doc/11 */
} else {
	$b->connection_admin_book = false;
}
?>

<?php  
if ( $b->connection_admin_book ) { 	 ?>	
	
			<style>
			/* ub barre2014 */
			div#page_socle.expanded div#page_base div#mdl_grid_2015 main#grid div.wrapper.wrap-grid,
			div#page_socle.expanded div#page_base div#mdl_grid_2015 div.wrapper-djax main#main.djax div.wrapper
			 {
			    left: 460px;
			    width: calc(90% - 480px) !important;
			}

			</style>


		
<!-- pr 2012 - barre de réglage -->			
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/jquery-ui-1.8.20.custom/css/smoothness/jquery-ui-1.8.20.custom.css" type="text/css">		
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/fontpicker/googlefontpicker.css" type="text/css" />
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/minicolors/jquery.miniColors.css" type="text/css" />

		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_admin_book.css" />

		
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>
		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/bootstrap-tooltip.js"></script>	
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/ui.combobox.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/fontpicker/jquery.googlefontpicker.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/infusion-jQuery-xcolor/jquery.xcolor.min.js"></script>			
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/minicolors/jquery.miniColors.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery.json-2.2.min.js"></script>	
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/bootstrap-popover.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/jquery.cookie.js"></script>		

		
		<!-- upload 22sep2014 -->
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/file-uploader/3.7.0/fineuploader.min.css" />
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/file-uploader/3.7.0/fineuploader-jquery.min.js"></script>
		
		<!-- cke editor 22sep2014 voir conf en bas -->
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/ckeditor/adapters/jquery.js"></script>
		
		
		<!-- barre reglages -->
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/css/ub_pr_2012.css" type="text/css" />
		
		<!-- sweet alert -->
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/sweetalert-master/lib/sweet-alert.js"></script>
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/js_jquery/sweetalert-master/lib/sweet-alert.css" type="text/css" />
		  
		  
		
		<style type="text/css">
			.ub_gal_base_box {				    background: url("<?=$b->url_abs_site;?>/2011_user_admin_img/motif_gris.gif") repeat scroll 0 0 #BBBBBB;}    
		    .gal ul.gal_n0 li.gal_box h3 {    	background: url("<?=$b->url_abs_site;?>/2011_user_admin_img/gray-grad.png") repeat-x scroll left top #DFDFDF;}
		    .fontbox {   						width: 126px;   margin: 1px  7px 0; }
		</style>
		<script type="text/javascript">CKEDITOR.disableAutoInline = true;</script>
		
		
		<!-- tooltip image upload -->
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/tipsy/jquery.tipsy-min.js"></script>
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/tipsy/tipsy.css"/>


<!-- barre de réglage fin -->
		
		
<?php  } ?>


<!-- stats live juil2016 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.4.5/socket.io.min.js" type="text/javascript"></script>




<!-- js core modele portfolio 2012 -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js/ub_book_core_mdl2012.min.js?ver=1.2" type="text/javascript"></script>



<script type="text/javascript">


/* stats live + archives  sep 2016 */
var stats_url_stats_archives = 'https://stats.ultraportfolio.info/';

var stats_us_id =					'<?=$b->us_dir;?>';
var stats_vignette =				'<?=$b->rep_pref;?>us_pf_img_vignette.gif';
var stats_nom_prenom =		'<?=$b->nom." ".$b->prenom;?>';
var stats_visiteur =				'<?=request()->cookie("us_pr_login");?>';
var stats_us_type =				'<?=$b->us_type;?>';


// page reglage load conf
var ub_pr = '';
var ub_pr_conf_init = 		<?=$b->cont_conf2012;?>;
      ub_pr_conf_init = 		ub_pr_conf_init['data'];
    
var ub_pr_conf_url = 		'<?=$b->url_abs_site;?>';
var ub_page_type = 			'<?=$b->page_type;?>';
var ub_page_mdl = 			'<?=$b->modele_book;?>';
var ub_navigateur_client = 	'<?=$b->navigateur_client;?>';


var fotorama = 				true;
var fotorama_reload = 		false;
var ele_fotorama_css = 		{};

var br_admin = 				false;

var ub_barre_r = 			'<?=(request()->query('pr') == 'public'?'hide':'show');?>';	// br hide


// gestion des bug - sep 2015
/*
window.onerror = function(msg, url, line) {
 		//demande de détails
	var details = console.log(
		'Une erreur a eu lieu ! \n\n'+
		'Essayer de vider le cache de votre navigateur (Menu préf.),\n'+
		'ou connectez-vous en navigation privée\n\n\n'+
		'Si celle-ci persiste contacter le support avec les informations ci-dessous :'+
		'\n' +url+' | ' +line+' | ' +msg
	);
};
*/

$(document).ready(function(){

		// br hide memo	
		if (ub_barre_r=='hide') localStorage.setItem("ub_barre_r",""); 	
		
		
		// page reglage
		ub_pr_data.init_conf();		
			
		});


</script>	 

</head>
