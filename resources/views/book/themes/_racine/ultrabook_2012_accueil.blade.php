{{-- Porte depuis 2011_html_pages_v2/ultrabook_2012_accueil.php (_outils/porter_gabarits.py) --}}
<div id="cont_ptf_img" style="display:none;">
<?php  $tmp_rub_k = 0;// print_r($b->menu); ?>
<?php  if ( ( $b->menu['ptf']['img'] && \App\Services\Book\Php7::count($b->menu['ptf']['img'])>0 && $b->accueil_ptf_vignette_aff != 'false' ) || request()->cookie('us_pr')  ) 		
foreach ($b->menu['ptf']['img'] as $key=>$img) { 
	if (\App\Services\Book\Php7::count($img)<1) break;	
	
	// test formule
	if ($tmp_rub_k > $b->us_formule_img_rub_nb) break;
	
	?>
			<div class="img_accueil_bloc" >
				<?php 
				$tmp_rub_k++;
				$img = $img[0];
				?>
				
			<?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>
					
				<div class="boxgrid caption nailthumb" id ="img_<?=$key?>" >
				<a  href="<?=wd_remove_accents($b->menu['ptf'][$tmp_rub_k-1]['rub_nom'])."-p".$b->menu['ptf'][$tmp_rub_k-1]['rub_id'];?>" 
					rel="img_<?=$b->menu['ptf'][$tmp_rub_k-1]['rub_id']?>" 
					title="<?=$b->menu['ptf'][$tmp_rub_k-1]['rub_nom']?>">
					
				<?php  if ($tmp_swf) { ?>
					<?=$b->url_abs_site.$b->rep_img_.$img['img_fichier']; ?>                    
                <?php  } else { ?>  
					<img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img550.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" alt="<?=$img['img_titre'];?>" />
				<?php  } ?>
				<div class="cover boxcaption">
					<div class="ub_font_accueil ub_font_ptf_titre"><span style="color:white"><?=$b->menu['ptf'][$tmp_rub_k-1]['rub_nom']?></span></div>
				</div>
				</a>
				</div>
   		                        
				</div>			
 	
			<?php  } ?>
			
</div>
<br clear="all"/>

<div id="cont_accueil_contenu_aff_b">
<?php 
// affiche la contenu de la page accueil
// a la position B
// 
if ( $b->accueil_contenu_aff_b != 'false' ) {
	$key = recursive_array_search($b->accueil_contenu_aff_b, 	$b->gal_cont_accueil);
	echo   book_actu_txt ($b->gal_cont_accueil[$key]['img_html'], $b->url_abs_site );  //htmlspecialchars_decode( $b->gal_cont_accueil[$key]['img_html'], ENT_QUOTES ); 
}
?>
</div>										
<br clear="all" />