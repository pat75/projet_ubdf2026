{{-- Porte depuis 2011_html_pages_v2/zoom2016/_ultrabook__barredereglage.tlp.php (_outils/porter_gabarits.py) --}}
<!-- reglage de la page debut  2015 -->
	
<!-- js ub_book_core_pr_2012 barre de reglage +translate -->

<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_web/ubdf_barrereglage_translate.tlp.php"></script>
<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_web/<?=$b->url_mdl;?>/js/ub_book_core_pr_2012.js"></script>





<script type="text/javascript">
/* barre de réglage */
/* conf */
var url_ajax_usadmin_pr = "<?=$b->url_abs_site;?>/front/ajax_2012_usadmin_pr.php";
var url_ajax_usadmin_img= "<?=$b->url_abs_site;?>/front/ajax_2010.php";
var url_ajax_usadmin_editxt = "<?=$b->url_abs_site;?>/front/ajax_2014_usadmin_editxt.php"; 


var gallery1;

var data_us_key = ''; <?php  /*=md5($b->us_dir.'passub2012'); obtenu par retour ajax*/?>

var br_admin = true;


// df language
//
var ub_pr_lang = {

	pr_msg_fontion_desactive : '<?=__('Fonctionnalitée désactivée pour la formule gratuite')?>'
	,msg_reglage_save_afaire : '<div id="bouton_validation" class="ui-state-highlight ui-corner-all" ' +
	'style="margin-left:10px;float:right;width:160px;padding: 5px 8px;"><span class="ui-icon ui-icon-alert" ' +
	'style="float: left; margin-right: .3em;"></span><?=__("Pas enregistré...")?></div>'

	,txt_supprimer : 				'<?=__("Supprimer ?")?>'
	,txt_contenuefface : 		'<?=__("Le contenu sera effacé")?>'
	,txt_annuler : 				'<?=__("Annuler")?>'
	,txt_supprimer_ok  : 		'<?=__("Supprimer !")?>'
	,txt_plusconnecte  : 		'<?=__("Vous n’etes plus connecte !")?>'
	,txt_reconnection  : 		'<?=__("Vous devez vous reconnecter pour accéder aux réglages...")?>'
	,txt_version_nav	: 			'<?=__("Votre version de navigateur ne prends pas en compte l’administration des portfolios, vous devez utilisez la derniéres version de Firefox, Chrome ou Safari)")?>'
	,txt_error_reconnecter : 	'<?=__("Erreur, reconnectez-vous !")?>'
	,txt_bt_sup_img : 			'<?=__("Supprimer le visuel")?>'
	,txt_bt_add_img: 				'<?=__("Ajouter un bloc image")?>'
	,txt_bt_sup_cont: 			'<?=__("Supprimer le contenu")?>'
	,txt_bt_add_cont: 			'<?=__("Ajouter un bloc de contenu")?>'
	,txt_bt_edit_cont: 			'<?=__("Editer")?>'
	,txt_bt_lien: 					'<?=__("Lien de l’image")?>'
	,txt_menu_desactive:			'<?=__("Menu désactiver sur la prévisualisation" )?>'

}



//
// fin init

$(document).ready(function(){ 


	// br decalage
	$('#page_socle').addClass('expanded');	
	// br bouton de fermeture de la br
	$('.full-overlay-main').prepend('<a class="control-panel collapse-sidebar" id="barrer2014_r"><i class="icon-chevron_right icon2x"></i></a>');
	
	// aide
	$('.aide_ptf').show();	
	
	
	
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
            console.log("us_dir:'<?=$b->us_dir;?>' ");
            console.log(jsondata);
	         
	         if ( ub_pr_data.connection_test(jsondata) ) {
	
				// barre de reglage
				//
	         	// $('#pr_titre').append(' <strong>'+ jsondata.us_login +'</strong>')
	         	
	         	// key par retour ajax
	         	data_us_key = jsondata.us_key;
	         	
	         	//console.log(jsondata)

					// lang
					$.extend( ub_pr, ub_pr_lang );

					// init
					ub_pr.init();


			} else {
				
				// plus connecte !
				//				
				ub_pr.plusconnecte();
				
			}			
			
	     },
	     error:function(){
	         alert("<?=__('Erreur - vous devez vous re-reconnecter...');?>");
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
							<i class="icon-cross_mark icon15x "></i>

							</a>
						</div>
					</div>


					<div class="section-header">						
						<a class="control-panel collapse-sidebar"><i class="icon-chevron_left icon2x "></i></a>
						<a class="control-panel mobile_view"></a>

						<h3 class="section-header-title"><?=__('Modèle Zoom')?></h3>

							<div class="section-header-action">
							<button id="br_save" class="ub_form_input btn" href="#" ><?=__('Enregistrer')?></button>
							<div id="br_msg_waite" class="br_msg"><img src="<?=$b->url_abs_site?>/img_front/loader2014.gif"></div>
							<div id="br_msg_info" class="br_msg"><i class="icon-check_circle icon2x"></i></div>
							<div id="br_msg_error" class="br_msg"><i class="icon-warning icon2x"></i></div>
							</div>
					</div>
						
					<ul>
				
						<!-- themes -->
						<li class="section ">
							<h3 class="section-title">
								<?=__('Thème')?>
							</h3>							
							<ul class="section-content">
									<li class="customize-control">
										<label>
											<strong><?=__('Choix des couleurs')?></strong>
											<a class="ub_help" data-original-title="<?=__('Choix du fond')?>" href="#"
											data-content="<?=__('Vous pouvez modifier la couleur de fond du bandeau du haut et du fond de page.<br/>Les couleurs des menus seront automatiquement ajustées suivant ces nouvelles couleurs.')?>"></a>
										</label>
										<div class="ub_fm_desactive"><?=$b->us_formule==0?__('Désactivée pour les formules gratuites<br/>'):'';?></div>
															
										<div id="us_pf_nav_coul" class="<?=$b->us_formule==1?'':'us_formule';?>">
											<input type="hidden" name="ub_couleur_nav" rel="color" class="colorSelector" />
											<div class="pr_label"><?=__('Couleur bandeau du haut')?></div>
										</div>
																		
										<div id="us_pf_bg_coul" class="<?=$b->us_formule==1?'':'us_formule';?>" >
											<input  type="hidden" name="ub_couleur_fond" rel="backgroundColor" class="colorSelector" />
											<div class="pr_label "><?=__('Couleur de fond')?></div>
										</div>
											
									</li>
									<li class="ombre"></li>
							</ul>
						</li>
						
						<!-- Menu -->
						<li class="section ">
							<h3 class="section-title">
								<?=__('Menu de navigation')?>
							</h3>							
							<ul class="section-content">
									<li class="customize-control">
										<label>
											<span class="customize-control-title"><?=__('Titre des menus')?></span>
											<a class="ub_help"
												data-original-title="<?=__('Noms des rubriques')?>"
												data-content="<?=__('Les titres des rubriques : <br/>Accueil, portfolio et actualités <br/>Vous pouvez modifier les intitulés à votre convenance ou même ne pas les afficher. Un lien est automatiquement ajouté sur la page d’accueil<br/><br/> Vous pouvez utiliser [accueil-blanc] ou [accueil-noir] pour afficher les icônes.')?>" href="#" ></a>
										</label>
									</li>
									
									<li class="customize-control">
										<!--<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value="" 	name="ub_menu_titre_accueil">-->
										<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value=""	name="link_accueil">
										<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value=""	name="link_bio">
										<input class="text ui-widget-content ui-corner-all pr_form_txt" type="text" value=""	name="link_contact">
										<br clear="all"/>
									</li>	

					  						<li class="ombre"></li>			
							</ul>
						</li>
						
						
						<!-- Plug-in -->
						<li class="section ">
							<h3 class="section-title"><?=__('Plug-in')?></h3>
							
							<ul class="section-content">
																											
									
									<li class="customize-control">
									<label>
									<span class="customize-control-title"><?=__('Partage réseaux sociaux')?></span>
										<a class="ub_help"
										data-original-title="<?=__('Haut de page à droite - Partage réseaux sociaux')?>"
										data-content="<?=__('Vous pouvez afficher les liens de partage sur les réseaux sociaux dans le bandeau du haut à droite.')?>"  href="#" >
										</a>	
									</label>							
									</li>
									
									<li class="customize-control">									
										<div id="ptf_activer_sociaux" class="radioSelector">
											<input type="radio" id="r_ptf_sociaux1" name="ptf_sociaux_actif" value="true"   /><label for="r_ptf_sociaux1"><?=__('Activer')?></label>
											<input type="radio" id="r_ptf_sociaux2" name="ptf_sociaux_actif" value="false"  /><label for="r_ptf_sociaux2"><?=__('Désactiver')?></label>
										</div>											
									</li>
									
									
									
									<li class="customize-control">
										<label>
										<span class="customize-control-title"><?=__('Formulaire de  contact')?></span>
										<a class="ub_help"
										data-original-title="<?=__('Page contact - Formulaire de  contact')?>"
										data-content="<?=__('Vous pouvez afficher un formulaire de contact dans la colonne de droite, les courriels arriveront sur votre boite mail.')?>"  href="#" ></a>
										</label>
									</li>
									<li class="customize-control">									
										<div id="ptf_activer_contact" class="radioSelector">
											<input type="radio" id="r_ptf_contact1" name="ptf_contact_actif" value="true"   /><label for="r_ptf_contact1"><?=__('Activer')?></label>
											<input type="radio" id="r_ptf_contact2" name="ptf_contact_actif" value="false"  /><label for="r_ptf_contact2"><?=__('Désactiver')?></label>
										</div>											
									</li>
																							
								
									<li class="customize-control">
									<label>
									<span class="customize-control-title"><?=__('Carte GoogleMap')?></span>
										<a class="ub_help"
										data-original-title="<?=__('Page contact - Carte GoogleMap')?>"
										data-content="<?=__('Vous pouvez afficher une mini carte GoogleMap dans la colonne de gauche.')?>"  href="#" >
										</a>	
									</label>
									<div class="ub_fm_desactive"><?=$b->us_formule==0?__('Désactivée pour les formules gratuites<br/>'):'';?></div>
									</li>
									
									<li class="customize-control <?=$b->us_formule==1?'':'us_formule';?>">									
										<div id="ptf_activer_gmap" class="radioSelector">
											<input type="radio" id="r_ptf_gmap1" name="ptf_gmap_actif" value="true"   /><label for="r_ptf_gmap1"><?=__('Activer')?></label>
											<input type="radio" id="r_ptf_gmap2" name="ptf_gmap_actif" value="false"  /><label for="r_ptf_gmap2"><?=__('Désactiver')?></label>
										</div>											
									</li>



								<!-- iso_cat -->
								<li class="customize-control">
									<label>
										<span class="customize-control-title"><?=__('Présentation structurée')?></span>
										<a class="ub_help"
											data-original-title="<?=__('Organisation par dossier image')?>"
											data-content="<?=__('Vous pouvez présenter vos visuels groupés par dossiers. Ou les présenter tous ensemble')?>"  href="#" >
										</a>
									</label>
								</li>

								<li class="customize-control">
									<div id="ptf_activer_iso_category" class="radioSelector">
										<input type="radio" id="r_ptf_iso_cat1" name="ptf_iso_cat_actif" value="true"   /><label for="r_ptf_iso_cat1"><?=__('Activer')?></label>
										<input type="radio" id="r_ptf_iso_cat2" name="ptf_iso_cat_actif" value="false"  /><label for="r_ptf_iso_cat2"><?=__('Désactiver')?></label>
									</div>
								</li>
								<!-- iso_cat =end -->


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
							<h3 class="section-title"><?=__('Expert Css')?></h3>
							
							<ul class="section-content">
									<li class="customize-control">
									<label>
										<span class="customize-control-title"><?=__('Modifier les styles Css')?></span>
										<a class="ub_help"
										data-original-title="<?=__('Modifier les styles Css')?>"
										data-content="<?=__('Vous pouvez placer ici vos propres feuilles de styles. Pensez à valider vos Css en cliquant sur la flèche Maj (juste à droite)<br/><br/>Attention : Nous ne faisons pas de support pour les modifications de Css.')?>"  href="#" ></a>
										
									</label>
									<div class="ub_fm_desactive"><?=$b->us_formule==0?__('Désactivée pour les formules gratuites<br/>'):'';?></div>
									</li>
									<li class="customize-control group">																			
										<textarea class="text ui-widget-content ui-corner-all pr_form_txtarea <?=$b->us_formule==1?'':'us_formule';?>" rows="3" cols="28" name="ub_css_hack"></textarea> 
										
									</li>
									<li class="ombre"></li>
							</ul>
						</li>

						
					</ul>
					
								
					
					
	</div>
	
	
	
