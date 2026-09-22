{{-- Porte depuis 2011_html_pages_v2/ultrabook_2012_menugauche.tlp.php (_outils/porter_gabarits.py) --}}
<!-- nav ptf -->

<div class="ub_menu_titre ub_font_menut" id="ub_menu_titre_accueil">
	Accueil
</div>

<div class="ub_menu_titre ub_font_menut" id="ub_menu_titre_ptf">
	Portfolio
</div>

<ul id="nav_ptf2012">
<?php   

if ($b->menu['ptf']) 		
	foreach ($b->menu['ptf'] as $key=>$ptf) {
	if (!$ptf['rub_id'])  break; 
	// test formule 2ptf
	if ($key > $b->us_formule_img_rub_nb) break;
	?>

	<?php if (is_array($b->menu['ptf']['img'][$ptf['rub_id']]) && $ptf['rub_id'] == $b->rub_id ) { ?>
	<a href="<?=wd_remove_accents($ptf['rub_nom'])."-p".$ptf['rub_id'];?>" rel="img_<?=$ptf['rub_id']?>" class="ub_font_menu_newsr ub_open">		
	<li class="ub_open" ><?=$ptf['rub_nom']?></li>	
	</a>
	<div id="nav_ptf_pos2"></div><div style="clear: both;"></div>
	<?php  } else { ?>
	<a href="<?=wd_remove_accents($ptf['rub_nom'])."-p".$ptf['rub_id'];?>" rel="img_<?=$ptf['rub_id']?>" class="ub_font_menu_newsr">		
	<li><?=$ptf['rub_nom']?></li>	
	</a>
	<?php  } ?>

<?php }?>
</ul>
<!-- nav fin -->
<!-- nav actu class="nav_act ub_font_menu"-->
<div class="ub_menu_titre ub_font_menut" id="ub_menu_titre_actu">
	Actualités
</div>

<div id="nav" class="nav_news" >
	<?php 
	// nav
	if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
	list ($tmp_html, $pag_select) = racine__front_nav_2011($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
	echo $tmp_html;
	?>
</div>
<!-- nav fin -->

<!-- bloc contenu accueil -->
<div id="cont_accueil_contenu_aff_a">
<?php 
// affiche la contenu de la page accueil
// a la position A
// 
//$b->accueil_contenu_aff_a = false;
if ( $b->accueil_contenu_aff_a != 'false' ) {
	$key = recursive_array_search($b->accueil_contenu_aff_a, 	$b->gal_cont_accueil);
	echo   book_actu_txt ($b->gal_cont_accueil[$key]['img_html'], $b->url_abs_site ); //htmlspecialchars_decode( $b->gal_cont_accueil[$key]['img_html'], ENT_QUOTES ); 
}
?>
</div>		




<?php 
//
// nav
//



?>