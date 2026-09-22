{{-- Porte depuis 2011_html_pages_v2/grid2015/_ultrabook_2015__barredereglage.tlp.php (_outils/porter_gabarits.py) --}}
<!-- reglage de la page debut  2015 -->
	
<!-- js ub_book_core_pr_2012 barre de reglage -->

<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js/ub_book_core_pr_2012.js"></script>


<script type="text/javascript">
/* barre de réglage */
/* conf */
var url_ajax_usadmin_pr = "<?=$b->url_abs_site;?>/front/ajax_2012_usadmin_pr.php";
var url_ajax_usadmin_img= "<?=$b->url_abs_site;?>/front/ajax_2010.php";
var url_ajax_usadmin_editxt = "<?=$b->url_abs_site;?>/front/ajax_2014_usadmin_editxt.php"; 


var gallery1;

var data_us_key = ''; <?php //=md5($b->us_dir.'passub2012'); obtenu par retour ajax?>

var br_admin = true;


//
// fin init

$(document).ready(function(){ 


	// br decalage
	$('#page_socle').addClass('expanded');	
	// br bouton de fermeture de la br
	$('.full-overlay-main').prepend('<a class="control-panel collapse-sidebar" id="barrer2014_r"><i class="icon-chevron_right icon2x"></i></a>');
	
		
	
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
	         	// $('#pr_titre').append(' <strong>'+ jsondata.us_login +'</strong>')
	         	
	         	// key par retour ajax
	         	data_us_key = jsondata.us_key;
	         	//console.log('key  '+jsondata.us_key)
	         	
	         		         	
	         	ub_pr.init();


			} else {
				
				// plus connecte !
				//				
				ub_pr.plusconnecte();
				
			}			
			
	     },
	     error:function(){
	         alert("Erreur - vous devez vous re-deconnecter...");
	     }
	     
		});


});
</script>






<div id="barrer2014" >

					<div class="section-header titre">
						<h3>
							<a href="<?=$b->url_abs_site?>/ubaction__user_pref_form">
								<img src="<?=$b->url_abs_site;?>/img_front/ub_logo_mobile.svg" alt="Ultra-book"><?=__('Modifier l’apparence')?>
							</a>
						</h3>
						<div class="fermer">
							<a href="<?=$b->url_abs_site?>/ubaction__user_pref_form">
								<i class="icon-cross_mark icon15x"></i>
							</a>
						</div>
					</div>

					<div class="section-header">						
							<a class="control-panel collapse-sidebar"><i class="icon-chevron_left icon2x"></i></a>
							<h3 class="section-header-title"><?=__('Modèle Grid')?></h3>

							<div class="section-header-action">
							<button id="br_save" class="ub_form_input btn" href="#" >Enregistrer</button>	
							<div id="br_msg_waite" class="br_msg"><img src="<?=$b->url_abs_site?>/img_front/loader2014.gif"></div>
							<div id="br_msg_info" class="br_msg"><i class="icon-check_circle icon2x"></i></div>	
							<div id="br_msg_error" class="br_msg"><i class="icon-warning icon2x"></i></div>
							</div>
					</div>
						
					<ul>
						<?php /*
						<!-- Titre -->
						<li class="section ">
							<h3 class="section-title">
								Titre de site et description								
							</h3>
							
							<ul class="section-content">
									<li class="customize-control">
										<label>
											<span class="customize-control-title">Titre du book</span>
											<input type="text" data-customize-setting-link="blogname" value="">
										</label>
									</li>
									<li class="ombre"></li>
							</ul>
						</li>
						*/?>
						<!-- themes -->
						<li class="section ">
							<h3 class="section-title">
								Thème						
							</h3>							
							<ul class="section-content">
									<li class="customize-control">
										
										Choix du fond de la page
											<a class="ub_help" data-original-title="Choix du fond" href="#" data-content="Vous pouvez ici choisir d'utiliser une image ou un aplat de couleur pour le fond du book
											<br/>Si vous choissisez une image pour le fond, vous pourrez la modifier à partir du menu : Réglage du modèle classique/Personnaliser mon book "></a>
											<div id="ptf_choix_fond" class="radioSelector">
												<input type="radio" id="r_ptffd2" name="ptf_v_aff" value="coul" /><label for="r_ptffd2">Couleur</label>
												<input type="radio" id="r_ptffd1" name="ptf_v_aff" value="img"  /><label for="r_ptffd1">Image</label>
											</div>
									
																				
										<div id="br_form_ajax-us_pf_bg" class="img_file_ajax_FineUp" data-fileapi_id="us_pf_bg" data-fileapi_deleted="false" data-fileapi_callback="ub_pr.us_pf_bg_change" original-title="Charger le visuel de fond ici">
										    <div class="pr_label">Image de fond</div>
										    <img src="<?=$b->url_abs_site.$b->cont_bg;?>" class="img_file_modify" style="width:140px;height:80px;">
										</div>
										
										<div id="us_pf_bg_coul" >
											<div class="pr_label">Couleur de fond</div>
											<a class="ub_help" data-original-title="Couleur ou image de fond" href="#" data-content="Vous pouvez sélectionner ici la couleur de fond du book"></a>				
											<input type="hidden" name="ub_couleur_fond" 	rel="backgroundColor" 			class="colorSelector" />
										</div>
										
										
										<div id="us_pf_nav_coul" >
											<div class="pr_label">Couleur des icones navigation</div>
											<a class="ub_help" data-original-title="Couleur des éléments de navigation" href="#" data-content="Vous pouvez sélectionner ici la couleur des élemtns de navigation"></a>				
											<input type="hidden" name="ub_couleur_nav" 	rel="color" 			class="colorSelector" />
										</div>
											
										<br clear="all"/>
										
										</li>
										
										
									<li class="ombre"></li>
							</ul>
						</li>
						
						<!-- Menu -->
						<li class="section ">
							<h3 class="section-title">Menu de navigation</h3>
							
							<ul class="section-content">
									<li class="customize-control">
										<label>
											<span class="customize-control-title">Titre des menus</span>
											<a class="ub_help"
								data-original-title="Noms des rubriques" 
								data-content="Les titres des rubriques : <br/>Accueil, portfolios et actualités <br/>Vous pouvez modifier les intitulés à votre convenance ou même ne pas les afficher. Un lien est automatiquement ajouté sur la page d'accueil
								<br/><br/> Vous pouvez utiliser [accueil-blanc] ou [accueil-noir] pour les icones respectifs blanc/noir du lien accueil"  href="#" ></a>						
							
										</label>
									</li>
									
									<li class="customize-control">
										<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value="" 	name="ub_menu_titre_accueil">
										<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value=""	name="ub_menu_titre_ptf">
										<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value=""	name="ub_menu_titre_actu">
									</li>	
									
									
									<li class="customize-control">
										<label>
											<span class="customize-control-title">Choix typographie et couleur</span>
										</label>
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
										
										<?php /*
										 <li>
												<div class="pr_bloc_slide">
												<div class="pr_label">Contenu à afficher sous le menu</div>
												<a href="#" class="ub_help" data-original-title="Afficher le contenu d'une page d'accueil"  
												data-content="Vous pouvez ici sélectionner une page dans la rubrique Accueil. Celle-ci apparaitra sous le menu" ></a>		
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
										 */?>
					  						<li class="ombre"></li>			
							</ul>
						</li>
						
						
						<?php /*
						<!-- page d'Accueil -->
						<li class="section ">
							<h3 class="section-title">Page accueil<span class="link-button" id="pr_link_accueil"></span></h3>
							
							<ul class="section-content">								
								
								
								
								<li class="customize-control">
										<label>
											<span class="customize-control-title">Positionnement</span>
										</label>
										<div class="ub_fm_desactive"><?=$b->us_formule==0?'Désactivée pour les formules gratuites<br/>':'';?></div>
								</li>	
								<li>
								 		<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?>" >
										<div class="pr_label">Marges du visuel haut/bas</div>
										<a href="#" class="ub_help" data-original-title="Marges du visuel"  data-content="Les marges sont placées en haut et en bas du visuel"></a>		
										<div class="pr_slider slider" id="accueil_img_marge_size" title="" rel="value:'0',min:'0',max:'160',step:'1'" ></div>
										<input class="pr_slider_input" type="text" style="color:#f6931f;" />
										<br clear="all"/>									
										</div>
										
								</li>
					  
								
								<li class="customize-control">
										<label>
											<span class="customize-control-title">Positionnement des pages d'accueil</span>
										</label>
								</li>									
								<li class="customize-control">
										<div class="pr_bloc_slide">
										<div class="pr_label">Contenu à afficher en haut de la page</div>
										<a href="#" class="ub_help" data-original-title="Affichage le contenu d'une page d'accueil"  
										data-content="Le contenu d'une page (de la rubrique Accueil) la peut s'afficher suivant 2 positions :<br/>
										A/ En haut de la page<br/> B/ En bas <br/>" ></a>		
										<div id="accueil_contenu_aff" >
											<select id="accueil_contenu_aff_c" class="combobox">
													<option value="false">Ne rien afficher</option>
												<?php  foreach ($b->gal_cont_accueil as $cont_accueil) { ?>
													<option value="<?=$cont_accueil['img_id'];?>" <?=($b->accueil_contenu_aff_c==$cont_accueil['img_id'])?'selected="selected"':'';?> ><?=$cont_accueil['img_titre'];?></option>
												<?php }?>
											</select>
			 							</div>
										</div>
								  </li>					  
				 				  <li class="customize-control">
										<div class="pr_bloc_slide">
										<div class="pr_label">Contenu à afficher en bas de la page</div>
										<a href="#" class="ub_help" data-original-title="Affichage le contenu d'une page d'accueil"  
										data-content="Le contenu d'une page (de la rubrique Accueil) la peut s'afficher suivant 2 positions :<br/>
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
								  </li>
								  <li class="ombre"></li>
							</ul>
						</li>
						*/?>
						
						
					
						<?php /*
						<!-- page portfolio -->
						<li class="section ">
						<h3 class="section-title">Page portfolio<span class="link-button" id="pr_link_portfolio"></span></h3>
						
						<ul class="section-content">
							
							<li class="customize-control">
									<label>
										<span class="customize-control-title">Présentation des galeries</span>
									</label>
							</li>
					  		<li class="customize-control">
									<div class="pr_bloc_slide">	
									<div id="ptf_type_presentation" class="radioSelector">
										<input type="radio" id="r_ptf_type_1" name="ptftype" value="slide"/><label for="r_ptf_type_1">Galerie</label>
										<input type="radio" id="r_ptf_type_2" name="ptftype" value="image"/><label for="r_ptf_type_2">Images</label>
										<input type="radio" id="r_ptf_type_3" name="ptftype" value="full" /><label for="r_ptf_type_3">Plein écran</label>
									</div>
									</div>
							</li>							
							<li class="customize-control">
									<label>
										<span class="customize-control-title">Position des vignettes</span>
									</label>
							</li>
					  		<li class="customize-control">
									<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?> ">
									<div id="ptf_position_vign" class="radioSelector">
										<input type="radio" id="r_ptfpos1" name="diapo_aff" value="top"    /><label for="r_ptfpos1">Haut</label>
										<input type="radio" id="r_ptfpos2" name="diapo_aff" value="bottom" /><label for="r_ptfpos2">Bas</label>
									</div>
									</div>
							</li>
							
							<li class="customize-control">
									<label>
										<span class="customize-control-title">Taille des vignettes</span>
									</label>
									<div class="ub_fm_desactive"><?=$b->us_formule==0?'Désactivée pour les formules gratuites<br/>':'';?></div>
							</li>
					  		<li class="customize-control">
									<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?> ">
									<div id="ptf_type_vign" class="radioSelector">
										<input type="radio" id="r_ptftv1" name="ptfv" value="petite"/><label for="r_ptftv1">Petites</label>
										<input type="radio" id="r_ptftv2" name="ptfv" value="moyenne"/><label for="r_ptftv2">Moyennes</label>
										<input type="radio" id="r_ptftv3" name="ptfv" value="grande"/><label for="r_ptftv3">Grandes</label>
									</div>
									</div>
									
							</li>
							<li class="customize-control">
									<label>
										<span class="customize-control-title">Affichage des titres sous les images</span>
									</label>
									<div class="ub_fm_desactive"><?=$b->us_formule==0?'Désactivée pour les formules gratuites<br/>':'';?></div>
							</li>
					  		<li class="customize-control">
					  			
									<div class="pr_bloc_slide <?=$b->us_formule==1?'':'us_formule';?> ">
									<a href="#" class="ub_help" data-original-title="Remarque"  
									data-content="Les legendes s'affichent uniquement pour les présentations : Galerie et Plein écran" ></a>			
									<div id="ptf_titre_aff" class="radioSelector">
										<input type="radio" id="r_ptft1" name="ptf_titre_aff" value="true"  /><label for="r_ptft1">Oui</label>
										<input type="radio" id="r_ptft2" name="ptf_titre_aff" value="false" /><label for="r_ptft2">Non</label>
									</div>
									</div>
									
							 </li>
							 <li class="ombre"></li>			  	
					</ul>
					</li>
					*/?>
					
					
					
					<li class="section">
							<h3 class="section-title">Expert Css</h3>
							
							<ul class="section-content">
									<li class="customize-control">
									<label>
										<span class="customize-control-title">Modifier les styles Css</span>
									</label>
									<div class="ub_fm_desactive"><?=$b->us_formule==0?'Désactivée pour les formules gratuites<br/>':'';?></div>
									</li>
									<li class="customize-control">
									<div class="pr_bloc_slide">
									<a class="ub_help"
									data-original-title="Modifier les styles Css" 
									data-content="Vous pouvez placer ici vos propres feuilles de styles. Pensez à valider vos Css en cliquant sur la flèche Maj (juste à droite)<br/><br/>Attention : Nous ne faisons pas de support pour les modifications de Css."  href="#" ></a>				
									</div>									
									<textarea class="text ui-widget-content ui-corner-all pr_form_txtarea <?=$b->us_formule==1?'':'us_formule';?>" rows="3" cols="28" name="ub_css_hack"></textarea> 
									
									</li>
									<li class="ombre"></li>
							</ul>
						</li>
						
						
						
						
						
					<?php /*		
					<h3 class="ub_gal_titre" id="ub_pr_portfolio"</h3>	
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
				
					 <!--
							<h3 class="section-title">
														
							</h3>
							
							<ul class="section-content">
									<li class="customize-control">
										
									</li>
									
							</ul>
						</li>
						-->
					
					

					
					

					
					
					
					

						<li class="section ">
							<h3 class="section-title">
								Version responsive							
							</h3>
							
							<ul class="section-content">
									<li class="customize-control" id="action_responsive">
										<i class="icon-display_screen icon2x" id="action_desktop"></i>
										<i class="icon-smartphone icon2x off" id="action_mobile"></i>
									</li>
									
							</ul>
						</li>
						
						
						<!--
						<li class="section ">
							<h3 class="section-title">
								Titre de site et description								
							</h3>
							<ul class="section-content">
									<li class="customize-control">
										<label>
											<span class="customize-control-title">Titre du book</span>
											<input type="text" data-customize-setting-link="blogname" value="">
										</label>
									</li>
									<li class="customize-control">
										<label>
											<span class="customize-control-title">Slogan</span>
											<input type="text" data-customize-setting-link="blogdescription" value="Un site utilisant WordPress">
										</label>
									</li>
									<li class="customize-control">
										<label>
											<input type="checkbox" data-customize-setting-link="header_textcolor" value="fff">
											Afficher le titre du site et son slogan									
										</label>
									</li>
							</ul>
						</li>
						-->
						*/?>
						
					</ul>
					
								
					
					
	</div>
	
	
	
