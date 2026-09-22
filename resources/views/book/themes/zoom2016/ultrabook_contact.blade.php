{{-- Porte depuis 2011_html_pages_v2/zoom2016/ultrabook_contact.php (_outils/porter_gabarits.py) --}}

<!-- Article -->
<article class="margintopPlus">
	
	
<!-- Section =ptf -->		
<section id="contenu_accueil" >
<div class="wrap wider ">
	<div class="grid" >
		<!-- unit -->	
		<div class="unit whole"></div>
		<!-- /unit -->	
	</div>
</div>
</section>
<!-- /Section =ptf -->


<!-- Section =bio/contact -->
<section  class="open" id="contenu_ajax">
<div class="wrap wider">
	<div class="grid" >
		
		<!-- nav actu -->
		<div class="ajax_data" id="contenu_menu">  
		<div class="unit one-third">
			
			<?php //print_r($b->obj_cont_data);?>
			<!-- gmap-contact -->	
			<?php  if( ! $b->connection_admin_book && ! $b->obj_cont_data->ptf_activer_gmap->ptf_activer_gmap ) {					
				} else { ?>				
			<div id="gmap_prec" class="<?=($b->obj_cont_data->ptf_activer_gmap->ptf_activer_gmap=='true')?'show':'hide';?>">
				<div id="" class="hide aide_ptf" style="width:340px;"><?=__('Vous pouvez modifier la carte (ci-dessous)<br/>à partir du menu: ')?><a href="<?=$b->url_abs_site?>/ubaction__user_modif_form"><?=__('Mon compte')?></a></div>
				<div id="gmap" ></div>
			</div>
			<?php }?>
			<!-- /gmap-contact -->						
		
		
		</div>
		</div>
		<!-- nav fin -->
			
		<!-- PAGE -->
		<div class="ajax_data" id="contenu">
			<div class="unit two-thirds" >	
				
				<!-- infos-contact =cke 2 -->		
				<div id="top_contact" class="cont_cke_edit ">					
					<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_menu_gauche2" >				
					<?=$b->ed_dom_txt->cont_menu_gauche2;?>					
					</div>					
				</div>							
				<!-- /infos-contact -->
				
				<!-- form-contact -->					
				<?php  if( ! $b->connection_admin_book && ! $b->obj_cont_data->ptf_activer_contact->ptf_activer_contact ) {					
				} else { ?>
				<div id="contact_box" class="<?=($b->obj_cont_data->ptf_activer_contact->ptf_activer_contact)?'':'hide';?>">
				<?=$b->contact?>
				</div>
				<?php }?>
				<!-- /form-contact -->				
				
					
			</div>		
		</div>
		<!-- /PAGE -->
	
	</div>
</div>	

<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_footer_section.php');?>

</section>
<!-- /Section =bio/contact -->

</article>
<!-- /Article -->

