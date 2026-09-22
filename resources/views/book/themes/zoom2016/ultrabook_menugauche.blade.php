{{-- Porte depuis 2011_html_pages_v2/zoom2016/ultrabook_menugauche.tlp.php (_outils/porter_gabarits.py) --}}
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




								

<!-- nav ptf -->

<ul class="menu">
<li class="section">
	<a href="/" class="ub_menu_titre ub_font_menut " id="ub_menu_titre_accueil" ><?=__('Accueil')?></a>
</li>
<li class="section">
	<a href="#portfolio" class="ub_menu_titre ub_font_menut" id="ub_menu_titre_ptf"><?=__('Portfolio')?></a>
	
	<ul class="sub-section">
	<?php   
	
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
				//if (is_array($array_img)) ub_aff_thumbs($array_img, $b->url_abs_site, $b->rep_img40, $b->rep_img900, $b->us_formule_img_nb); 
				
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

<li class="section">
	
	<a  href="#" class="ub_menu_titre ub_font_menut link_bio"><?=__('Actualités')?></a>
	<?php 
	// nav
	if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
	list ($tmp_html, $pag_select) = zoom2016__front_nav_2015($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
	echo $tmp_html;
	?>
</li>

</ul>
</nav>
<!-- nav fin -->
