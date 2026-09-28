{{-- Porte depuis 2011_html_pages_v2/grid2015/ultrabook_accueil.php (_outils/porter_gabarits.py) --}}


	<div class="djax-preload hideifnojs+"><span class="grid_icon-spin6 animate-spin"></span></div>
	 
	<div class="wrapper-djax">
		<div id="main" class="djax" data-typePage="<?php echo $typePage ?>"></div>
	</div>

	<!-- GRID -->
	

	
	<main id="grid">
		<div class="wrapper centerV wrap-grid transition scroolbarre">
			<div class="content-iscroll">
				<div class="all-item">		
				<?php  $tmp_rub_k = 0;
				
				
				//print_r($b->menu); ?>
				<?php  
				if ( ( $b->menu['ptf']['img'] && \App\Services\Book\Php7::count($b->menu['ptf']['img'])>0 && $b->accueil_ptf_vignette_aff != 'false' ) || ( (request()->cookie('us_pr') !== null) or request()->query('pr')=='true' )  ) 		
				if ($b->menu['ptf']['img']) {
					
				//print_r($b->menu['ptf']['img']);			
				foreach ($b->menu['ptf']['img'] as $key=>$img) {
	
					if ($tmp_rub_k > $b->us_formule_img_rub_nb) break;// test formule	
					
					$tmp_rub_k++;
					$img_ = $img[0];
					
					if ( ! empty($img) ) {			
					
					if (preg_match('/\.swf$/', $img_['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>
					
						
					<div class="item big type-book show-scale">	
					<a  	href="<?=wd_remove_accents($b->menu['ptf'][$tmp_rub_k-1]['rub_nom'])."-p".$b->menu['ptf'][$tmp_rub_k-1]['rub_id'];?>" 
							rel="img_<?=$b->menu['ptf'][$tmp_rub_k-1]['rub_id']?>" 
							title="<?=$b->menu['ptf'][$tmp_rub_k-1]['rub_nom']?>"
							class="bt-full"
							>										
								<?php  if ($tmp_swf) { ?>
									<?=$b->url_abs_site.$b->rep_img_.$img_['img_fichier']; ?>                    
							    <?php  } else { ?>  
									<?php 
									if (preg_match('#ultra-book_default_#', $img_['img_fichier'])) {	?>
									<img  src="<?=$b->url_abs_site;?>/ultra-book_default_368x368.gif" width="335" height="335" alt="<?=$img['img_titre'];?>" />
									<?php  } else {  ?>	
										<img  src="<?='/books/'.$b->us_dir.'/carre_335/'.$img_['img_fichier']?>"  alt="<?=$img['img_titre'];?>" />
															
									<?php  } ?>
								<?php  } ?>		
						
								<div class="bloc-titre" style="background-color:#<?=preg_replace('/#/','',$b->menu['ptf'][$tmp_rub_k-1]['rub_coul']);?>">
								<h2><?=$b->menu['ptf'][$tmp_rub_k-1]['rub_nom']?></h2>
								</div>
				 	
				 	</a>
				 	</div>
				<?php  
				} 
				}
				} 
				?>	
				
				
				
				<?php 
				// nav
				if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
				list ($tmp_html, $pag_select) = grid2015__front_nav_2015_accueil($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
				echo $tmp_html;
				?>
				
				
				
				<div class="item small type-book show-scale hidden"></div>
				<?php //affiche_grid($data_gridBook);  ?>
			</div>
		</div>
		
		</div>
	</main>
	<!-- /GRID -->


