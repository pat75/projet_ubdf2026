{{-- Porte depuis 2011_html_pages_v2/classique2015/ultrabook_portfolio.php (_outils/porter_gabarits.py) --}}
<?php 
/*
// chemin vignettes
if ( $b->us_pf_img_vignette ) {
	$img =        $b->url_abs_site.$b->rep_pref.$b->us_pf_img_vignette;
	}
else {
	$img =        '/img_front/_ultra_book_62x62.gif' ;	
	}	
 
// bouclage rub
$array_rub = $b->gal_cont['gal'];
//print_r($b->gal_cont['gal']);
//print_r($b->gal_cont['img']);
*/
?>





<div class="fotorama_base"></div>


<?php /*
<style>
	.fotorama__img {
    top: 0 !important;
    margin-top: 0 !important;
	}
</style>

<div id="fotorama_slide" ></div>	
*/?>



<?php 	
/*
if (  is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {
    	//$b->gal_cont['img'][$rub['rub_id']][0]['img_fichier']!=''		
    		
		
    	if (is_array($b->gal_cont['img'][$rub['rub_id']])) { // si 1ere image existe
    	
			?>			
			<div id="fotorama" >		
			<?php 
            foreach ($b->gal_cont['img'][$rub['rub_id']] as $key=>$img) {                	
 			if ($key >= $b->us_formule_img_nb ) break;
				
			$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
			$img['img_titre'] = strip_tags($img['img_titre']);
			
			if ($img['img_fichier']!='') {		                    
            ?>
            <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false;  ?>				
			<img 
			src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img900.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" 
			data-caption="<?=$img['img_titre'];?><br/><span><?=$img['img_desc'];?></span>"
			>			
        	<?php  
			}	
			} 
			?>
			</div>
			<p class="fotorama-caption"></p>
			<?php 
		   	}
	
		}

*/		
?>