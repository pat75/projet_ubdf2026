{{-- Porte depuis 2011_html_pages_v2/pinter/ultrabook_2012_menugauche.tlp.php (_outils/porter_gabarits.py) --}}
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


<div id="nav_all">
<!-- nav ptf -->

<div class="ub_menu_titre ub_font_menut" id="ub_menu_titre_accueil">
	Accueil
</div>

<div class="ub_menu_titre ub_font_menut" id="ub_menu_titre_ptf">
	Portfolio
</div>

<ul id="nav_ptf2012">		
<?php 
$nb_ptf = 0; 
if ($b->menu['ptf']) 		
	foreach ($b->menu['ptf'] as $key=>$ptf) {
		
	if (!$ptf['rub_id'])  break;	
	if ($key > $b->us_formule_img_rub_nb) break;// test formule 2ptf
	$nb_ptf++;
	?>		
	<li>
		<a href="#filter=.rub_<?=$key;?>" rel="img_<?=$ptf['rub_id']?>" class="ub_font_menu_newsr" title="<?=$ptf['rub_nom']?>">
			<?=$ptf['rub_nom']?>
		</a>
	</li>

<?php }?>

<?php  if ($nb_ptf>1) { ?>
	<li ><a href="#filter=*"  class="ub_font_menu_newsr tous" title="Tout afficher">Tout afficher</a></li>
<?php  } ?>

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
	list ($tmp_html, $pag_select) = pinter__front_nav_2011($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
	echo $tmp_html;
	?>
</div>
<!-- nav fin -->
</div>





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








<?php 
//
// nav
//



?>