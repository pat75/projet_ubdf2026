{{-- Porte depuis 2011_html_pages_v2/ultrabook_news.php (_outils/porter_gabarits.py) --}}

<?php //echo '.'.$b->book_id; ?>

<script type="text/javascript">

$(document).ready( function () {
    // On cache les sous-menus
    // sauf celui qui porte la classe "open_at_load" :
    $("ul.subMenu:not('.open_at_load')").hide();
	
   // ajout des span rub
   $("li.toggleSubMenu").prepend('<span></span>');
   
   // ajout des span sousrub
   $("ul.subMenu li").prepend('<span class="nav_pag"></span>');   
   
   
   
    // On modifie l'evenement "click" sur les liens dans les items de liste
    // qui portent la classe "toggleSubMenu" :
    $("li.toggleSubMenu > a").click( function () {
        // Si le sous-menu etait deja ouvert, on le referme :
        if ($(this).next("ul.subMenu:visible").length != 0) {
            $(this).next("ul.subMenu").slideUp("normal", function () { $(this).parent().removeClass("open") } );
        }
        // Si le sous-menu est cache, on ferme les autres et on l'affiche :
        else {
            $("ul.subMenu").slideUp("normal", function () { $(this).parent().removeClass("open") } );
            $(this).next("ul.subMenu").slideDown("normal", function () { $(this).parent().addClass("open") } );
        }
        // On empêche le navigateur de suivre le lien :
        return false;
    });

} ) ;

</script>

    
    
<div class="news">	


<div class="news_g">
<!-- nav -->
<div id="nav">
<?php 
// nav

//RewriteRule 	^/([-_0-9A-Za-z]*)-r([0-9]{1,12})-p([0-9]{1,12})    /index.php?rub=$2&pag=$3  [P]
// http://www.ultra-book.com/front/action.php?book_id=5402&book_key=6cd1c65f6&book_top=0&book_page=news&id_rub=20903&id_art=103301
//$lien = 'action.php?'.$b->book_lien.'&amp;book_page='.$b->book_page.'&amp;id_rub='.$b->id_rub.'&amp;id_art=';

if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';

//$b->gal_cont['gal'];
//$b->gal_cont['img'];


// debug 
// http://patrice_t.ultra-book.com/premiere_news-r4193-c36310?sd
if ($b->book_id == 8464 ) {
	
	//var_dump($b);
	
}


list ($tmp_html, $pag_select) = racine__front_nav_2011($b->gal_cont['gal'], $b->gal_cont['img'], $b->rub_id, $b->pag_id);

echo $tmp_html;


?>



</div>
<!-- nav fin -->
</div>



<div class="news_d">
		<div id="cms">
			
			<!-- titre -->
			<?php  if ($pag_select['img_titre']) : ?>
			<h2><?=$pag_select['img_titre']?></h2>
			<?php  endif; ?>

			<div class="news_centre">
			<?php if (isset($pag_select['img_html'])) echo preg_replace( "#(src|href)=\"\/users_2#", '$1="'.$b->url_abs_site.'/users_2', htmlspecialchars_decode( $pag_select['img_html'], ENT_QUOTES ) ); ?>	
			</div>		
		</div>
</div>
    
<div style="clear: both;"></div>
	
	
</div>


<?php 
//
// nav
//



?>