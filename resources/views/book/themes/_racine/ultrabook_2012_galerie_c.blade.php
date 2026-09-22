{{-- Porte depuis 2011_html_pages_v2/ultrabook_2012_galerie_c.tlp.php (_outils/porter_gabarits.py) --}}
<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!-- Consider specifying the language of your content by adding the `lang` attribute to <html> -->
<!--[if IE 7]>    <html class="no-js ie7 oldie"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">

		<meta name="description" content="">
		<link rel="shortcut icon" href="">

		<title><?=$b->prenom;?> <?=$b->nom;?> : Ultra-book galerie</title>

	
		<!--[if lt IE 9]>
			<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Oswald'  type='text/css' />
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_css/ub_galerie_b.css"/>
		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/colorbox/colorbox.css"/>
	
		<link rel="stylesheet"  href="<?=$b->url_abs_site;?>/2012_css/icones/jqm-icon-pack-2.0-original.css" />
		<style>
			/*	Resets
				------	*/
			
			html, body, div, span, object, iframe, h1, h2, h3, h4, h5, h6, 
			p, blockquote, pre, a, abbr, address, cite, code, del, dfn, em, 
			img, ins, kbd, q, samp, small, strong, sub, sup, var, b, i, hr, 
			dl, dt, dd, ol, ul, li, fieldset, form, label, legend, 
			table, caption, tbody, tfoot, thead, tr, th, td,
			article, aside, canvas, details, figure, figcaption, hgroup, 
			menu, footer, header, nav, section, summary, time, mark, audio, video {
				margin: 0;
				padding: 0;
				border: 0;
			}
			
			/*	Stuf
				------	*/
				
			.ubtop_b {
				width:980px;margin: 0 auto ;
			}
						
			.ubtop_vignette {
				float:left;margin-right: 10px;display:block;padding:4px;margin-left: -15px;
			}
			
			.rub_action {
				padding: 20px 0 18px 0;
			}
			
			
			.rub_action div {
				float:left;
				padding: 4px 60px 4px 0px;
				cursor: pointer;
			}
			.rub_action div:hover {
				color:#CECECE;
			}
			
			.visited {
				opacity: 0.4;
			}		
			
			

		/* icones */
		.ui-icon-plus, .ui-icon-minus, .ui-icon-delete, .ui-icon-arrow-r,
		.ui-icon-arrow-l, .ui-icon-arrow-u, .ui-icon-arrow-d, .ui-icon-check,
		.ui-icon-gear, .ui-icon-refresh, .ui-icon-forward, .ui-icon-back,
		.ui-icon-grid, .ui-icon-star, .ui-icon-alert, .ui-icon-info, .ui-icon-home, .ui-icon-search, .ui-icon-searchfield:after, 
		.ui-icon-checkbox-off, .ui-icon-checkbox-on, .ui-icon-radio-off, .ui-icon-radio-on, .ui-icon-email , .ui-icon-page,
		.ui-icon-question , .ui-icon-foursquare , .ui-icon-twitter , .ui-icon-facebook , .ui-icon-dollar , .ui-icon-euro,
		.ui-icon-pound , .ui-icon-apple , .ui-icon-chat , .ui-icon-trash , .ui-icon-bell , .ui-icon-mappin , .ui-icon-direction,
		.ui-icon-heart , .ui-icon-wrench , .ui-icon-play , .ui-icon-pause , .ui-icon-stop , .ui-icon-person , .ui-icon-music,
		.ui-icon-rss , .ui-icon-wifi , .ui-icon-phone , .ui-icon-power , .ui-icon-lock , .ui-icon-flag , .ui-icon-calendar,
		.ui-icon-lightning , .ui-icon-drink , .ui-icon-android , .ui-icon-edit {
			background-image: url('<?=$b->url_abs_site;?>/2012_css/icones/images/icons-36-white-pack.png');
			-moz-background-size: 774px 54px;
			-o-background-size: 774px 54px;
			-webkit-background-size: 774px 54px;
			background-size: 774px 54px;
			background-color:  #555;
			
		}
		
 
		
		.ui-btn-icon-left .ui-btn-inner .ui-icon, .ui-btn-icon-right .ui-btn-inner .ui-icon {
		    position: absolute;		   
		}

		.ui-icon {
		    height: 17px;
		    width: 17px;
		    
		}
		

		.col2 {
			width:220px;
		}
		</style>
		
		

		
		
		
		<script src="<?=$b->url_abs_site;?>/2012_js/modernizr-2.6.1.min.js"></script>
		
		
		
		
	</head>

	<body lang="en">
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
        <![endif]-->
        
	<?php 		// chemin vignettes
	if ( $b->us_pf_img_vignette ) {
		$img =        $b->url_abs_site.$b->rep_pref.$b->us_pf_img_vignette;
		}
	else {
		$img =        '/img_front/_ultra_book_62x62.gif' ;	
		}	
	?>
	
<header  id="galblock_top">
<div class="ubtop_b">		
	<div class="ubtop_vignette">
		<img src="<?=$img;?>"  width="32" height="32" alt="<?=$b->prenom;?> <?=$b->nom;?>" />
	</div>
	<div>
		<?=$b->prenom;?> <?=$b->nom;?><br/>
		<span class="font_small"><?=$b->us_type;?> <?=$b->us_statut;?></span>
		<span style="color:#999" class="font_small"> / <?=$b->us_ville;?> <?=$b->us_pays;?></span>
	</div>
	
	
	<div style="float:right;margin-right:20px;margin-top:-30px;">	
		<?php  if ( $b->us_http != '' ) { ?>
		<a  href="http://<?=$b->us_http;?>"  class="ui-btn ui-shadow ui-btn-corner-all ui-btn-icon-right  " style="padding-left:24px;">
		<span class="ui-btn-inner ui-btn-corner-all">
		<span class="ui-icon ui-icon-refresh ui-icon-shadow ui-icon-home">&nbsp;</span>
		</span>
		</a>
		<?php  } ?>
		<?php  if ( $b->us_twitter_url != '' ) { ?>
		<a  href="http://<?=$b->us_twitter_url;?>"  class="ui-btn ui-shadow ui-btn-corner-all ui-btn-icon-right  " style="padding-left:24px;">
		<span class="ui-btn-inner ui-btn-corner-all">
		<span class="ui-icon ui-icon-refresh ui-icon-shadow ui-icon-twitter">&nbsp;</span>
		</span>
		</a>
		<?php  } ?>
		<?php  if ( $b->us_facebook_url != '' ) { ?>
		<a  href="http://<?=$b->us_facebook_url;?>"  class="ui-btn ui-shadow ui-btn-corner-all ui-btn-icon-right  " style="padding-left:20px;">
		<span class="ui-btn-inner ui-btn-corner-all">
		<span class="ui-icon ui-icon-refresh ui-icon-shadow ui-icon-facebook">&nbsp;</span>
		</span>
		</a>
		<?php  } ?>
					
	</div>
	
</div> 
</header>






<div id="galblock_img">
		
<?php  
 
// bouclage rub
$array_rub = $b->gal_cont['gal'];

// <div style="float:left;" id="hsort">HS</div> - 	<div id="rsort">HS</div>
?>
	
	


<nav class="rub_action">			
<?php 		
if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	
    	if ( $rub[rub_nom] != '')  {
    	?>    			
		<div id="rub_<?=$k;?>"> <?=$rub[rub_nom];?></div>
		<?php  }
		} ?>
		<div style="padding: 4px 0px 4px 0px;"  id="rub_all">Tous les travaux</div>

</nav>	
<br clear="all"/>





<div id="rub_cont" style="margin-left:-4px;">			
<?php 		
if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	?>
		<?php  					
           if ($array_rub['img'][$rub['rub_id']]) 
            foreach ($array_rub['img'][$rub['rub_id']] as $key=>$img) {                	
 			if ($key >= $b->us_formule_img_nb ) break;
				
			$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
			$img['img_titre'] = strip_tags($img['img_titre']);
			
			if ($img['img_fichier']!='') {		                    
            ?>
            <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>				
				<div class="box photo col2 rub_<?=$k;?> rub_all">					
			    <a href="<?=$b->url_abs_site.$b->rep_img900.$img['img_fichier'];?>" class="ubgalerie" title="<?=$img['img_titre'];?>" style="color:#444;text-decoration:none;">
			    	<img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img320.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" title="<?=$img['img_titre'];?>"></img>
				    <div style="background-color:#dadada;padding:10px;color:#222;text-decoration:none;margin:1px -8px -8px -8px;font-size:11px;">
				    <?=$img['img_titre'];?>
				    </div>
			    </a>
				</div>				
        	<?php  
			}
			} 
			?>					
	<?php 
	}			
	?>
</div>





<footer  id="page_footer" style="margin-top:20px;">	        
    <?php if ($b->us_formule==1 && $b->cont_piedpage=='[invisible]' ) { ?>    	
    <?php  } else { ?>			
		<?php if ($b->us_formule==1 && !empty($b->cont_piedpage) ) { ?>
			<?=htmlspecialchars_decode($b->cont_piedpage,ENT_QUOTES);?>					
		<?php  } else { ?>
			<a href="http://www.ultra-book.com" class="footer_ub">Ultra-book.com <span style="color:#aaa">| création de book en ligne 2012</span></a>
		<?php  } ?>
		<?php if ($b->us_partage_lien=='1') { ?>	
		 		<?php  list($tmp_titre, $tmp_url) = book_socializer ($b->cont_page_titre); ?>              
	            <div class="social-bookmarks-services">
				    <div class="social-bookmarks-service social-bookmarks-service-facebook">
				        <a href="http://www.facebook.com/sharer.php?u=<?=$tmp_url;?>"  title="Partager sur 'Facebook'" rel="nofollow" target="_blank">Partager sur 'Facebook'</a>
				    </div>				    
				    <div class="social-bookmarks-service social-bookmarks-service-twitter">
				        <a href="http://twitter.com/home?status=<?=$tmp_url;?>"  title="Partager sur 'Twitter'" rel="nofollow" target="_blank">Partager sur 'Twitter'</a>
				    </div>				
				    <div class="social-bookmarks-service social-bookmarks-service-linkedin">
				        <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>"   title="Partager sur 'LinkedIn'" rel="nofollow" target="_blank">Partager sur 'LinkedIn'</a>
				    </div>				
				    <div class="social-bookmarks-service social-bookmarks-service-viadeo">
				        <a href="http://www.viadeo.com/shareit/share/?url=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>&amp;urllanguage=fr" title="Partager sur 'Viadeo'" rel="nofollow" target="_blank">Partager sur 'Viadeo'</a>
				    </div>
				    <div class="social-bookmarks-service social-bookmarks-service-google_bookmarks">
				        <a href="http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>"  title="Partager sur 'Google'" rel="nofollow" target="_blank">Partager sur 'Google'</a>
				    </div>
				    <div class="social-bookmarks-service social-bookmarks-service-pinterest">
				    	<a class="pin-it-button"  href="javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)})());">
				    		Pin It!
				    	</a>
				    </div>				    
				</div>		
		<?php  } ?>
	<?php  } ?>			
	</div>
</footer>




        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?=$b->url_abs_site;?>/2012_js/jquery-1.8.0.min.js"><\/script>')</script>

		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/colorbox/jquery.colorbox-min.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/vGrid/jquery.vgrid.min.js"></script>
		<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js" type="text/javascript" charset="UTF-8"></script>
		
		<script type="text/javascript">
			$(function() {

							// vgrid
						  	vg = $('#rub_cont').vgrid({
								easing: "easeOutQuint",
								time: 300,
								delay: 20,
								fadeIn: {
									time: 200,
									delay: 40
								}
							});
							
							//colorbox
							$(".ubgalerie").colorbox({
								rel:'group2', 
								transition:"fade",
								current:	"{current} / {total}",
								previous:	"Précédent",
								next:		"Suivant",
								close: 		"fermer",
								opacity:	0.65,
								width:"75%", 
								height:"75%"
							});
							

							
							// hover
							$('.box').hover( function() {
								$(this).css('opacity','0.7')							
								},function() {
									if ( $(this).is('.visited') ){
									}	else {
										$(this).css('opacity','1');
									}
								}							
							);
							
							// visited
							$('.box').click(function(e){
								$(this).addClass('visited').css('opacity','0.4')	
							});
								
								
							// vgrid filtrage des rub	
							$('.rub_action > div').click(function(e){
								rub_select = $(this).attr('id');
								
								$('.box').each( function() {									
									
									if ( $(this).is("."+rub_select) ){
									  	 $(this).fadeIn(0, function() {vg.vgrefresh();});									  	 
									} else {
										 $(this).fadeOut(0, function() {vg.vgrefresh();});								
									}
									
									// passer toutes les rub et faire un hidden saur sur select
									//$container.
								});
																
							});
										
			});			
		</script>
		
</body>		
</html>