<?php

/*
|--------------------------------------------------------------------------
| Themes des books
|--------------------------------------------------------------------------
|
| Les onze habillages du legacy (dix gabarits : `mdl_default` est converti
| en `mdl_2014_responsive` a l import, comme le legacy le faisait a
| l affichage).
|
| - `gabarit` : point d'entree du theme (le `display()` du legacy) ;
| - `accueil` : ce que montre la page d'accueil — `portfolio` (Zoom, 2020 :
|   les galeries) ou `pages` (2012 a 2015 : les pages d'accueil,
|   categorie 1). Voir inc/inc_user_book_modele.php, mdl_*_action() ;
| - `portfolio_vers_accueil` : /portfolio sans galerie rend l'accueil ;
| - `contact` : `page` quand le theme n'a pas de gabarit contact — le
|   formulaire est alors presente comme une page de rubrique ;
| - `dossier` : repertoire des vues Blade (resources/views/book/themes/)
|   et, pour les themes 2012+, du gabarit Savant d origine
|   (2011_html_pages_v2/<dossier>/) ;
| - `assets` : repertoire des feuilles et scripts sous public/2012_web/ ;
| - `defaut` : configuration JSON appliquee quand le createur n a jamais
|   regle son theme. Extraite telle quelle de conf/conf_mdl_book.php et de
|   2011_front/action_book.php (themes 2012 et 2013). `%prenom%`, `%nom%`,
|   `%site_url%` et `%site_nom%` sont substitues a l affichage.
|
| GENERE depuis le legacy le 2026-09-22. Seule retouche : « ActualitÃ©s »
| (double encodage dans la source de Slide 2012) rendu en « Actualités ».
*/

return [
    'mdl_classique' => [
        'titre' => 'Modèle classique',
        'dossier' => '_racine',
        'gabarit' => 'ultrabook_type',
        'accueil' => 'classique',
        'assets' => null,
        'colonne_legacy' => null,
        'defaut' => null,
    ],

    'mdl_2012' => [
        'titre' => 'Modèle portfolio 2012',
        'dossier' => 'base',
        'gabarit' => 'ultrabook_2012_type',
        'accueil' => 'pages',
        'contact' => 'page',
        'portfolio_vers_accueil' => true,
        'assets' => 'base',
        'colonne_legacy' => 'us_pf_conf2012',
        'defaut' => '{"data":{"accueil_contenu_aff_a":{"accueil_contenu_aff_a":"false"},"accueil_contenu_aff_b":{"accueil_contenu_aff_b":"false"},"accueil_menu_aff":{"accueil_menu_aff":"true"},"ub_menu_titre_accueil":{"form_text":"Accueil"},"ub_menu_titre_ptf":{"form_text":"Portfolio"},"ub_menu_titre_actu":{"form_text":"Actualités"},".ub_font_menut":{"fontFamily":"Oleo Script","color":"#e04545"},".ub_font_menu_newsr":{"fontFamily":"Oswald","color":"#000000"},".ub_font_menu_newsp":{"fontFamily":"Ubuntu Condensed","color":"#787878"},"accueil_img_size":{"accueil_img_size":345},"accueil_img_marge_size":{"accueil_img_marge_size":14},"titre_aff":{"titre_aff":"true"},"ptf_vignette_aff":{"ptf_vignette_aff":"true"},"top_marge_size":{"top_marge_size":23},"link_css":{"link_css":"white"},".ub_couleur_fond":{"backgroundColor":"#ebebeb"},".ub_font_ptf_titre":{"fontFamily":"Signika","color":"#000000","fontSize":"14px"},".ub_font_ptf_legende":{"color":"#8f8f8f","fontFamily":"Signika","fontSize":"11px"},"ptf_taille_vignette":{"ptf_taille_vignette":"l"},"ptf_diapo_aff":{"ptf_diapo_aff":"false"},"ptf_titre_aff":{"ptf_titre_aff":"true"},"ptf_nb_vignette":{"ptf_nb_vignette":22},"ptf_position_vignette":{"ptf_position_vignette":"2"},"ptf_vignette_aff_att":{"ptf_vignette_aff_att":"black"},"ptf_vignette_ombre":{"ptf_vignette_ombre":"true"},".ub_ptf_couleur_fond_page":{"ub_ptf_couleur_fond_page":"#ffffff"},"ptf_choix_fond":{"ptf_choix_fond":"coul"},"pr_ombre_aff":{"pr_ombre_aff":"page_base_ombre_fonce"}}}',
    ],

    'mdl_2012_slide' => [
        'titre' => 'Modèle portfolio 2012-slide',
        'dossier' => 'slide',
        'gabarit' => 'ultrabook_2012_type',
        'accueil' => 'pages',
        'contact' => 'page',
        'portfolio_vers_accueil' => true,
        'assets' => 'slide',
        'colonne_legacy' => 'us_pf_conf2012_slide',
        'defaut' => '{"data":{"accueil_contenu_aff_a":{"accueil_contenu_aff_a":"false"},"accueil_contenu_aff_b":{"accueil_contenu_aff_b":"false"},"accueil_menu_aff":{"accueil_menu_aff":"true"},"ub_menu_titre_accueil":{"form_text":"[accueil-blanc]"},"ub_menu_titre_ptf":{"form_text":"Portfolio"},"ub_menu_titre_actu":{"form_text":"Actualités"},".ub_font_menut":{"fontFamily":"Economica","color":"#ffffff","fontSize":"20px"},".ub_font_menu_newsr":{"fontFamily":"Dosis","color":"#d9d2d2","fontSize":"16px"},".ub_font_menu_newsp":{"fontFamily":"Dosis","color":"#b8b2b2","fontSize":"13px"},"accueil_img_size":{"accueil_img_size":308},"accueil_img_marge_size":{"accueil_img_marge_size":14},"titre_aff":{"titre_aff":"true"},"ptf_vignette_aff":{"ptf_vignette_aff":"true"},"top_marge_size":{"top_marge_size":19},"link_css":{"link_css":"black"},".ub_couleur_fond":{"backgroundColor":"#363636"},".ub_font_ptf_titre":{"fontFamily":"Signika","color":"#000000","fontSize":"14px"},".ub_font_ptf_legende":{"color":"#8f8f8f","fontFamily":"Signika","fontSize":"11px"},"ptf_taille_vignette":{"ptf_taille_vignette":"l"},"ptf_diapo_aff":{"ptf_diapo_aff":"false"},"ptf_titre_aff":{"ptf_titre_aff":"false"},"ptf_nb_vignette":{"ptf_nb_vignette":19},"ptf_position_vignette":{"ptf_position_vignette":"2"},"ptf_position_vign":{"ptf_position_vign":"top"},"ptf_type_vign":{"ptf_type_vign":"thumbs"},".ub_ptf_couleur_fond":{"ub_ptf_couleur_fond":"#211f1f"},".fotorama__caption":{"fontFamily":"Belleza","fontSize":"17px","color":"#e33be3"},"ptf_choix_fond":{"ptf_choix_fond":"coul"},".ub_ptf_vigncouleur_fond":{"ub_ptf_vigncouleur_fond":"#292424"},"pr_ombre_aff":{"pr_ombre_aff":"page_base_ombre_fonce"},"ptf_vignette_aff_att":{"ptf_vignette_aff_att":"black"},".ub_menu_coul_fond":{"ub_menu_coul_fond":"#4e4f4c"},"ptf_vignette_ombre":{"ptf_vignette_ombre":"true"},".ub_ptf_couleur_fond_page":{"ub_ptf_couleur_fond_page":"#424242"}}}',
    ],

    'mdl_2013_pinter' => [
        'titre' => 'Modèle portfolio 2013-Pinter',
        'dossier' => 'pinter',
        'gabarit' => 'ultrabook_2012_type',
        'accueil' => 'pages',
        'contact' => 'page',
        'portfolio_vers_accueil' => true,
        'assets' => 'pinter',
        'colonne_legacy' => 'us_pf_conf2013_pinter',
        'defaut' => '{"data":{"accueil_contenu_aff_a":{"accueil_contenu_aff_a":"false"},"accueil_contenu_aff_b":{"accueil_contenu_aff_b":"false"},"ub_menu_titre_accueil":{"form_text":""},"ub_menu_titre_ptf":{"form_text":"Portfolio"},"ub_menu_titre_actu":{"form_text":"Infos"},".ub_font_menut":{"fontFamily":"Oleo Script","color":"#e04545"},".ub_font_menu_newsr":{"fontFamily":"Oswald","color":"#000000"},".ub_font_menu_newsp":{"fontFamily":"Ubuntu Condensed","color":"#787878"},"top_marge_size":{"top_marge_size":46},"link_css":{"link_css":"white"},".ub_couleur_fond":{"backgroundColor":"#e3e3e0"},".ub_font_ptf_titre":{"fontFamily":"Signika","color":"#000000","fontSize":"14px"},".ub_font_ptf_legende":{"color":"#8f8f8f","fontFamily":"Signika","fontSize":"11px"},".ub_ptf_couleur_fond_page":{"ub_ptf_couleur_fond_page":"#ffffff"},"ptf_choix_fond":{"ptf_choix_fond":"coul"},"pr_ombre_aff":{"pr_ombre_aff":"page_base_ombre_fonce"},"titre_aff":{"titre_aff":"true"},"bando_aff":{"bando_aff":"true"}}}',
    ],

    'mdl_2014_responsive' => [
        'titre' => 'Modèle Responsive 2014',
        'dossier' => 'responsive',
        'gabarit' => 'ultrabook_2014_type',
        'accueil' => 'pages',
        'contact' => 'page',
        'portfolio_vers_accueil' => true,
        'assets' => 'responsive',
        'colonne_legacy' => 'us_pf_conf2014_responsive',
        'defaut' => '{"data":{"accueil_contenu_aff":{"accueil_contenu_aff_c":"false"},"ub_menu_titre_accueil":{"form_text":"[accueil-noir]"},"ub_menu_titre_ptf":{"form_text":"Portfolio"},"ub_menu_titre_actu":{"form_text":"Bio"},".ub_font_menut":{"fontFamily":"Ruda","color":"rgb(184, 122, 46)"},".ub_font_menu_newsr":{"fontFamily":"Ruda","color":"#000000"},".ub_font_menu_newsp":{"fontFamily":"Ruda","color":"#787878"},".ub_font_ptf_titre":{"fontFamily":"Ruda","color":"#000000","fontSize":"14px"},".ub_font_ptf_legende":{"color":"#8f8f8f","fontFamily":"Ruda","fontSize":"11px"},"ptf_type_presentation":{"ptf_type_presentation":"slide"},"ptf_position_vign":{"ptf_position_vign":"bottom"},"ptf_type_vign":{"ptf_type_vign":"thumbs"},"ptf_titre_aff":{"ptf_titre_aff":"false"},".ub_couleur_fond":{"backgroundColor":"#fff"}}}',
    ],

    'mdl_2015_classique' => [
        'titre' => 'Modèle Classique 2015',
        'dossier' => 'classique2015',
        'gabarit' => 'ultrabook_2015_type',
        'accueil' => 'pages',
        'contact' => 'page',
        'assets' => 'classique2015',
        'colonne_legacy' => 'us_pf_conf2015_classique',
        'defaut' => '{"data":{"accueil_contenu_aff":{"accueil_contenu_aff_c":"false"},"accueil_img_marge_size":{"accueil_img_marge_size":"0"},"ub_menu_titre_accueil":{"form_text":"[accueil-noir]"},"ub_menu_titre_ptf":{"form_text":"Portfolio"},"ub_menu_titre_actu":{"form_text":"Bio"},".ub_font_menut":{"fontFamily":"Ruda","color":"rgb(184, 122, 46)"},".ub_font_menu_newsr":{"fontFamily":"Ruda","color":"#000000"},".ub_font_menu_newsp":{"fontFamily":"Ruda","color":"#787878"},".ub_font_ptf_titre":{"fontFamily":"Ruda","color":"#000000","fontSize":"14px"},".ub_font_ptf_legende":{"color":"#8f8f8f","fontFamily":"Ruda","fontSize":"11px"},"ptf_type_presentation":{"ptf_type_presentation":"slide"},"ptf_position_vign":{"ptf_position_vign":"bottom"},"ptf_type_vign":{"ptf_type_vign":"moyenne"},"ptf_titre_aff":{"ptf_titre_aff":"false"},".ub_couleur_fond":{"backgroundColor":"#fff"}}}',
    ],

    'mdl_2015_grid' => [
        'titre' => 'Modèle Grid 2015',
        'dossier' => 'grid2015',
        'gabarit' => 'ultrabook_2015_type',
        'accueil' => 'pages',
        'contact' => 'page',
        'assets' => 'grid2015',
        'colonne_legacy' => 'us_pf_conf2015_grid',
        'defaut' => '{"data":{"accueil_contenu_aff":{"accueil_contenu_aff_c":"false"},"accueil_img_marge_size":{"accueil_img_marge_size":"140"},"ub_menu_titre_accueil":{"form_text":"[accueil-noir]"},"ub_menu_titre_ptf":{"form_text":"Portfolio"},"ub_menu_titre_actu":{"form_text":"Bio"},".ub_font_menut":{"fontFamily":"Ruda","color":"rgb(184, 122, 46)"},".ub_font_menu_newsr":{"fontFamily":"Ruda","color":"#000000"},".ub_font_menu_newsp":{"fontFamily":"Ruda","color":"#787878"},".ub_font_ptf_titre":{"fontFamily":"Ruda","color":"#000000","fontSize":"14px"},".ub_font_ptf_legende":{"color":"#8f8f8f","fontFamily":"Ruda","fontSize":"11px"},"ptf_type_presentation":{"ptf_type_presentation":"slide"},"ptf_position_vign":{"ptf_position_vign":"bottom"},"ptf_type_vign":{"ptf_type_vign":"moyenne"},"ptf_titre_aff":{"ptf_titre_aff":"false"},".ub_couleur_fond":{"backgroundColor":"#fff"}}}',
    ],

    'mdl_2016_zoom' => [
        'titre' => 'Modèle Zoom 2016',
        'dossier' => 'zoom2016',
        'gabarit' => 'ultrabook_type',
        'accueil' => 'portfolio',
        'assets' => 'zoom2016',
        'colonne_legacy' => 'us_pf_conf2016_zoom',
        'defaut' => '{"data":{"link_accueil":{"form_text":"Portfolio"},"link_bio":{"form_text":"Bio"},"link_contact":{"form_text":"Contact"},"ptf_activer_contact":{"ptf_activer_contact":"true"},"ptf_activer_gmap":{"ptf_activer_gmap":"false"},"ptf_activer_sociaux":{"ptf_activer_sociaux":"true"},"ptf_activer_iso_category":{"ptf_activer_iso_category":"false"},".ub_couleur_fond":{"backgroundColor":"#ffffff"},".ub_couleur_nav":{"color":"#292929"}}}',
    ],

    'mdl_2020_ultra_zen' => [
        'titre' => 'Ultra-zen',
        'dossier' => 'ultra2020',
        'gabarit' => 'ultrabook_type',
        'accueil' => 'portfolio',
        'assets' => 'ultra2020',
        'colonne_legacy' => 'us_pf_conf2020_ultra_zen',
        'defaut' => '{"data":{"expert":"false","cursor":"false","theme":"theme_white","visuel_size":"normal","header":"8","header_size":"S","titre":"%prenom% %nom%","description":"Direction artistique, design graphique, illustration","nav_link":{"name_portfolio":"Portfolio","name_page":"Bio","name_contact":"Contact"},"expert_css":"","social_link":{},"contact_titre":"Et si on parlait de votre projet ?","contact_footer":"","footer":"<a href=%site_url%>%site_nom% | modèle Ultra-frais</a>","end":{"end":"no-comma-at-end"}}}',
    ],

    'mdl_2020_ultra_frais' => [
        'titre' => 'Ultra-frais',
        'dossier' => 'ultra2020',
        'gabarit' => 'ultrabook_type',
        'accueil' => 'portfolio',
        'assets' => 'ultra2020',
        'colonne_legacy' => 'us_pf_conf2020_ultra_zen',
        'defaut' => '{"data":{"expert":"false","cursor":"false","theme":"theme_white","visuel_size":"normal","header":"8","header_size":"S","titre":"%prenom% %nom%","description":"Direction artistique, design graphique, illustration","nav_link":{"name_portfolio":"Portfolio","name_page":"Bio","name_contact":"Contact"},"expert_css":"","social_link":{},"contact_titre":"Et si on parlait de votre projet ?","contact_footer":"","footer":"<a href=%site_url%>%site_nom% | modèle Ultra-frais</a>","end":{"end":"no-comma-at-end"}}}',
    ],

];
