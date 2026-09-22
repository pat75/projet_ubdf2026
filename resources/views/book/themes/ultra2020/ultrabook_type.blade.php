{{-- Porte depuis 2011_html_pages_v2/ultra2020/ultrabook_type.tlp.php (_outils/porter_gabarits.py) --}}
<?php
/* ------------------------------------------------
	Ultra-Book Ultra 2020 | Modèle page : struture
------------------------------------------------ */




// fonctions communes -nov2016
//
include \App\Services\Book\Gabarit::chemin('ultra2020/_fonction.php');


// Is admin v2020 via token
$b->connection_admin_book =          ultra2020__is_admin();
$b->connection_admin_token_exist =   ultra2020__token_exist();

//echo $b->connection_admin_book. '#####'.request()->query('pr');




if($b->page_type == 'accueil') 			$typePage = "home";
if($b->page_type == 'portfolio') 		$typePage = "book";
if($b->page_type == 'news') 				$typePage = "page";


// recup pref JSON
//
$b->obj_pref = json_decode($b->cont_conf2012);






// var_dump( $b->obj_pref );
//echo $b->modele_book;
//echo  	$b->obj_pref->data->link_accueil->form_text;
//$data_coulBGBook = 	$obj_pref->data->{'.ub_couleur_fond'}->backgroundColor;// couleur fond



// frais OR zen mdl book
$b->theme_mdl = ( $b->modele_book ==  'mdl_2020_ultra_zen') ? 'theme_ultrazen' :  'theme_ultrafrais';


include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/_ultrabook__layout.tlp.php');




//echo 'ok+++++++++++++'.$b->page_type ;
// echo $b->connection_admin_book;



/*
echo 'ok+++++++++++++'.$b->page_type ;
echo $b->connection_admin_book;
*/

/*
 * Savant2 Object
(
    [_call] =&gt; plugin
    [_compiler] =&gt;
    [_error] =&gt;
    [_escape] =&gt; Array
        (
            [0] =&gt; htmlspecialchars
        )

    [_extract] =&gt;
    [_output] =&gt;
    [_path] =&gt; Array
        (
            [resource] =&gt; Array
                (
                    [0] =&gt; /Users/pat/Sites_2013/_projet_ub2014/savant2/Savant2/
                )

            [template] =&gt; Array
                (
                    [0] =&gt; ./
                )

        )

    [_resource] =&gt; Array
        (
            [plugin] =&gt; Array
                (
                )

            [filter] =&gt; Array
                (
                )

        )

    [_reference] =&gt;
    [_restrict] =&gt;
    [_script] =&gt;
    [_template] =&gt;
    [language] =&gt; fr_FR
    [connection_admin_book] =&gt; 1
    [IsMobile] =&gt;
    [modele_book] =&gt; mdl_2020_ultra_zen
    [navigateur_client] =&gt; web
    [us_dir] =&gt; t93
    [us_partage_lien] =&gt; true
    [us_formule] =&gt; 0
    [us_map] =&gt; 48.876444,2.358396,14
    [us_type] =&gt; Architecte
    [nom] =&gt; Tardif
    [prenom] =&gt; Patrice
    [cont_page_titre] =&gt; BBBddAaaaZA Portfolio
    [rep_user] =&gt; /users_2/t/9/t93
    [rep_pref] =&gt; /users_2/t/9/t93/cms_pref/
    [url_abs_site] =&gt; https://ub2016.ddns.net
    [rep_img_] =&gt; /users_2/t/9/t93/img_/
    [rep_img40] =&gt; /users_2/t/9/t93/img_ptf_small/
    [rep_img550] =&gt; /users_2/t/9/t93/img_ptf_medium/
    [rep_img900] =&gt; /users_2/t/9/t93/img_/
    [rep_img75] =&gt; /users_2/t/9/t93/img_iph_small/
    [rep_img320] =&gt; /users_2/t/9/t93/img_iph_medium/
    [rep_img180] =&gt; /users_2/t/9/t93/img_adm_medium/
    [book_id] =&gt; 45316
    [cont_nav] =&gt; Array
        (
            [0] =&gt; ultra-book_default_logo.gif
            [1] =&gt; ultra-book_default_accueil.gif
            [2] =&gt; ultra-book_default_portefolio.gif
            [3] =&gt; ultra-book_default_news.gif
            [4] =&gt; ultra-book_default_image.gif
            [5] =&gt; https://graph.facebook.com/10153837745561162/picture?width=1
            [6] =&gt; ultra-book_default_fond.gif
        )

    [cont_book_titre] =&gt; BBBddAaaaZA
    [cont_page_meta] =&gt; Architecte, ckh,fg,:;hn,cvb:;,n:;,bv
lxgklmgkfgmlhjklaaaaaamfghdfgh
fglhgdlfghdgfjmlmfhglmjZZZZsdfd
sqf
dfkhlmdfkglmhkflmhgjkmljgkhjmk
fmglhkjmlfghkmjlkAAA
    [cont_page_key] =&gt; Architecte, animation 2d,architecture architecte DPLG,dition edition culinaire,design cration objets,design conception,art Burner,architect commercial
    [cont_analytic] =&gt; bbbb555gffsdgfsdg
    [cont_center] =&gt; 1
    [cont_bg] =&gt; /users_2/t/9/t93/cms_pref/ultra-book_default_fond.gif
    [cont_bgcoul] =&gt; #4ec200
    [cont_bg_choix] =&gt; 1
    [cont_piedpage] =&gt; Pied
&lt;a href="http://www.google.fr"&gt;Test&lt;/a&gt;
    [cont_visuel2012] =&gt; ultra-book_default_980x200.gif
    [cont_visuel2014] =&gt; ultra-book_default_215x215.gif
    [cont_visuel2015_1] =&gt;
    [cont_visuel2015_2] =&gt;
    [cont_visuel2015_3] =&gt;
    [cont_visuel2015_accueil] =&gt;
    [us_pf_diff_web] =&gt; true
    [us_pf_v2] =&gt; true
    [us_formule_img_rub_nb] =&gt; 3
    [us_formule_img_nb] =&gt; 24
    [us_formule_img_nb_mobil] =&gt; 10
    [phpThumb_path] =&gt; /phpthumb_last
    [page_type] =&gt; accueil
    [cont_conf2012] =&gt; {"data":{
		"link_accueil":{"form_text":"Portfolio zen"},
		"link_bio":{"form_text":"Bio zen"},
		"link_contact":{"form_text":"Contact zen"},

	}}
    [accueil_contenu_aff_a] =&gt;
    [accueil_contenu_aff_b] =&gt;
    [accueil_contenu_aff_c] =&gt;
    [accueil_contenu_aff_d] =&gt;
    [accueil_ptf_vignette_aff] =&gt; true
    [obj_cont_data] =&gt;
    [icone] =&gt; https://ub2016.ddns.net/img_front/ultra-book_icon.png
    [icone_iphone] =&gt; https://ub2016.ddns.net/img_front/touch-icon-iphone.png
    [icone_ipad] =&gt; https://ub2016.ddns.net/img_front/touch-icon-ipad.png
    [url_mdl] =&gt; ultra2020
    [menu] =&gt; Array
        (
            [ptf] =&gt; Array
                (
                    [0] =&gt; Array
                        (
                            [rub_id] =&gt; 128719
                            [rub_nom] =&gt; Nouvelle rubrique
                            [rub_ordre_img] =&gt; _641342_641340_641235_641339
                            [rub_link] =&gt;
                            [rub_publier] =&gt; publie
                            [rub_coul] =&gt; CCCCCC
                        )

                    [1] =&gt; Array
                        (
                            [rub_id] =&gt; 128720
                            [rub_nom] =&gt; Nouvelle rubrique
                            [rub_ordre_img] =&gt; _641327_641322_641320_641321_641319_641317_641318_641241_641270_641236
                            [rub_link] =&gt;
                            [rub_publier] =&gt; publie
                            [rub_coul] =&gt; c42f2f
                        )

                    [2] =&gt; Array
                        (
                            [rub_id] =&gt; 128703
                            [rub_nom] =&gt; Nouvelle rubrique
                            [rub_ordre_img] =&gt; _641215_641208_641207_641206_641205_641204_641203_641201_641202_641199
                            [rub_link] =&gt;
                            [rub_publier] =&gt; publie
                            [rub_coul] =&gt; CCCCCC
                        )

                    [img] =&gt; Array
                        (
                            [128719] =&gt; Array
                                (
                                    [0] =&gt; Array
                                        (
                                            [img_id] =&gt; 641342
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; test_arbre.png
                                            [img_titre_alt] =&gt; test_arbre.png
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-08 11:43:42
                                            [img_fichier] =&gt; test_arbre_png__641342.png
                                            [img_poids] =&gt; 135
                                            [img_type] =&gt; image/png
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128719
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [1] =&gt; Array
                                        (
                                            [img_id] =&gt; 641340
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; letreport.jpg
                                            [img_titre_alt] =&gt; letreport.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-08 10:10:23
                                            [img_fichier] =&gt; letreport_jpg__641340.jpg
                                            [img_poids] =&gt; 29
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128719
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [2] =&gt; Array
                                        (
                                            [img_id] =&gt; 641235
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; leman_evian_bateau_barque.jpg
                                            [img_titre_alt] =&gt; leman_evian_bateau_barque.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-03 15:30:51
                                            [img_fichier] =&gt; leman_evian_bateau_barqu__641235.jpg
                                            [img_poids] =&gt; 113
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128719
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [3] =&gt; Array
                                        (
                                            [img_id] =&gt; 641339
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; Carte_postale_ancienne_Saint_Cucufa.jpg
                                            [img_titre_alt] =&gt; Carte_postale_ancienne_Saint_Cucufa.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-08 10:10:18
                                            [img_fichier] =&gt; carte_postale_ancienne_s__641339.jpg
                                            [img_poids] =&gt; 410
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128719
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                )

                            [128720] =&gt; Array
                                (
                                    [0] =&gt; Array
                                        (
                                            [img_id] =&gt; 641327
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; leman_evian_bateau_barque.jpg
                                            [img_titre_alt] =&gt; leman_evian_bateau_barque.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-04 18:13:58
                                            [img_fichier] =&gt; leman_evian_bateau_barqu__641327.jpg
                                            [img_poids] =&gt; 113
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [1] =&gt; Array
                                        (
                                            [img_id] =&gt; 641322
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; ub_fd_sep2014.jpg
                                            [img_titre_alt] =&gt; ub_fd_sep2014.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-04 18:06:24
                                            [img_fichier] =&gt; ub_fd_sep2014_jpg__641322.jpg
                                            [img_poids] =&gt; 182
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [2] =&gt; Array
                                        (
                                            [img_id] =&gt; 641320
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; repper_pattern2.jpg
                                            [img_titre_alt] =&gt; repper_pattern2.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-04 18:06:23
                                            [img_fichier] =&gt; repper_pattern2_jpg__641320.jpg
                                            [img_poids] =&gt; 29
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [3] =&gt; Array
                                        (
                                            [img_id] =&gt; 641321
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; test_arbre.png
                                            [img_titre_alt] =&gt; test_arbre.png
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-04 18:06:23
                                            [img_fichier] =&gt; test_arbre_png__641321.png
                                            [img_poids] =&gt; 135
                                            [img_type] =&gt; image/png
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [4] =&gt; Array
                                        (
                                            [img_id] =&gt; 641319
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; letreport.jpg
                                            [img_titre_alt] =&gt; letreport.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-04 18:06:23
                                            [img_fichier] =&gt; letreport_jpg__641319.jpg
                                            [img_poids] =&gt; 29
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [5] =&gt; Array
                                        (
                                            [img_id] =&gt; 641317
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; cubes 1.jpg
                                            [img_titre_alt] =&gt; cubes 1.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-04 18:06:21
                                            [img_fichier] =&gt; cubes_1_jpg__641317.jpg
                                            [img_poids] =&gt; 245
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [6] =&gt; Array
                                        (
                                            [img_id] =&gt; 641318
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; leman_evian_bateau_barque.jpg
                                            [img_titre_alt] =&gt; leman_evian_bateau_barque.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-04 18:06:22
                                            [img_fichier] =&gt; leman_evian_bateau_barqu__641318.jpg
                                            [img_poids] =&gt; 113
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [7] =&gt; Array
                                        (
                                            [img_id] =&gt; 641241
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; us_pf_zoom2016_visuel_accueil.png
                                            [img_titre_alt] =&gt; us_pf_zoom2016_visuel_accueil.png
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-03 15:33:28
                                            [img_fichier] =&gt; us_pf_zoom2016_visuel_ac__641241.png
                                            [img_poids] =&gt; 58
                                            [img_type] =&gt; image/png
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [8] =&gt; Array
                                        (
                                            [img_id] =&gt; 641270
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; letreport.jpg
                                            [img_titre_alt] =&gt; letreport.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-03 18:26:49
                                            [img_fichier] =&gt; letreport_jpg__641270.jpg
                                            [img_poids] =&gt; 29
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [9] =&gt; Array
                                        (
                                            [img_id] =&gt; 641236
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; cubes 1.jpg
                                            [img_titre_alt] =&gt; cubes 1.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-03 15:30:51
                                            [img_fichier] =&gt; cubes_1_jpg__641236.jpg
                                            [img_poids] =&gt; 245
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128720
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                )

                            [128703] =&gt; Array
                                (
                                    [0] =&gt; Array
                                        (
                                            [img_id] =&gt; 641215
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; us_pf_zoom2016_visuel_accueil.png
                                            [img_titre_alt] =&gt; us_pf_zoom2016_visuel_accueil.png
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-02 16:41:28
                                            [img_fichier] =&gt; us_pf_zoom2016_visuel_ac__641215.png
                                            [img_poids] =&gt; 58
                                            [img_type] =&gt; image/png
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [1] =&gt; Array
                                        (
                                            [img_id] =&gt; 641208
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; test_arbre.png
                                            [img_titre_alt] =&gt; test_arbre.png
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-02 16:32:21
                                            [img_fichier] =&gt; test_arbre_png__641208.png
                                            [img_poids] =&gt; 135
                                            [img_type] =&gt; image/png
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [2] =&gt; Array
                                        (
                                            [img_id] =&gt; 641207
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; ub_fd_sep2014.jpg
                                            [img_titre_alt] =&gt; ub_fd_sep2014.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-02 16:30:30
                                            [img_fichier] =&gt; ub_fd_sep2014_jpg__641207.jpg
                                            [img_poids] =&gt; 182
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [3] =&gt; Array
                                        (
                                            [img_id] =&gt; 641206
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; us_pf_img_vignette.gif
                                            [img_titre_alt] =&gt; us_pf_img_vignette.gif
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-02 16:28:38
                                            [img_fichier] =&gt; us_pf_img_vignette_gif__641206.gif
                                            [img_poids] =&gt; 6
                                            [img_type] =&gt; image/gif
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [4] =&gt; Array
                                        (
                                            [img_id] =&gt; 641205
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; cubes 1.jpg
                                            [img_titre_alt] =&gt; cubes 1.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-02 16:24:34
                                            [img_fichier] =&gt; cubes_1_jpg__641205.jpg
                                            [img_poids] =&gt; 245
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [5] =&gt; Array
                                        (
                                            [img_id] =&gt; 641204
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; letreport.jpg
                                            [img_titre_alt] =&gt; letreport.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-02 16:01:46
                                            [img_fichier] =&gt; letreport_jpg__641204.jpg
                                            [img_poids] =&gt; 29
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt; _
                                            [img_html] =&gt; _
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [6] =&gt; Array
                                        (
                                            [img_id] =&gt; 641203
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; cubes 1 - copie.jpg
                                            [img_titre_alt] =&gt; cubes 1 - copie.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-02 15:58:55
                                            [img_fichier] =&gt; cubes_1_copie_jpg__641203.jpg
                                            [img_poids] =&gt; 245
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt; _
                                            [img_html] =&gt; _
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [7] =&gt; Array
                                        (
                                            [img_id] =&gt; 641201
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; repper_pattern2.jpg
                                            [img_titre_alt] =&gt; repper_pattern2.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-10-07 07:13:40
                                            [img_fichier] =&gt; repper_pattern2_jpg__641201.jpg
                                            [img_poids] =&gt; 29
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [8] =&gt; Array
                                        (
                                            [img_id] =&gt; 641202
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; test_arbre.png
                                            [img_titre_alt] =&gt; test_arbre.png
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-10-07 07:14:16
                                            [img_fichier] =&gt; test_arbre_png__641202.png
                                            [img_poids] =&gt; 135
                                            [img_type] =&gt; image/png
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [9] =&gt; Array
                                        (
                                            [img_id] =&gt; 641199
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; leman_evian_bateau_barque.jpg
                                            [img_titre_alt] =&gt; leman_evian_bateau_barque.jpg
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-10-07 07:13:38
                                            [img_fichier] =&gt; leman_evian_bateau_barqu__641199.jpg
                                            [img_poids] =&gt; 113
                                            [img_type] =&gt; image/jpeg
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128703
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                )

                        )

                )

            [act] =&gt; Array
                (
                    [0] =&gt; Array
                        (
                            [rub_id] =&gt; 128718
                            [rub_nom] =&gt; Test
                            [rub_ordre_img] =&gt; _641272_641269_641267_641266_641265
                            [rub_link] =&gt;
                            [rub_publier] =&gt; publie
                            [rub_coul] =&gt; CCCCCC
                        )

                    [1] =&gt; Array
                        (
                            [rub_id] =&gt; 128665
                            [rub_nom] =&gt; Première rubrique
                            [rub_ordre_img] =&gt; _641253_641252_641251_0
                            [rub_link] =&gt;
                            [rub_publier] =&gt; publie
                            [rub_coul] =&gt; CCCCCC
                        )

                    [2] =&gt; Array
                        (
                            [rub_id] =&gt; 128714
                            [rub_nom] =&gt; Nouvelle rubrique
                            [rub_ordre_img] =&gt; _641258
                            [rub_link] =&gt;
                            [rub_publier] =&gt; publie
                            [rub_coul] =&gt; CCCCCC
                        )

                    [img] =&gt; Array
                        (
                            [128718] =&gt; Array
                                (
                                    [0] =&gt; Array
                                        (
                                            [img_id] =&gt; 641272
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; Nouvelle page
                                            [img_titre_alt] =&gt; Nouvelle page
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-04 12:02:20
                                            [img_fichier] =&gt;
                                            [img_poids] =&gt; 0
                                            [img_type] =&gt;
                                            [img_desc] =&gt;
                                            [img_html] =&gt; &lt;p&gt;&lt;img alt="" src="/users_2/t/9/t93/img_cms/images/jungleok.jpg" style="width: 901px; height: 1226px;" /&gt;&lt;/p&gt;
                                            [fk_rub_id] =&gt; 128718
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [1] =&gt; Array
                                        (
                                            [img_id] =&gt; 641269
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; Nouvelle page
                                            [img_titre_alt] =&gt; Nouvelle page
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-03 18:20:05
                                            [img_fichier] =&gt;
                                            [img_poids] =&gt; 0
                                            [img_type] =&gt;
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128718
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [2] =&gt; Array
                                        (
                                            [img_id] =&gt; 641267
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; Nouvelle page
                                            [img_titre_alt] =&gt; Nouvelle page
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-03 18:20:05
                                            [img_fichier] =&gt;
                                            [img_poids] =&gt; 0
                                            [img_type] =&gt;
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128718
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [3] =&gt; Array
                                        (
                                            [img_id] =&gt; 641266
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; Nouvelle page
                                            [img_titre_alt] =&gt; Nouvelle page
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-03 18:20:04
                                            [img_fichier] =&gt;
                                            [img_poids] =&gt; 0
                                            [img_type] =&gt;
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128718
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                    [4] =&gt; Array
                                        (
                                            [img_id] =&gt; 641265
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; Nouvelle page
                                            [img_titre_alt] =&gt; Nouvelle page
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-11-03 18:20:04
                                            [img_fichier] =&gt;
                                            [img_poids] =&gt; 0
                                            [img_type] =&gt;
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
                                            [fk_rub_id] =&gt; 128718
                                            [img_offre_similaire] =&gt;
                                            [img_offre_vente] =&gt;
                                        )

                                )

                            [128665] =&gt; Array
                                (
                                    [] =&gt; Array
                                        (
                                            [img_id] =&gt; 641119
                                            [img_id_us] =&gt; 45316
                                            [img_publier] =&gt; publie
                                            [img_titre] =&gt; Page  2
                                            [img_titre_alt] =&gt;
                                            [img_link] =&gt;
                                            [img_date_crea] =&gt; 2017-07-06 15:58:21
                                            [img_fichier] =&gt;
                                            [img_poids] =&gt; 0
                                            [img_type] =&gt;
                                            [img_desc] =&gt;
                                            [img_html] =&gt;
 */
