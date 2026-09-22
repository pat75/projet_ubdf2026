{{-- Porte depuis 2011_html_pages_v2/base/ultrabook_2012_type.tlp.php (_outils/porter_gabarits.py) --}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
<title><?=$b->cont_page_titre;?></title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="keywords" content="Ultra-book, creation de book, <?=str_replace(array("[&quot;","&quot;]","&quot;,&quot;"), array("","",","),  $b->cont_page_key);?>"/>
<meta name="description" content="book <?=$b->cont_page_meta?> <?=(($b->page_type=='accueil')?$b->gal_cont['img'][0]['img_titre_alt']:'');?> <?=(($b->page_type=='news')?$b->gal_cont['img'][0]['img_titre_alt']:'');?>" />

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


<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/base/css/core_book_2012.css" type="text/css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_css/galleriffic_ub.css" type="text/css" />
<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2010_js/facebox/facebox.css" type="text/css" />


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


<!-- js jq gg -->
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

<!-- js tooltip 2012 -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/bootstrap-tooltip.js"></script>



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

<?php   if ( $b->connection_admin_book ) { 	 ?>	
	
		<!-- pr 2012 - barre de réglage -->	
		
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/jquery-ui-1.8.20.custom/css/smoothness/jquery-ui-1.8.20.custom.css" type="text/css">
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/fontpicker/googlefontpicker.css" type="text/css" />
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/minicolors/jquery.miniColors.css" type="text/css" />

		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery-ui-1.8.20.custom/js/jquery-ui-1.8.20.custom.min.js"></script> 
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/ui.combobox.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/fontpicker/jquery.googlefontpicker.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/infusion-jQuery-xcolor/jquery.xcolor.min.js"></script>			
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/minicolors/jquery.miniColors.js"></script>		
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery.json-2.2.min.js"></script>	
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/bootstrap-popover.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/js_jquery/jquery.cookie.js"></script>
		
		<!-- barre de réglage fin -->
<?php  } ?>
	




<!-- pr 2012 -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/nailthumb/jquery.nailthumb.1.0.min.js"></script>

<!-- ptf -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery.history-min.js"></script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery.galleriffic.js"></script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery.opacityrollover.js"></script>

<!-- js pngfix -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/pngfix/jquery.pngFix.pack.js"></script>

<!-- js facebox -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/facebox/facebox.js" ></script>

<!-- js core modele portfolio 2012 -->
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_web/base/js/ub_book_core_mdl2012.js"></script>


<script type="text/javascript">

// page reglage load conf
var ub_pr_conf_init = <?=$b->cont_conf2012;?>;
    ub_pr_conf_init = ub_pr_conf_init['data'];
    
var ub_pr_conf_url = '<?=$b->url_abs_site;?>';
var ub_page_type = '<?=$b->page_type;?>';

var gallery1 = true;
var gallery_img_nb = <?=\App\Services\Book\Php7::count($b->gal_cont['img'][$b->rub_id]);?>;


$(document).ready(function(){


		$('#page_container').css({'opacity':'0'});
		
		// rool over
		// recup des var par defaut de la barre de reglage
		//
		var ub_rollover_options = {
		       height_ : 	((typeof(ub_pr_conf_init.ptf_nb_vignette) == 'undefined' )?'325px':ub_pr_conf_init.ptf_nb_vignette.accueil_img_size+'px')			
		};
		$.extend( ub_rollover, ub_rollover_options );


		// menu
		ub_menu.init();	
				

						
		// gallerie
		// tooltip
		if ( ub_page_type == 'portfolio' && gallery_img_nb > 0)		{ 
			$("a[rel=tooltip]").tooltip(); 
			ub_gallerie_mdl2012.init();			
			}


		// page reglage
		ub_pr_data.init_conf();		


		// png fix
		$().pngFix();
		

		// menu rool over
		$('#cont_ptf_img').hide();    
		if ( ub_page_type == 'accueil' && <?=$b->accueil_ptf_vignette_aff?> )	{
			 ub_rollover.init();
			 $('#cont_ptf_img').show(); 
			}	
				
		
		$('#page_container').fadeTo('slow', 1)
		
		
		// ScrollTo top
		//
		ub_plugin.UItoTop({min: 200});	 
		
		
});
</script>	 



		

</head>
<body class="ub_couleur_fond" rel="<?=$b->url_abs_site.$b->cont_bg;?>">

<a name="top_page"></a>
<div id="page_base"> 
	
<!-- reglage de la page -->	
<?php   if ( $b->connection_admin_book ) { 	 
	//print_r($_SESSION);
	include \App\Services\Book\Gabarit::chemin('base/ultrabook_2012_barredereglage.tlp.php');
}
?>
<!-- reglage de la page fin -->

<?php  if (  $b->cont_visuel2012 != '' ) { ?>
<div id="page_top"><a href="/"><img src="<?=$b->url_abs_site.$b->rep_pref.$b->cont_visuel2012;?>" alt="<?=$b->cont_page_titre;?>" /></a></div>
<?php  } ?>


            <div id="page_container">
            	<div id="page_container_marge"></div>             
	            <div id="page_bloc_g">
	            	<?php include \App\Services\Book\Gabarit::chemin('base/ultrabook_2012_menugauche.tlp.php'); ?>            
	            </div>
	            <div id="page_bloc_d">
		            <?php 
		            //echo 'ok+++++++++++++'.$b->page_type ;					
		            switch ($b->page_type) {
		                case 'accueil':
						default:									
		                    include \App\Services\Book\Gabarit::chemin('base/ultrabook_2012_accueil.php');
		                    break;
		                case 'portfolio':							
		                    include \App\Services\Book\Gabarit::chemin('base/ultrabook_2012_portfolio.php');
		                    break;           
		                case 'news':
		                    include \App\Services\Book\Gabarit::chemin('base/ultrabook_2012_news.php');
		                    break; 
						 case 'contact':
		                    include \App\Services\Book\Gabarit::chemin('base/ultrabook_contact.php');
		                    break;   	    
		            }
					?>
				</div> 	
				<br clear="all"/>						             
            </div>        	
	        

			<div  id="page_footer">	        
			    <?php if ($b->us_formule==1 && $b->cont_piedpage=='[invisible]'  || $b->inc_action_view == 'df'  ) { ?>
			    	
			    <?php  } else { ?>	
					
					<?php if ($b->us_formule==1 && !empty($b->cont_piedpage) ) { ?>
						<?=htmlspecialchars_decode($b->cont_piedpage,ENT_QUOTES);?>					
					<?php  } else { ?>
						<a href="http://www.ultra-book.com" class="footer_ub"><strong>Ultra-book.com</strong> | création de book en ligne <span style="font-size:7px">2012</span></a>
					<?php  } ?>
					
					
					
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



<div style="display:none">
<!-- bloc data contenu accueil - uniquement affichee pour l'admin -->
<?php  if ( (request()->cookie('us_pr') !== null) or request()->query('pr')=='true' ) foreach ($b->gal_cont_accueil as $cont_accueil) { ?>
	<div id="accueil_contenu__<?=$cont_accueil['img_id'];?>">
		<?=htmlspecialchars_decode(  book_actu_txt($cont_accueil['img_html'],$b->url_abs_site), ENT_QUOTES );?>
	</div>
<?php }?>
<!-- bloc data fin -->
</div>	


</body>

<?php 
// stats sur le serveur extra-book.com
//
$stats_url =			'https://www.extra-book.com/2012_stats/st_action.php';
$stats_action =		    'add';
$stats_st_champ =		'st_book';
$stats_us_login =		$b->us_dir;
$stats_st_cles = 		md5($b->us_dir . 'pat75ub2012publique' );
$stats_i = 				rand(0,9999);
$stats_img = $stats_url.'?action='.$stats_action.'&st_champ='.$stats_st_champ.'&us_login='.$stats_us_login.'&st_cles='.$stats_st_cles.'&r='.$stats_i;
?>
<img src="<?=$stats_img;?>" width="1" height="1" style="display:none"/>




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



</html>