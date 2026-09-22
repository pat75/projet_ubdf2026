{{-- Porte depuis 2011_html_pages_v2/classique2015/ultrabook_news.php (_outils/porter_gabarits.py) --}}
<div class="news ub_font_act ">	
		<div id="cms">			
			<!-- titre -->
			<?php  /*if ($pag_select['img_titre']) : ?>
			<h2><?=$pag_select['img_titre']?></h2>
			<?php  endif; */?>
			<div class="news_centre">
			<?php if (isset($pag_select['img_html'])) echo book_actu_txt ($pag_select['img_html'], $b->url_abs_site); 
			//preg_replace( "#src=\"\/users_2#", 'src="'.$b->url_abs_site.'/users_2', htmlspecialchars_decode( $pag_select['img_html'], ENT_QUOTES ) ); ?>	
			</div>		
		</div>   
</div>