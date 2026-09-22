{{-- Porte depuis 2011_html_pages_v2/classique2015/ultrabook_accueil.php (_outils/porter_gabarits.py) --}}
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

<!-- accueil ptf 2014 -->
<div class="grid">
<div class="unit whole no-gutter " id="cont_accueil">
	
		
		
<?php /*
<!-- cke - haut 
		<div class="cont_cke_edit">
			<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_acceuil_haut" >
			<?=$b->ed_dom_txt->cont_acceuil_haut;?>
			</div>			
		</div>
		-->
		<!-- img accueil -->*/
		
		
		
if ( $b->cont_visuel2015_accueil == '' )  $b->cont_visuel2015_accueil = $b->url_abs_site."/2012_web/classique2015/img/ub_default_1180x600.gif";

?>
	

		<a href="/portfolio" class="img_file_ajax_link">
			<div id="menu_visuel_accueil" class="img_file_ajax_FineUp <?=($b->cont_visuel2015_accueil == 'deleted')?'img_file_ajax_deleted':''?>" 
				data-fileapi_id="us_pf_clas2015_visuel_accueil" 
				data-img_default="/2012_web/classique2015/img/ub_default_1180x600.gif" >		
				<?php  if ($b->cont_visuel2015_accueil!= 'deleted') { ?>				 
					<img  
					src="<?=$b->splugin('ub_img_gestioncache', $b->cont_visuel2015_accueil, $b->rep_pref, $b->url_abs_site )?>" 
					alt="<?=$b->cont_page_titre;?>" class="img_file_modify" title="<?=$b->cont_visuel2015_accueil?>" > 	
				<?php }?>
			
			</div> 
		</a>




		<!-- cke - bas -->
		<div class="cont_cke_edit">
			<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_acceuil_bas" >
			<?=$b->ed_dom_txt->cont_acceuil_bas;?>
			</div>			
		</div>

</div>
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


