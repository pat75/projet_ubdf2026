{{-- Porte depuis 2011_html_pages_v2/grid2015/ultrabook_2015_type.tlp.php (_outils/porter_gabarits.py) --}}
<?php 
/* ------------------------------------------------
	Ultra-Book GRID | Modèle page : home
------------------------------------------------ */

	// toutes les data du book en dur
	//require_once('include/ub-data-global.php');
	
	// toutes les fonctions utiles (type affiche_...) 
	//require_once('include/ub-grid-template.php');
		
	
	// function mdl
	//
	require_once \App\Services\Book\Gabarit::chemin('grid2015/__ultrabook_function_mdl.php');
	
	/*
 	Mdl grid 2015 
	
	
	!
	Les CSS relatives au mdl GRID 2015 doivent commencées pas  #mdl_grid_2015
	Pour ne pas contaminer les autres CSS
	!
	
	
	
	
	
	http://raphaeltardif.fr/clients/ultra-book/
	<br/>
	http://ub.selfip.net/2011_html_pages_v2/grid2015/Rafa_grid_mdl_UB______archive-1/archive_rafa/index.php
	<br/>
	
	*/
	
	
?>
 
<?php 
//echo 'ok+++++++++++++'.$b->page_type ;
	
if($b->page_type == 'accueil') 			$typePage = "home";
if($b->page_type == 'portfolio') 		$typePage = "book";
if($b->page_type == 'news') 				$typePage = "page";


// recup pref JSON
//
$obj_pref = json_decode($b->cont_conf2012);

//var_dump($obj_pref);
$data_coulBook =  	$obj_pref->data->{'.ub_couleur_nav'}->color;// Couleur de personalisation
$data_coulBGBook = 	$obj_pref->data->{'.ub_couleur_fond'}->backgroundColor;// couleur fond


?>	
			


	            

<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/_ultrabook__header.tlp.php'); ?>




<body class="ub_couleur_fond bg_image <?php echo $typePage; ?>" rel="<?=$b->url_abs_site.$b->cont_bg;?>">

<a name="top_page"></a>


<div id="page_socle"> 

<!-- reglage de la page -->	
<?php  if ( $b->connection_admin_book ) { 
	//print_r($_SESSION);
	?>
	<!-- reglage de la page 2015 -->		
	<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/_ultrabook_2015__barredereglage.tlp.php');?>	
	
<?php  } ?>
<!-- reglage de la page fin -->




<div id="page_base" >

<div id="mdl_grid_2015" >
	
<a id="barrer2014_r" class="control-panel collapse-sidebar"><i class="icon-chevron_right icon2x"></i></a>	

			<!-- wrapper header -->
			<header class="wrapper header" role="banner">
					
					<!-- logo -->
					<div id="top_visuel" class="img-book show-fade">						
						<a href="/accueil" class="img_file_ajax_link ">
							<div id="visuel_accueil" class="img_file_ajax_FineUp <?=($b->visuel_accueil == 'deleted')?'img_file_ajax_deleted':''?>" 
								data-fileapi_id="<?=$b->visuel_accueil_key;?>" 
								original-title="Vous pouvez déposer ici votre visuel pour - Accueil - par un glisser-poser ( Format: jpeg, Gif en RVB - Poids Max.: 
								<?=$b->visuel_accueil_sizelimit?> Ko - Taille Max.: <?=$b->visuel_accueil_width?>x<?=$b->visuel_accueil_height?> pixels )"
								data-img_default="/img_default/ultra-book_default_160x160.gif" >	
								  <?php  if ($b->visuel_accueil != 'deleted') { ?>
								  <img  src="<?=(preg_match("#http#",$b->visuel_accueil)?'':$b->url_abs_site.$b->rep_pref).$b->visuel_accueil;?>" alt="<?=$b->cont_page_titre;?>" class="img_file_modify" />
								  <?php }?> 	
							</div> 
						</a>
					</div>
					<!-- /logo -->
				
					
					<div class="bt bt-menu show-fade ">
						<span class="icon menu"><span></span></span>
						<span class="text h3-like">MENU</span>
					</div>
					
					<div class="wrap-menu show-fade">
						<div class="content-iscroll">
							<!-- nav -->
							<nav class="nav no-select" role="navigation">								
								<?php include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_menugauche.tlp.php'); ?>
							</nav>							
							<!-- /nav -->
		
							<!-- infos-book -->			
							<!-- cke 1 -->
							<div class="cont_cke_edit infos-book">
								<div <?=( $b->connection_admin_book )?'class="cont_cke_edit_bloc" contenteditable="true"':''?> id="cont_menu_gauche" >
								<?=$b->ed_dom_txt->cont_menu_gauche;?>
								</div>
								
							</div>							
							<!-- /infos-book -->
							
							
						</div>
					</div>

			</header>
			<!-- /header -->


			
			<?php 
            //echo 'ok+++++++++++++'.$b->page_type ;
            		
            switch ($b->page_type) {
            	case 'accueil':							
                    include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_accueil.php');
                    break;							                
                case 'portfolio':							
                    include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_portfolio.php');
                    break;           
                case 'news':
                    include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_news.php');
                    break; 
				 case 'contact':
                    include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_contact.php');
                    break;   	    
            }
			?>

</div>
</div>							
		
		<!-- footer -->
		<?php 
		include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_footer.php');
		include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_footer_stats.php');
		?>
		<!-- /footer -->


</div>



</body>
</html>