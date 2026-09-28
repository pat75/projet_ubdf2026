{{-- Porte depuis 2011_html_pages_v2/grid2015/ultrabook_portfolio.php (_outils/porter_gabarits.py) --}}

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
	
	//$titre = 'titre gal';
	
	
	//$content = $data_galerie_chrildren1;
	//$index = $data_index_chrildren1;
	//$titrePage = ucfirst($typePage).' : '.$titre;
 ?>
 
 
<?php 

// bouclage rub
$array_rub = $b->gal_cont['gal'];

//print_r($b);


// gal content
$content = 			'';	
$content_index = 	'';	



if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	
    	
    	//if ($k > $b->us_formule_img_rub_nb) break;
		
	 if (is_array($array_rub['img'][$rub['rub_id']]) && $rub['rub_id'] == $b->rub_id)	{
	    	
				//print_r($rub);
		 
				// gal titre 	 
			    $titre = $rub['rub_nom'];
				
				// page title
				$titrePage = $b->prenom.' '.$b->nom.' | '.ucfirst($typePage).' : '.$titre;
				
				
					
				if ($array_rub['img'][$rub['rub_id']]) 
			    foreach ($array_rub['img'][$rub['rub_id']] as $key=>$img) {                	
			 			if ($key >= $b->us_formule_img_nb ) break;
						
						//print_r($img);
							
						//$img['img_desc'] = 	strip_tags($img['img_desc']);
						$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
						$img['img_titre'] = strip_tags($img['img_titre']);
						
						if ($img['img_fichier']!='') {           
			           			            
							if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ 
														
							$content = $content. ' <a href="'.( (!$tmp_swf) ? $b->url_abs_site.$b->rep_img900.$img['img_fichier'] : $b->url_abs_site.'/2010_images/icone_flash.gif').'" data-caption="<strong>'.$img['img_titre'].'</strong> '.$img['img_desc'].'"></a>'; 
			           		$content_index = $content_index.' <a href="'.( (!$tmp_swf) ? $b->url_abs_site.$b->rep_img180.$img['img_fichier'] : $b->url_abs_site.'/2010_images/icone_flash.gif').'" title="'.$img['img_titre'].'"></a>'; 
							
						}
				} 	
	 	   
	 } 	
		
} 


//print_r($content);
?>	






<!-- BOOK -->
<div class="wrapper-djax">
	<main id="main" class="djax" data-typePage="book">
		
		<div class="wrapper wrap-fotorama transition ease"  >
			<?php grid2015__affiche_galerie($content, $titre, $content_index); ?>
		</div>
		
				
		<div class="back-grid show-fade hideifnojs">
			<a href="./" title="Back" class="no-border"><span class="bt grid_icon-down">&nbsp;</span></a>
		</div>
		
		
	</main>
</div>
<!-- /BOOK -->








