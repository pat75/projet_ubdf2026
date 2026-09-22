{{-- Porte depuis 2011_html_pages_v2/ultrabook_iphone_portfolio_ptf_list.php (_outils/porter_gabarits.py) --}}
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
	 -->
	 
			
	<!--
		IPAD FIX - TO BE ENABLED IF IPAD USER IS DETECTED
		<link href="jaipho/Themes/Default/jaipho-ipad.css" rel="stylesheet" type="text/css"/>
	-->
			
			<!--			
			The whole source in one file.		
			-->	
			
			
			<!--				
			Developer version. All classes are in separate files. Much easier to debug.
		
			
				<script src="jaipho/Jph/Util/Touches.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/PreloaderItem.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/Preloader.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/OrientationManager.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/Event.js" type="text/javascript"></script>
				<script src="jaipho/Jph/Util/Console.js" type="text/javascript"></script>
				<script src="jaipho/Jph/common.js" type="text/javascript"></script>
			
	
		
     <link href="<?=$b->url_abs_site;?>/2011_iphone/jaipho/jaipho.css" rel="stylesheet" type="text/css"/>
     
     -->			
	*/?>
	
     <link href="<?=$b->url_abs_site;?>/2011_iphone/jaipho/Themes/Default/jaipho.css" rel="stylesheet" type="text/css"/>
     <script src="<?=$b->url_abs_site;?>/2011_iphone/jaipho/jaipho-0.55.00-preload-src.js" type="text/javascript"></script>
	 
	 
	
	
			
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
		var BASE_URL							=	'<?=$b->url_abs_site;?>2011_iphone/jaipho/';
		var SLIDE_MAX_IMAGE_ELEMENS				=	50;
		
		// debug parameters
		var DEBUG_MODE							=	false;
		var DEBUG_LEVELS						=	'';
		
	 	if (DEBUG_MODE)
			JphUtil_Console.CreateConsole( DEBUG_LEVELS);
		
	</script>	
	
	
<script type="text/javascript">

  var _gaq = _gaq || [];
  
  _gaq.push(['_setAccount', 'UA-464814-11']);
  _gaq.push(['_setDomainName', 'ultra-book.com']);
  _gaq.push(['_trackPageview']);

<?php  if (isset($b->cont_analytic)) : ?>
  _gaq.push(['t2._setAccount', '<?=$b->cont_analytic?>']);
  _gaq.push(['t2._setDomainName', '<?=$b->us_dir;?>.ultra-book.com']);
  _gaq.push(['t2._trackPageview']);
<?php  endif; ?>


  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>


</head>

<body>
	
	<script type="text/javascript">
	
	scrollTo(0,1);
		var or_mngr	=	new JphUtil_OrientationManager( 320, 480);	// iPhone version
	// var or_mngr	=	new JphUtil_OrientationManager( 768, 1024); // iPad version
	
	or_mngr.Init();

	</script>
	
   <div class="toolbar">
		<table cellpadding="0" cellspacing="0">
		<tr>
			<td class="wing">
			<!-- 
				<a class="button" href="">
					Back 
				</a>
			--> 
			</td>
			<td class="center">
				<?=$b->prenom;?> <?=$b->nom;?>
			</td>
			<td class="wing"></td>
		</tr>
		</table>
		
    </div>
    <?php 	//print_r ($b->gal_cont['gal']);?>
    	
    <ul>
    <?php  foreach ($b->gal_cont['gal'] as $key=>$rub) {
    		 
    	
    	if ($key > $b->us_formule_img_rub_nb) break;
		
    	?>
            <li>
			<a href="<?=wd_remove_accents($rub['rub_nom'])."-pi".$rub['rub_id'];?>">									
				<span class="image">
					<?php  
					//print_r ($b->gal_cont['img'][$rub['rub_id']][0]);
					$img = $b->gal_cont['img'][$rub['rub_id']][0]; //1ere img
					if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ 
					?>										
					<img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img75.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>">
				</span>
				<span class="title"><?=$rub['rub_nom']?></span>
				<span class="text">
					<?php 
					
					$tmp_img = \App\Services\Book\Php7::count(explode("_", $rub['rub_ordre_img']))-1;
					
					if ($tmp_img >= $b->us_formule_img_nb_mobil) $tmp_img = $b->us_formule_img_nb_mobil;
					if ($tmp_img != 0) 
						echo $tmp_img.' Images';					
					?> 				
				</span>
			</a>
        	
        </li>    
	<?php  } ?>     
    </ul>

	
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