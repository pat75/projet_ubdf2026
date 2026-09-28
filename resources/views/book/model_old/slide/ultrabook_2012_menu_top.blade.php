{{-- Porte depuis 2011_html_pages_v2/slide/ultrabook_2012_menu_top.tlp.php (_outils/porter_gabarits.py) --}}

<div class="grey" >

<!-- nav ptf -->
<ul id="mega-menu" class="mega-menu">

	
	
<li>
	<a href="#" id="ub_menu_titre_accueil" class="ub_font_menut">Accueil</a>
</li>



<!-- nav actu -->
<li  style="float:right;">
	<a href="#" id="ub_menu_titre_actu" class="ub_font_menut">Actualités</a>
	<ul id="nav" >
		<?php 
		// nav
		if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
		list ($tmp_html, $pag_select) = slide__front_nav_2011($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
		echo $tmp_html;
		?>
	</ul>
</li>




<!-- nav pft -->
<li  style="float:right;" class="dir_right">
	<a href="#" id="ub_menu_titre_ptf" class="ub_font_menut ">Portfolio</a>
	<ul id="nav_ptf2012">
	<?php   
	
	if ($b->menu['ptf']) 		
		foreach ($b->menu['ptf'] as $key=>$ptf) {
		if (!$ptf['rub_id'])  break; 
		// test formule 2ptf
		if ($key > $b->us_formule_img_rub_nb) break;
		?>
	
		<?php if (is_array($b->menu['ptf']['img'][$ptf['rub_id']]) && $ptf['rub_id'] == $b->rub_id ) { // class="ub_open" ?>			
		<li>
			<a href="<?=wd_remove_accents($ptf['rub_nom'])."-p".$ptf['rub_id'];?>" rel="img_<?=$ptf['rub_id']?>" class="ub_font_menu_newsr ub_open">
				<?=$ptf['rub_nom']?>
			</a>
		</li>	
		
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





</ul>
</div>








<?php 
//
// nav
//



?>