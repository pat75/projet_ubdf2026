{{-- Porte depuis 2011_html_pages_v2/zoom2016/ultrabook_portfolio.php (_outils/porter_gabarits.py) --}}
<?php 
/* ------------------------------------------------
	Ultra-Book GRID | Modèle page : book
------------------------------------------------ */

	// toutes les data du book en dur
	//require_once('include/ub-data-global.php');
	
	// toutes les fonctions utiles (type affiche_...) 
	//require_once('include/ub-grid-template.php');
	
	// pour la class de la balise home + titre page
	$typePage = "book";
	

// bouclage rub
$array_rub = $b->gal_cont['gal'];
//print_r($b->gal_cont['gal']);


// gal content
$content = 			'';	

$tmp_rep_image = ($b->IsMobile) ? $b->rep_img550 : $b->rep_img900;


if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	
    	
    	//if ($k > $b->us_formule_img_rub_nb) break;
		
	 if (is_array($array_rub['img'][$rub['rub_id']]) )	{   // selection rub suivant le lien /*&& $rub['rub_id'] == $b->rub_id */
	    	
				//print_r($rub);
		 
				// page title
				$titrePage = $b->prenom.' '.$b->nom.' | '.ucfirst($typePage).' : '.$titre;
				
				if ($array_rub['img'][$rub['rub_id']]) 
			    foreach ($array_rub['img'][$rub['rub_id']] as $key=>$img) {
			    	                	
			 			if ($key >= $b->us_formule_img_nb ) break;						
						//print_r($img);
						
						
						$img['img_titre'] = 	strip_tags($img['img_titre']);
						$img['img_desc'] = 	    nl2br(strip_tags($img['img_desc']));

						//$img['img_titre'] = str_replace("\'", "&#39;", $img['img_titre'] );						
						//$img['img_desc'] = nl2br(html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8'));
						//$img['img_desc'] = str_replace("\"", "", $img['img_desc'] );						
						//echo $img['img_titre'].' - <br>';
						//echo $img['img_desc'].' - <br>';
												
						
						if ($img['img_fichier']!='') {           
			           			            
							if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ 													

							$rub_name_url = strtolower (preg_replace("/(\s|\/|&|\(|\)|\"|\'|\.|\+|\||@|;|,|#|!)+/", "-", $rub['rub_nom']) );
							$rub_name_url = filter_var ($rub_name_url, FILTER_SANITIZE_URL);


							$content .= '
							<div class="grid_item '.$rub_name_url.'" 
							data-category="'.$k.'__'.$rub_name_url.'"
							>
							<a href="'.( (!$tmp_swf) ? $b->url_abs_site.$tmp_rep_image.$img['img_fichier'] : $b->url_abs_site.'/2010_images/icone_flash.gif').'">
								<img src="'.( (!$tmp_swf) ? $b->url_abs_site.$b->rep_img320.$img['img_fichier'] : $b->url_abs_site.'/2010_images/icone_flash.gif').'"
								alt="'.$img['img_titre'].'" data-legende="'.$img['img_legende'].' '.$img['img_desc'].'" id="visuel_'.$key.'_'.$rub['rub_id'].'" class="effect_show_scale">
							</a>
							</div>
							';

							/*
														$content .= '
														<div class="grid_item '.$rub_name_url.'" 	data-category="'.$k.'__'.$rub_name_url.'" >
															<a href="'.( (!$tmp_swf) ? $b->url_abs_site.$tmp_rep_image.$img['img_fichier'] : $b->url_abs_site.'/2010_images/icone_flash.gif').'">
															 <img class="effect_show_scale-desac unveil"
																	  src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
																	  data-src="'.( (!$tmp_swf) ? $b->url_abs_site.$b->rep_img320.$img['img_fichier'] : $b->url_abs_site.'/2010_images/icone_flash.gif').'"
																	  alt="'.$img['img_titre'].'" data-legende="'.$img['img_legende'].' '.$img['img_desc'].'" id="visuel_'.$key.'_'.$rub['rub_id'].'">
																<noscript>
																	<img src="'.( (!$tmp_swf) ? $b->url_abs_site.$b->rep_img320.$img['img_fichier'] : $b->url_abs_site.'/2010_images/icone_flash.gif').'"
																		  alt="'.$img['img_titre'].'" data-legende="'.$img['img_legende'].' '.$img['img_desc'].'" id="visuel_'.$key.'_'.$rub['rub_id'].'">
																</noscript>
															</a>
														</div>
														';
							*/




						}
				} 	
	 	   
	 } 	
		
} 


//print_r($content);
?>	







<!-- Article -->
<article class="margintopPlus">

	<div id="nav_filtre">
		<div class="wrap wider ">
			<div class="grid" >
				<div class="unit whole bloc_filtre">
					<nav class="button-group filter-button-group" ></nav>
				</div>
			</div>
		</div>
	</div>



	<!-- Section =ptf -->
<section id="contenu_accueil" >
<div class="wrap wider ">
	<div class="grid" >
		<!-- unit -->	
		<div class="unit whole">	
					<div id="" class="hide aide_ptf"><?=__('Vous pouvez modifier les images du portfolio (ci-dessous)<br/>à partir du menu: ')?><a href="<?=$b->url_abs_site?>/ubactiontype__projet&gal_id_categorie=portfolio"><?=__('Portfolio')?></a></div>
					<div class="iso_grid grid " id="galerieA">											
					<?=$content?>				
					</div>
					
		</div>
		<!-- /unit -->	
	</div>
</div>

<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_footer_section.php');?>

</section>
<!-- /Section =ptf -->



<!-- Section =bio/contact -->
<section  class="close " id="contenu_ajax">
<div class="wrap wider">
	<div class="grid" >
		
		<div class="ajax_data" id="contenu_menu">
			<div class="unit  one-third"></div>
		</div>
			
		<div class="ajax_data" id="contenu">
			<div class="unit two-thirds"></div>		
		</div>		
		
	</div>
</div>

<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_footer_section.php');?>

</section>
<!-- /Section =bio/contact -->

</article>
<!-- /Article -->

