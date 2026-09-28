<?php

use App\Services\Book\Gabarit;

// Fonctions des gabarits de ce theme, extraites par _outils/porter_gabarits.py.
// Chargees par App\Services\Book\Gabarit avant tout rendu.

if (! function_exists('ultra2020__stripslashes_')) {
    function ultra2020__stripslashes_($v)
    {
        // if (empty($v)) return "";
        $v = str_replace('&apos;', "'", $v);
        $v = str_replace('&quot;', '"', $v);
        $v = str_replace('&amp;', '&', $v);

        return $v;
    }
}

if (! function_exists('ultra2020__admin_promo')) {
    function ultra2020__admin_promo()
    {

        require_once 'inc/inc_user_marketing.php';

        $marketing_gettarif = new user_marketing;
        $marketing_gettarif->promo_action_noconnect(); // test si eligible-> action promo

        /*
        print_r(null);
        print_r(null);
        print_r(null);
        */

    }
}

if (! function_exists('ultra2020__token_exist')) {
    function ultra2020__token_exist()
    {

        global $token;

        if (empty($token)) {
            return false;
        }

        return true;
    }
}

if (! function_exists('ultra2020__is_admin')) {
    function ultra2020__is_admin()
    {

        global $user_book, $token;

        // if ( request()->query('pr') == 'public' ) return false;

        // if ( empty(null) && empty($token) ) return false;

        if (request()->cookie('admin_book_iframe') == 'true' && request()->query('pr') != 'true') {
            return false;
        }

        $url = request()->getHost();
        $matches = [];
        if (preg_match("/(?:http:\/\/)?(?:www\.)?([a-z0-9\.\-_]+)\.[a-z0-9\-_]{2,}\.[a-z]{2,4}(?:.*)/i", $url, $matches)) {
            $subdomains = explode('.', $matches[1]);
        }

        // print_r($subdomains);
        // echo $subdomains[0].' '. $token;

        // echo '> token '.$token. ' ### '.null;

        // switch on session (not url)
        // $token = null;

        // token for book admin 2020
        if ($user_book->user2020_token_book_verify($subdomains[0], $token)) {

            // is admin

            // if ( ( (request()->cookie('us_pr') !== null) || request()->query('pr') == 'true' ) && request()->cookie('us_pr_login') == $subdomains[0] && request()->query('pr') != 'public' ) {

            Gabarit::ignorer('us_pr', true, time() + 3600);
            Gabarit::ignorer('us_pr_login', $subdomains[0], time() + 3600);

            // nb connection
            /*
                    if ( empty( request()->cookie("us_nb_login") ))  {
                        \App\Services\Book\Gabarit::ignorer( "us_nb_login", 0, time() + 36000 );
                    } else {
                        \App\Services\Book\Gabarit::ignorer( "us_nb_login", request()->cookie("us_nb_login") + 1, time() + 36000 );
                    }
            */
            Gabarit::ignorer('us_nb_login', request()->cookie('us_nb_login') + 1, time() + 36000);

            return true;
        }

        // is not admin
        return false;

    }
}

if (! function_exists('ultra2020__front_nav_2020')) {
    function ultra2020__front_nav_2020($array_rub, $array_pag, $rub_id, $pag_id)
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
        echo count($actu_cont);*/
        if (count($actu_cont) >= 1) {
            foreach ($actu_cont as $tmp_rub) {
                $tmp_kkk = $tmp_kkk + ((count($tmp_rub) > 0) ? 1 : 0);
            }
        }

        // Juste Une rub de contenu
        $pasdelienrub = ($tmp_kkk == 1) ? true : false;

        // echo '#####'.$pasdelienrub;
        // echo 		'============='.$tmp_kkk;

        /*
        <div class="ui text vertical menu menu-page">
            <div class="item">
                <div class="header">Products</div>
                <div class="menu menu-page-sub">
                    <div class="item"><a href="#b" class="">page 1</a></div>
                    <div class="item"><a href="#c" class="">page 2</a></div>
                </div>
            </div>
            <div class="item">
                <div class="header">CMS Solutions</div>
                <div class="menu menu-page-sub">
                    <div class="item"><a href="#b" class="">page 1</a></div>
                    <div class="item"><a href="#c" class="">page 2</a></div>
                </div>
            </div>
        </div>
         */

        // bouclage rub - classique
        if (is_array($array_rub)) {

            // rub et pag par default
            if ($rub_id == 0) {
                $rub_id = $array_rub[0]['rub_id'];
            }

            $tmp_html .= '<div class="ui text vertical menu menu-page hidden">';
            // $tmp_html .= '<ul  id="navigation" '.($pasdelienrub)?'':' style="margin-left:0px'.'>\r';
            foreach ($array_rub as $key_rub => $rub) {

                if (! $rub['rub_id']) {
                    break;
                }

                // liste des pages d'apres rub_id
                $pags = $array_pag[$rub['rub_id']];

                // lien rub
                if (! $pasdelienrub && count($pags) > 0) {

                    $first_page = array_shift(array_values($pags));

                    $tmp_html .= '<div class="item '.(($rub['rub_id'] == $rub_id) ? 'active' : '').' ">';
                    $tmp_html .= '<div class="header">';
                    $tmp_html .= '<a href="'.wd_remove_accents($rub['rub_nom']).'-r'.$rub['rub_id'].'-c'.$first_page['img_id'].'" class="cursor_effect">';
                    $tmp_html .= $rub['rub_nom'].'</a>';
                    $tmp_html .= '</div>';
                }

                // bouclage page
                if (is_array($pags) && count($pags) > 0) {

                    // $tmp_html .= "<ul class=\"subMenu ".(($rub['rub_id']==$rub_id || $pasdelienrub)?'open_at_load':'')."\" >\r";

                    if (! $pasdelienrub) {
                        $tmp_html .= '<div class="menu menu-page-sub cursor_effect '.(($rub['rub_id'] == $rub_id || $pasdelienrub) ? 'open_at_load' : '').'" >'."\r";
                    }

                    // echo '--->'.count($pags);

                    foreach ($pags as $key => $pag) {

                        // echo $key.'###'.$pag_id. '   |  ';

                        $tmp_first = ($key == 0 && $pag_id == 0 && $key_rub == 0) ? true : false;

                        // lien page
                        /*
                        $tmp_html .= "<li ".(($pasdelienrub)?' class="actu"':'').">\r";
                        $tmp_html .= "<a class=\"ub_font_menu_newsp\" href=\"".wd_remove_accents($pag['img_titre'])."-r".$rub['rub_id']."-c".$pag['img_id']."\" >".$pag['img_titre']."</a>\r";
                        $tmp_html .= "</li>\r";
                        */

                        $tmp_html .= '<div class="item '.(($pag['img_id'] == $pag_id || $tmp_first) ? 'active' : '').' ">';
                        $tmp_html .= '<a href="'.wd_remove_accents($pag['img_titre']).'-r'.$rub['rub_id'].'-c'.$pag['img_id'].'" class="cursor_effect">';
                        $tmp_html .= $pag['img_titre'];
                        $tmp_html .= '</a></div>';

                        // contenu de la page
                        if ($pag_id == $pag['img_id'] || ($pag_id == 0 && $key == 0) && ! $pag_select) {
                            $pag_select = $pag;
                        }
                    }

                    if (! $pasdelienrub) {
                        $tmp_html .= "</div>\r";
                    }

                }

                if (! $pasdelienrub && count($pags) > 0) {
                    $tmp_html .= "</div>\r";
                }
            }

            $tmp_html .= "</div>\r";
        }

        // page accueil
        if ($rub_id == 0 && $pag_id == 0) {
            $pag_select = $array_pag[$array_rub[0]['rub_id']][0];
        }

        // page introuvable
        if (! isset($pag_select)) {
            $pag_select['img_titre'] = __('Page introuvable');
        }

        return [$tmp_html, $pag_select];
    }
}

if (! function_exists('ultra2020__front_add_http')) {
    function ultra2020__front_add_http($url)
    {

        if (! preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = 'https://'.$url;
        }

        return $url;
    }
}
