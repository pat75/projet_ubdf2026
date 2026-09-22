{{-- Porte depuis 2011_html_pages_v2/slide/ultrabook_2012_barredereglage.tlp.php (_outils/porter_gabarits.py) --}}
<!-- reglage de la page debut -->

<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_web/slide/css/ub_pr_2012.css" type="text/css" />

<style type="text/css">
	.ub_gal_base_box {
    background: url("<?=$b->url_abs_site;?>/2011_user_admin_img/motif_gris.gif") repeat scroll 0 0 #BBBBBB;}    
    .gal ul.gal_n0 li.gal_box h3 {
    background: url("<?=$b->url_abs_site;?>/2011_user_admin_img/gray-grad.png") repeat-x scroll left top #DFDFDF;}
</style>

	
<!-- js ub_book_core_pr_2012 barre de reglage -->

<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_web/slide/js/ub_book_core_pr_2012.js"></script>


<script type="text/javascript">
/* barre de réglage */
/* conf */
var url_ajax_usadmin_pr = "<?=$b->url_abs_site;?>/front/ajax_2012_usadmin_pr.php";

var gallery1;

var data_us_key = '';


//
// fin init

$(document).ready(function(){ 

	// recup ajax de la barre de reglage
	// en cross domain
	//
	$.ajax({
	     url: url_ajax_usadmin_pr,
	     dataType: 'jsonp',
		 data:   { action: 'us_login', us_dir:'<?=$b->us_dir;?>'  },
		 cache: false,
	     success:function(jsondata){
	         
	         //$('#debug_retourajax').html(jsondata.us_login+' ok2');
	         
	         
	         if ( ub_pr_data.connection_test(jsondata) ) {
	
				// barre de reglage
				//
	         	$('#pr_titre').append(' <strong>'+ jsondata.us_login +'</strong>')
	         	
	         	// key par retour ajax
	         	data_us_key = jsondata.us_key;
	         	
	         	ub_pr.init();
	
			} 
			
			$('#page_reglage_menu').fadeIn();
			
	     },
	     error:function(){
	         alert("Erreur - vous devez vous re-deconnecter...");
	     }
	     
		});


});
</script>
	

<!-- reglage de la page -->		
<div id="page_reglage">
	
	<!--<div id="page_reglage_bouton"></div>-->
	
	<div id="page_reglage_menu">		
	<div class="ub_gal_base_box ">		
	
	<?php /*
	<!-- debug 	
	<div id="debug" style="width:300px;height:140px;background-color:white;overflow: scroll;">***</div><br/>
	<div id="debug_retourajax"  style="width:300px;height:140px;background-color:white">###</div><br/>
	-->	
	<!--<div id="test_b">Refresh</div>-->
	*/
	
	//$b->us_formule = 0;
	
	
	
	?>
    
	<div class="gal ub_corner6" id="gal_base">
				<div class="" style="margin-bottom:8px;">
					<div id="handle" style="width:auto;cursor: move;padding: 7px 9px;background-image: -moz-linear-gradient(center top , #fafafa, #bbb);" class="ui-state-default ui-corner-all">	
						<span id="pr_titre" style="text-shadow: 0 1px 0 #FFFFFF;">Barre de réglages - Slide</span>
						<span class="ui-icon ui-icon-arrow-4"></span>
							<?php  if ($b->us_formule==0) { ?>
						 	<br/><div style="color:red;font-size:10px;">Certaines fonctions sont désactivées<br/>en formule gratuite</div>
							<?php  } ?>
					
						
			
					</div>
				</div>
			
			<ul class="gal_n0">  
			   <li class="gal_box">			   	
				<h3 class="ub_gal_titre" id="ub_pr_menu" >Thème</h3>	
			  	<ul class="gal_n1">
			  		
			  		<li>		  			
			  			<div class="pr_soustitre" style="">Le contenu du book</div>
			  		</li>			  							  		
					  <li>
							<div class="pr_bloc_slide">
							<div class="pr_label">Gamme de couleur</div>
							<a class="ub_help" data-original-title="Choix de la gamme de couleur" href="#" data-content="Vous pouvez sélectionner ici une couleur pour la partie centrale de votre du book"   ></a>				
		
							<div id="link_css" class="radioSelector">
								<input type="radio" id="r_trans" name="link_css" value="transparent" /><label for="r_trans">Transparent</label>
								<input type="radio" id="r_white" name="link_css" value="white" /><label for="r_white">Blanc</label>
								<input type="radio" id="r_black" name="link_css" value="black" /><label for="r_black">Noir</label>
							</div>
							</div>
					  </li>
					<li>
						<div class="pr_bloc_slide ">
							<div class="pr_label">Couleur de fond du book</div>
							<a class="ub_help" data-original-title="Couleur de fond de la page" href="#" data-content="Vous pouvez sélectionner ici la couleur de fond de la page du book"></a>				
							<input type="hidden" name="ub_ptf_couleur_fond_page" rel="backgroundColor"		class="colorSelector" />
						</div>
						<br clear="all"/>	
					</li>
					  <li>
					 		<div class="pr_bloc_slide">
							<div class="pr_label">Marge du haut</div>	
							<a class="ub_help" data-original-title="Marge du haut" href="#" data-content="Vous pouvez régler ici la marge entre votre bandeau image et le contenu du book"   ></a>		
							<div class="pr_slider slider" id="top_marge_size" title="" rel="value:'10',min:'0',max:'80',step:'1'" ></div>
							<input class="pr_slider_input" type="text" style="color:#f6931f;" />
							<br clear="all"/>
							</div>
					  </li>
					
			  		<li>		  			
			  			<div class="pr_soustitre" style="">Choix du fond de la page</div>
			  		</li>
					  <li>
					 		<div class="pr_bloc_slide">
				
							<a class="ub_help" data-original-title="Choix du fond" href="#" data-content="Vous pouvez ici choisir d'utiliser une image ou un aplat de couleur pour le fond du book
							<br/>Si vous choissisez une image pour le fond, vous pourrez la modifier à partir du menu : Réglage du modèle classique/Personnaliser mon book "></a>
							<div id="ptf_choix_fond" class="radioSelector">
								<input type="radio" id="r_ptffd1" name="ptf_v_aff" value="img"  /><label for="r_ptffd1">Image</label>
								<input type="radio" id="r_ptffd2" name="ptf_v_aff" value="coul" /><label for="r_ptffd2">Couleur</label>
							</div>							
							</div>
					  </li>	
					  
					  <li>
					 		<div class="pr_bloc_slide">					 			
					 			
					 		<div style="position: absolute;margin-left:130px">
							    <div class="pr_label">Image de fond</div>
							    <img src="<?=$b->url_abs_site.$b->cont_bg;?>" style="width:70px;height:22px;">
							</div>
							
							
							<div class="pr_label">Couleur de fond</div>
							<a class="ub_help" data-original-title="Couleur ou image de fond" href="#" data-content="Vous pouvez sélectionner ici la couleur de fond du book<br/>Vous pourrez modifier l'image à partir du menu : Réglage du modèle : classique dans le menu : Personnaliser mon book "   ></a>				
							<input type="hidden" name="ub_couleur_fond" 	rel="backgroundColor" 			class="colorSelector" />
							<br clear="all"/>
							</div>
					  </li>		
						<li>
								<div class="pr_bloc_slide">
								<div class="pr_label">Ombre de la page</div>		
								<div id="pr_ombre_aff" class="radioSelector">
									<input type="radio" id="r_pr_omb1" name="pr_omb_aff" value="sans"  /><label for="r_pr_omb1">Sans</label>
									<input type="radio" id="r_pr_omb2" name="pr_omb_aff" value="page_base_ombre_fonce" /><label for="r_pr_omb2">Foncé</label>
									<input type="radio" id="r_pr_omb3" name="pr_omb_aff" value="page_base_ombre_claire" /><label for="r_pr_omb3">Claire</label>
								</div>
								</div>
						</li>
						
						
			  	</ul>
			  </li>			  
			</ul> 
			
			
			
			<ul class="gal_n0">  
			   <li class="gal_box">
			   	
				<h3 class="ub_gal_titre" id="ub_pr_menu" >Menu</h3>	
			  	<ul class="gal_n1">			   
					
					<li>
						<div class="pr_bloc_slide ">
							<div class="pr_label">Couleur de fond du menu</div>
							<a class="ub_help" data-original-title="Couleur de fond du menu" href="#" data-content="Vous pouvez sélectionner ici la couleur de fond du menu<br/>"   ></a>				
							<input type="hidden" name="ub_menu_coul_fond" rel="backgroundColor"		class="colorSelector" />							
						</div>
					</li>
					
  					  <li>					  
							<div class="pr_bloc_slide <?php /*=$b->us_formule==1?'':'us_formule';*/?>">
							<div class="pr_label">Nom des rubriques
							<a class="ub_help"
							data-original-title="Noms des rubriques" 
							data-content="Les titres des rubriques : <br/>Accueil, portfolios et actualités <br/>Vous pouvez modifier les intitulés à votre convenance ou même ne pas les afficher. Un lien est automatiquement ajouté sur la page d'accueil
							<br/><br/> Vous pouvez utiliser [accueil-blanc] ou [accueil-blanc] pour les icones respectifs blanc/noir du lien accueil"  href="#" ></a>				
							</div>									
							<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value="" 	name="ub_menu_titre_accueil">
							<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value=""	name="ub_menu_titre_ptf">
							<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value=""	name="ub_menu_titre_actu">
							</div>	
							<br clear="all"/>				    
					  </li>
					  
					  <li>					  
							<div class="pr_bloc_slide">
							<div class="pr_label">Menu Titre</div>		
							<a href="#" rel="ub_font_menut" class="plus ui-corner-left"></a>&nbsp;
							<a href="#" rel="ub_font_menut" class="minus ui-corner-right"></a>&nbsp;
							<a href="#" rel='ub_font_menut' class="font_selector ui-corner-all" id='selectH1'>Cabin</a>
							<input type="hidden" name="ub_font_menut" 	rel="color" 		class="colorSelector" />
							</div>	
							<br clear="all"/>				    
					  </li>
  					  <li>					  
							<div class="pr_bloc_slide">
							<div class="pr_label">Menu Rubriques</div>
							<a href="#" rel="ub_font_menu_newsr" class="plus ui-corner-left"></a>&nbsp;
							<a href="#" rel="ub_font_menu_newsr" class="minus ui-corner-right"></a>&nbsp;
							<a href="#" rel='ub_font_menu_newsr' class="font_selector ui-corner-all" id='selectH6'>Cabin</a>
							<input type="hidden" name="ub_font_menu_newsr" 	rel="color" 			class="colorSelector" />
							</div>	
							<br clear="all"/>				    
					  </li>
  					  <li>					  
							<div class="pr_bloc_slide ">
							<div class="pr_label">Menu Pages</div>			
							<a href="#" rel="ub_font_menu_newsp" class="plus ui-corner-left"></a>&nbsp;
							<a href="#" rel="ub_font_menu_newsp" class="minus ui-corner-right"></a>&nbsp;
							<a href="#" rel='ub_font_menu_newsp' class="font_selector ui-corner-all" id='selectH7'>Cabin</a>
							<input type="hidden" name="ub_font_menu_newsp" 	rel="color" 			class="colorSelector" />
							</div>	
							<br clear="all"/>				    
					  </li>
			  	</ul>
			  </li>			  
			</ul>	
			  		
			 <ul class="gal_n0">
			  <li class="gal_box">
				<h3 class="ub_gal_titre" id="ub_pr_accueil">Page accueil</h3>	
			  	<ul class="gal_n1">
			  		
			  		<li>		  			
			  			<div class="pr_soustitre" style="">Vignettes</div>
			  		</li>			  		
			  		<li>
							<div class="pr_bloc_slide">
							<div class="pr_label">Affichage des vignettes portfolios</div>		
							<div id="ptf_vignette_aff" class="radioSelector">
								<input type="radio" id="r_ptfvaff1" name="ptf_vignette_aff" value="true"  /><label for="r_ptfvaff1">Oui</label>
								<input type="radio" id="r_ptfvaff2" name="ptf_vignette_aff" value="false" /><label for="r_ptfvaff2">Non</label>
							</div>
							</div>
					</li>
			  		<li>
							<div class="pr_bloc_slide">
							<div class="pr_label">Survole des vignettes portfolios</div>		
							<div id="ptf_vignette_aff_att" class="radioSelector">								
								<input type="radio" id="r_ptfvaffatt1" name="ptf_vignette_aff_att" value="sans"  /><label for="r_ptfvaffatt1">Sans</label>
								<input type="radio" id="r_ptfvaffatt2" name="ptf_vignette_aff_att" value="white" /><label for="r_ptfvaffatt2">Blanc</label>
								<input type="radio" id="r_ptfvaffatt3" name="ptf_vignette_aff_att" value="black" /><label for="r_ptfvaffatt3">Noir</label>
							</div>
							</div>
					</li>
					
					<li>
							<div class="pr_bloc_slide">
							<div class="pr_label">Ombre des vignettes portfolios</div>		
							<div id="ptf_vignette_ombre" class="radioSelector">
								<input type="radio" id="r_ptfvaffomb1" name="ptf_vignette_omb" value="true"  /><label for="r_ptfvaffomb1">Oui</label>
								<input type="radio" id="r_ptfvaffomb2" name="ptf_vignette_omb" value="false" /><label for="r_ptfvaffomb2">Non</label>
							</div>
							</div>
					</li>
														
					  <li>
					 		<div class="pr_bloc_slide">
							<div class="pr_label">Taille des vignettes images</div>	
							<div class="pr_slider slider" id="accueil_img_size" rel="value:'300',min:'60',max:'550',step:'1'"></div>
							<input class="pr_slider_input" type="text" style="color:#f6931f;" />							
							<br clear="all"/>
							</div>
					  </li>
					  				
					  <li>
					 		<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?>" >
							<div class="pr_label">Taille des marges des vignettes</div>
							<a href="#" class="ub_help" data-original-title="Marges des images"  data-content="Les marges sont placées à gauche et en dessous de chaque image"></a>		
							<div class="pr_slider slider" id="accueil_img_marge_size" title="" rel="value:'10',min:'0',max:'60',step:'1'" ></div>
							<input class="pr_slider_input" type="text" style="color:#f6931f;" />
							<br clear="all"/>
							</div>
					  </li>
					   <li>
							<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?>" >
							<div class="pr_label">Affichage des titres des vignettes</div>
							<a href="#" class="ub_help" data-original-title="Affichage des titres"  data-content="Les titres des portfolios s'affichent au passage de la souris sur les images, vous pouvez désactiver cette fonction" ></a>		
							<div id="titre_aff" class="radioSelector">
								<input type="radio" id="r_titre1" name="titre_aff" value="true"  /><label for="r_titre1">Oui</label>
								<input type="radio" id="r_titre2" name="titre_aff" value="false" /><label for="r_titre2">Non</label>
							</div>
							</div>
					  </li>
				  
			  		<li>		  			
			  			<div class="pr_soustitre" style="">Positionnement des pages d'accueil</div>
			  		</li>
			  		
					  <li>
							<div class="pr_bloc_slide">
							<div class="pr_label">Page affichée sur la page d'accueil en haut</div>
							<a href="#" class="ub_help" data-original-title="Affichage le contenu de la page d'accueil"  
							data-content="Le contenu de la page d'accueil peut s'afficher suivant 2 positions :<br/>
							A/ En haut de la page<br/> B/ En bas <br/>" ></a>		
							<div id="accueil_contenu_aff" >
								<select id="accueil_contenu_aff_a" class="combobox">
										<option value="false">Ne rien afficher</option>
									<?php  foreach ($b->gal_cont_accueil as $cont_accueil) { ?>
										<option value="<?=$cont_accueil['img_id'];?>" <?=($b->accueil_contenu_aff_a==$cont_accueil['img_id'])?'selected="selected"':'';?> ><?=$cont_accueil['img_titre'];?></option>
									<?php }?>
								</select>
 							</div>
							</div>
					  </li>					  
	 				  <li>
							<div class="pr_bloc_slide">
							<div class="pr_label">Page affichée sur la page d'accueil en bas</div>
							<a href="#" class="ub_help" data-original-title="Affichage le contenu de la page d'accueil"  
							data-content="Le contenu de la page d'accueil peut s'afficher suivant 2 positions :<br/>
							A/ En haut de la page<br/> B/ En bas <br/>" ></a>		
							<div id="accueil_contenu_aff" >
								<select id="accueil_contenu_aff_b" class="combobox">
										<option value="false">Ne rien afficher</option>
									<?php  foreach ($b->gal_cont_accueil as $cont_accueil) { ?>
										<option value="<?=$cont_accueil['img_id'];?>" <?=($b->accueil_contenu_aff_b==$cont_accueil['img_id'])?'selected="selected"':'';?> ><?=$cont_accueil['img_titre'];?></option>
									<?php }?>
								</select>
								
								
							</div>
							</div>
							<br/>
					  </li>

			  	</ul>
			  </li>	
			 </ul>
			
			

			
			<ul class="gal_n0">
			  <li class="gal_box">
			  	
				<h3 class="ub_gal_titre" id="ub_pr_portfolio">Page portfolio</h3>	
			  	<ul class="gal_n1">


			  		<li>
							<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?> ">
							<div class="pr_label">Position des vignettes</div>		
							<div id="ptf_position_vign" class="radioSelector">
								<input type="radio" id="r_ptfpos1" name="diapo_aff" value="top"    /><label for="r_ptfpos1">Haut</label>
								<input type="radio" id="r_ptfpos2" name="diapo_aff" value="bottom" /><label for="r_ptfpos2">Bas</label>
								<input type="radio" id="r_ptfpos3" name="diapo_aff" value="right"  /><label for="r_ptfpos3">Droite</label>
								<input type="radio" id="r_ptfpos4" name="diapo_aff" value="left"   /><label for="r_ptfpos4">Gauche</label>
							</div>
							</div>
					</li>
					<!--
					<li>
						<div class="pr_bloc_slide ">
							<div class="pr_label">Couleur de fond des images</div>
							<a class="ub_help" data-original-title="Couleur ou image de fond" href="#" data-content="Vous pouvez sélectionner ici la couleur de fond du book<br/>Vous pourrez modifier l'image à partir du menu : Réglage du modèle : classique dans le menu : Personnaliser mon book "   ></a>				
							<input type="hidden" name="ub_ptf_couleur_fond" rel="backgroundColor"		class="colorSelector" />
						</div>
					</li>
					<li>
						<div class="pr_bloc_slide ">
							<div class="pr_label">Couleur de fond des vignettes</div>
							<a class="ub_help" data-original-title="Couleur ou image de fond" href="#" data-content="Vous pouvez sélectionner ici la couleur de fond du book<br/>Vous pourrez modifier l'image à partir du menu : Réglage du modèle : classique dans le menu : Personnaliser mon book "   ></a>				
							<input type="hidden" name="ub_ptf_vigncouleur_fond" rel="backgroundColor"		class="colorSelector" />
						</div>
					</li>
					-->
			  		<li>
							<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?> ">
							<div class="pr_label">Type de vignettes</div>		
							<div id="ptf_type_vign" class="radioSelector">
								<input type="radio" id="r_ptftv1" name="ptfv" value="thumbs"/><label for="r_ptftv1">Images</label>
								<input type="radio" id="r_ptftv2" name="ptfv" value="dots"  /><label for="r_ptftv2">Points</label>
								<input type="radio" id="r_ptftv3" name="ptfv" value="none"  /><label for="r_ptftv3">Sans</label>
							</div>
							</div>
					</li>
					
			  		<li>
							<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?>">
							<div class="pr_label">Affichage des titres sous les images</div>		
							<div id="ptf_titre_aff" class="radioSelector">
								<input type="radio" id="r_ptft1" name="ptf_titre_aff" value="true"  /><label for="r_ptft1">Oui</label>
								<input type="radio" id="r_ptft2" name="ptf_titre_aff" value="false" /><label for="r_ptft2">Non</label>
							</div>
							</div>
					 </li>			  	
					<!--  	
				    <li>					  
							<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?>">
							<div class="pr_label">Typo titre image</div>							
							<a href="#" rel="fotorama__caption" class="plus ui-corner-left"></a>&nbsp;
							<a href="#" rel="fotorama__caption" class="minus ui-corner-right"></a>&nbsp;
							<a href="#" rel='fotorama__caption' class="font_selector ui-corner-all" id='selectH2'>Cabin</a>
							<input type="hidden" name="fotorama__caption" class="colorSelector"	rel="color"/>
							</div>	
							<br clear="all"/>				    
					  </li>					  
					 --> 

			  	</ul>
			  </li>	
			 </ul>	 
			 
			 <ul class="gal_n0">
			  <li class="gal_box">			  	
				<h3 class="ub_gal_titre" id="ub_pr_portfolio">Expert Css</h3>	
			  	<ul class="gal_n1">			  							
			  		 <li>					  
							<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?>">
							<div class="pr_label">Modifier les styles Css
							<a class="ub_help"
							data-original-title="Modifier les styles Css" 
							data-content="Vous pouvez placer ici vos propres feuilles de styles. Pensez à valider vos Css en cliquant sur la flèche Maj (juste à droite)<br/><br/>Attention : Nous ne faisons pas de support pour les modifications de Css."  href="#" ></a>				
							</div>									
							<textarea class="text ui-widget-content ui-corner-all pr_form_txtarea" rows="3" cols="28" name="ub_css_hack"></textarea> 
							</div>	
							<br clear="all"/>				    
					  </li>
			  	</ul>
			  </li>	
			 </ul>	
					
	</div>


	<button class="ub_form_input btn" href="#" id="save">Enregistrer</button>

	</div>
	</div>
</div>	
<!-- reglage de la page fin -->

		

