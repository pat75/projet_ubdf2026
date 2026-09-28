{{-- Porte depuis 2011_html_pages_v2/zoom2016/ultrabook_footer_section.php (_outils/porter_gabarits.py) --}}
<!-- footer -->
<footer  role="contentinfo" class="footer_ub">
   <div class="wrap wider">
	<div class="grid">
		<!-- unit -->	
		<div class="unit whole align-center">

			    <?php if ($b->us_formule==1 && $b->cont_piedpage=='[invisible]'  || $b->inc_action_view == 'df'  ) { ?>
			    	
			    <?php  } else { ?>	

					<?php if ($b->us_formule==1 && !empty($b->cont_piedpage) ) { ?>
                        <div class="footer_center">
						<?=htmlspecialchars_decode($b->cont_piedpage,ENT_QUOTES);?>
                        </div>
					<?php  } else { ?>


					<?php  } ?>

				<?php  } ?>


		</div>
	</div>
	</div>



</footer>					
<!-- footer -->



<customhtml>
    <div class="custom_ubdf_link">
        <a href="<?=$b->inc_url_dom_www?>" target="_blank"><?=__('Fonctionne avec')?> <?=$b->inc_site_name?></a>
    </div>
</customhtml>