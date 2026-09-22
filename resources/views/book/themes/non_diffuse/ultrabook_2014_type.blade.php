{{-- Porte depuis 2011_html_pages_v2/non_diffuse/ultrabook_2014_type.tlp.php (_outils/porter_gabarits.py) --}}
<!doctype html>

<!--[if lt IE 7 ]> <html class="ie ie6 ie-lt10 ie-lt9 ie-lt8 ie-lt7 no-js" lang="fr-FR"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7 ie-lt10 ie-lt9 ie-lt8 no-js" lang="fr-FR"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8 ie-lt10 ie-lt9 no-js" lang="fr-FR"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9 ie-lt10 no-js" lang="fr-FR"> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" lang="fr-FR"><!--<![endif]-->
<!-- the "no-js" class is for Modernizr. --> 

<head>	
<title>Portfolio non diffusé</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="keywords" content="Ultra-book, creation de book"/>
<meta name="description" content="book portfolio" />
<meta name="viewport" content="width=device-width,initial-scale=1">



<!-- icon -->
<link rel="SHORTCUT ICON" href="<?=$b->icone;?>"/>
<link rel="apple-touch-icon" href="<?=$b->icone_iphone;?>"/>
<link rel="apple-touch-icon" sizes="72x72" href="<?=$b->icone_ipad;?>" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />






<link href='https://fonts.googleapis.com/css?family=Titillium+Web:400,600' rel='stylesheet' type='text/css'>

<?php /*  IE is limited to 32 stylesheets. - sinon bug ds les js avec styleSheet.cssText*/?>


<!-- concatenate and minify for production -->
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/reset.css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_style_01.css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_layout_01.css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/_/css/ub_style_non_diffuse.css" />


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





</head>


<body>

<a name="top_page"></a>


<div id="page_socle">	
<div id="page_base">
<div class="wrap wider full-overlay-main">
		
		
			
			<div class="grid ">
					<div class="debug+ unit whole " >
					<div id="ub_container">
					<h2>Ce portfolio n'est pas diffusé pour le moment...</h2>
					Vous pouvez diffuser votre portfolio à partir du menu "Diffusion" de votre espace d'administration.
					</div>
					</div>
			</div>
			
			<div  class="grid">
					<div class="unit whole " style="text-align:center;margin: 0 auto ;">
						<div id="ub_footer">
						<div class="center">
						<?php  if ( $b->inc_action_view == 'df' ) { ?>
							<a href="http://www.dustfolio.com" class="footer_ub">
								<strong>Dustfolio.com</strong> | create portfolio online
							</a>
						<?php } else { ?>
							<a href="http://www.ultra-book.com" class="footer_ub">
							<img src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/img/ub_logo_footer.png">
							<strong>Ultra-book.com</strong> | création de book en ligne
							</a>
						<?php } ?>
						</div>
						</div>					
					</div>	
			</div>
</div>
</div>
</div>

<?php /*	
// stats : pixel de comptage interne (voir StatsBookController)
//
$stats_action =			'add';
$stats_st_champ =		'st_book';
$stats_us_login =		$b->us_dir;
$stats_st_cles = 		md5($book . 'pat75ub2012publique' );
$stats_i = 				rand(0,9999);
$stats_img = '/ubstats.gif?r='.$stats_i;
								  	
<img src="<?=$stats_img;?>" width="1" height="1" style="display:none"/>
*/?>

<script type="text/javascript">
// ga
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-464814-11']);
  _gaq.push(['_setDomainName', 'ultra-book.com']);
  _gaq.push(['_trackPageview']);
<?php  if (isset($b->cont_analytic)) : ?>
  _gaq.push(['t2._setAccount', '<?=$b->cont_analytic?>']);
  _gaq.push(['t2._setDomainName', '<?=$b->us_dir;?>.ultra-book.com']);
  _gaq.push(['t2._trackPageview']);
<?php  endif; ?>
  (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);})();  
</script>


</body>
</html>