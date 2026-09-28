{{-- Porte depuis 2011_html_pages_v2/ultrabook_2012_galerie_b.tlp.php (_outils/porter_gabarits.py) --}}
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title><?=$b->prenom;?> <?=$b->nom;?> : Ultra-book galerie</title>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 


		<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Oswald'  type='text/css' />
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_css/ub_galerie_b.css"/>
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/colorbox/colorbox.css"/>
		

		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery.masonry.min.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/colorbox/jquery.colorbox-min.js"></script>
		
		<script type="text/javascript">
			$(function() {

				var $container = $('.am-container');
				
				$container.imagesLoaded( function(){
				  
				  	$container.masonry({
				    itemSelector : '.box',
				    isAnimated: true,
				    animationOptions: {
					    duration: 750,
					    easing: 'linear',
					    queue: false
					  }
  
				  	});
				  
				  
				  	$(".ubgalerie").colorbox({
					rel:'group2', 
					transition:"fade",
					current:	"{current} / {total}",
					previous:	"Précédent",
					next:		"Suivant",
					close: 		"fermer",
					opacity:	0.65,
					width:"75%", 
					height:"75%"
					});
				  
				});

							
			});
			
		</script>
		
		
		
    </head>
    <body>
	<?php 		// chemin vignettes
	if ( $b->us_pf_img_vignette ) {
		$img =        $b->url_abs_site.$b->rep_pref.$b->us_pf_img_vignette;
		}
	else {
		$img =        '/img_front/_ultra_book_62x62.gif' ;	
		}
	
	?>
	
<div id="galblock_top">
<div style="width:980px;margin: 0 auto ;">		
	<div style="float:left;margin-right: 10px;display:block;padding:4px;margin-left: -15px;">
	<img src="<?=$img;?>"  width="32" height="32" alt="<?=$b->prenom;?> <?=$b->nom;?>" />
	</div>
	<div>
		<?=$b->prenom;?> <?=$b->nom;?><br/>
	<span class="font_small"><?=$b->us_type;?> <?=$b->us_statut;?></span>
	<span style="color:#999" class="font_small"> / <?=$b->us_ville;?> <?=$b->us_pays;?></span>
	</div>
</div>    
</div>   

<div id="galblock_img">
		
<?php  
 
// bouclage rub
$array_rub = $b->gal_cont['gal'];
	
		
if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	?>
		
		<h3><?=$rub[rub_nom];?></h3>
		
		<div class="am-container" id="am-container" style="margin-left:-4px;">
		<?php  
						
           if ($array_rub['img'][$rub['rub_id']]) 
            foreach ($array_rub['img'][$rub['rub_id']] as $key=>$img) {                	
 			if ($key >= $b->us_formule_img_nb ) break;
				
			$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
			$img['img_titre'] = strip_tags($img['img_titre']);
			
			if ($img['img_fichier']!='') {		                    
            ?>
            <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>				
				<div class="box col2">  
			    <a href="<?=$b->url_abs_site.$b->rep_img900.$img['img_fichier'];?>" class="ubgalerie" title="<?=$img['img_titre'];?>">
			    	<img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img320.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" title="<?=$img['img_titre'];?>"></img>
			    </a>
				</div>
				
            	<?php  
				}
				} 
				?>
			</div>
			<br clear="all"/>			
			<?php 
			}			
			?>










<div  id="page_footer">	        
    <?php if ($b->us_formule==1 && $b->cont_piedpage=='[invisible]' ) { ?>    	
    <?php  } else { ?>			
		<?php if ($b->us_formule==1 && !empty($b->cont_piedpage) ) { ?>
			<?=htmlspecialchars_decode($b->cont_piedpage,ENT_QUOTES);?>					
		<?php  } else { ?>
			<a href="http://www.ultra-book.com" class="footer_ub">Ultra-book.com <span style="color:#aaa">| création de book en ligne 2012</span></a>
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















