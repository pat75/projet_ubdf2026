{{-- Porte depuis 2011_html_pages_v2/non_diffuse/ultrabook_2012_accueil.php (_outils/porter_gabarits.py) --}}
<?php 
// affiche la contenu de la page accueil
// a la position C
// echo '='.$b->accueil_contenu_aff_c.'-';
// 
if ( $b->accueil_contenu_aff_c != 'false' && $b->accueil_contenu_aff_c != '' or (request()->cookie('us_pr') !== null) or request()->query('pr')=='true' ) { ?>
<div class="grid">
<div class="unit whole cont_accueil_contenu_aff"  id="cont_accueil_contenu_aff_c">
<?php 
	$key = recursive_array_search($b->accueil_contenu_aff_c, 	$b->gal_cont_accueil);
	echo   book_actu_txt ($b->gal_cont_accueil[$key]['img_html'], $b->url_abs_site );   
?>
</div>
</div>
<?php }
?>
<?php /*
<script>
	$(function() {
    $(".imgLiquidFill").imgLiquid({
        fill: true,
        horizontalAlign: "center",
        verticalAlign: "center"
    });   
});
</script>
*/?>


<!-- accueil ptf 2014 -->
<div id="mdl2014_accueil_ptf" >


<?php  $tmp_rub_k = 0;// print_r($b->menu); ?>
<?php  
if ( ( $b->menu['ptf']['img'] && \App\Services\Book\Php7::count($b->menu['ptf']['img'])>0 && $b->accueil_ptf_vignette_aff != 'false' ) || ( (request()->cookie('us_pr') !== null) or request()->query('pr')=='true' )  ) 		
if ($b->menu['ptf']['img']) {
	
//print_r($b->menu['ptf']['img']);

foreach ($b->menu['ptf']['img'] as $key=>$img) {
	
	//if ( !empty($img) ) print_r($img);		
	// \App\Services\Book\Php7::count($img) > 0 &&		 
	
	
	if ($tmp_rub_k > $b->us_formule_img_rub_nb) break;// test formule	
	
	
	
	
	$tmp_rub_k++;
	$img_ = $img[0];
	
	if ( ! empty($img) ) {

	
	if (preg_match('/\.swf$/', $img_['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>
		
		
	<a  	href="<?=wd_remove_accents($b->menu['ptf'][$tmp_rub_k-1]['rub_nom'])."-p".$b->menu['ptf'][$tmp_rub_k-1]['rub_id'];?>" 
			rel="img_<?=$b->menu['ptf'][$tmp_rub_k-1]['rub_id']?>" 
			title="<?=$b->menu['ptf'][$tmp_rub_k-1]['rub_nom']?>">	
	
	<div class="ub_hover">
		<div class="ub_hover_default">
	
				<div class="unit two-fifths gutters_1px">		
				<div class="img_header " id ="img_<?=$key?>">		
				<?php  if ($tmp_swf) { ?>
					<?=$b->url_abs_site.$b->rep_img_.$img_['img_fichier']; ?>                    
			    <?php  } else { ?>  
					<?php /*
					 * <div class="imgLiquidFill imgLiquid img_header " id ="img_<?=$key?>">		
					 * <img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img550.$img_['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" alt="<?=$img['img_titre'];?>" />
					 * */
					if (preg_match('#ultra-book_default_#', $img_['img_fichier'])) {	?>
					<img  src="/img_default/ultra-book_default_368x368.gif" width="368" height="368" alt="<?=$img['img_titre'];?>" />
					<?php  } else { ?>				
					<img  src="<?='/books/'.$b->us_dir.'/carre_368/'.$img_['img_fichier']?>"  alt="<?=$img['img_titre'];?>" />
					<?php  } ?>
				<?php  } ?>		
			    </div>               
				</div>			
				
			 	<div class="unit three-fifths hide-on-mobiles gutters_1px">
				<div class="grid ">
			 		<?php  
			 		// les 6 autres images
			 		$img = \App\Services\Book\Php7::array_slice($img, 1);
			 		foreach ($img as $k=>$img__) {
			 		if ($k > 5) break;
					if ($img__['img_fichier']!='') {		 	
			 		?>
			 		<?php  if ($k==3) { ?>
			 			</div>  					          
						<div class="grid">
			 		<?php }?>
			 		<div class="unit one-third"> 			
			 		<div class="img_thumb"> 			
			 			
			 			<?php /*
						 * <div class="imgLiquidFill imgLiquid img_thumb"> 	
						 * <img  src="<?=$b->url_abs_site.$b->rep_img550.$img__['img_fichier'];?>" />
						 * */
						if (preg_match('#ultra-book_default_#', $img__['img_fichier'])) {	?>
							<img  src="/img_default/ultra-book_default_183x183.gif" width="183" height="183" alt="<?=$img['img_titre'];?>" />
						<?php  } else { ?>	 
				 			<img  src="<?='/books/'.$b->us_dir.'/carre_183/'.$img__['img_fichier']?>" />
				       <?php  } ?>
			        </div>
			        </div>			
					<?php 
					}
					}
			 		?>
			     </div> 
				 </div>
		</div>		 
		<div class="ub_hover_on ">
				<h1><?=$b->menu['ptf'][$tmp_rub_k-1]['rub_nom']?></h1>
		</div>	
 	</div>
 	</a>
 	
<?php  
} 
}
} 
?>

</div>
<!-- end- accueil ptf 2014 -->




<?php 
// affiche la contenu de la page accueil
// a la position B
// 
if ( $b->accueil_contenu_aff_b != 'false' && $b->accueil_contenu_aff_b != '' or (request()->cookie('us_pr') !== null) or request()->query('pr')=='true') { ?>	
<div class="grid">
<div class="unit whole cont_accueil_contenu_aff"  id="cont_accueil_contenu_aff_b">
<?php 
$key = recursive_array_search($b->accueil_contenu_aff_b, 	$b->gal_cont_accueil);
	echo   book_actu_txt ($b->gal_cont_accueil[$key]['img_html'], $b->url_abs_site );  //htmlspecialchars_decode( $b->gal_cont_accueil[$key]['img_html'], ENT_QUOTES ); 
?>
</div>
</div>
<?php  } ?>							
<br clear="all" />