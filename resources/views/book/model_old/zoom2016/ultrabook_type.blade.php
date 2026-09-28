{{-- Porte depuis 2011_html_pages_v2/zoom2016/ultrabook_type.tlp.php (_outils/porter_gabarits.py) --}}
<?php 
/* ------------------------------------------------
	Ultra-Book Zoom 2016 | Modèle page : struture
------------------------------------------------ */

	// function mdl
	//
	//require_once \App\Services\Book\Gabarit::chemin('zoom2016/__ultrabook_function_mdl.php');
	
	/*
 	Mdl grid 2015 
	
	
	!
	Les CSS relatives au mdl GRID 2015 doivent commencées pas  #mdl_grid_2015
	Pour ne pas contaminer les autres CSS
	!
	
	
	 * 
	 * 
	 * à intégrer
	 * http://ub.selfip.net/2012_web/zoom2016/_/js/ish-master/index.php?url=http%3A%2F%2F14dec2015.ub.selfip.net%2Faccueil#
	 * 
	*/
	
	
?>
 
<?php

// fonctions communes -nov2016
//
include \App\Services\Book\Gabarit::chemin('zoom2016/_fonction.php');





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
			


	            

<?php 


	include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/_ultrabook__header.tlp.php'); 


            //echo 'ok+++++++++++++'.$b->page_type ;
            		
            switch ($b->page_type) {
            	                					                
                case 'accueil':		
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



	include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_footer_stats.php');
	
	include \App\Services\Book\Gabarit::chemin($b->url_mdl.'/ultrabook_footer.php');	
	

?>