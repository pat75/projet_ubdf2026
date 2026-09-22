{{-- Porte depuis 2011_html_pages_v2/ultra2020/page.tlp.php (_outils/porter_gabarits.py) --}}


<?php if ( $b->theme_mdl == 'theme_ultrafrais') { ?>
<!-- menu page ultrafrais -->
<div class="four wide column  column-page-menu-ultrafrais">

    <div class="column-page-menu">
	<?php
	// nav
	if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
	list ($tmp_html, $pag_select) = ultra2020__front_nav_2020($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
	echo $tmp_html;
	?>
    </div>

</div>
<?php } ?>





<!--
<div class="column-page-menus-mobile">
    <div class="ui text menu ">
        <div class="page_dropdown ui dropdown" id="dropdown-page-menus-mobile" tabindex="0">

            <div class="menu_title">
                <div>b pages bio</div>
                <div class="icon_plus"></div>
            </div>

            <div class="ui text vertical menu menu-page hidden" tabindex="-1">
                <div class="menu menu-page-sub">
                    <div class="item  ">
                        <a href="page_1-r304826-c1652263">Page  1</a></div>
                    <div class="item  "><a href="page_2-r304826-c1652264">Page  2</a></div>
                    <div class="item  "><a href="page_3-r304826-c1652265">Page  3</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
-->

<!-- menu page mobile for all theme-->
<div class="column-page-menus-mobile">
	<div class="ui text menu ">
		<div class="page_dropdown ui dropdown" id="dropdown-page-menus-mobile">

			<div class="menu_title">
                <div><?=ultra2020__stripslashes_($b->obj_pref->data->nav_link->name_page);?></div>
                <div class="icon_plus"></div>
            </div>

			<?php
			// nav
			if (isset($b->id_rub)) $lien = '-r'.$b->id_rub.'-c';
			list ($tmp_html, $pag_select) = ultra2020__front_nav_2020($b->menu['act'], $b->menu['act']['img'], $b->rub_id, $b->pag_id);
			echo $tmp_html;
			?>
		</div>
	</div>
</div>





<article class="twelve wide column column-page">

	<?php if (isset($pag_select['img_html'])) echo book_actu_txt ($pag_select['img_html'], $b->url_abs_site)?>

    <?php /*
		   <h1>Mon titre H1</h1>

		   <h2>Mon titre H2</h2>

		   <h3>Mon titre H3</h3>

		   <div class="sous-titre-bold">
			   Sous-titre bold
		   </div>

		   <div class="titre-de-rubrique">
			   Titre de rubrique
		   </div>

		   <p>
			   body/paragraphe - <a href="#">Voici un lien</a>
			   L’objectif de l’architecte était d’allier les bâtiments existants aux nouvelles constructions afin de répondre aux exigences du client qui désirait un centre de formation à la pointe de la technologie accueillant aussi un pôle d’hébergement haut de gamme.
		   </p>

		   <cite>
			   body/citation mise en exergue
		   </cite>

		   <p class="petit-paragraph">
			   body/paragraphe - Voici un lien
			   L’objectif de l’architecte était d’allier les bâtiments existants aux nouvelles constructions afin de répondre aux exigences du client qui désirait un centre de formation à la pointe de la technologie accueillant aussi un pôle d’hébergement haut de gamme.
		   </p>

		   <img src="https://unsplash.it/700/380?image=21" alt="">
	   */?>

</article>
