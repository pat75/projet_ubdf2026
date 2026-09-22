{{-- Porte depuis 2011_html_pages_v2/zoom2016/ultrabook_news.php (_outils/porter_gabarits.py) --}}




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
		<div class="unit  one-third">		
				<nav id="menugauche">
					<ul>
					<li class="section">						
						<?php 
						// <a  href="#" class="ub_menu_titre ub_font_menut link_bio" id="">Actualités</a>	
						// nav
						if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
						list ($tmp_html, $pag_select) = zoom2016__front_nav_2015($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
						echo $tmp_html;
						?>
					</li>
					</ul>
				</nav>		
		</div>
		</div>
		<!-- nav fin -->
			
		<!-- PAGE -->
		<div class="ajax_data" id="contenu">
			<div class="unit two-thirds">	
				<div id="" class="hide aide_ptf" style="left:35.5%;width:360px;"><?=__('Vous pouvez modifier les pages (ci-dessous)<br/>à partir du menu: ')?><a href="<?=$b->url_abs_site?>/ubactiontype__projet&gal_id_categorie=news"><?=__('Editeur de pages')?></a></div>
				<?php if (isset($pag_select['img_html'])) echo book_actu_txt ($pag_select['img_html'], $b->url_abs_site)?>		
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

