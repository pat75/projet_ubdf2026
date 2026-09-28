{{-- Porte depuis 2011_html_pages_v2/ultra2020/_admin_js.tlp.php (_outils/porter_gabarits.py) --}}

<script type="text/javascript">


    // stats live + archives  sep 2016
    var stats_url_stats_archives =      'https://stats.ultraportfolio.info/';

    var stats_us_id =					'<?=$b->us_dir;?>';
    var stats_vignette =				'<?=$b->rep_pref;?>us_pf_img_vignette.gif';
    var stats_nom_prenom =			    '<?=$b->nom." ".$b->prenom;?>';
    var stats_visiteur =				'<?=request()->cookie("us_pr_login");?>';
    var stats_us_type =				    '<?=$b->us_type;?>';


    var ub_pr_conf_url = 		'<?=$b->url_abs_site;?>';
    var ub_page_type = 			'<?=$b->page_type;?>';
    var ub_page_mdl = 			'<?=$b->modele_book;?>';
    var ub_navigateur_client =  '<?=$b->navigateur_client;?>';



    var br_admin = 				false;
    var ub_barre_r = 			'<?=(request()->query('pr') == 'public'?'hide':'show');?>';	// br hide

    var user_lng = 				'<?=$b->user_lng;?>';
    var user_lat = 				'<?=$b->user_lat;?>';

    var IsMobile = 				'<?=$b->IsMobile;?>';



    /* conf */
    var url_ajax_usadmin_pr =       "<?=$b->url_abs_site;?>/front/ajax_2012_usadmin_pr.php";
    var url_ajax_usadmin_img=       "<?=$b->url_abs_site;?>/front/ajax_2010.php";
    var url_ajax_usadmin_editxt =   "<?=$b->url_abs_site;?>/front/ajax_2014_usadmin_editxt.php";
    var data_us_key =               "";<?php  /*=md5($b->us_dir.'passub2012'); obtenu par retour ajax*/?>


	// book token admin 2020
    var token_book =    "<?=null?>";


</script>



<!-- jquery allplugin -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js/jquery_allplugin_mdl2020<?=$js_ext_date?>"></script>

<!-- admin -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js/core_admin.js"></script>
<link  href='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/css/core_admin.css' rel='stylesheet'  type='text/css' media='all'/>

<!-- Include Handlebars from a CDN -->
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/lib_js/handlebars.js"></script>
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/lib_js/Handlebars-setDelimiter.js"></script>
<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/lib_js/easyViewport.js"></script>
