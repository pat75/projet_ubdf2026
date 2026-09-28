{{-- Porte depuis 2011_html_pages_v2/_ultrabook_portfolio.php (_outils/porter_gabarits.py) --}}
<?php  
/*<!-- 
 Start Advanced Gallery Html Containers
 http://code.google.com/p/galleriffic/issues/detail?id=13
 --> */

/*
	 [img_id] => 12157
	 [img_id_us] => 222
	 [img_publier] => publie
	 [img_titre] => test1
	 [img_titre_alt] => Nouvelle image
	 [img_link] =>
	 [img_date_crea] => 2011-07-26 19:23:06
	 [img_fichier] => test1__12157.jpg
	 [img_poids] => 11
	 [img_type] => image/jpeg
	 [img_desc] =>
	 [img_html] =>
	 [fk_rub_id] => 4164

*/

 
// bouclage rub
$array_rub = $b->gal_cont['gal'];


// page default
$img = $b->gal_cont['img'][ $array_rub[0]['rub_id'] ][0];

//print_r($img);
//print_r($array_rub);


?>

<div>


<div class="ptf" id="nav_ptf">		
<?php 
// rub par default
if ($b->rub_id==0) $b->rub_id = $array_rub[0]['rub_id'];
		
		
if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	
    	//$kk[$k]=$k;    	
    	if ($k > $b->us_formule_img_rub_nb) break;
	 	?>        	    	

    <?php  
    if (is_array($b->gal_cont['img'][$rub['rub_id']]) && $rub['rub_id'] == $b->rub_id )	
    { 
    
    ?>
    <a href="<?=wd_remove_accents($rub['rub_nom'])."-p".$rub['rub_id'];?>">
    <h3><?=$rub['rub_nom']?><span class="ui-icon ub_gal_img_open ui-icon-triangle-1-s"></span></h3> 
	</a>
	
	<div id="controls" class="controls"></div>
    <div id="thumbs_0" class="navigation" style="margin-left: 4px;">
        <ul class="thumbs noscript">
            <?php  
            
            //print_r($b->gal_cont['img'][$rub['rub_id']]);
			
            foreach ($b->gal_cont['img'][$rub['rub_id']] as $key=>$img) {                	
			
			if ($key >= $b->us_formule_img_nb ) break;
			$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
			$img['img_titre'] = strip_tags($img['img_titre']);
			
			if ($img['img_fichier']!='') {		                    
            ?>
            <li>
                <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>
                <a class="thumb  <?php if($img['img_titre']!=''){?>vtip<?php }?>" 
                	name="<?=$img['img_fichier'];?>" 
                	href="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img550.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" 
                	<?php /*=(isset($b->cont_analytic)?' onclick="_gaq.push([\'_trackEvent\', \'portfolio_ '.preg_replace("#\'#", ' ', $img['img_titre']).'\', \'clicked\'])" ':'');*/?>
                	title="<?=$img['img_titre'];?>" >
					<span class="vign40x40"> 
					<img
					src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img40.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>"
					alt="<?=$img['img_titre'];?>"
					style="margin: 0 auto;" />
					</span>
                </a>
                <div class="caption">                    
					
                    <?php  if ($tmp_swf) { ?>
                    <div class="image-flash">
						<?=$b->url_abs_site.$b->rep_img_.$img['img_fichier']; ?>
                    </div>
                    <?php  } ?>                        
                    <div class="image-zoom">
						<?=$b->url_abs_site.$b->rep_img_.$img['img_fichier']; ?>
                    </div>
								
                    <div class="image-title">
						<?=$img['img_titre']; ?>
                    </div>
                    <div class="image-desc">
						<?=str_replace("\n", '<br/>', $img['img_desc']); ?>
                    </div>
					
					
                </div>
            </li>
            <?php  
				}
			} 
			?>
        </ul>
    </div>
    <br clear="all" />
    <?php  } else { ?>
    
    <a href="<?=wd_remove_accents($rub['rub_nom'])."-p".$rub['rub_id'];?>">
    <h3><?=$rub['rub_nom']?><span class="ui-icon ub_gal_img_open ui-icon-triangle-1-e"></span></h3> 
	</a>
    <?php  } ?>
    




<?php  } ?>

</div>
<div style="clear: both;"></div>



<div id="gallery" class="content_pft">
    <div class="slideshow-container">
        <div id="loading" class="loader"></div>
        <div id="slideshow" class="slideshow">        	
            <?php  if (is_array($img) && $img['img_fichier']!='' ) { ?>
	            <?php  if (preg_match('/\.swf$/', $img['img_fichier'])) { ?>
	                    <div class="image-flash">
							<?= $b->url_abs_site.$b->rep_img_.$img['img_fichier']; ?>
	                    </div>				
	            <?php  }  else { ?> 
	            <img alt="<?=strip_tags($img['img_titre']);?>" src="<?=$b->url_abs_site.$b->rep_img550.$img['img_fichier'];?>">
	            <?php  } ?>
            <?php  } ?>
        </div>
    </div>
    <div id="zoom_lien"></div>
    <div id="caption" class="caption-container"></div>
</div>

<div style="clear: both;"></div>
</div>




<?php  
//print_r($kk);
/*	
 <script type="text/javascript" src="<?=$b->url_abs_site;?>/2010_js/ub_book_core.js"></script>
 <script type="text/javascript" src="http://www.ultra-book.com/2010_js/ub_book_core.js"></script>
 
 
 compression
 http://fmarcia.info/jsmin/test.html


 
 */
?>




<script type="text/javascript">
jQuery(document).ready(function($){$(document).pngFix();var titre_page=document.title;$('div.slideshow').html('');$('div.navigation').css({'width':'210px','float':'left'});$('div.content_pft').css('display','block');var onMouseOutOpacity=0.34;$('#thumbs ul.thumbs li').opacityrollover({mouseOutOpacity:onMouseOutOpacity,mouseOverOpacity:1.0,fadeSpeed:'fast',exemptionSelector:'.selected'});var defaultConf={delay:2500,numThumbs:21,preloadAhead:10,enableTopPager:true,enableBottomPager:true,maxPagesToShow:5,imageContainerSel:'#slideshow',controlsContainerSel:'#controls',captionContainerSel:'#caption',loadingContainerSel:'#loading',renderSSControls:true,renderNavControls:true,playLinkText:'Diaporama &rsaquo;',pauseLinkText:'Pause',prevLinkText:'&lsaquo;',nextLinkText:'&rsaquo;',nextPageLinkText:'Suiv. &rsaquo;',prevPageLinkText:'&lsaquo; Préc.',enableHistory:true,autoStart:false,syncTransitions:false,defaultTransitionDuration:800,onSlideChange:function(prevIndex,nextIndex){this.find('ul.thumbs').children().eq(prevIndex).fadeTo('fast',onMouseOutOpacity).end().eq(nextIndex).fadeTo('fast',1.0);},onPageTransitionOut:function(callback){this.fadeTo('fast',0.0,callback);},onPageTransitionIn:function(){this.fadeTo('fast',1.0);}}
var gallery1=$('#thumbs_0').galleriffic(defaultConf);function pageload(hash){if(hash){$.galleriffic.gotoImage(hash);}
else{$.galleriffic.gotoImage(0);}}
$.historyInit(pageload,"portefolio");$("a[rel='history']").live('click',function(e){if(e.button!=0)
return true;var hash=this.href;hash=hash.replace(/^.*#/,'');var hash_title=$(this).find('img').attr('alt');if(!hash_title){hash_title='';}
else{hash_title=' : '+hash_title;}
document.title=titre_page+hash_title;$.historyLoad(hash);return false;});});
</script>
