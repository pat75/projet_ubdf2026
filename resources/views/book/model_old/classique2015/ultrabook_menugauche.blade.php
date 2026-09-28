{{-- Porte depuis 2011_html_pages_v2/classique2015/ultrabook_menugauche.tlp.php (_outils/porter_gabarits.py) --}}




<?php /*
view-source:http://ckeditor.com/latest/samples/jquery.html
http://ckeditor.com/forums/Support/Refresh-in-line-instances.

=MYSQL
ub2_edit_txt =TABLE 
- id
- id_us
- modele
- data  =JSON (concerne toutes les pages+menus)
- dom_id => cont_menu_gauche
- dom_data => txtxtxtxtxtxtt
*/?>
<br/>

<!-- cke 1 -->
<div class="cont_cke_edit">
	<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_menu_gauche" >
	<?=$b->ed_dom_txt->cont_menu_gauche;?>
	</div>
	
</div>



  
                      
<div class="cont_accueil_contenu_aff" id="cont_accueil_contenu_aff_d">
<?php 
// affiche la contenu de la page accueil
// a la position D
// 
if ( $b->accueil_contenu_aff_d != 'false' && $b->accueil_contenu_aff_d != '' or (request()->cookie('us_pr') !== null) or request()->query('pr')=='true') {
	$key = recursive_array_search($b->accueil_contenu_aff_d, 	$b->gal_cont_accueil);
	echo   book_actu_txt ($b->gal_cont_accueil[$key]['img_html'], $b->url_abs_site );
}
?>
</div>	

<!-- nav ptf -->
<nav id="nav_">
<ul id="nav_ptf2014">
<li class="accueil"  >
	<a href="/" class="ub_menu_titre ub_font_menut " id="ub_menu_titre_accueil" >Accueil</a>
</li>
<li>
	<a href="#" class="ub_menu_titre ub_font_menut" id="ub_menu_titre_ptf">Portfolio</a>
	<ul id="ptf_thumbs">
	<?php   
	
	
	//print_r($b->menu['ptf']);
	//echo '-----------------------';	
	//print_r($b->menu['act']);
	

	//print_r($b->gal_cont['gal']);
	// rub par default
	if ($b->rub_id==0)  $b->rub_id = $b->menu['ptf'][0]['rub_id'];
	//echo $b->rub_id;
	
	
	
	
	if ($b->menu['ptf']) 		
		foreach ($b->menu['ptf'] as $key=>$ptf) {
		if (!$ptf['rub_id'])  break; 
		// test formule 2ptf
		if ($key > $b->us_formule_img_rub_nb) break;
		?>
	
		<?php if (is_array($b->menu['ptf']['img'][$ptf['rub_id']]) && $ptf['rub_id'] == $b->rub_id ) { ?>
				
		<li class="ub_open" >
			<a href="<?=wd_remove_accents($ptf['rub_nom'])."-p".$ptf['rub_id'];?>" rel="img_<?=$ptf['rub_id']?>" class="ub_font_menu_newsr ub_open">
			<?=$ptf['rub_nom']?>
			</a>
			
			<?php  
		
				$array_rub = 	$b->gal_cont['gal'];
				$rub = 			$array_rub[$key];
				//$array_img = 	$b->gal_cont['img'][$rub['rub_id']];				
				
				$array_img = 	$b->gal_cont['gal']['img'][$rub['rub_id']];				
				
				
				// si 1ere image existe
				if (is_array($array_img)) classique2015__ub_aff_thumbs($array_img, $b->url_abs_site, $b->rep_img40, $b->rep_img900, $b->us_formule_img_nb); 
				
				?>
				
		</li>		
		<!--<div id="nav_ptf_pos2"></div><div style="clear: both;"></div>-->
		<?php  } else { ?>				
		<li>
			<a href="<?=wd_remove_accents($ptf['rub_nom'])."-p".$ptf['rub_id'];?>" rel="img_<?=$ptf['rub_id']?>" class="ub_font_menu_newsr">
			<?=$ptf['rub_nom']?>
			</a>
		</li>		
		<?php  } ?>
	
	<?php }?>
	</ul>
</li>
<!-- fin nav ptf -->






<!-- nav actu -->

<li>
	<a  href="#" class="ub_menu_titre ub_font_menut" id="ub_menu_titre_actu">Actualités</a>	
	<?php 
	// nav
	if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
	list ($tmp_html, $pag_select) = classique2015__front_nav_2015($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
	echo $tmp_html;
	?>
</li>

</ul>
</nav>
<!-- nav fin -->

<!-- cke 2 -->
<div class="cont_cke_edit">
	<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_menu_gauche2" >
	<?=$b->ed_dom_txt->cont_menu_gauche2;?>
	</div>	
</div>


<?php /*
<!-- bloc contenu accueil -->
<div class="cont_accueil_contenu_aff" id="cont_accueil_contenu_aff_a">
<?php 
// affiche la contenu de la page accueil
// a la position A
// 
//$b->accueil_contenu_aff_a = false;
if ( $b->accueil_contenu_aff_a != 'false' && $b->accueil_contenu_aff_a != ''  or (request()->cookie('us_pr') !== null) or request()->query('pr')=='true') {
	$key = recursive_array_search($b->accueil_contenu_aff_a, 	$b->gal_cont_accueil);
	echo   book_actu_txt ($b->gal_cont_accueil[$key]['img_html'], $b->url_abs_site ); //htmlspecialchars_decode( $b->gal_cont_accueil[$key]['img_html'], ENT_QUOTES ); 
}
?>
</div>		
*/?>



<?php 


// aff nav ptf -menu gauche
// aff thumbs 
//






// aff nav actu -menu gauche
// nav - func
//



?>

