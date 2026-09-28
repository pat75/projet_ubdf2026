{{-- Porte depuis 2011_html_pages_v2/classique2015/ultrabook_menutop.tlp.php (_outils/porter_gabarits.py) --}}
<?php 

if ( $b->cont_visuel2015_1 == '' )  $b->cont_visuel2015_1 = $b->url_abs_site."/2012_web/classique2015/img/top_1.gif";
if ( $b->cont_visuel2015_2 == '' )  $b->cont_visuel2015_2 = $b->url_abs_site."/2012_web/classique2015/img/top_2.gif";
if ( $b->cont_visuel2015_3 == '' )  $b->cont_visuel2015_3 = $b->url_abs_site."/2012_web/classique2015/img/top_3.gif";

?>





<div id="menu_top">
		<a href="/accueil" class="img_file_ajax_link">
			<div id="menu_visuel_top1" class="img_file_ajax_FineUp infobulles <?=($b->cont_visuel2015_1 == 'deleted')?'img_file_ajax_deleted':''?>" 
				data-fileapi_id="us_pf_clas2015_visuel_top1" 
				original-title="Vous pouvez déposer ici votre visuel pour - Accueil - par un glisser-poser (jpeg ou Gif en RVB de 290x110pixels)"
				data-img_default="/2012_web/classique2015/img/top_1.gif" >	
				  <?php  if ($b->cont_visuel2015_1 != 'deleted'&& $b->cont_visuel2015_1 != '' ) { ?>
				  <img  src="<?=(preg_match("#http#",$b->cont_visuel2015_1)?'':$b->url_abs_site.$b->rep_pref).$b->cont_visuel2015_1;?>?<?=rand(0,9999)?>" alt="<?=$b->cont_page_titre;?>" class="img_file_modify" />
				  <?php }?> 	
			</div> 
		</a>
		<a href="/portfolio" class="img_file_ajax_link">
			<div id="menu_visuel_top2" class="img_file_ajax_FineUp infobulles <?=($b->cont_visuel2015_2 == 'deleted')?'img_file_ajax_deleted':''?>" data-fileapi_id="us_pf_clas2015_visuel_top2" 
			original-title="Vous pouvez déposer ici votre visuel pour - Portfolio - par un glisser-poser (jpeg ou Gif en RVB de 620x110pixels)"
			data-img_default="/2012_web/classique2015/img/top_2.gif" >			
				  <?php  if ($b->cont_visuel2015_2 != 'deleted' && $b->cont_visuel2015_2 != ' ' ) { ?>				 
				  <img  src="<?=(preg_match("#http#",$b->cont_visuel2015_2)?'':$b->url_abs_site.$b->rep_pref).$b->cont_visuel2015_2;?>?<?=rand(0,9999)?>" alt="<?=$b->cont_page_titre;?>" class="img_file_modify" />
				  <?php }?> 	
			</div> 
		</a>
		<a href="/news" class="img_file_ajax_link">
			<div id="menu_visuel_top3" class="img_file_ajax_FineUp infobulles <?=($b->cont_visuel2015_3 == 'deleted')?'img_file_ajax_deleted':''?>" data-fileapi_id="us_pf_clas2015_visuel_top3" 
			original-title="Vous pouvez déposer ici votre visuel pour - Contact - par un glisser-poser (jpeg ou Gif en RVB de 270x110pixels)"
			data-img_default="/2012_web/classique2015/img/top_3.gif" >		
				  <?php  if ($b->cont_visuel2015_3 != 'deleted' && $b->cont_visuel2015_3 != ' ' ) { ?>
				  <img  src="<?=(preg_match("#http#",$b->cont_visuel2015_3)?'':$b->url_abs_site.$b->rep_pref).$b->cont_visuel2015_3;?>?<?=rand(0,9999)?>" alt="<?=$b->cont_page_titre;?>" class="img_file_modify" />
				  <?php }?>	
			</div> 
		</a>			
</div>
