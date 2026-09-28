{{-- Porte depuis 2011_html_pages_v2/responsive/ultrabook_2014_type.tlp.php (_outils/porter_gabarits.py) --}}
<!doctype html>

<!--[if lt IE 7 ]> <html class="ie ie6 ie-lt10 ie-lt9 ie-lt8 ie-lt7 no-js" lang="fr-FR"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7 ie-lt10 ie-lt9 ie-lt8 no-js" lang="fr-FR"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8 ie-lt10 ie-lt9 no-js" lang="fr-FR"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9 ie-lt10 no-js" lang="fr-FR"> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" lang="fr-FR"><!--<![endif]-->
<!-- the "no-js" class is for Modernizr. --> 

<head>	
<title><?=$b->cont_page_titre;?></title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="keywords" content="Ultra-book, creation de book, <?=str_replace(array("[&quot;","&quot;]","&quot;,&quot;"), array("","",","),  $b->cont_page_key);?>"/>
<meta name="description" content="book <?=$b->cont_page_meta?> <?=(($b->page_type=='accueil')?$b->gal_cont['img'][0]['img_titre_alt']:'');?> <?=(($b->page_type=='news')?$b->gal_cont['img'][0]['img_titre_alt']:'');?>" />
<meta name="viewport" content="width=device-width,initial-scale=1">



<!-- icon -->
<link rel="SHORTCUT ICON" href="<?=$b->icone;?>"/>
<link rel="apple-touch-icon" href="<?=$b->icone_iphone;?>"/>
<link rel="apple-touch-icon" sizes="72x72" href="<?=$b->icone_ipad;?>" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<?php /* à terminer <!-- icon 
<link rel="apple-touch-icon" sizes="114x114" href="/img_front/touch-icon-iphone4.png" />
<link rel="shortcut icon" sizes="196x196" href="/img_front/touch-icon-iphone_196.png" />
-->*/?>





<link href='https://fonts.googleapis.com/css?family=Concert+One|Belleza|Belgrano|Quantico|Vollkorn|Codystar|Oleo+Script|Averia+Sans+Libre|Ubuntu+Mono|Economica|Dosis|Ruda|Signika|Oswald|Amatic+SC|Jockey+One|Philosopher|Duru+Sans|Rationale|Medula+One|Sansita+One|Patua+One|Ubuntu+Condensed|Open+Sans' rel='stylesheet' type='text/css'>
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
<?php /*  IE is limited to 32 stylesheets. - sinon bug ds les js avec styleSheet.cssText*/?>
<?php /*
<link href='https://fonts.googleapis.com/css?family=Jockey+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Philosopher' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Medula+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Sansita+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Patua+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
<![endif]-->
*/?>

<!-- concatenate and minify for production -->
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/reset.css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_style_01.css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_layout_01.css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_style_responsive2014.css" />


<!-- Icones -->
<link rel="stylesheet" href="/html_pages_v2018/_/font/mfglabs-iconset-master/css/mfglabs_iconset.css">


<?php /*
<!--

  
/Applications/__script_minify_css_js/script_minify.sh 
  
 
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/css/core_book_2012.css" type="text/css" />


2012_web/<?=$b->url_mdl;?>/
					css/
					js/
					img/
					
				
				
				plus grande parts aux images
				menu roll-over
				principe de de slide personnalisable pour les portfolios
					
					

  
 Menu default
 Titre	border-bottom: 1px dotted rgb(204, 204, 204); font-family: Ruda; color: rgb(224, 149, 69);
 Rub	font-family: Ruda; color: rgb(0, 0, 0);


-->
*/?>

<!-- Modernizr -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/modernizr-2.7.1.min.js"></script>

<!-- Jquery -->
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>

<!--toTop -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/sksmatt-UItoTop-jQuery-Plugin/js/jquery.ui.totop.min.js"></script>
	
<!-- js tooltip 2012 -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/bootstrap-tooltip.js"></script>

<!-- enquire responsive js + media.match for IE -->
<script type="text/javascript">
			Modernizr.load([
		    {
		        test: window.matchMedia,
		        nope: "<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/media.match.js"
		    },		
		    "<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/enquire.min.js"
			]);
</script>


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


if (  ( (request()->cookie('us_pr') !== null) || request()->query('pr')=='true' ) &&   request()->cookie('us_pr_login') == $subdomains[0]  && request()->query('pr')!='public' ) {	
	\App\Services\Book\Gabarit::ignorer("us_pr", true, time()+3600);	
	\App\Services\Book\Gabarit::ignorer("us_pr_login", $subdomains[0], time()+3600);	
	$b->connection_admin_book = false; /* PORTAGE : edition en phase 5, voir _doc/11 */
} else {
	$b->connection_admin_book = false;
}
?>

<?php  
if ( $b->connection_admin_book ) { 	 ?>	
	
		
<!-- pr 2012 - barre de réglage -->			
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/jquery-ui-1.8.20.custom/css/smoothness/jquery-ui-1.8.20.custom.css" type="text/css">		
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/fontpicker/googlefontpicker.css" type="text/css" />
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/minicolors/jquery.miniColors.css" type="text/css" />

		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_admin_book.css" />

		
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>	
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/ui.combobox.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/fontpicker/jquery.googlefontpicker.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/infusion-jQuery-xcolor/jquery.xcolor.min.js"></script>			
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/minicolors/jquery.miniColors.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery.json-2.2.min.js"></script>	
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/bootstrap-popover.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/jquery.cookie.js"></script>		
<?php /*
 * 
 * 
 *  tous les autres upload posent des pb de cross somaine
 * 
<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/dropzone-3.10.2/dropzone.min.js"></script>	
<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/Valums-File-Uploader-file-uploader-2b/client/fileuploader.js"></script>	

 * 
 * 
 upload 22sep2014

<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/Valums-File-Uploader-file-uploader-2b/client/fileuploader.js"></script>	

<script type="text/javascript">
    window.FileAPI = {
                
        debug: true // debug mode
        , cors: true
        , staticPath: '/js_jquery/jquery.fileapi-master/FileAPI/' // path to *.swf
    };
</script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/jquery.fileapi-master/FileAPI/FileAPI.min.js"></script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/jquery.fileapi-master/FileAPI/FileAPI.exif.js"></script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/jquery.fileapi-master/jquery.fileapi.js"></script>

*/?>


<!-- upload 22sep2014 -->
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/file-uploader/3.7.0/fineuploader.min.css" />
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/file-uploader/3.7.0/fineuploader-jquery.min.js"></script>

<!-- cke editor 22sep2014 voir conf en bas -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/ckeditor/adapters/jquery.js"></script>

<!-- Icones -->
<link rel="stylesheet" href="/html_pages_v2018/_/font/mfglabs-iconset-master/css/mfglabs_iconset.css">


<!-- br -->
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





<!-- barre de réglage fin -->
		
		
<?php  } ?>
	


<!-- js core modele portfolio 2012 _min  -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js/ub_book_core_mdl2012.js" type="text/javascript"></script>

<!-- fotorama.css & fotorama.js. -->
<?php /*
<link  href="http://fotorama.s3.amazonaws.com/4.4.9/fotorama.css" rel="stylesheet">
<script src="http://fotorama.s3.amazonaws.com/4.4.9/fotorama.js"></script>
// ne pas prendre la version online à cause du tracker watch.js
*/?>
<link  href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/fotorama-4.6.2/fotorama.css" rel="stylesheet">
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/fotorama-4.6.2/fotorama_nowtach.js"></script>


<!-- menu responsive -->
<link href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/meanMenu-master/meanmenu_min.css" rel="stylesheet"  media="all" />
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/js/meanMenu-master/jquery.meanmenu_min.js"></script>


<!-- stats live juil2016 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.4.5/socket.io.min.js" type="text/javascript"></script>




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
var ub_pr_conf_init = <?=$b->cont_conf2012;?>;
    ub_pr_conf_init = ub_pr_conf_init['data'];
    
var ub_pr_conf_url = '<?=$b->url_abs_site;?>';
var ub_page_type = '<?=$b->page_type;?>';
var ub_page_mdl = '<?=$b->modele_book;?>';
var ub_navigateur_client = '<?=$b->navigateur_client;?>';


var fotorama = true;
var fotorama_reload = false;
var ele_fotorama_css = {};

var br_admin = false;


$(document).ready(function(){
	

		$('#page_container').css({'opacity':'0'});



		// stats live 9sep - 2016
		//
		if (typeof io !== 'undefined') {
			socket = io(stats_url_stats_archives);
			ub_image.statlive('');
		}



		// menu
		ub_menu.init();	
		
		// menu - responsive		
		$('nav#nav_').meanmenu({
	    	meanScreenWidth: "979",
	    	onePage: true,
	    	meanRemoveAttrs: true,
	    	meanRemoveStyle:true,	    	
	    	meanMenuContainer: '#page_base',
	    	meantitle:'<?=$b->cont_book_titre;?>'
    	});		

		// page reglage
		ub_pr_data.init_conf();		
		
		
		// gallerie - fotorama
		// 
		
		if ( ub_page_type == 'portfolio' )		{ 
			fotorama_reload = true;
			ub_fotorama_mdl2014.init();		
			}		
	
		
			
		// ScrollTo top
		ub_plugin.UItoTop({min: 200});	
				
		// png fix
		$().pngFix();
	
		// iPhone/iPad URL bar hides
		ub_plugin.iphone();	
	
				
});
</script>	 


</head>
<body class="ub_couleur_fond bg_image" rel="<?=$b->url_abs_site.$b->cont_bg;?>">

<a name="top_page"></a>


<div id="page_socle"> 
	
<!-- reglage de la page -->	
<?php  if ( $b->connection_admin_book ) { 
	//print_r($_SESSION);
	?>
	<!-- reglage de la page 2014 -->	
	
	<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_2014_barredereglage.tlp.php');?>
	
	<?php 
	}
	?>
<!-- reglage de la page fin -->

<!--
<iframe id="responsive_iframe" src="about:blank"  frameborder="0" class="viewport" style="display: block; width: 825px; height: 496px;"></iframe>
-->

<div id="page_base"> 


<div class="wrap wider full-overlay-main">
		
		
			
			<div class="grid grid-menu">
				<div class="debug+ unit one-fifth" id="ub_menu">
					<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_2012_menugauche.tlp.php'); ?> 
				</div>
				<div class="debug+ unit four-fifths " id="ub_cont">
					<?php 
		            //echo 'ok+++++++++++++'.$b->page_type ;
		            //$b->page_type = 	'portfolio'	;		
		            switch ($b->page_type) {
		                case 'accueil':
						default:									
		                    include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_2012_accueil.php');
		                    break;
		                case 'portfolio':							
		                    include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_2012_portfolio.php');
		                    break;           
		                case 'news':
		                    include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_2012_news.php');
		                    break; 
						 case 'contact':
		                    include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_contact.php');
		                    break;   	    
		            }
					?>
				
				
					<div id="page_footer"  class="grid hide-on-mobiles-tablette ">
					<div class="unit half">        
								    <?php if ($b->us_formule==1 && $b->cont_piedpage=='[invisible]'  || $b->inc_action_view == 'df'  ) { ?>
								    	
								    <?php  } else { ?>	
										
										<?php if ($b->us_formule==1 && !empty($b->cont_piedpage) ) { ?>
											<?=htmlspecialchars_decode($b->cont_piedpage,ENT_QUOTES);?>					
										<?php  } else { ?>
											<a href="http://ultra-book.fr" class="footer_ub"><strong>Ultra-book</strong> | création de book en ligne <span>2014</span></a>
										<?php  } ?>
					</div>					
					<div class="unit half" > 	
										<?php if ($b->us_partage_lien=='1') { ?>	
										 		<?php  list($tmp_titre, $tmp_url) = book_socializer ($b->cont_page_titre); ?>              
									            <div class="social-bookmarks-services">
												    <div class="social-bookmarks-service social-bookmarks-service-facebook">
												        <a href="http://www.facebook.com/sharer.php?u=<?=$tmp_url;?>"  title="Partager sur 'Facebook'" rel="nofollow" target="_blank">Partager sur 'Facebook'</a>
												    </div>				    
												    <div class="social-bookmarks-service social-bookmarks-service-twitter">
												        <a href="http://twitter.com/home?status=<?=$tmp_url;?>"  title="Partager sur 'Twitter'" rel="nofollow" target="_blank">Partager sur 'Twitter'</a>
												    </div>				
												    <div class="social-bookmarks-service social-bookmarks-service-linkedin">
												        <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>"   title="Partager sur 'LinkedIn'" rel="nofollow" target="_blank">Partager sur 'LinkedIn'</a>
												    </div>				
												    <div class="social-bookmarks-service social-bookmarks-service-viadeo">
												        <a href="http://www.viadeo.com/shareit/share/?url=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>&amp;urllanguage=fr" title="Partager sur 'Viadeo'" rel="nofollow" target="_blank">Partager sur 'Viadeo'</a>
												    </div>
												    <div class="social-bookmarks-service social-bookmarks-service-google_bookmarks">
												        <a href="http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>"  title="Partager sur 'Google'" rel="nofollow" target="_blank">Partager sur 'Google'</a>
												    </div>
												    <div class="social-bookmarks-service social-bookmarks-service-pinterest">
												    	<a class="pin-it-button"  href="javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)})());">
												    		Pin It!
												    	</a>
												    </div>				    
												</div>		
										<?php  } ?>
									<?php  } ?>			
					</div>
					</div>



				</div>
			</div>
			<br clear="all"/>


<div style="display:none">
<!-- bloc data contenu accueil - uniquement affichee pour l'admin -->
<?php  if ( (request()->cookie('us_pr') !== null) or request()->query('pr')=='true' ) foreach ($b->gal_cont_accueil as $cont_accueil) { ?>
	<div id="accueil_contenu__<?=$cont_accueil['img_id'];?>">
		<?php //=htmlspecialchars_decode( $cont_accueil['img_html'], ENT_QUOTES );?>
		<?=htmlspecialchars_decode(  book_actu_txt($cont_accueil['img_html'],$b->url_abs_site), ENT_QUOTES );?>
	</div>
<?php }?>
<!-- bloc data fin -->
</div>	



</div>
</div>


<div id="fotorama_full"></div>




</div>

<?php 
// stats : pixel de comptage interne (voir StatsBookController)
//
$stats_action =		    'add';
$stats_st_champ =		'st_book';
$stats_us_login =		$b->us_dir;
$stats_st_cles = 		md5($b->us_dir . 'pat75ub2012publique' );
$stats_i = 				rand(0,9999);
$stats_img = '/ubstats.gif?r='.$stats_i;
?>
<img src="<?=$stats_img;?>" width="1" height="1" style="display:none"/>



<?php /* Analytics : ga.js a ete arrete par Google en 2024. Seule la
   mesure propre au createur subsiste, en GA4, quand il en a declare une. */ ?>
<?php if (! empty($b->cont_analytic)) : ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=e($b->cont_analytic)?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?=e($b->cont_analytic)?>');
</script>
<?php endif; ?>

<!-- js pngfix -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/pngfix/jquery.pngFix.pack.js"></script>	


</body>
</html>