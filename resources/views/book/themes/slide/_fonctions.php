<?php

// Fonctions des gabarits de ce theme, extraites par _outils/porter_gabarits.py.
// Chargees par App\Services\Book\Gabarit avant tout rendu.

if (! function_exists('slide__front_nav_2011')) {
    function slide__front_nav_2011($array_rub, $array_pag, $rub_id, $pag_id)
    {

        // $id = ($array['id_art']);
        $tmp_html = '';

        // bouclage rub
        if (is_array($array_rub)) {

            // rub et pag par default
            if ($rub_id == 0) {
                $rub_id = $array_rub[0]['rub_id'];
            }

            // $tmp_html .= "<ul  id=\"navigation\" >\r";
            foreach ($array_rub as $key => $rub) {

                if (! $rub['rub_id']) {
                    break;
                }

                // liste des pages d'apres rub_id
                $pags = $array_pag[$rub['rub_id']];

                // lien rub
                $tmp_html .= '<li class="toggleSubMenu '.(($rub['rub_id'] == $rub_id) ? 'open' : '')." \" >\r";
                $tmp_html .= '<a class="ub_font_menu_newsr" href="'.wd_remove_accents($rub['rub_nom']).'-r'.$rub['rub_id'].'-c'.$pags[0]['img_id'].'" >'.$rub['rub_nom']."</a>\r";

                // bouclage page
                if (is_array($pags)) {

                    $tmp_html .= '<ul class="subMenu '.(($rub['rub_id'] == $rub_id) ? 'open_at_load' : '')."\" >\r";
                    foreach ($pags as $key => $pag) {

                        // lien page
                        $tmp_html .= "<li>\r";
                        $tmp_html .= '<a class="ub_font_menu_newsp" href="'.wd_remove_accents($pag['img_titre']).'-r'.$rub['rub_id'].'-c'.$pag['img_id'].'" >'.$pag['img_titre']."</a>\r";
                        $tmp_html .= "</li>\r";

                        // contenu de la page
                        if ($pag_id == $pag['img_id'] || ($pag_id == 0 && $key == 0)) {
                            $pag_select = $pag;
                        }

                    }
                    $tmp_html .= "</ul>\r";
                }

                $tmp_html .= "</li>\r";
            }

            // $tmp_html .= "</ul>\r";
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
