{{-- Porte depuis 2011_html_pages_v2/responsive/ultrabook_2012_menugauche.tlp.php (_outils/porter_gabarits.py) --}}





<a href="/" class="upload_img_del_link" >
	<div id="cont_accueil_logo_d" class="img_file_ajax_FineUp <?=($b->cont_visuel2014 == 'deleted')?'img_file_ajax_deleted':''?> " data-fileapi_id="us_pf_visuel2014" >	
		  <?php  if ($b->cont_visuel2014 != 'deleted') { ?>
		    	<img  src="<?=$b->url_abs_site.$b->rep_pref.$b->cont_visuel2014;?>" alt="<?=$b->cont_page_titre;?>" class="img_file_modify">
		  <?php }?>
	</div> 
</a>



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
<br/>

<!-- cke 1 -->
<div class="cont_cke_edit">
	<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_menu_gauche" >
	<?=$b->ed_dom_txt->cont_menu_gauche;?>
	</div>
	
</div>



  
                      
<div class="cont_accueil_contenu_aff" id="cont_accueil_contenu_aff_d">
<?php 
// affiche la contenu de la page accueil
// a la position D
// 
if ( $b->accueil_contenu_aff_d != 'false' && $b->accueil_contenu_aff_d != '' or (request()->cookie('us_pr') !== null) or request()->query('pr')=='true') {
	$key = recursive_array_search($b->accueil_contenu_aff_d, 	$b->gal_cont_accueil);
	echo   book_actu_txt ($b->gal_cont_accueil[$key]['img_html'], $b->url_abs_site );
}
?>
</div>	





<!-- nav ptf -->
<nav id="nav_">
<ul id="nav_ptf2014">
<li class="accueil">
	<a href="/" class="ub_menu_titre ub_font_menut" id="ub_menu_titre_accueil">Accueil</a>
</li>
<li>
	<a href="#" class="ub_menu_titre ub_font_menut" id="ub_menu_titre_ptf">Portfolio</a>
	<ul>
	<?php   
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

<li>
	<a  href="#" class="ub_menu_titre ub_font_menut" id="ub_menu_titre_actu">Actualités</a>	
	<?php 
	// nav
	if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
	list ($tmp_html, $pag_select) = responsive__front_nav_2015($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
	echo $tmp_html;
	?>
</li>

</ul>
</nav>
<!-- nav fin -->

<!-- cke 2 -->
<div class="cont_cke_edit">
	<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_menu_gauche2" >
	<?=$b->ed_dom_txt->cont_menu_gauche2;?>
	</div>	
</div>


<!-- bloc contenu accueil -->
<div class="cont_accueil_contenu_aff" id="cont_accueil_contenu_aff_a">
<?php 
// affiche la contenu de la page accueil
// a la position A
// 
//$b->accueil_contenu_aff_a = false;
if ( $b->accueil_contenu_aff_a != 'false' && $b->accueil_contenu_aff_a != ''  or (request()->cookie('us_pr') !== null) or request()->query('pr')=='true') {
	$key = recursive_array_search($b->accueil_contenu_aff_a, 	$b->gal_cont_accueil);
	echo   book_actu_txt ($b->gal_cont_accueil[$key]['img_html'], $b->url_abs_site ); //htmlspecialchars_decode( $b->gal_cont_accueil[$key]['img_html'], ENT_QUOTES ); 
}
?>
</div>		

<br clear="all"/>
<div class="social-bookmarks-services group" > 	
<style>
	.social-bookmarks-services a {
		font-size:26px;
		text-decoration: none;
	}
</style>	
<?php if ($b->us_partage_lien=='1') { ?>	
 		<?php  list($tmp_titre, $tmp_url) = book_socializer ($b->cont_page_titre); ?>              
        <div class="social-bookmarks-services" >
		   
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
		    									 
		  		<a class="pin-it-button"  href="javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)})());">
				<img class="icon24x"  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAACWUlEQVRIibVWMWtUQRDeCAYbDSoagpiTFAqxFcGAIJYRoz9AUsRCRAiCyFk+sLE+7u65u/N9ghYHVmJ3Yn6AomAlioWCERRFYiwkgUts5ulm7t2LAbOwxdud/b6db2Z2nnMVQ0RqIlIH0CW5SHJF5yKArojURaRWhVE6vPfjADoAeiTXqyaAHoCO9378n8ABzABY3gy4hGgZwEwlOMl5kmvm8BeSt0MIUzHGURHZR3JSROZILlhvSM4PvLkFB3C/0Wjscc65LMt2xRhPi8j5EMLR4pyInCP53Ui20RPV/KcBz3V7CMANAEvGswXv/Zhe7hSA1VSuDTEB0DHgb7MsG3bODZF8WKH7qyzLdqi8d81ep3CxVpItV5X4shq/JjmR5/lBki9T2xDClHPOxRjP2HiISM0BuGVvVmhM8jnJ9RjjdJIIdwzQNSUYtTgiUnckn9gN7/2IerCkILsTgkeG4LrGcaREwq7TCt2w0Wq1DivBG107oJk0bGtERC6q7bGSOC06LX3LPJvUxXqM8YLKcNbYrRbkJC+VEKwMInjnvR8jOalrE3rLm8b2QSLd41ICAJ8GpOEPAM8AfCxAYozTSTG+aDab+zUTT7L/BfgjUV+Qy25Z5Hue50cAnEi+DwH4MKBOuqVpaoI4p/LMArhXPM/tdnuviFwB8LXibH1QoaU1cVxlaCfrvzbx+m+hlT0VyVzz3u/UIL7fDNQQdOxjV9YDvjnnXAhhaovgy30NSBtNzxh+1r2nWwDvf66TXLYNZwVAthVwDmo4xpPtaZkmJtvT9NPxP35bfgPyz5P+gTt51AAAAABJRU5ErkJggg=="/>
				</a>							    
		    			    
		</div>		
<?php  } ?>
</div>
					
					
<?php 

// aff nav actu -menu gauche
// nav - func
//



?>