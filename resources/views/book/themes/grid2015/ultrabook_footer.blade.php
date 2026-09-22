{{-- Porte depuis 2011_html_pages_v2/grid2015/ultrabook_footer.php (_outils/porter_gabarits.py) --}}



<footer  role="contentinfo">
<div 	class="footer">
<div class="footer_ub">
    
			    <?php if ($b->us_formule==1 && $b->cont_piedpage=='[invisible]'  || $b->inc_action_view == 'df'  ) { ?>
			    	
			    <?php  } else { ?>	
					
					<?php if ($b->us_formule==1 && !empty($b->cont_piedpage) ) { ?>
						<?=htmlspecialchars_decode($b->cont_piedpage,ENT_QUOTES);?>					
					<?php  } else { ?>

							<a href="http://ultra-book.fr" class="footer_ub">
							<div class="icon-ub"></div>
							<div class="ub_logo_text"><strong>Ultra-book</strong></a></div>
							
					<?php  } ?>
					<br clear="all"/>
					<div class="social-bookmarks-services group"> 	
					<?php if ($b->us_partage_lien=='1') { ?>	
					 		<?php  list($tmp_titre, $tmp_url) = book_socializer ($b->cont_page_titre); ?>              
				            <div class="social-bookmarks-services">
							   
							        <a href="http://www.facebook.com/sharer.php?u=<?=$tmp_url;?>"  title="Partager sur 'Facebook'" rel="nofollow" target="_blank">
							        	<i class="icon-facebook_circle icon24x"></i>
							        </a>
							    
							        <a href="http://twitter.com/home?status=<?=$tmp_url;?>"  title="Partager sur 'Twitter'" rel="nofollow" target="_blank">
										<i class="icon-twitter_circle icon24x"></i>
									</a>							   			
							   			
							        <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>"   title="Partager sur 'LinkedIn'" rel="nofollow" target="_blank">
										<i class="icon-linked_in_circle icon24x"></i>
									</a>
									
							        <a href="http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>"  title="Partager sur 'Google'" rel="nofollow" target="_blank">
							        	<i class="icon-google_plus_circle icon24x"></i>
							        </a>
							    							    
							     	<a href="http://www.viadeo.com/shareit/share/?url=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>&amp;urllanguage=fr" title="Partager sur 'Viadeo'" rel="nofollow" target="_blank">
									<img class="icon24x" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAACbElEQVRIibWWPWgUURSFX4Jg1EIiVv4kpLAWFGwUUvpTxMpCBCttRFOJK4gw1guLLGZ3X979Dsaoq0jESrJpJBYRsY6gjagbxUICEQxJ2MQiE9lsZiYrmAcPhnlz/865951xLmOZWa+Z5YCapLqkhXjXgZqZ5cysN8tH4vLe9wBVoCFpJWsDDaDqve9pyzkwAMxt5jgh0BwwkOlc0uAmWc9mnC3HtoOpmUta3iTLr5ImEs6+AxebIFtfSYx5u7DcBRbj59/AgxBCn3OuQ9KPNbjWcQJU/wHvz5LGJY2VSqVu55yLoqgziqIuYLKp2qpzbrUV2+mWFqimvPe71xI0s2Nxom+bu8vMeh1wM8PRkqRHwAVJ/SGEM8AI8M0511EoFHYAJwqFwo5isbhd0nyzvZnlXAppK5I+AYedc25oaOjg8PDwSe/9EeAeMAWUQwhnm2C+nZBgzQEzSe0YQugrlUrdwPOE8wngpaRbZnbczO6kwFx3Wh39lZbSrkRRtE3SmxTongEjbfC1kBRg3nu/08wuZbWqpDxwGniYGaAVIuBdPNUvMsi/LGnczPaY2dWMAPUkkl/FpE2mGYYQjkqarVQqh9YmOCWR2oY2BabjCkZTDD9KOg8s5fP5XWZ2PS2AmeU2DBrQCCEcMLNTKYY3JH0BnsaV1lKyb/zVioSrohJXcb/l/WtJT4AZYF8M1XJKgGrrZferJfq5KIo6gWvAe+CxpFFgpFwu7/fe7wU+pDif2yBArdc1sGhmuSiKuuJPOpp0oz/mIhGaVOFRguAAPyWNAUVAwHRG16QLTnMlWyaZLZxsjeg3r//x2/IHDtDRorAVkD4AAAAASUVORK5CYII="/></a>
																		 
							  		<a class="pin-it-button"  href="javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)})());">
									<img class="icon24x"  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAACWUlEQVRIibVWMWtUQRDeCAYbDSoagpiTFAqxFcGAIJYRoz9AUsRCRAiCyFk+sLE+7u65u/N9ghYHVmJ3Yn6AomAlioWCERRFYiwkgUts5ulm7t2LAbOwxdud/b6db2Z2nnMVQ0RqIlIH0CW5SHJF5yKArojURaRWhVE6vPfjADoAeiTXqyaAHoCO9378n8ABzABY3gy4hGgZwEwlOMl5kmvm8BeSt0MIUzHGURHZR3JSROZILlhvSM4PvLkFB3C/0Wjscc65LMt2xRhPi8j5EMLR4pyInCP53Ui20RPV/KcBz3V7CMANAEvGswXv/Zhe7hSA1VSuDTEB0DHgb7MsG3bODZF8WKH7qyzLdqi8d81ep3CxVpItV5X4shq/JjmR5/lBki9T2xDClHPOxRjP2HiISM0BuGVvVmhM8jnJ9RjjdJIIdwzQNSUYtTgiUnckn9gN7/2IerCkILsTgkeG4LrGcaREwq7TCt2w0Wq1DivBG107oJk0bGtERC6q7bGSOC06LX3LPJvUxXqM8YLKcNbYrRbkJC+VEKwMInjnvR8jOalrE3rLm8b2QSLd41ICAJ8GpOEPAM8AfCxAYozTSTG+aDab+zUTT7L/BfgjUV+Qy25Z5Hue50cAnEi+DwH4MKBOuqVpaoI4p/LMArhXPM/tdnuviFwB8LXibH1QoaU1cVxlaCfrvzbx+m+hlT0VyVzz3u/UIL7fDNQQdOxjV9YDvjnnXAhhaovgy30NSBtNzxh+1r2nWwDvf66TXLYNZwVAthVwDmo4xpPtaZkmJtvT9NPxP35bfgPyz5P+gTt51AAAAABJRU5ErkJggg=="/>
									</a>							    
							    			    
							</div>		
					<?php  } ?>
				<?php  } ?>	
				</div>	
</div>
</div>
</footer>
					

		
		
		
<?php /*
<!-- bloc data contenu accueil - uniquement affichee pour l'admin -->
<div style="display:none">
<?php  if ( (request()->cookie('us_pr') !== null) or request()->query('pr')=='true' ) foreach ($b->gal_cont_accueil as $cont_accueil) { ?>
	<div id="accueil_contenu__<?=$cont_accueil['img_id'];?>">
		<?php //=htmlspecialchars_decode( $cont_accueil['img_html'], ENT_QUOTES );?>
		<?=htmlspecialchars_decode(  book_actu_txt($cont_accueil['img_html'],$b->url_abs_site), ENT_QUOTES );?>
	</div>
<?php }?>
</div>
*/?>

<!-- bloc data fin -->