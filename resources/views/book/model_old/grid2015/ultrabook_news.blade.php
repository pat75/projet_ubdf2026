{{-- Porte depuis 2011_html_pages_v2/grid2015/ultrabook_news.php (_outils/porter_gabarits.py) --}}
<!-- PAGE -->
<div class="wrapper-djax">
	<main id="main" class="djax" data-typePage="<?php echo $typePage ?>">
		<div class="wrapper wrap-page show-fade transition ease">
			
			<div class="wrap-nav-page">
				<div class="col-gauche no-select ">
					<h1 class="titre"><?php echo strip_tags($pag_select['img_titre']); ?></h1>
				</div>
				<div class="col-droite no-select ">
					<div class="close float-L" role="button">
						<a href="./" title="Close" class="no-border bt-full">
							<span class="bt grid_icon-close">&nbsp;</span></a></div>
				</div>
			</div>

			<div class="content-iscroll"><!-- content-iscroll_desac -->
				<?php if (isset($pag_select['img_html'])) echo book_actu_txt ($pag_select['img_html'], $b->url_abs_site)?>
			</div>
		</div>
		
		<div class="back-grid show-fade hideifnojs">
			<a href="./" title="Back" class="no-border"><span class="bt icon-down">&nbsp;</span></a>
		</div>
	</main>
</div>
<!-- /PAGE -->
