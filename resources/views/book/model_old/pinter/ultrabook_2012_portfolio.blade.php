{{-- Porte depuis 2011_html_pages_v2/pinter/ultrabook_2012_portfolio.php (_outils/porter_gabarits.py) --}}
<div id="nav_ptf_pos1"></div>

		
<?php 
 
// bouclage rub
// print_r($b->gal_cont);	
$array_rub = $b->gal_cont['gal'];

// <div style="float:left;" id="hsort">HS</div> - 	<div id="rsort">HS</div>
?>


<div id="container" class="photos clearfix">			
<?php 		
if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	?>
		<?php  					
           if ($array_rub['img'][$rub['rub_id']]) 
            foreach ($array_rub['img'][$rub['rub_id']] as $key=>$img) {                	
 			if ($key >= $b->us_formule_img_nb ) break;
				
			$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
			$img['img_titre'] = strip_tags($img['img_titre']);
			
			if ($img['img_fichier']!='') {		                    
            ?>
            <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>				
				<div class="photo rub_<?=$k;?>" >					
			   	<a href="<?=$b->url_abs_site.$b->rep_img900.$img['img_fichier'];?>" class="fancybox"  rel="gallery_rub_<?=$k;?>">
			   		<?php  //list($width, $height, $type, $attr) = getimagesize($b->url_abs_site.$b->rep_img320.$img['img_fichier']); ?>
			       	<img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img550.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" class="tooltip2" title="<?=$img['img_titre'];?>"></img>
				   	<div class="element_titre">
				    <?=$img['img_titre'];?>
				    </div>
			    </a>
				</div>				
        	<?php  
			}
			} 
			?>					
	<?php 
	}			
	?>
</div>



<div style="clear: both;"></div>
<div id="nav_ptf_pos3"></div>

