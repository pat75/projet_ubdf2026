{{-- Porte depuis 2011_html_pages_v2/ultrabook_accueil.php (_outils/porter_gabarits.py) --}}


<?php  if ($b->gal_cont['img'])  foreach ($b->gal_cont['img'] as $key=>$pag_select)	{ ?>
<div>
<?=preg_replace( "#(src|href)=\"\/users_2#", '$1="'.$b->url_abs_site.'/users_2', htmlspecialchars_decode($pag_select['img_html']) ); ?>	
</div>
<br clear="all" />
<?php  } ?>


<div style="clear: both;"></div>