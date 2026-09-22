{{-- Porte depuis 2011_html_pages_v2/base/ultrabook_2012_portfolio.php (_outils/porter_gabarits.py) --}}
<?php  



// <br/>&lt;a href=&quot;/accueil&quot;&gt;Accueil&lt;/a&gt;<br/>&lt;br/&gt;<br/>


/*<!-- 
 Start Advanced Gallery Html Containers
 http://code.google.com/p/galleriffic/issues/detail?id=13
 --> */

/*
	 [img_id] => 12157
	 [img_id_us] => 222
	 [img_publier] => publie
	 [img_titre] => test1
	 [img_titre_alt] => Nouvelle image
	 [img_link] =>
	 [img_date_crea] => 2011-07-26 19:23:06
	 [img_fichier] => test1__12157.jpg
	 [img_poids] => 11
	 [img_type] => image/jpeg
	 [img_desc] =>
	 [img_html] =>
	 [fk_rub_id] => 4164

*/

 
// bouclage rub
$array_rub = $b->gal_cont['gal'];


// page default
$img = $b->gal_cont['img'][ $array_rub[0]['rub_id'] ][0];

//print_r($img);
//print_r($array_rub);


?>



<!--[if IE]>
<style type="text/css">
div.slideshow img {
    width:expression(document.body.clientWidth > 755 ? "755px" : "auto");
    height:auto;
}
</style>
<![endif]-->

<div id="nav_ptf_pos1"></div>
	
<div class="ptf ptf_active" id="nav_ptf" >	
	
<?php 
// rub par default
if ($b->rub_id==0) $b->rub_id = $array_rub[0]['rub_id'];
		
		
if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	
    	//$kk[$k]=$k;
    	
    	if ($k > $b->us_formule_img_rub_nb) break;
		
    	?>        	
        	

	
    <?php  
    if (is_array($b->gal_cont['img'][$rub['rub_id']]) && $rub['rub_id'] == $b->rub_id )	
    { 
    
	/*
    ?>
    <a href="<?=wd_remove_accents($rub['rub_nom'])."-p".$rub['rub_id'];?>">
    <h3><?=$rub['rub_nom']?><span class="ui-icon ub_gal_img_open ui-icon-triangle-1-s"></span></h3> 
	</a>
	*/ ?>
	
	<div id="controls" class="controls"></div>
	
    <!--<div id="thumbs_<?=$k?>" class="navigation">-->
    <div id="thumbs_0" class="navigation thumbs_0">
    	<a class="pageLink prev" style="margin-top:20px;" href="#" title="Previous Page"></a>
        <ul class="thumbs noscript">
            <?php  
            
            //print_r($b->gal_cont['img'][$rub['rub_id']]);
			
            foreach ($b->gal_cont['img'][$rub['rub_id']] as $key=>$img) {                	
			
			if ($key >= $b->us_formule_img_nb ) break;
				
			//$img['img_desc'] = 	strip_tags($img['img_desc']);
			$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
			$img['img_titre'] = strip_tags($img['img_titre']);
			
			if ($img['img_fichier']!='') {		                    
            ?>
            <li>
                <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>
                <a class="thumb"
                	<?php if($img['img_titre']!=''){?>
                	rel="tooltip"
                	<?php }?>
                	name="<?=$img['img_fichier'];?>" 
                	href="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img900.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" 
                	title="<?=$img['img_titre'];?>" >
					<div class="vign40x40">					
					<img
					src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img40.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>"
					alt="<?=$img['img_titre'];?>"
					/>
					</div>
					
                </a>
                <div class="caption">          
					
                    <?php  if ($tmp_swf) { ?>
                    <div class="image-flash">
						<?=$b->url_abs_site.$b->rep_img_.$img['img_fichier']; ?>
                    </div>
                    <?php  } ?> 
                    <?php /*                       
                    <div class="image-zoom">
						<?=$b->url_abs_site.$b->rep_img_.$img['img_fichier']; ?>
                    </div>
					*/?>			
                    <div class="image-title ub_font_ptf_titre">
						<?=$img['img_titre']; ?>
                    </div>
                    <div class="image-desc ub_font_ptf_legende">
						<?=str_replace("\n", '<br/>', $img['img_desc']); ?>
                    </div>
					
					
                </div>
            </li>
            <?php  
				}
			} 
			?>
        </ul>
        <a class="pageLink next" style="margin: 6px 0 0 -4px;" href="#" title="Next Page"></a>
        <br clear="all" />
    </div>
    <?php  } else { /*?>
    
    <a href="<?=wd_remove_accents($rub['rub_nom'])."-p".$rub['rub_id'];?>">
    <h3><?=$rub['rub_nom']?><span class="ui-icon ub_gal_img_open ui-icon-triangle-1-e"></span></h3> 
	</a>
	
    <?php  */ } ?>
    




<?php  } ?>

</div>
<div style="clear: both;"></div>


<div id="gallery" class="content_pft">
    <div class="slideshow-container">
        <div id="loading" class="loader"></div>
        <div id="slideshow" class="slideshow">        	
            <?php  if (is_array($img) && $img['img_fichier']!='' ) { ?>
	            <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) { ?>
	                    <div class="image-flash">
							<?= $b->url_abs_site.$b->rep_img_.$img['img_fichier']; ?>
	                    </div>				
	            <?php  }  else { ?> 
	            		<img alt="<?=strip_tags($img['img_titre']);?>" src="<?=$b->url_abs_site.$b->rep_img900.$img['img_fichier'];?>">
	            <?php  } ?>			
            <?php  } ?>
        </div>
        <div id="zoom_lien"></div>
        <div id="caption" class="caption-container"></div>
    </div>
</div>
<div style="clear: both;"></div>
<div id="nav_ptf_pos3"></div>

