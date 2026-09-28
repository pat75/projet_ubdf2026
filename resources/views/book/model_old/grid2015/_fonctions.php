<?php

// Fonctions des gabarits de ce theme, extraites par _outils/porter_gabarits.py.
// Chargees par App\Services\Book\Gabarit avant tout rendu.

if (! function_exists('grid2015__affiche_custom_css')) {
    function grid2015__affiche_custom_css($data_coulBook = '', $data_coulBGBook = '')
    {

        // DOM
        if ($data_coulBook && $data_coulBGBook) {
            echo '
		body,html,
		.wrap-nav-page,
		.wrapper.wrap-fotorama,
		.wrapper.wrap-page{
			background-color: '.$data_coulBGBook.';
		}
	
		body{
		  scrollbar-base-color: '.$data_coulBook.';
		  scrollbar-3dlight-color: '.$data_coulBGBook.';
		  scrollbar-highlight-color: '.$data_coulBGBook.';
		  scrollbar-track-color: '.$data_coulBGBook.';
		  scrollbar-arrow-color: '.$data_coulBook.';
		  scrollbar-shadow-color: '.$data_coulBook.';
		  scrollbar-dark-shadow-color: '.$data_coulBook.';
		}
		
		.bt-menu .icon span, 
		.bt-menu .icon span:before, 
		.bt-menu .icon span:after,
		::-webkit-scrollbar-thumb{
			background-color: '.$data_coulBook.';
		}
		body,
		a, a:hover,
		.wrap-nav-fotorama,
		.fotorama--fullscreen .fotorama__fullscreen-icon,
		.wrap-nav-page h1,
		.djax-preload,
		.footer_ub a{
			color: '.$data_coulBook.'; 
		}
		a:hover,
		.fotorama__caption a:hover{
			border-bottom-color: '.$data_coulBook.';
		}
		ul.menu li.section, ul.menu ul.sub-section li.sub-sub-section{
			border-top-color: '.$data_coulBook.';
		}
		.bt-menu .text, 
		ul.menu ul.sub-section li, 
		ul.menu li.section a, 
		ul.menu ul.sub-section li a:hover {
		color: '.$data_coulBook.';	border-bottom-color: '.$data_coulBook.';
		}



		.mCSB_scrollTools.mCSB_scrollTools_horizontal .mCSB_dragger .mCSB_dragger_bar {
		    background-color: '.$data_coulBook.';
		}

	
		';
        }
    }
}

if (! function_exists('grid2015__affiche_page')) {
    function grid2015__affiche_page($data_contenu = '', $data_titre_page = '')
    {

        echo $data_contenu;
        echo '<div class="margin-Tx6">&nbsp;</div>';
    }
}

if (! function_exists('grid2015__affiche_galerie')) {
    function grid2015__affiche_galerie($data_img_galerie = '', $data_titre_galerie = '', $data_index_galerie = '')
    {
        $hash = '"false"';
        $click = '"true"';

        // DOM PHOTOS
        if ($data_img_galerie) {
            ?>
		
		<div class="wrap-nav-fotorama show-fade">
			<div class="col-gauche no-select ">
				<h1 class="titre"><?php echo strip_tags($data_titre_galerie); ?></h1>
				<div class="compteur"><span class="current"></span></div>
			</div>
			<div class="col-droite no-select ">
				<div class="index float-L hideifnojs" role="button"><a href="#" title="Index" class="no-border bt-full no-djax"><span class="bt grid_icon-grid-close">&nbsp;</span></a></div>
				<div class="full float-L margin-R hideifnojs" role="button"><a href="#" title="Full" class="no-border bt-full"><span class="bt grid_icon-resize-open">&nbsp;</span></a></div>
				<div class="close float-L" role="button"><a href="./" title="Close" class="no-border bt-full"><span class="bt grid_icon-close">&nbsp;</span></a></div>
			</div>
		</div>
		
		<div class="fotorama galerie no-select show-fade"
			data-transition="slide"
			data-width="100%"
			data-fit="scaledown"
			data-nav="none"
			data-arrows="true"
			data-click=<?php echo $click; ?>
			data-swipe=<?php echo $click; ?>
			data-trackpad="true"
			data-keyboard="true"
			data-loop="true"
			data-hash=<?php echo $hash; ?>
			data-allowfullscreen="true"
			data-transitionduration="500"
			data-shadows="false"
			data-captions="true"
			data-auto="false"
		 >
		<?php echo $data_img_galerie; ?>
		</div>
		
		<div class="wrap-index-fotorama transition hideifnojs">
			<div class="content-iscroll">
			<?php echo $data_index_galerie; ?>
			<div class="clear">&nbsp;</div>
			</div>
		</div>
		
		<?php
        }

    }
}

if (! function_exists('grid2015__front_nav_2015')) {
    function grid2015__front_nav_2015($array_rub, $array_pag, $rub_id, $pag_id)
    {

        // $id = ($array['id_art']);
        $tmp_html = '';

        // print_r($array_rub);

        $actu_cont = array_pop($array_rub);
        $actu_rub = $array_rub;

        /*
        //print_r($actu_rub);
        //print_r($actu_cont);

        echo count($actu_rub);
        echo count($actu_cont);
        */
        if (count($actu_cont) >= 1) {
            foreach ($actu_cont as $tmp_rub) {
                $tmp_kkk = $tmp_kkk + ((count($tmp_rub) > 0) ? 1 : 0);
            }
        }
        // Juste Une rub de contenu
        $pasdelienrub = ($tmp_kkk == 1) ? true : false;
        // echo '<br>#####'.$pasdelienrub;
        // echo 		'============='.$tmp_kkk;

        // bouclage rub - classique
        if (is_array($array_rub)) {

            // rub et pag par default
            if ($rub_id == 0) {
                $rub_id = $array_rub[0]['rub_id'];
            }

            $tmp_html .= '<ul class="sub-section">';
            foreach ($array_rub as $key => $rub) {

                if (! $rub['rub_id']) {
                    break;
                }

                // liste des pages d'apres rub_id
                $pags = $array_pag[$rub['rub_id']];

                // lien rub
                if (! $pasdelienrub) {
                    $tmp_html .= '<li class=" '.(($rub['rub_id'] == $rub_id) ? 'open' : '')." \" >\r";
                    $tmp_html .= '<a class="ub_font_menu_newsr" href="'.wd_remove_accents($rub['rub_nom']).'-r'.$rub['rub_id'].'-c'.$pags[0]['img_id'].'" >'.$rub['rub_nom']."</a>\r";
                }

                // bouclage page

                if (is_array($pags) && count($pags) > 0) {

                    $tmp_html .= '<ul class="sub-section">';
                    // $tmp_html .= "<ul class=\"subMenu ".(($rub['rub_id']==$rub_id || $pasdelienrub)?'open_at_load':'')."\" >\r";
                    foreach ($pags as $key => $pag) {

                        // lien page
                        $tmp_html .= "<li>\r";
                        $tmp_html .= '<a class="ub_font_menu_newsp"  href="'.wd_remove_accents($pag['img_titre']).'-r'.$rub['rub_id'].'-c'.$pag['img_id'].'" >'.$pag['img_titre']."</a>\r";
                        $tmp_html .= "</li>\r";

                        // contenu de la page
                        if ($pag_id == $pag['img_id'] || ($pag_id == 0 && $key == 0)) {
                            $pag_select = $pag;
                        }

                    }
                    $tmp_html .= "</ul>\r";
                }

                if (! $pasdelienrub) {
                    $tmp_html .= "</li>\r";
                }
            }

            $tmp_html .= "</ul>\r";
        }

        // page accueil
        if ($rub_id == 0 && $pag_id == 0) {
            $pag_select = $array_pag[$array_rub[0]['rub_id']][0];
        }

        // page introuvable
        if (! isset($pag_select)) {
            $pag_select['img_titre'] = 'Page introuvable';
        }

        return [$tmp_html, $pag_select];
    }
}

if (! function_exists('grid2015__front_nav_2015_accueil')) {
    function grid2015__front_nav_2015_accueil($array_rub, $array_pag, $rub_id, $pag_id)
    {

        // $id = ($array['id_art']);
        $tmp_html = '';

        // print_r($array_rub);

        $actu_cont = array_pop($array_rub);
        $actu_rub = $array_rub;

        // print_r($array_rub);

        /*
        //print_r($actu_cont);

        echo count($actu_rub);
        echo count($actu_cont);
        */
        if (count($actu_cont) >= 1) {
            foreach ($actu_cont as $kk => $tmp_rub) {
                $tmp_kkk = $tmp_kkk + ((count($tmp_rub) > 0) ? 1 : 0);
            }
        }
        // Juste Une rub de contenu
        $pasdelienrub = ($tmp_kkk == 1) ? true : false;

        // echo '<br>#####'.$pasdelienrub;
        // echo 		$kk.'============='.$tmp_kkk;

        // juste une rub => on aff les 4 premieres pages
        if ($pasdelienrub) {

            // print_r($array_pag);

            // coul de la rub
            foreach ($array_rub as $key => $rub) {

                $pags = $array_pag[$rub['rub_id']];
                if (count($pags) > 0) {
                    break;
                }
            }

            $pags = $array_pag[$rub['rub_id']]; // les pages de la premier rub

            if (is_array($pags) && count($pags) > 0) {

                foreach ($pags as $key => $pag) {

                    if ($key > 50) {
                        break;
                    }

                    // html
                    $tmp_html .= '<div class="item small type-page show-scale">';
                    $tmp_html .= '<a href="'.wd_remove_accents($pag['img_titre']).'-r'.$rub['rub_id'].'-c'.$pag['img_id'].'"  title="'.$pag['img_titre'].'" class="bt-full" >';
                    $tmp_html .= '<div class="bloc-titre" style="background-color:#'.preg_replace('/#/', '', $rub['rub_coul']).'"  >';
                    $tmp_html .= '<h2>'.$pag['img_titre'].'</h2>';
                    $tmp_html .= '</div>';
                    $tmp_html .= '</a>';
                    $tmp_html .= "</div>\r";
                }
            }

        } else {
            // plusieurs rub on aff les 10 premiére rub
            //
            // bouclage rub - classique
            if (is_array($array_rub)) {

                // rub et pag par default
                if ($rub_id == 0) {
                    $rub_id = $array_rub[0]['rub_id'];
                }

                foreach ($array_rub as $key => $rub) {

                    if (! $rub['rub_id'] || $key > 50) {
                        break;
                    }

                    // trouver la premier page
                    $pags = $array_pag[$rub['rub_id']];
                    if (count($pags) > 0) {

                        // html
                        $tmp_html .= '<div class="item small type-page show-scale">';
                        $tmp_html .= '<a href="'.wd_remove_accents($rub['rub_nom']).'-r'.$rub['rub_id'].'-c'.$pags[0]['img_id'].'"  title="'.$rub['rub_nom'].'" class="bt-full" >';
                        $tmp_html .= '<div class="bloc-titre" style="background-color:#'.preg_replace('/#/', '', $rub['rub_coul']).'"  >';
                        $tmp_html .= '<h2>'.$rub['rub_nom'].'</h2>';
                        $tmp_html .= '</div>';
                        $tmp_html .= '</a>';
                        $tmp_html .= "</div>\r";

                    }
                }

            }

        }

        // page accueil
        if ($rub_id == 0 && $pag_id == 0) {
            $pag_select = $array_pag[$array_rub[0]['rub_id']][0];
        }

        // page introuvable
        if (! isset($pag_select)) {
            $pag_select['img_titre'] = 'Page introuvable';
        }

        return [$tmp_html, $pag_select];
    }
}

if (! function_exists('grid2015__ub_aff_thumbs')) {
    function grid2015__ub_aff_thumbs($array_img, $url_abs_site, $rep_img40, $rep_img900, $us_formule_img_nb)
    {

        ?>			
			<div class="thumbs moyenne">		
			<?php
        foreach ($array_img as $key => $img) {
            if ($key >= $us_formule_img_nb) {
                break;
            }

            // $img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
            $img['img_titre'] = strip_tags($img['img_titre']);

            if ($img['img_fichier'] != '') {
                ?>
           
			<a href="<?= (! $tmp_swf) ? $url_abs_site.$rep_img900.$img['img_fichier'] : $url_abs_site.'/2010_images/icone_flash.gif'; ?>"  data-caption="<?= $img['img_titre']; ?><br/><?= $img['img_desc']; ?>" >
				<img src="<?= (! $tmp_swf) ? $url_abs_site.$rep_img40.$img['img_fichier'] : $url_abs_site.'/2010_images/icone_flash.gif'; ?>"   >
			</a>
						
        	<?php
            }
        }
        ?>
			</div>
			<?php

    }
}
