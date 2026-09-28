{{-- Porte depuis 2011_html_pages_v2/ultrabook_iphone_portfolio.php (_outils/porter_gabarits.py) --}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	 
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
	
	<title><?=$b->cont_page_titre;?></title>
	<?php /*
	<!-- 
		JAIPHO BETA version 0.55.00 - iPhone optimized javascript gallery
		Check on http://www.jaipho.com/ for latest news and source updates 

		<link href="jaipho/Themes/Default/jaipho.css" rel="stylesheet" type="text/css"/>-->
		*/?>	
		<link href="<?=$b->url_abs_site;?>/2011_iphone/jaipho/Themes/Default/jaipho.css" rel="stylesheet" type="text/css"/>

	<!--
		IPAD FIX - TO BE ENABLED IF IPAD USER IS DETECTED
		<link href="jaipho/Themes/Default/jaipho-ipad.css" rel="stylesheet" type="text/css"/>
	-->
			
			<!--
			
			The whole source in one file.
		
			
			
				<script src="jaipho/jaipho-0.55.00-preload-src.js" type="text/javascript"></script>-->					
			    <script src="<?=$b->url_abs_site;?>/2011_iphone/jaipho/jaipho-0.55.00-preload-src.js" type="text/javascript"></script>
	 
	 		<?php /*
			<!--	
			
			Developer version. All classes are in separate files. Much easier to debug.
		
			
				<script src="jaipho/Jph/Util/Touches.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/PreloaderItem.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/Preloader.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/OrientationManager.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/Event.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/Console.js" type="text/javascript"></script>
				<script src="jaipho/Jph/common.js" type="text/javascript"></script>
			-->			
			*/?>	
			
				
	<script type="text/javascript">
		
		// CONFIGURATION BLOCK
		// v 0.55
		
		// basic parameters
		var TOOLBARS_HIDE_TIMEOUT				=	5000;
		var SLIDESHOW_ROLL_TIMEOUT				=	3000;
		var SLIDE_SCROLL_DURATION				=	'0.4s';
		var SLIDE_PRELOAD_TIMEOUT				=	1100;
		var SLIDE_PRELOAD_SEQUENCE				=	'1,-1,2';
		var SPLASH_SCREEN_DURATION				=	1000;
		var DEFAULT_STARTUP_MODE				=	'thumbs';  // thumbs, slider, slideshow
		var SLIDE_SPACE_WIDTH					=	40;

		// advanced parameters
		var ENABLE_SAFARI_HISTORY_PATCH			=	true;
		var MAX_CONCURENT_LOADING_THUMBNAILS	=	4;
		var MAX_CONCURENT_LOADING_SLIDE			=	1;
		var MIN_DISTANCE_TO_BE_A_DRAG			=	70;
		var MAX_DISTANCE_TO_BE_A_TOUCH			=	5;
		var CHECK_ORIENTATION_INTERVAL			=	1000;
		var BLOCK_VERTICAL_SCROLL				=	true;
		var BASE_URL							=	'<?=$b->url_abs_site;?>/2011_iphone/jaipho/';
		var SLIDE_MAX_IMAGE_ELEMENS				=	50;
		
		// debug parameters
		var DEBUG_MODE							=	false;
		var DEBUG_LEVELS						=	'';
		
	 	if (DEBUG_MODE)
			JphUtil_Console.CreateConsole( DEBUG_LEVELS);
		
	</script>	
	
	
	
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


</head>

<body onload="init_jaipho()">
	<?php /*
	<!-- 
		Important! 
		Do not remove elements with html attribute id set to some value. Those elements are required by javascript application.
		All other can be customized as required by project needs.
	 -->
	 * 
    320x480px pour l'iPhone 3
    	320x480px pour l'iPhone 4
    	320x568px pour l'iPhone 5
    768x1024px pour l'iPad 2
    768x1024px pour l'iPad 3
	 * 
	*/?>
	<!-- SPLASH SCREEN -->
	<table id="splash-screen" class="splash-screen">
	<tr>
		<td class="text">
		<?=$b->cont_page_titre;?>			
		<div style="margin:10px 0 10px 0;font-size:9px;">Ultra-book.com</div>
		</td>
	</tr>
	<tr>
		<td class="image">
		&nbsp;
		</td>
	</tr>
	</table>
	
	<script type="text/javascript">
	
		// SPLASH SCREEN INIT	
		scrollTo(0,1);
				var or_mngr	=	new JphUtil_OrientationManager( 320, 480);	// iPhone version
		// var or_mngr	=	new JphUtil_OrientationManager( 768, 1024); // iPad version
		
		or_mngr.Init();

	</script>
	
	<!-- JAIPHO PRELOAD IMAGES -->
	<div id="preloader">
	</div>
		
	<!-- THUMBNAILS -->
	<div class="toolbar" id="thumbs-toolbar-top">
	
		<table cellpadding="0" cellspacing="0">
		<tr>
			<td class="wing">
				<a class="button" href="/">
					Retour 
				</a>
			</td>
			<td class="center"><?=$b->prenom;?> <?=$b->nom;?></td>
			<td class="wing"></td>
		</tr>
		</table>
		
	</div>
	
    <div id="thumbs-container">
		<div id="thumbs-images-container">
		</div>	
		<div id="thumbs-count-text"></div>
		<div style="padding: 6px; margin-bottom: 10px;">
            <div class="footer_bloc">
                <a class="footer_ub" href="http://www.ultra-book.com"><strong>Ultra-book / mobile</strong> | création de book en ligne v2</a>
            </div>
        </div>
        
    </div>
	<?php 
	// bouclage rub
	$rub = $b->gal_cont['gal'][0];
	
	?>
	<!-- SLIDER -->
	<div id="slider-overlay">
		
		<div class="toolbar" id="slider-toolbar-top">
			
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="wing">
					<a class="button" href="javascript: app.ShowThumbsAction();">
						<?=$rub['rub_nom']?>
					</a> 
				</td>
				<td class="center" id="navi-info">
				</td>
				<td class="wing">&nbsp;</td>
			</tr>
			</table>
		</div>

		<div class="toolbar" id="slider-toolbar-bottom">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr>
				<td>
					<a class="navi-button" id="navi-prev" href="javascript: void(0);">
					</a> 
				</td>
				<td style="width: 80px;">
					<a class="navi-button" id="navi-play" href="javascript: void(0);">
					</a>
					<a class="navi-button" id="navi-pause" href="javascript: void(0);">
					</a>
				</td>
				<td>
					<a class="navi-button" id="navi-next" href="javascript: void(0);">
					</a>
				</td>
			</tr>
			</table>
		</div>
	</div>
	
    <div id="slider-container">
    </div>


			<!--
			
			The whole source in one file.
		
				
			
			<script src="jaipho/jaipho-0.55.00-main-src.js" type="text/javascript"></script>-->				
     		<script src="<?=$b->url_abs_site;?>/2011_iphone/jaipho/jaipho-0.55.00-main-src.js" type="text/javascript"></script>

			
	<script type="text/javascript">

		// APPLICATION INIT BLOCK 
		// v 0.53
		
		 // load images
		var dao	=	new Jph_Dao();

			/*
			dao.ReadImage( 0,'test-image-1.jpg','test-image-1-thumb.jpg','','');
			dao.ReadImage( 1,'test-image-2.jpg','test-image-2-thumb.jpg','JAIPHO beta','Congratulations. It seems that your installation is working OK.');	
			*/		
			
			<?php  
            if ( $b->gal_cont['img'][$rub['rub_id']] ) foreach ($b->gal_cont['img'][$rub['rub_id']] as $key=>$img) {
            	
			$img['img_titre']	= addcslashes($img['img_titre'],"\\\'\"&\n\r<>");	
			$img['img_desc']	= addcslashes($img['img_desc'],"\\\'\"&\n\r<>");			                	
			if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ 
			
			
			if ($key >= $b->us_formule_img_nb_mobil) break;
			
				if ( $img['img_fichier']!='') {
				?>             
	            dao.ReadImage( <?=$key?>,'<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img320.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>','<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img75.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>','<?=$img['img_titre'];?>','<?php /*=$img['img_desc'];*/?>');	
				<?php  
				}
			}
			?>
			
			
		// global reference to jaipho application
		var app;
		var splash	=	document.getElementById( 'splash-screen');
		function init_jaipho()
		{
			if (SPLASH_SCREEN_DURATION > 0)
				splash.style.display	=	'table';
			
			setTimeout('_init_jaipho()', SPLASH_SCREEN_DURATION);
		}
		
		function _init_jaipho()
		{
			// remove splash screen
			splash.style.display	=	'none';
			
			// start jaipho
			app	=	new Jph_Application( dao, or_mngr, splash);
			app.Init();
			app.Run();
		}
		
	</script>
	
	
<?php 	
// stats : pixel de comptage interne (voir StatsBookController)
//
$stats_action =			'add';
$stats_st_champ =		'st_iphone';
$stats_us_login =		$b->us_dir;
$stats_st_cles = 		md5($book . 'pat75ub2012publique' );
$stats_i = 				rand(0,9999);
$stats_img = '/ubstats.gif?r='.$stats_i;
?>								  	
<img src="<?=$stats_img;?>" width="1" height="1" style="display:none"/>

				

</body>
</html>