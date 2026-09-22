{{-- Porte depuis 2011_html_pages_v2/ultrabook_2012_ipad.tlp.php (_outils/porter_gabarits.py) --}}
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />        
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" name="viewport" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
	
        <title><?=$b->cont_page_titre;?></title>
        

	<link href="https://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.css" rel="stylesheet" />
	<link rel="stylesheet"  href="<?=$b->url_abs_site;?>/2012_css/icones/jqm-icon-pack-2.0-original.css" />
	
	<link href="<?=$b->url_abs_site;?>/2012_js/photoswipe/photoswipe.css" type="text/css" rel="stylesheet" />
	<style>

		.gallery { list-style: none; padding: 0; margin: 0; }
		.gallery:after { clear: both; content: "."; display: block; height: 0; visibility: hidden; }
		.gallery li img { display: none; width: 100%; height: auto; }
		#Gallery1 .ui-content, #Gallery2 .ui-content { overflow: hidden; }		
		.gallery li a { display: block; margin: 0 1px 1px 0; border: none; }



		li .image
		{
			float: left;
			border: none;
			background:#444;
			overflow:hidden;
			margin: 2px 2px;
			margin-right: 8px;
			height:75px;
			width:75px;
		}
		
		li .title
		{
			display: block;
			font-weight: bold;
			font-size: 16px;
			padding-bottom: 4px;
			margin-right: 20px;
		}
		
		li .text
		{
			font-size: 13px;
			font-weight: normal;
			display: block;
			margin-right: 20px;
		}


		/* ub */
		.ub_iphone_lien {
		    padding: 0 0 0 4px;
		}
		
		div.footer_bloc {
		    border-top: 1px solid #CCCCCC;
		    padding-top: 8px;
		    text-align: left;
			
		}
		
		div.footer_bloc strong {
		    color: #444444;
		}
		
		a.footer_ub {
		    background: url("<?=$b->url_abs_site;?>/2010_images/ultra-book_logo.gif") no-repeat scroll 0 0 transparent;
		    color: #777777;
		    font-size: 10px;
		    font-weight: normal;
		    padding: 1px 0 0 18px;
		    text-decoration: none;
			height:20px;
		}
		
		a.footer_ub:hover {
		    color: #CCCCCC;
		}
		
		div.footer_bloc strong:hover {
		    color: #999999;
		}



	@@media screen and (max-width: 1024px) and (orientation:portrait) {
			.top { max-width:768px;}		
			.gallery li { float: left; width: 25%; }		
	}
	
	@@media screen and (max-width: 1024px) and (orientation:landscape) {	
			.top {	max-width:1024px; }
			.gallery li { float: left; width: 18,7%; }			
	}	
	
	
	</style>
	
	<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/photoswipe/lib/klass.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.6.4.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.js"></script>
	
	<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/photoswipe/code.photoswipe.jquery-3.0.4.min.js"></script>	
	<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/nailthumb/jquery.nailthumb.1.0.min.js"></script>

	<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>
	<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/jquery.ui.map.full.min.js"></script>
	
	<script type="text/javascript">
		/*
		 * IMPORTANT!!!
		 * REMEMBER TO ADD  rel="external"  to your anchor tags. 
		 * If you don't this will mess with how jQuery Mobile works
		 */
<?php  if ( $b->us_map != ',' ) { ?>
		$(function() {
			
			var ubbookLatLng = new google.maps.LatLng(<?=$b->us_map;?>);

			$('#map_canvas')
			.gmap({ 'zoom' :<?=$b->us_map_zoom;?>, center : ubbookLatLng})
			.gmap( 'addMarker', {'position': '<?=$b->us_map;?>'})
			.click(function() {
				$('#map_canvas').gmap('openInfoWindow', {'content': $('#ub_content_marker').html() }, this);
			});
		});
<?php  } ?>	                
		
		
		(function(window, $, PhotoSwipe){
			
			$(document).ready(function(){
				
			//alert('ok');			
			
			$(window).bind('orientationchange', function() {
			   if(window.orientation==90) {
			   		$('.gallery li').css({'width': '18.7%'});
			   } else {
			   		$('.gallery li').css({'width': '25%'});
			   }
			});

			
			
				$('div.gallery-page')
					.live('pageshow', function(e){
						
								$('.nailthumb').nailthumb({ 
                				   	width:183, 
					            	height:183,				            	
									animationTime: 0,
									animateTitle: false
									}).show();
																
						var 
							currentPage = $(e.target),
							options = {},
							photoSwipeInstance = $("ul.gallery a", e.target).photoSwipe(options,  currentPage.attr('id'));
							
						return true;
						
					})
					
					.live('pagehide', function(e){
						
						var 
							currentPage = $(e.target),
							photoSwipeInstance = PhotoSwipe.getInstance(currentPage.attr('id'));

						if (typeof photoSwipeInstance != "undefined" && photoSwipeInstance != null) {
							PhotoSwipe.detatch(photoSwipeInstance);
						}
						
						return true;
						
					});				
			});
		
		}(window, window.jQuery, window.Code.PhotoSwipe));
		
	</script>
    </head>
    <body>
    	
    	<!-- Page Accueil -->	
        <div data-role="page" id="page_accueil" >
            <div data-role="content" >
            	
		        <div id="page_top_ipad" style="margin: -15px -15px 20px -15px;" >
		        	<?php if( $b->cont_visuel2012 !='' && $b->cont_visuel2012 != 'ultra-book_default_980x200.gif' ) { ?>
		        	 <a href="/">
						<img class="top" src="<?=$b->url_abs_site?><?=$b->rep_pref?><?=$b->cont_visuel2012;?>" alt="<?=$b->cont_page_titre;?> | Ultra-book" />
					</a>
					<?php }?>	
		        </div>
		        


            	
            	<?php 		// chemin vignettes
						if ( $b->us_pf_img_vignette ) {
							$img =        $b->url_abs_site.$b->rep_pref.$b->us_pf_img_vignette;
							}
						else {
							$img =        '/img_front/_ultra_book_62x62.gif' ;	
							}
						
				?>
<div class="ui-grid-c">

<div class="ui-block-a" style="width:100px;">
	<div style="float:left;width:72px;height:72px;margin:0 12px 70px 0;background-color:#ccc;clear:both;">
		<img src="<?=$img;?>"  width="72" height="72" alt="<?=$b->prenom;?> <?=$b->nom;?>" />
	</div>
</div>




<div class="ui-block-b" style="width:450px;" id="ub_content_marker" >            
    <h2 style="margin:-2px 10px 6px 0;">
       <?=$b->prenom;?><br/>
       <?=$b->nom;?>
    </h2>	                
    <div style="font-size:14px">
        <strong><?=$b->us_type;?></strong>
        <br />
        <?=$b->us_ville;?> | <?=$b->us_pays;?>
        <br />
        <?=$b->us_statut;?>
        <br />
    </div>
    <div style="margin:15px 0 0 0">
    <?=html_entity_decode(nl2br($b->us_pf_descp_mobile), ENT_QUOTES, 'UTF-8');?>
    </div>
    <br/>
</div>


<div class="ui-block-c"  style="float:right;">

					<?php  if ( $b->us_http != '' ) { ?>
                   	<a href="http://<?=$b->us_http;?>" data-role="button" data-icon="home" data-iconpos="notext" data-mini="true" data-inline="true">Site Web</a>
                   	<?php  } ?>
					<?php  if ( $b->us_facebook_url != '' ) { ?>
					<a href="http://<?=$b->us_facebook_url;?>" data-role="button" data-icon="facebook" data-iconpos="notext" data-mini="true" data-inline="true">Facebook</a>
					<?php  } ?>
					<?php  if ( $b->us_twitter_url != '' ) { ?>
					<a href="http://<?=$b->us_twitter_url;?>" data-role="button" data-icon="twitter" data-iconpos="notext" data-mini="true" data-inline="true">Twitter</a>
					<?php  } ?>
					
					<?php  if ( ! preg_match("# |,#", $b->us_map )) { ?>
					<a href="#page_geo" data-transition="slide" data-role="button" data-icon="mappin" data-iconpos="notext" data-mini="true" data-inline="true">Géo-book</a>
					<?php  } ?>
					
				<!--<a href="#page_geo" data-transition="slide" data-role="button" data-icon="mappin" data-iconpos="notext" data-mini="true" data-inline="true">Géo-book</a>
				<a href="#page_geo" data-transition="slide" data-role="button" data-icon="phone" data-iconpos="notext" data-mini="true" data-inline="true">Géo-book</a>-->
					
</div>
	
</div>
<br clear="all"/>
						
                 <ul data-role="listview" data-divider-theme="a" data-inset="false">
                    <li data-role="list-divider" role="heading">
                        Portfolios
                    </li>
                    <?php  foreach ($b->gal_cont['gal'] as $key=>$rub) {  			    	
				    	if ($key > $b->us_formule_img_rub_nb) break;						
				    	?>
				    	
				            <li>
							<!--<a href="#Gallery1" data-transition="slide" >	-->
							<a href="<?=wd_remove_accents($rub['rub_nom'])."-pi".$rub['rub_id'];?>" data-transition="slide" >
																	
								<span class="image">
									<?php  
									//print_r ($b->gal_cont['img'][$rub['rub_id']][0]);
									$img = $b->gal_cont['img'][$rub['rub_id']][0]; //1ere img
									if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ 
									?>										
									<img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img75.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>">
								</span>
								<span class="title"><?=$rub['rub_nom']?></span>
								<span class="text">
									<?php 
									
									$tmp_img = \App\Services\Book\Php7::count(explode("_", $rub['rub_ordre_img']))-1;
									
									if ($tmp_img >= $b->us_formule_img_nb_mobil) $tmp_img = $b->us_formule_img_nb_mobil;
									if ($tmp_img != 0) 
										echo $tmp_img.' Images';					
									?> 				
								</span>
							</a>				        	
				        </li>    
					<?php  } ?>   

                </ul>
             
            </div>
                    


					
					
     
		<div style="padding: 6px; margin: 0 0 10px 10px;">
			
					<?php if ($b->us_partage_lien=='1') { ?>				
					<!-- AddThis Button BEGIN -->
					<div class="addthis_toolbox addthis_default_style ">
						<a class="addthis_button_facebook_send"></a>
						<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
						<a class="addthis_button_tweet"></a>
						<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>						
					</div>
					<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4ee6410f48e8fce2"></script>
					<!-- AddThis Button END -->	
					<br/>			
					<?php  } ?>   
					
            <div class="footer_bloc" style="border:none;">
                <a class="footer_ub" href="http://www.ultra-book.com"><strong>Ultra-book mobile</strong> | création de book en ligne</a>
            </div>
        </div>  
        
        </div>
        
        
<!-- Page Géo-book -->	
<div id="page_geo" data-role="page">
	<div data-role="header">
		<h1><?=$b->prenom;?> <?=$b->nom;?> : Géo-book</h1>
		<a data-rel="back" data-icon="arrow-l" data-iconpos="notext" >Retour</a>
	</div>
	<div data-role="content">	
		<div class="ui-bar-c ui-corner-all ui-shadow" style="padding:1em;">
			<div id="map_canvas" style="height:450px;"></div>
		</div>
		<div class="footer_bloc">
	        <a class="footer_ub" href="http://www.ultra-book.com"><strong>Ultra-book mobile</strong> | création de book en ligne</a>
	    </div>
	    
	</div>
</div>
	
        
        
		<script type="text/javascript">
		
		  var _gaq = _gaq || [];
		  
		  _gaq.push(['_setAccount', 'UA-464814-11']);
		  _gaq.push(['_setDomainName', 'ultra-book.com']);
		  _gaq.push(['_trackPageview']);
		
		<?php  if (isset($b->cont_analytic)) : ?>
		  _gaq.push(['t2._setAccount', '<?=$b->cont_analytic?>']);
		  _gaq.push(['t2._setDomainName', '<?=$b->us_dir;?>.ultra-book.com']);
		  _gaq.push(['t2._trackPageview']);
		<?php  endif; ?>
		
		
		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		
		</script>
		
        	<?php 	
			// stats : pixel de comptage interne (voir StatsBookController)
			//
			$stats_action =			'add';
			$stats_st_champ =		'st_ipad';
			$stats_us_login =		$b->us_dir;
			$stats_st_cles = 		md5($book . 'pat75ub2012publique' );
			$stats_i = 				rand(0,9999);
			$stats_img = '/ubstats.gif?r='.$stats_i;
			?>								  	
			<img src="<?=$stats_img;?>" width="1" height="1" style="display:none"/>
    </body>
    
</html>

