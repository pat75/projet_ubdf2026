{{-- Porte depuis 2011_html_pages_v2/ultrabook_type.tlp.php (_outils/porter_gabarits.py) --}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
<title><?=$b->cont_page_titre;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<meta name="keywords" content="<?=str_replace(array("[&quot;","&quot;]","&quot;,&quot;"), array("","",","),  $b->cont_page_key);?>"/>
<meta name="description" content="portfolio <?=$b->cont_page_meta?> <?=(($b->page_type=='accueil')?$b->gal_cont['img'][0]['img_titre_alt']:'');?> <?=(($b->page_type=='news')?$b->gal_cont['img'][0]['img_titre_alt']:'');?>" />

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


<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2010_css/reset.css" type="text/css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2011_css/galleriffic-3.css" type="text/css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2011_css/core_book.css" type="text/css" />


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
<link href='https://fonts.googleapis.com/css?family=Jockey+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Philosopher' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Duru+Sans' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Rationale' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Medula+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Sansita+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Patua+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
<![endif]-->


<!-- js jq -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/jquery-1.4.2.min.js"></script>

<!-- ptf -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/jquery.history-min.js"></script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/jquery.galleriffic-min.js"></script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/jquery.opacityrollover.js"></script>

<!-- js pngfix -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/pngfix/jquery.pngFix.pack.js"></script>

<!-- js facebox -->
<link href="<?=$b->url_abs_site;?>/2010_js/facebox/facebox.css" rel="stylesheet" type="text/css" />
<script src="<?=$b->url_abs_site;?>/2010_js/facebox/facebox.js" type="text/javascript"></script>

<!-- js tooltip -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/vTip_v2/vtip-min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$b->url_abs_site;?>/2010_js/vTip_v2/css/vtip.css" />



<!-- stats live juil2016 -->
<script type="text/javascript" src="https://cdn.socket.io/socket.io-1.4.5.js"></script>
<script type="text/javascript">
	/* stats live + archives  sep 2016 */
	var stats_url_stats_archives = 'https://stats.ultraportfolio.info/';

	var stats_us_id =					'<?=$b->us_dir;?>';
	var stats_vignette =				'<?=$b->rep_pref;?>us_pf_img_vignette.gif';
	var stats_nom_prenom =			'<?=$b->nom." ".$b->prenom;?>';
	var stats_visiteur =				'<?=request()->cookie("us_pr_login");?>';
	var stats_us_type =				'<?=$b->us_type;?>';


	var ub_image = {

		////////////////////////////////////////
		//
		//   stats live 9sep - 2016
		//
		///////////////////////////////////////

		statlive: function ( img_url ) {

			//console.log('send1');
			if (typeof socket === 'undefined') return;
			if (typeof stats_us_type === 'undefined') return;

			var statlive_imd_id = 		img_url.substring(img_url.lastIndexOf('/') + 1);
			statlive_imd_id = 			statlive_imd_id.replace(/\.([a-zA-Z]+)$/, '');

			if ( img_url =='' ) {
				statlive_imd_id = '';
			}

			socket.emit('stats', {
				'us_id': 			stats_us_id,
				'us_vignette': 		stats_vignette ,
				'us_nom_prenom': 	stats_nom_prenom,
				'us_type': 			stats_us_type,
				'img_id': 			stats_us_id + '_' + statlive_imd_id,
				'img_url': 			img_url,
				'visiteur': 		stats_visiteur,
				'type_book':		'book'
			});
			//console.log('send2');

		}

	};



	$(document).ready(function() {

		// stats live 9sep - 2016
		//
		if (typeof io !== 'undefined') {
			socket = io(stats_url_stats_archives);
			ub_image.statlive('');
		}

	});

</script>


<?php 
/*
print_r($_SERVER);
*/ 
?>



<style type="text/css">
body {
	
<?php  if ($b->cont_bg_choix == 1 ) : ?>
background-image: 	url("<?=$b->url_abs_site.$b->cont_bg;?>");
<?php  endif; ?>

<?php  if ($b->cont_bg_choix == 2 ) : ?>
background-color: 	<?=$b->cont_bgcoul;?>;
<?php  endif; ?>


<?php  if ($b->cont_center) : ?>
margin-right:		auto;
margin-left:		auto;
width: 				849px;
<?php  endif; ?>
}
</style>


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
<body>
<div id="page">        	
			
	        <div id="top">
	            <table>
	                <tr>
	                    <td>
	                        <a href="/book"><img src="<?=$b->url_abs_site;?><?=$b->rep_pref?><?=$b->cont_nav[0].'?'.rand(0,999);?>" width="290" height="110" alt="accueil book" border="0" /></a>
	                    </td>
	                    <td>
	                        <a href="/book"><img src="<?=$b->url_abs_site;?><?=$b->rep_pref?><?=$b->cont_nav[1].'?'.rand(0,999);?>" width="166" height="110" alt="accueil book" border="0" /></a>
	                    </td>
	                    <td>
	                        <a href="/portfolio"><img src="<?=$b->url_abs_site;?><?=$b->rep_pref?><?=$b->cont_nav[2].'?'.rand(0,999);?>" width="166" height="110" alt="portefolio" border="0" /></a>
	                    </td>
	                    <td>
	                        <a href="/actualites"><img src="<?=$b->url_abs_site;?><?=$b->rep_pref?><?=$b->cont_nav[3].'?'.rand(0,999);?>" width="166" height="110" alt="actualités" border="0" /></a>
	                    </td>
	                    <td>
	                        <img src="<?=$b->url_abs_site;?><?=$b->rep_pref?><?=$b->cont_nav[4].'?'.rand(0,999);?>" width="62" height="110" alt="book" />
	                    </td>
	                </tr>
	            </table>
	        </div>
				
			
			
            <div id="container">
            <?php 
            //echo 'ok+++++++++++++'.$b->page_type ;	
			
            switch ($b->page_type) {
                case 'accueil':
				default:									
                    include \App\Services\Book\Gabarit::chemin('ultrabook_accueil.php');
                    break;
                case 'portfolio':							
                    include \App\Services\Book\Gabarit::chemin('ultrabook_portfolio.php');
                    break;           
                case 'news':
					//echo '+++++++++';
                    include \App\Services\Book\Gabarit::chemin('ultrabook_news.php');
                    break; 
				 case 'contact':
                    include \App\Services\Book\Gabarit::chemin('ultrabook_contact.php');
                    break;   	    
            }
			?>  			             
            </div>
        
	        <div style="padding:20px;margin-bottom:60px;">

	        
	        <?php if ($b->us_formule==1 && $b->cont_piedpage=='[invisible]' || $b->inc_action_view == 'df' ) { ?>
	        <br/>
	        <?php  } else { ?>	
			<div class="footer_bloc">
				
				<?php if ($b->us_formule==1 && !empty($b->cont_piedpage) ) { ?>
					<?=htmlspecialchars_decode($b->cont_piedpage,ENT_QUOTES);?>					
				<?php  } else { ?>
					<a href="http://www.ultra-book.com" class="footer_ub"><strong>Ultra-book.com</strong> | création de book en ligne <span style="font-size:8px">V2.0</span></a>
				<?php  } ?>
	            <?php if ($b->us_partage_lien=='1') { ?>				
				<div style="float:right">
				<!-- AddThis Button BEGIN -->
				<div class="addthis_toolbox addthis_default_style ">
					<a class="addthis_button_facebook_send"></a>
					<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
					<a class="addthis_button_tweet"></a>
					<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
					<a class="addthis_button_print"></a>
					<a class="addthis_button_linkedin"></a>
					<a class="addthis_button_pinterest_pinit"></a>	
				</div>
				<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4ee6410f48e8fce2"></script>
				<!-- AddThis Button END -->			
				</div>				
				<?php  } ?>			
				
			</div>
			<?php  } ?>	
			
			
			</div>
</div>
		

		
<?php 	
// stats : pixel de comptage interne (voir StatsBookController)
//
$stats_action =			'add';
$stats_st_champ =		'st_book';
$stats_us_login =		$b->us_dir;
$stats_st_cles = 		md5($book . 'pat75ub2012publique' );
$stats_i = 				rand(0,9999);
$stats_img = '/ubstats.gif?r='.$stats_i;
?>								  	
<img src="<?=$stats_img;?>" width="1" height="1" style="display:none"/>





<!-- analytic fin -->


</body>