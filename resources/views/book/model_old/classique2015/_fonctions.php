<?php

// Fonctions des gabarits de ce theme, extraites par _outils/porter_gabarits.py.
// Chargees par App\Services\Book\Gabarit avant tout rendu.

if (! function_exists('classique2015__ub_aff_thumbs')) {
    function classique2015__ub_aff_thumbs($array_img, $url_abs_site, $rep_img40, $rep_img900, $us_formule_img_nb)
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
            <?php if (preg_match('/\.swf$/', $img['img_fichier'])) {
                $tmp_swf = true;
            } else {
                $tmp_swf = false;
            } /* aff des swf */ ?>				
			
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

if (! function_exists('classique2015__front_nav_2015')) {
    function classique2015__front_nav_2015($array_rub, $array_pag, $rub_id, $pag_id)
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

            $tmp_html .= '<ul  id="navigation" '.($pasdelienrub) ? '' : ' style="margin-left:0px'.'>\r';
            foreach ($array_rub as $key => $rub) {

                if (! $rub['rub_id']) {
                    break;
                }

                // liste des pages d'apres rub_id
                $pags = $array_pag[$rub['rub_id']];

                // lien rub
                if (! $pasdelienrub) {
                    $tmp_html .= '<li class="toggleSubMenu actu '.(($rub['rub_id'] == $rub_id) ? 'open' : '')." \" >\r";
                    $tmp_html .= '<a class="ub_font_menu_newsr" href="'.wd_remove_accents($rub['rub_nom']).'-r'.$rub['rub_id'].'-c'.$pags[0]['img_id'].'" >'.$rub['rub_nom']."</a>\r";
                }

                // bouclage page

                if (is_array($pags) && count($pags) > 0) {

                    $tmp_html .= '<ul class="subMenu '.(($rub['rub_id'] == $rub_id || $pasdelienrub) ? 'open_at_load' : '')."\" >\r";
                    foreach ($pags as $key => $pag) {

                        // lien page
                        $tmp_html .= '<li '.(($pasdelienrub) ? ' class="actu"' : '').">\r";
                        $tmp_html .= '<a class="ub_font_menu_newsp" href="'.wd_remove_accents($pag['img_titre']).'-r'.$rub['rub_id'].'-c'.$pag['img_id'].'" >'.$pag['img_titre']."</a>\r";
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
