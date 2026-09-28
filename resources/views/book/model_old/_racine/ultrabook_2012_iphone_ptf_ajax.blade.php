{{-- Porte depuis 2011_html_pages_v2/ultrabook_2012_iphone_ptf_ajax.tlp.php (_outils/porter_gabarits.py) --}}
<script type="text/javascript">
if ( typeof jQuery == 'undefined' ) {  
	// jQuery n'est pas chargé
	// window.location
	document.location.href='http://<?=request()->getHost();?>';
}
</script>

   	

<div data-role="page" data-add-back-btn="true" id="Gallery1" class="gallery-page">

	<div data-role="header">
		<h1>
		<?php 
		// bouclage rub
		$rub = $b->gal_cont['gal'][0];		
		?>
		<?=$rub['rub_nom']?>
		</h1>
		<a data-rel="back" data-icon="arrow-l" data-iconpos="notext" >Retour</a>
	</div>

	<div data-role="content">	
		
		<ul class="gallery">
				
			<?php  
            foreach ($b->gal_cont['img'][$rub['rub_id']] as $key=>$img) {
            	
			$img['img_titre']	= addcslashes($img['img_titre'],"\\\'\"&\n\r<>");	
			$img['img_desc']	= addcslashes($img['img_desc'],"\\\'\"&\n\r<>");			                	
			if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ 
			if ($key >= $b->us_formule_img_nb_mobil) break;
		
				if ( $img['img_fichier']!='') {
				?> 				
				<li>
					<a  href="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img900.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" rel="external">
					<img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img320.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" alt="<?=$img['img_titre'];?>" class="nailthumb" />
					</a>
				</li>
				<?php  
				}
			}
			?>
		
		</ul>
		
	</div>
	
		<?php if ($b->us_partage_lien=='1') { ?>				
		<!-- AddThis Button BEGIN -->
		<div class="addthis_toolbox addthis_default_style ">
			<a class="addthis_button_facebook_send"></a>
			<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
			<a class="addthis_button_tweet"></a>
			<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
			<a href="javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)})());"><img alt="Pin It!" style='border: none;' src="http://www.blogdecodesign.fr/wp-content/themes/codium/images/pinit.gif"/></a>
			
		</div>
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4ee6410f48e8fce2"></script>
		<!-- AddThis Button END -->	
		<br/>			
		<?php  } ?>  
					
					
		<div style="padding: 6px; margin: 0 0 10px 10px;">
            <div class="footer_bloc" style="border:none;font-size:11px;text-align:center;padding-bottom:12px;">
        	<?php if ($b->us_formule==1 && $b->cont_piedpage=='[invisible]' ) { ?>			    	
			    <?php  } else { ?>					
					<?php if ($b->us_formule==1 && !empty($b->cont_piedpage) ) { ?>
						<?=htmlspecialchars_decode($b->cont_piedpage,ENT_QUOTES);?>					
					<?php  } else { ?>
						<a href="http://www.ultra-book.com" class="footer_ub"><strong>Ultra-book.com</strong> | création de book en ligne <span style="font-size:7px">2012</span></a>
					<?php  } ?>
				<?php  } ?>		
            </div>
        </div> 
	
</div>
