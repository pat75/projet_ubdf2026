{{-- Porte depuis 2011_html_pages_v2/slide/ultrabook_2012_portfolio.php (_outils/porter_gabarits.py) --}}


<?php 		// chemin vignettes
if ( $b->us_pf_img_vignette ) {
	$img =        $b->url_abs_site.$b->rep_pref.$b->us_pf_img_vignette;
	}
else {
	$img =        '/img_front/_ultra_book_62x62.gif' ;	
	}	
?>

	
	
		
<?php  
 
// bouclage rub
$array_rub = $b->gal_cont['gal'];
//print_r($b->gal_cont['gal']);
//print_r($b->gal_cont['img']);


// <div style="float:left;" id="hsort">HS</div> - 	<div id="rsort">HS</div>
?>



<div id="pos_fotorama" ></div>	
<?php 		
if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {
    			
    	if ($b->gal_cont['img'][$rub['rub_id']][0]['img_fichier']!='') { // si 1ere image existe
    	/*
    	<!--<div id="rub_<?=$k;?>"> <?=$rub[rub_nom];?></div>-->
    	
			<!--<div class="fotorama" data-width="700" data-height="467" data-fullscreenIcon="true" >-->
			*/
			?>
			
			<div id="fotorama" style="display:none;">		
			<?php 
            foreach ($b->gal_cont['img'][$rub['rub_id']] as $key=>$img) {                	
 			if ($key >= $b->us_formule_img_nb ) break;
				
			$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
			$img['img_titre'] = strip_tags($img['img_titre']);
			
			if ($img['img_fichier']!='') {		                    
            ?>
            <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>				
			<img 
			src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img900.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" 
			alt="<?=$img['img_titre'];?><br/><span><?=$img['img_desc'];?></span>"
			>
        	<?php  
			}
			} 
			?>
			</div>
			
			<?php 
		   	}

		}			
		?>
<div style="clear: both;"></div>
	
