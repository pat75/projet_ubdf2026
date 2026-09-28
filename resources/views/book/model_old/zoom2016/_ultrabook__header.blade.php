{{-- Porte depuis 2011_html_pages_v2/zoom2016/_ultrabook__header.tlp.php (_outils/porter_gabarits.py) --}}
<!doctype html>
<html lang="fr-FR" class="no-js">
	<head>
		<meta content="text/html; charset=UTF-8" name="Content-Type" />
		
		<title><?=ucfirst($b->cont_page_titre);?></title>
		
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		<meta name="viewport" id="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=no,minimal-ui">
		
		<meta name="keywords" content="<?=__('Ultra-book, creation de book,')?> <?=str_replace(array("[&quot;","&quot;]","&quot;,&quot;"), array("","",","),  $b->cont_page_key);?>"/>
		<meta name="description" content="book <?=$b->cont_page_meta?> <?=(($b->page_type=='accueil')?$b->gal_cont['img'][0]['img_titre_alt']:'');?> <?=(($b->page_type=='news')?$b->gal_cont['img'][0]['img_titre_alt']:'');?>" />

		<!-- icon -->
		<link rel="shortcut icon" href="<?=$b->icone;?>"/>
		<link rel="apple-touch-icon" href="<?=$b->icone_iphone;?>"/>
		<link rel="apple-touch-icon" sizes="72x72" href="<?=$b->icone_ipad;?>" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		
		<!-- microdata -->
		<?php  
			$tmp_description = 	$b->cont_page_meta.(($b->page_type=='accueil')?$b->gal_cont['img'][0]['img_titre_alt']:'').(($b->page_type=='news')?$b->gal_cont['img'][0]['img_titre_alt']:'');
			$tmp_url = 			request()->getHost().request()->getPathInfo();
			
			if (preg_match("/accueil|portfolio/",$b->page_type)) {
				$tmp_kk =  			 @\App\Services\Book\Php7::key($b->gal_cont['gal']['img']);
				$tmp_img = 			 $b->url_abs_site.$b->rep_img900.$b->gal_cont['gal']['img'][$tmp_kk][0]['img_fichier'];
				$tmp_img_tw =		 $b->url_abs_site.$b->rep_img320.$b->gal_cont['gal']['img'][$tmp_kk][0]['img_fichier'];
			} else {
				$tmp_img = 			(preg_match("#http#",$b->visuel_accueil)?'':$b->url_abs_site.$b->rep_pref).$b->visuel_accueil;
			}
			
			if ( ! empty($tmp_img) && ! preg_match("#\/$#", $tmp_img) ) 	list($tmp_w,$tmp_h) = @@getimagesize($tmp_img);
				
		?>
		
		<meta property="og:locale"				   	 content="fr_FR" />
		<meta property="og:type"                   content="website" />
		<meta property="og:title"                  content="<?=ucfirst($b->cont_page_titre);?>" />
		<meta property="og:image"                  content="<?=$tmp_img;?>" />
		<meta property="og:description"            content="<?=$tmp_description;?>" />
		<meta property="og:url"                    content="<?=$tmp_url;?>" />
		<meta property="og:image:width"            content="<?=$tmp_w;?>" />
		<meta property="og:image:height"           content="<?=$tmp_h;?>" />
		<meta property="og:site_name" 			    content="<?=ucfirst($b->cont_page_titre);?>" />
		
		<meta name="twitter:card" 						 content="summary" />
		<meta name="twitter:title" 					 content="<?=ucfirst($b->cont_page_titre);?>" />
		<meta name="twitter:description" 			 content="<?=$tmp_description;?>" />
		<meta name="twitter:url" 			 			 content="<?=$tmp_url;?>" />
		<meta name="twitter:image" 					 content="<?=$tmp_img_tw;?>" />
		<meta name="twitter:image:width"           content="<?=$tmp_w/2;?>" />
		<meta name="twitter:image:height"          content="<?=$tmp_h/2;?>" />
				
		
				
		<!--Font A  voir à la fin -->			
		<link href='https://fonts.googleapis.com/css?family=Dosis:400,500,700' rel='stylesheet' type='text/css'>
		
	
		<!-- js+css UB2015 -->	
		<link rel="stylesheet" href="/html_pages_v2018/_/font/mfglabs-iconset-master/css/mfglabs_iconset.css">
	
	
		<!--Zoom 2016 CSS -->	
		<link rel="stylesheet" type="text/css" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/normalize_plus.css" />
		<link rel="stylesheet" type="text/css" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_layout_01.css" />	
		<link rel="stylesheet" type="text/css" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/mdl_zoom.css" />
					
		
		
		<!--Zoom 2016 JS -->
		<script src="https://maps.googleapis.com/maps/api/js?key=<?=$maps_googleapis?>" type="text/javascript"></script>


		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/2.8.3.modernizr.min.js"></script>
		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/jquery.min.js"></script>
		
		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/TweenMax.min.js"></script>
		
		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/isotop_min_v1.5.26.js"></script>

		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/imagesloaded.pkgd.min.js"></script>
		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/color-thief.min.js"></script>	
		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/jquery.colourbrightness.min.js"></script>		
		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/djax_jquery.djax.js"></script>
		
		<link  href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/fotorama.css" rel="stylesheet">
		<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/fotorama.js"></script>




		<style>						
		/* bando top */	 
		#page_socle #barrer2014_r {  display:none; }
		 		
  		
        /* bg */  		
        body { 			background-color: <?=$b->obj_cont_data->{'.ub_couleur_fond'}->backgroundColor?> } 
        .base .iso_grid .grid_item { 	border-color: <?=$b->obj_cont_data->{'.ub_couleur_fond'}->backgroundColor?> }
        .base .iso_grid .grid_item a .seen_triangle { border-right-color: <?=$b->obj_cont_data->{'.ub_couleur_fond'}->backgroundColor?>; }
        	
        /* bando top */	
        .bg {  			background-color: <?=$b->obj_cont_data->{'.ub_couleur_nav'}->color?> } 	   	
        		
		</style>		
	

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
if ( $b->connection_admin_book  ) { 	 ?>	
	
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

		  
		<script type="text/javascript">CKEDITOR.disableAutoInline = true;</script>
		
		
		<!-- tooltip image upload -->
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/tipsy/jquery.tipsy-min.js"></script>
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/tipsy/tipsy.css"/>


<!-- barre de réglage fin -->
		
		
<?php  } ?>

<!-- stats live juil2016 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.4.5/socket.io.min.js" type="text/javascript"></script>

<!-- unveil -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/jquery.unveil.min.js"></script>

<!-- all plug-in -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/mdl_all_plugin<?=$js_ext_date?>" type="text/javascript"></script>

<!-- js core modele portfolio 2012 =réglages de la conf -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js/ub_book_core_mdl2012<?=$js_ext_date?>" type="text/javascript"></script>

<!-- isotop_extend -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/isotop_extend<?=$js_ext_date?>"></script>

<!-- Mdl Zoom -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/mdl_zoom<?=$js_ext_date?>"></script>









<?php 	//print_r($b);?>
<script type="text/javascript">


/* stats live + archives  sep 2016 */
var stats_url_stats_archives = 'https://stats.ultraportfolio.info/';

var stats_us_id =					'<?=$b->us_dir;?>';
var stats_vignette =				'<?=$b->rep_pref;?>us_pf_img_vignette.gif';
var stats_nom_prenom =			'<?=$b->nom." ".$b->prenom;?>';
var stats_visiteur =				'<?=request()->cookie("us_pr_login");?>';
var stats_us_type =				'<?=$b->us_type;?>';



// page reglage load conf
var ub_pr = '';
var ub_pr_conf_init = 		<?=$b->cont_conf2012;?>;
	 ub_pr_conf_init = 		ub_pr_conf_init['data'];


var ub_pr_conf_url = 		'<?=$b->url_abs_site;?>';
var ub_page_type = 			'<?=$b->page_type;?>';
var ub_page_mdl = 			'<?=$b->modele_book;?>';
var ub_navigateur_client = '<?=$b->navigateur_client;?>';


var fotorama = 				true;
var fotorama_reload = 		false;
var ele_fotorama_css = 		{};

var br_admin = 				false;
var ub_barre_r = 				'<?=(request()->query('pr') == 'public'?'hide':'show');?>';	// br hide

var user_lng = 				'<?=$b->user_lng;?>';
var user_lat = 				'<?=$b->user_lat;?>';

var IsMobile = 				'<?=$b->IsMobile;?>';



$(document).ready(function(){


	//console.log(ub_pr_conf_init.ptf_activer_iso_category.ptf_activer_iso_category);

	// cas de var non definie
	if ( ub_pr_conf_init.ptf_activer_iso_category == undefined ) {
		ub_pr_conf_init.ptf_activer_iso_category = {
			ptf_activer_iso_category: false
		}
	}
	var isotop_conf =  (	ub_pr_conf_init.ptf_activer_iso_category.ptf_activer_iso_category =='true')? 'catogory':'classique';
	ub_image.iso_layout_selecteur = isotop_conf;


	// init UB mdl Zoom
	ub_image.ub_init();


	// br hide memo
	if (ub_barre_r=='hide') localStorage.setItem("ub_barre_r","");

	// page reglage
	ub_pr_data.init_conf();


});
</script>


</head>

<body class="pushmenu-push ub_couleur_fond bg_image" rel="<?=$b->url_abs_site.$b->cont_bg;?>" id="<?=$b->page_type?>" >


<?php /**/?>
<div id="page_socle"> 

<!-- reglage de la page -->	
<?php if ( $b->connection_admin_book ) {
	// print_r($_SESSION);
	?>
	<!-- reglage de la page 2015 -->		
	<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/_ultrabook__barredereglage.tlp.php');?>	
	
<?php  } ?>
<!-- reglage de la page fin -->


<div id="page_base" class="" >	
<a id="barrer2014_r" class="control-panel collapse-sidebar"><i class="icon-chevron_right icon2x"></i></a>	




<!-- PushMenu -->
<nav class="pushmenu pushmenu-left">
    <h3><?=__('Menu')?></h3>
    <div id="nav_principal_mobile"></div> 			
</nav>


<!-- Fotorama -->
<div id="fotorama_overlay"></div>
<div id="fotorama_close" class="the_icone"></div>
<div id="fotorama" class="fotorama"
		data-width="100%"
        data-height="95%"
        data-hash="true"
        data-nav="false"
        data-auto="false"
        data-trackpad="true"
		data-keyboard="true"
		data-loop="true"
        >     
</div>

<a name="top_page"></a>




<!-- base -->
<div class="base">

<section id="contenu_top" class="group">	
<div class="grid">
	<div class="unit whole bg">
		
		<div id="pushmenu_bouton">
		<div  class="burger"></div>
		</div>
		
		<!-- logo -->
		<?php 
		$tmp_img = 	 	(preg_match("#http#",$b->visuel_accueil)?'':$b->url_abs_site.$b->rep_pref).$b->visuel_accueil;



		//$protocol = stripos('HTTP/1.1','https') === true ? 'https://' : 'http://';
		//$tmp_img = $protocol.request()->getHost().$b->rep_pref.$b->visuel_accueil;

		if ( ! empty($tmp_img) && ! preg_match("/deleted$|\/$/i", $tmp_img) ) {
			/*
			list($tmp_w,$tmp_h) = @@getimagesize($tmp_img);
			if (empty($tmp_w)) $tmp_w = 250;
			$tmp_style = " style='height:auto;max-width:${tmp_w}px;max-height:250px;' ";
			*/

			$tmp_img = 		$tmp_img.'?'.date("Gi");

			} else {
			$tmp_img = $b->url_abs_site.'/img_default/ultra-book_default_160x160.png';
		}
		//echo $tmp_img;
		?>
		
		<div id="top_visuel" class="img-book show-fade">						
			<a href="/accueil" class="img_file_ajax_link ">
				<div id="visuel_accueil" class="img_file_ajax_FineUp <?=($b->visuel_accueil == 'deleted')?'img_file_ajax_deleted':''?>" 
					data-fileapi_id="<?=$b->visuel_accueil_key;?>" 
					original-title="<?=__('Vous pouvez déposer ici votre visuel pour - Accueil - par un glisser-poser ( Format: jpeg, Gif en RVB - Poids Max.:')?>
								<?=$b->visuel_accueil_sizelimit?> <?=__('Ko - Taille Max.:')?> <?=$b->visuel_accueil_width?>x<?=$b->visuel_accueil_height?> pixels )"
								data-img_default="/img_default/ultra-book_default_160x160.png" >
					  <?php  if ($b->visuel_accueil != 'deleted') { ?>
					  <img <?php /*=$tmp_style;*/?>
						  src="<?=$tmp_img?>"
						  alt="<?=$b->cont_page_titre;?>"
						  class="img_file_modify"
					  />
					  <?php }?> 	
				</div> 
			</a>
		</div>
		<!-- /logo -->
		
		<!-- infos-book -->			
		<!-- cke 1 -->
		<div id="top_titre" class="cont_cke_edit infos-book">
			
			<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_menu_gauche" >				
			<?=$b->ed_dom_txt->cont_menu_gauche;?>					
			</div>
			
		</div>							
		<!-- /infos-book -->
							
							
		<nav id="nav_principal">			

			<!-- partage -->	
			<?php  if( ! $b->connection_admin_book && ! $b->obj_cont_data->ptf_activer_sociaux->ptf_activer_sociaux ) {					
			} else { ?>
				<div id="partage" class="<?=($b->obj_cont_data->ptf_activer_sociaux->ptf_activer_sociaux)?'':'hide';?>">
				<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_partage.tlp.php');?>
				</div>
			<?php }?>
			<!-- /partage -->
			
		<ul>
			<li class="<?=(preg_match("/accueil|portfolio/",$b->page_type))?'open':'';?>">
				<a href="/accueil" id="link_accueil" class="ub_menu_titre link_accueil colourBrightness" title="<?=__('Portfolio')?>" >
					Portfolio
				</a>
				<div class="parent">
						<div class="child selecteur"></div>
						<div class="child sous_menu">	
							
							<div class="button-group filter-button-group">
								
							  <div class="button is-checked colourBrightness" data-filter="*"><?=__('Tous')?></div>
							  <?php 
								// liste des ptf
								$tmp = '';
								$array_rub = $b->gal_cont['gal'];								
								if (is_array($array_rub))  foreach ($array_rub as $k=>$rub) {	
								    if (is_array($array_rub['img'][$rub['rub_id']]) )	{   // selection rub suivant le lien /*&& $rub['rub_id'] == $b->rub_id */

										 //$rub_name_url = strtolower (rawurlencode($rub['rub_nom']));
										//$rub_name_url = strtolower (filter_var (preg_replace("/( |\.)+/",'-',$rub['rub_nom']), FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_HIGH));


										 $rub_name_url = strtolower (preg_replace("/(\s|\/|&|\(|\)|\"|\'|\.|\+|\||@|;|,|#|!)+/", "-", $rub['rub_nom']) );
										 $rub_name_url = filter_var ($rub_name_url, FILTER_SANITIZE_URL);

										 //echo $rub_name_url."\n";
										 $tmp .= '<div class="button colourBrightness" data-filter=".'.$rub_name_url.'">'.$rub['rub_nom'].'</div>';
									 }
									}									
								echo $tmp;
								?>
							</div>	
													
						</div>
					</div>
				
			</li>
			<li  class="<?=(preg_match("/news/",$b->page_type))?'open':'';?>">
				<!-- nav actu -->
				<a  href="/actualites" class="ub_menu_titre link_bio colourBrightness" id="link_bio" title="<?=__('Actualités')?>">
					Actualités
				</a>
				<div class="parent">
						<div class="child selecteur"></div>						
				</div>
			</li>
			<li  class="<?=(preg_match("/contact/",$b->page_type))?'open':'';?>">
				<a href="/contact" id="link_contact" class="ub_menu_titre link_contact colourBrightness"><?=__('Contact')?></a>
				<div class="parent">
						<div class="child selecteur"></div>						
				</div>
			</li>
		</ul>
		</nav>
		
	</div>
</div>
</section>