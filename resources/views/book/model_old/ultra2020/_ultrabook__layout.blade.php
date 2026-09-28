{{-- Porte depuis 2011_html_pages_v2/ultra2020/_ultrabook__layout.tlp.php (_outils/porter_gabarits.py) --}}
<!DOCTYPE html>
<html>
<head>


	<!-- Standard Meta -->
	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

	<title><?=ucfirst($b->cont_page_titre);?></title>


	<meta name="keywords" content="<?=__('Ultra-book, creation de book,')?> <?=str_replace(array("[&quot;","&quot;]","&quot;,&quot;"), array("","",","),  $b->cont_page_key);?>"/>
	<meta name="description" content="book <?=$b->cont_page_meta?> <?=(($b->page_type=='accueil')?$b->gal_cont['img'][0]['img_titre_alt']:'');?> <?=(($b->page_type=='news')?$b->gal_cont['img'][0]['img_titre_alt']:'');?>" />

	<!-- icon -->
	<link rel="shortcut icon" href="<?=$b->icone;?>"/>
	<link rel="apple-touch-icon" href="<?=$b->icone_iphone;?>"/>
	<link rel="apple-touch-icon" sizes="72x72" href="<?=$b->icone_ipad;?>" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

	<!-- microdata -->
	<?php
		$tmp_description = 	$b->cont_page_meta.(($b->page_type=='accueil')?$b->gal_cont['img'][0]['img_titre_alt']:'').(($b->page_type=='news')?$b->gal_cont['img'][0]['img_titre_alt']:'');
		$tmp_url = 			request()->getHost().request()->getPathInfo();



		//echo $b->visuel_accueil;



		//if (preg_match("/accueil|portfolio/",$b->page_type)) {
        if (empty($b->visuel_accueil)) {
			$tmp_kk =  			 @\App\Services\Book\Php7::key($b->gal_cont['gal']['img']);
			$tmp_img = 			 $b->url_abs_site.$b->rep_img900.$b->gal_cont['gal']['img'][$tmp_kk][0]['img_fichier'];
			$tmp_img_tw =		 $b->url_abs_site.$b->rep_img320.$b->gal_cont['gal']['img'][$tmp_kk][0]['img_fichier'];
		} else {
			$tmp_img = 			(preg_match("#http#",$b->visuel_accueil)?'':$b->url_abs_site.$b->rep_pref).$b->visuel_accueil;
		}


		if ( ! empty($tmp_img) && ! preg_match("#\/$#", $tmp_img) ) 	list($tmp_w,$tmp_h) = @@getimagesize($tmp_img);


	?>

	<meta property="og:locale"				   content="fr_FR" />
	<meta property="og:type"                   content="website" />
	<meta property="og:title"                  content="<?=ucfirst($b->cont_page_titre);?>" />
	<meta property="og:image"                  content="<?=$tmp_img;?>" />
	<meta property="og:description"            content="<?=$tmp_description;?>" />
	<meta property="og:url"                    content="<?=$tmp_url;?>" />
	<meta property="og:image:width"            content="<?=$tmp_w;?>" />
	<meta property="og:image:height"           content="<?=$tmp_h;?>" />
	<meta property="og:site_name" 			   content="<?=ucfirst($b->cont_page_titre);?>" />

	<meta name="twitter:card" 				   content="summary" />
	<meta name="twitter:title" 				   content="<?=ucfirst($b->cont_page_titre);?>" />
	<meta name="twitter:description" 		   content="<?=$tmp_description;?>" />
	<meta name="twitter:url" 			 	   content="<?=$tmp_url;?>" />
	<meta name="twitter:image" 				   content="<?=$tmp_img_tw;?>" />
	<meta name="twitter:image:width"           content="<?=$tmp_w/2;?>" />
	<meta name="twitter:image:height"          content="<?=$tmp_h/2;?>" />



	<!-- Jquery -->
	<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script async src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
    <!-- Jquery fallback -->
    <script>
        window.jQuery || document.write('<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js_cdn/jquery-1.12.4.min.js"><\/script>');
        window.jQuery || document.write('<script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js_cdn/jquery-migrate-1.4.1.min.js"><\/script>')
    </script>



	<!-- sementic UI -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
	<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>


	<!-- css core -->
	<link rel='stylesheet' href='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/css/core.css?v=2' type='text/css' media='all'/>
	<link rel='stylesheet' href='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/css/masory.css?v=2' type='text/css' media='all'/>


    <!-- jQuery LavaLamp -->
    <link rel='stylesheet' href='<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/lib_js/Lava-Lamp-master/css/jquery.lavalamp.css' type='text/css' media='all'/>
    <script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/lib_js/Lava-Lamp-master/js/jquery.lavalamp.js"></script>


	<!-- Magnific Popup -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>


    <!-- js core front -->
    <script src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js/core.js?v=2"></script>



<?php if ( $b->connection_admin_book ) {
	//print_r($_SESSION);
	?>

    <!-- upload admin -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/file-uploader/3.7.0/fineuploader.min.css" />
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/file-uploader/3.7.0/fineuploader-jquery.min.js"></script>


	<!-- core admin -->
	<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/_admin_js.tlp.php'); ?>
	<!-- core admin #fin -->

<?php } else { ?>

	<?php if ( $b->connection_admin_token_exist ) { ?>
        <script>
            alert("<?=__('Error - Reconnectez-vous !')?>");
        </script>
	<?php } ?>

<?php } ?>


<script>

    var __ = {
        __ : {
            'Doit contenir plus de {ruleValue} caracteres' :    '<?=__( 'Doit contenir plus de {ruleValue} caractères' )?>',
            'Indiquer votre nom' :                              '<?=__( 'Indiquer votre nom' )?>',
            'Votre nom de book/identifiant doit contenir plus de  {ruleValue} caracteres' :  '<?=__( 'Votre nom de book/identifiant doit contenir plus de  {ruleValue} caractères' )?>',
            'Caracteres incorrecte' :                           '<?=__( 'Caractéres incorrecte' )?>',
            'Ce nom existe deja' :                              '<?=__( 'Ce nom existe déja' )?>',
            'Selectionner un metier ou domaine' :               '<?=__( 'Sélectionner un métier ou domaine' )?>',
            'Votre mot de passe doit contenir plus de  {ruleValue} caracteres':    '<?=__( 'Votre mot de passe doit contenir plus de  {ruleValue} caractères' )?>',
            'Vous devez accepter les conditions d’utilisation' : '<?=__( 'Vous devez accepter les conditions d’utilisation' )?>',

            'Il ne s’agit pas d’un mail' :                       '<?=__( 'Il ne s’agit pas d’un mail' )?>',
            'Votre mail doit contenir plus de  {ruleValue} caracteres' : '<?=( 'Votre mail doit contenir plus de  {ruleValue} caractères' )?>',
            'Indiquer votre mail' :                              '<?=__( 'Indiquer votre mail'  )?>',
            'Le montant maximum est inférieur au montant minimum':'<?=__( 'Le montant maximum est inférieur au montant minimum'  )?>',
            'Indiquer une valeur':                               '<?=__( 'Indiquer une valeur'  )?>',
            'Indiquez votre identifiant (pas votre mail)' :      '<?=__( 'Indiquez votre identifiant (pas votre mail)'  )?>',
            'Champ vide' :                                       '<?=__( 'Champ vide')?>',
            'Supprimer l’image' :                                '<?=__( 'Supprimer l’image')?>',
            'Formule premium' :                                  '<?=__( 'Modifications réservées aux formules premium')?>',
            'Error window' :                                     '<?=__( 'La fenêtre de votre navigateur est trop petite pour afficher le module de modification')?>'
        }
    };


    var reCAPTCHA_key_public  =      '<?='6Lc8O5IUAAAAAJer15iYwddEROZzZnnIVzQe4P_1';?>'

    // page reglage load conf
    var ub_pr = '';
    var ub_pr_conf_init = 		<?=$b->cont_conf2012;?>;
    ub_pr_conf_init = 		    ub_pr_conf_init['data'];


</script>

<style type='text/css' media='all' title='css_expert'>
	<?=trim($b->obj_pref->data->expert_css);?>
</style>


</head>
<body class="<?=$b->theme_mdl?> <?=$b->obj_pref->data->theme?>" id="<?=$b->page_type?>">

<!-- tpl front header icon !-->
<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/__template_front.tpl.php') ?>



<?php if ( $b->connection_admin_book ) { ?>
<!-- admin menu -->
<div class="ui sidebar vertical menu " id="admin_menu">
	<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/admin_menu.tpl.php') ?>
</div>
<?php } ?>



<!-- cursor ! -->

<div id="cursor_follower">
    <div id="circle1"></div>
    <div id="circle2"></div>
</div>




<!-- content !-->
<div class="pusher">


	<!-- mobile -->
	<div class="myViewport_ transition hidden">
		<div id="myViewport" class="viewport"></div>
	</div>

	<!-- content -->
	<div class="full height">



	<?php if ( $b->connection_admin_book ) { ?>
		<!-- admin -->
		<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/__sidebar.tpl.php') ?>
		<!-- admin #fin -->
	<?php } ?>



		<div class="btn_top_move cursor_effect " id="btn_top_action"></div>
		<div class="top_position_show"></div>
		<div class="top_position"></div>

		<!-- mobile menu -->
		<i class="burger   mobile_menu_btn">
			<img src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/svg/hamburger.svg" alt="menu">
		</i>
		<i class="close    mobile_menu_btn hide">
			<img src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/svg/croix-dark.svg" alt="close">
		</i>
		<!-- mobile menu end -->




		<div class="main ui container">


			<div class="ui stackable  grid">
				<div class="four wide column column_ultrafrais">
					<div class="column-menu ">


						<?php if ( ! $b->connection_admin_book ) { ?>
                        <a href="/">
						<?php } ?>
                        <div class="admin_edit admin_mode_icon header_icon cursor_effect"
                             data-admin-edit="edit-icon-trash"
                             data-admin-objet="header"
                             data-admin-edit-viaobj="true">
                            <?php $tmp_header = (string)($b->obj_pref->data->header ?? '');
                            // N'écho que si c'est du HTML (SVG, img…), pas une valeur numérique
                            // stockée par core.js (ex: 1 = "photo perso") qui causait un flash "1" visible
                            if (strpos($tmp_header, '<') !== false) echo $tmp_header; ?>
                        </div>
						<?php if ( $b->connection_admin_book ) { ?>
                        </a>
                        <?php } ?>

						<?php if ( ! $b->connection_admin_book ) { ?>
                        <a href="/">
                        <?php } ?>
						<h1 class="admin_edit admin_mode_textarea header_title cursor_effect"
						    data-admin-edit="edit"
						    data-admin-objet="titre"
						    data-admin-edit-viaobj="true" >
							<?=ultra2020__stripslashes_($b->obj_pref->data->titre);?>
						</h1>
                        <?php if ( $b->connection_admin_book ) { ?>
                        </a>
					    <?php } ?>


						<h2 class="admin_edit admin_mode_textarea"
						    data-admin-edit="edit-trash"
						    data-admin-objet="description"
						    data-admin-edit-viaobj="true">
							<?=ultra2020__stripslashes_($b->obj_pref->data->description);?>
						</h2>

						<div class="admin_edit admin_mode_nav mobile_menu_root"
						     data-admin-edit="edit"
						     data-admin-objet="nav"
						     data-admin-edit-viaobj="true"
						>

							<nav class="ui vertical text menu menu-underline mobile_menu">
								<div class="item admin_nav_item <?=(preg_match("/accueil|portfolio/",$b->page_type))?'active':'';?>">
									<a href="/portfolio" class="cursor_effect">
										<?=ultra2020__stripslashes_($b->obj_pref->data->nav_link->name_portfolio);?>
									</a>
								</div>
								<div class="item  admin_nav_item <?=(preg_match("/news/",$b->page_type))?'active':'';?>">
									<a href="/actualites"  class="cursor_effect">
										<?=ultra2020__stripslashes_($b->obj_pref->data->nav_link->name_page);?>
									</a>

									<?php if (preg_match("/news/",$b->page_type) && $b->theme_mdl == 'theme_ultrazen') { ?>
									<!-- menu page ultrazen -->
									<div class="four wide column  column-page-menu-ultrazen">
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


								</div>
								<div class="item admin_nav_item <?=(preg_match("/contact/",$b->page_type))?'active':'';?>">
									<a href="/contact"  class="cursor_effect">
										<?=ultra2020__stripslashes_($b->obj_pref->data->nav_link->name_contact);?>
									</a>
								</div>
							</nav>

						</div>




						<div class="ub-social">
							<div class="item social_link_facebook cursor_effect">
								<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_facebook);?>"
								   class="<?=( $b->obj_pref->data->social_link->link_facebook == '' )?'hide':''?> cursor_effect">
									facebook
								</a>
							</div>
							<div class="item social_link_instagram">
								<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_instagram);?>"
								   class="<?=( $b->obj_pref->data->social_link->link_instagram == '' )?'hide':''?>">
									instagram
								</a>
							</div>
							<div class="item social_link_pinterest">
								<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_pinterest);?>"
								   class="<?=( $b->obj_pref->data->social_link->link_pinterest == '' )?'hide':''?>">
									pinterest
								</a>
							</div>
							<div class="item social_link_twitter">
								<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_twitter);?>"
								   class="<?=( $b->obj_pref->data->social_link->link_twitter == '' )?'hide':''?>">
									twitter
								</a>
							</div>
							<div class="item social_link_linkedin">
								<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_linkedin);?>"
								   class="<?=( $b->obj_pref->data->social_link->link_linkedin == '' )?'hide':''?>">
									linkedin
								</a>
							</div>

						</div>



					</div>
				</div>



				<?php
					switch ($b->page_type) {

					case 'accueil':
					case 'portfolio':
					include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/portfolio.tlp.php');
					break;

					case 'news':
					include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/page.tlp.php');
					break;

					case 'contact':
					include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/contact.tlp.php');
					break;
					}

				?>

			</div>


			<div class="ui equal width center aligned  grid ub-social social-footer">


				<?php if( $b->obj_pref->data->social_link->link_facebook ) { ?>
					<div class="column social_link_facebook cursor_effect">
						<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_facebook);?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path fill="none" d="M0 0h24v24H0z"/><path d="M13 9h4.5l-.5 2h-4v9h-2v-9H7V9h4V7.128c0-1.783.186-2.43.534-3.082a3.635 3.635 0 0 1 1.512-1.512C13.698 2.186 14.345 2 16.128 2c.522 0 .98.05 1.372.15V4h-1.372c-1.324 0-1.727.078-2.138.298-.304.162-.53.388-.692.692-.22.411-.298.814-.298 2.138V9z" fill="#000"/></svg>
							<span>facebook</span>
						</a>
					</div>
				<?php } ?>

				<?php if( $b->obj_pref->data->social_link->link_instagram ) { ?>
					<div class="column social_link_instagram cursor_effect">
						<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_instagram);?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm0-2a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm6.5-.25a1.25 1.25 0 0 1-2.5 0 1.25 1.25 0 0 1 2.5 0zM12 4c-2.474 0-2.878.007-4.029.058-.784.037-1.31.142-1.798.332-.434.168-.747.369-1.08.703a2.89 2.89 0 0 0-.704 1.08c-.19.49-.295 1.015-.331 1.798C4.006 9.075 4 9.461 4 12c0 2.474.007 2.878.058 4.029.037.783.142 1.31.331 1.797.17.435.37.748.702 1.08.337.336.65.537 1.08.703.494.191 1.02.297 1.8.333C9.075 19.994 9.461 20 12 20c2.474 0 2.878-.007 4.029-.058.782-.037 1.309-.142 1.797-.331.433-.169.748-.37 1.08-.702.337-.337.538-.65.704-1.08.19-.493.296-1.02.332-1.8.052-1.104.058-1.49.058-4.029 0-2.474-.007-2.878-.058-4.029-.037-.782-.142-1.31-.332-1.798a2.911 2.911 0 0 0-.703-1.08 2.884 2.884 0 0 0-1.08-.704c-.49-.19-1.016-.295-1.798-.331C14.925 4.006 14.539 4 12 4zm0-2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153a4.908 4.908 0 0 1 1.153 1.772c.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 0 1-1.153 1.772 4.915 4.915 0 0 1-1.772 1.153c-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 0 1-1.772-1.153 4.904 4.904 0 0 1-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428a4.88 4.88 0 0 1 1.153-1.772A4.897 4.897 0 0 1 5.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2z" fill="#000"/></svg>
							<span>instagram</span>
						</a>
					</div>
				<?php } ?>

				<?php if ($b->obj_pref->data->social_link->link_pinterest )  { ?>
					<div class="column social_link_pinterest cursor_effect">
						<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_pinterest);?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path fill="none" d="M0 0h24v24H0z"/><path d="M8.49 19.191c.024-.336.072-.671.144-1.001.063-.295.254-1.13.534-2.34l.007-.03.387-1.668c.079-.34.14-.604.181-.692a3.46 3.46 0 0 1-.284-1.423c0-1.337.756-2.373 1.736-2.373.36-.006.704.15.942.426.238.275.348.644.302.996 0 .453-.085.798-.453 2.035-.071.238-.12.404-.166.571-.051.188-.095.358-.132.522-.096.386-.008.797.237 1.106a1.2 1.2 0 0 0 1.006.456c1.492 0 2.6-1.985 2.6-4.548 0-1.97-1.29-3.274-3.432-3.274A3.878 3.878 0 0 0 9.2 9.1a4.13 4.13 0 0 0-1.195 2.961 2.553 2.553 0 0 0 .512 1.644c.181.14.25.383.175.59-.041.168-.14.552-.176.68a.41.41 0 0 1-.216.297.388.388 0 0 1-.355.002c-1.16-.479-1.796-1.778-1.796-3.44 0-2.985 2.491-5.584 6.192-5.584 3.135 0 5.481 2.329 5.481 5.14 0 3.532-1.932 6.104-4.69 6.104a2.508 2.508 0 0 1-2.046-.959l-.043.177-.207.852-.002.007c-.146.6-.248 1.017-.288 1.174-.106.355-.24.703-.4 1.04a8 8 0 1 0-1.656-.593zM12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" fill="#000"/></svg>
							<span>pinterest</span>
						</a>
					</div>
				<?php } ?>

				<?php if ($b->obj_pref->data->social_link->link_twitter )  { ?>
					<div class="column social_link_twitter cursor_effect">
						<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_twitter);?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path fill="none" d="M0 0h24v24H0z"/><path d="M15.3 5.55a2.9 2.9 0 0 0-2.9 2.847l-.028 1.575a.6.6 0 0 1-.68.583l-1.561-.212c-2.054-.28-4.022-1.226-5.91-2.799-.598 3.31.57 5.603 3.383 7.372l1.747 1.098a.6.6 0 0 1 .034.993L7.793 18.17c.947.059 1.846.017 2.592-.131 4.718-.942 7.855-4.492 7.855-10.348 0-.478-1.012-2.141-2.94-2.141zm-4.9 2.81a4.9 4.9 0 0 1 8.385-3.355c.711-.005 1.316.175 2.669-.645-.335 1.64-.5 2.352-1.214 3.331 0 7.642-4.697 11.358-9.463 12.309-3.268.652-8.02-.419-9.382-1.841.694-.054 3.514-.357 5.144-1.55C5.16 15.7-.329 12.47 3.278 3.786c1.693 1.977 3.41 3.323 5.15 4.037 1.158.475 1.442.465 1.973.538z" fill="#000"/></svg>
							<span>twitter</span>
						</a>
					</div>
				<?php } ?>

				<?php if( $b->obj_pref->data->social_link->link_linkedin  )  { ?>
					<div class="column social_link_linkedin cursor_effect">
						<a href="<?=ultra2020__front_add_http($b->obj_pref->data->social_link->link_linkedin);?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 9.55C12.917 8.613 14.111 8 15.5 8a5.5 5.5 0 0 1 5.5 5.5V21h-2v-7.5a3.5 3.5 0 0 0-7 0V21h-2V8.5h2v1.05zM5 6.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm-1 2h2V21H4V8.5z" fill="#000"/></svg>
							<span>linkedin</span>
						</a>
					</div>
				<?php } ?>

				<div class="equal width row">
					<div class="column">
						<div class="ub-footer <?=(($b->us_formule == 1 && false)?'admin_edit admin_mode_textarea':'admin_edit_premium')?>"
						     data-admin-edit="edit-trash"
						     data-admin-objet="footer"
						     data-admin-edit-viaobj="true">
							<?=ultra2020__stripslashes_($b->obj_pref->data->footer);?>
						</div>
					</div>
				</div>


			</div>




			</div>

		</div>

	</div>




<?php if ( $b->connection_admin_book ) { ?>
	<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/__template_back.tpl.php') ?>
<?php } ?>



<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_footer_stats.php'); ?>


</body>
</html>