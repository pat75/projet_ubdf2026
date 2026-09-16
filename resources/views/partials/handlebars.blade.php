{{-- Templates Handlebars du front 2018, repris tels quels.
     Ils sont consommes par js2019/js_core_cards.js : toute modification
     doit rester synchronisee avec le contrat JSON de FrontApiController. --}}

@verbatim
<script id="tpl_bloc_modal_content_ajax_intermediate_header_creatif" type="text/x-handlebars">

    <h2 class="ui icon header">
		<img src="{{book_info_recup_img_vign}}" class="ui circular image">
		<div class="content">
			{{this.user_detail.book_prenom_nom}}
			<!--<div class="sub header">{{this.user_detail.book_type}}<span>{{this.user_detail.book_statut}}</span></div>-->
		</div>
	</h2>

	{{#if this.user_detail.book_ville}}
	<h6 class="ui center aligned header localisation">
		<div>
			<i class="map marker alternate icon"></i>
			{{this.user_detail.book_pays}}
			<span class="ville">{{this.user_detail.book_ville}}</span>
		</div>
	</h6>
	{{/if}}

	<p>
		{{this.user_legende}}
	</p>

	<p>
	<div class="ui left labeled mini button disponible">
		{{#is this.user_detail.book_dispo 'true'}}
		<a class="ui olive right pointing label">
			<i class="coffee icon "></i>
		</a>
		<div class="ui button">
	Disponible    </div>
	{{/is}}
	{{#is this.user_detail.book_dispo 'false'}}
	<a class="ui orange right pointing label">
		<i class="plane icon "></i>
	</a>
	<div class="ui button">
	Indisponible    </div>
	{{/is}}
</div>
</p>

{{#is this.type_demande 'work_A_contact'}}
{{else}}
<p>
	<img class="ui centered medium image" src="{{ book_visuel_selection }}">
</p>
{{/is}}

</script>

<script id="tpl_bloc_modal_content_ajax_intermediate_form_creatif" type="text/x-handlebars">

    <!--  intermediate form  response error -->
	<div class="ui segment basic space1 hidden" id="segment_intermediate_reponse_error">
	Nous avons rencontré une ou plusieurs erreurs dans le formulaire    <p class="error_list"></p>

	<div class="btn_back_form"><i class="angle left icon "></i>Retour</div>
	</div>

	<!--  intermediate form  response-->
	<div class="ui segment basic space1 hidden" id="segment_intermediate_reponse">
		<i class="check icon"></i>
		<div class="ui header">Merci</div>
	Votre demande vient d’être envoyée    </div>

	<!--  intermediate form -->
	<div class="ui segment basic space1 " id="segment_intermediate_form">

		<div class="ui left aligned basic small segment">

			<form id="intermediate_form" class="ui form">

				<input type="hidden" name="action" value="{{ this.type_demande }}">
				<input type="hidden" name="mf_request_detail" value="{{ this.mf_request_detail }}">
				<input type="hidden" name="us_dir" value="{{ this.user_id }}" >
				<input type="hidden" name="us_key" value="{{ this.user_detail.book_key }}" >
				<input type="hidden" name="g-recaptcha-response">
				<input type="hidden" name="us_book_visuel" value="{{ this.book_visuel_selection }}" >

				{{#is this.type_demande 'work_B_similary'}}
				<div class="ui header">
					<i class="clone icon "></i>Je voudrais commander un travail similaire    </div>

	<div class="sub_title">
		<a class="ui teal image label"><i class="info circle icon "></i><div class="detail">Comment bien formuler ma demande ?</div></a>
				</div>


                <div class="sub_title_info ui pointing label">
	Pour obtenir un devis précis, indiqué si vous êtes une société, son activité et le public visé.<br/>
Présenté votre projet, ce que vous attendez précisément du travail demandé.<br/>
Le type de rémunération que vous proposez (au forfait, au temps passé) Les délais et les modalités de livraison.<br/>
S’il y a lieu pour calculer les droits d’auteur, le type de diffusion (numérique, print...), la durée et la territorialité.    </div>

	{{/is}}


	{{#is this.type_demande 'work_C_buy'}}
	<div class="ui header">
		<i class="shopping bag icon "></i>
	Je voudrais acheter cette image    </div>


	<div class="sub_title">
		<a class="ui teal image label"><i class="info circle icon "></i><div class="detail">Comment bien formuler ma demande ?</div></a>
				</div>
                <div class="sub_title_info ui pointing label">
	Pour obtenir un devis précis, indiqué si vous êtes une société, son activité, et le public visé.
                    Présenté votre projet, ce que vous attentez précisément du travail demandé. Le type de rémunération que vous proposez (au forfait, au temps passé) Les délais et les modalités de livraison.
                    S’il y a lieu pour calculer les droits d’auteur, le type de diffusion (numérique, print...), la durée et la territorialité.    </div>

	{{/is}}

	{{#is this.type_demande 'work_A_contact'}}
	<div class="ui header">
		<i class="shopping bag icon "></i>
	Je souhaite vous contacter    </div>
	{{/is}}



	<div class="grouped fields">
		<div class="ui  field">
			<div class="ui input">
				<textarea rows="5" name="us_message"
						  placeholder="{{#is this.type_demande 'work_A_contact'}}Mon message{{else}}Mes indications pour le devis{{/is}}"></textarea>
						</div>
					</div>
				</div>

				<span class="sub_title">
	Contact    </span>

	<div class="grouped fields">
		<div class="ui  field">
			<div class="ui input">
				<input type="text" name="us_nom_prenom"
					   placeholder="Prénom, nom">
						</div>
					</div>
				</div>


				<div class="grouped fields">
					<div class="ui  field">
						<div class="ui left icon input">
							<input type="text" name="us_mail"  value=""
							       placeholder="Indiquez votre mail">
							<i class="mail icon"></i>
						</div>
					</div>
				</div>

				<!-- CAPTCHA -->
				<div class="grouped fields" style="margin-top:10px;">
					<div class="ui field">
						<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
							<img id="captcha_img" src="/captcha_img?1789541606" alt="captcha" style="height:46px;border:1px solid #ddd;border-radius:3px;cursor:pointer;" title="Cliquez pour changer">
							<div class="ui input" style="width:130px;">
								<input type="text" name="captcha_answer" id="captcha_answer_input" maxlength="6"
								       placeholder="Recopiez"
								       autocomplete="off" style="letter-spacing:2px;text-transform:uppercase;">
							</div>
							<i class="sync alternate icon" id="captcha_reload_btn" style="cursor:pointer;color:#888;font-size:1.1em;" title="Nouvelle image"></i>
						</div>
						<div class="ui pointing red basic label" id="captcha_error_msg" style="display:none;margin-top:4px;">Code incorrect, essayez à nouveau.</div>
					</div>
				</div>

				<button class="ui teal button valider_submit_inter"
				        type="submit">Valider</button>
			</form>

		</div>
	</div>


</script>

<script id="tpl_bloc_titre_recherche" type="text/x-handlebars">

	<!-- bloc titre recherche -->
	<h2>Recherche <span class="coultxt_{{this.domaine}}">{{this.domaine}}</span></h2>

	<div class="sub_title motscles">
		<div class="ui type_recherche label">{{this.type_recherche}}</div>
		{{mcles this.terme_recherche}}
		{{#if this.flt_pro }}
			<div class="ui type_flt_pro label"><i class="toggle on icon"></i> Ultra-book</div>
		{{/if}}
		{{#if this.flt_sel }}
			<div class="ui type_flt_sel label"><i class="toggle on icon"></i> Sélection</div>
		{{/if}}
	</div>

</script>

<script id="tpl_bloc_portfolios_seul" type="text/x-handlebars">

	<!-- container Portfolios -->
	<div class="ui container bloc_portfolios">
		<div class="visibility {{infinite}}" >

			<div class="ui five doubling cards" id="accueil_portfolio">
				<div id="position_card_last"></div>
			</div>

			<div class="ui basic segment">
				<div class="ui grid result_message"></div>
			</div>

			<div class="ui horizontal icon divider result_end">
				<i class="circular large  angle up  icon"></i>
			</div>

			<div class="ui large centered inline text loader active">
				Chargement...			</div>

		</div>
	</div>

</script>

<script id="tpl_bloc_portfolios" type="text/x-handlebars">

	<!-- container Portfolios -->
	<div class="ui container bloc_portfolios">

			<div class="bloc_titre"></div>

			<div id="bloc_rechercher_top2" class="ui mobile hidden">

				<form action="/recherche" class="form_rechercher2018">

					<div class="ui action input search rech2018">
						<div class="ui menu ">


							<!-- domaine -->
							<div class="ui menu rechercher_change_domain">
								<div class="ui pointing dropdown link item link_rechercher_change_domain">
									<input type="hidden" name="page_domaine" value="">
									<span class="text">Tous</span>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="header">Métiers</div>

										<div class="item" data-value="tous">
											<div class="nuancier coul_tous "></div>
											Tous										</div>
																																	<div class=" item " data-value="Illustration">
													<div class="nuancier coul_illustrateur "></div>
													Illustration												</div>
																																												<div class=" item " data-value="Illustration jeunesse">
													<div class="nuancier coul_illustrateur_jeunesse "></div>
													Illustration jeunesse												</div>
																																												<div class=" item " data-value="Graphisme">
													<div class="nuancier coul_graphiste "></div>
													Graphisme												</div>
																																												<div class=" item " data-value="Direction artistique">
													<div class="nuancier coul_directeur_artistique "></div>
													Direction artistique												</div>
																																												<div class=" item " data-value="Digital & développement">
													<div class="nuancier coul_digital "></div>
													Digital & développement												</div>
																																												<div class=" item " data-value="Art">
													<div class="nuancier coul_plasticien "></div>
													Art												</div>
																																												<div class=" item " data-value="Photographie">
													<div class="nuancier coul_photographe "></div>
													Photographie												</div>
																																												<div class=" item " data-value="Design objet">
													<div class="nuancier coul_design "></div>
													Design objet												</div>
																																												<div class=" item " data-value="Architecture">
													<div class="nuancier coul_architecte "></div>
													Architecture												</div>
																																																																																																									
									</div>
								</div>
							</div>



							<!-- input -->
							<div class="item">
								<div class="ui left input">
									<input class="prompt" type="text" name="q" value="" required>

									<div class="option link_rechercher_options">
										<div class="border"></div>
										<i class="toggle on icon"></i>
										<i class="toggle off icon"></i>
									</div>

									<input type="hidden" name="type_recherche" value="pseudo">

									<button type="submit" class="ui small grey button submit_rechercher  cursor_effect">
									Rechercher									</button>

								</div>
							</div>

						</div>
					</div>

					<div class="ui basic red pointing prompt label transition error_prompt ">Indiquer un mot clés ou un nom</div>

				</form>

			</div>

			<div class="visibility infinite" >

				<div class="ui five doubling cards" id="accueil_portfolio">
					<div id="position_card_last"></div>
				</div>

				<div class="ui basic segment">
					<div class="ui grid result_message"></div>
				</div>

				<div class="ui horizontal icon divider result_end">
					<i class="circular large  angle up  icon"></i>
				</div>

				<div class="ui large centered inline text loader active">
					Chargement...				</div>


		</div>

	</div>

</script>

<script id="tpl_memobook_vide" type="text/x-handlebars">

	<div class="column centered row vide hide">
		<h2 class="ui center aligned icon header">
		  <i class="circle outline icon"></i>
		  {{this.message}}
		</h2>
	</div>

</script>

<script id="tpl_book_open_contact" type="text/x-handlebars">

		<h2 class="ui icon header">
			<img src="{{book_info_recup_img_vign}}" class="ui circular image">
			<div class="content">
				{{this.user_detail.book_prenom_nom}}

				<div class="sub header">{{this.user_detail.book_type}}<span>{{this.user_detail.book_statut}}</span></div>
			</div>
		</h2>

		{{#if this.user_detail.book_ville}}
		<h6 class="ui center aligned header localisation">
			<div>
			<i class="map marker alternate icon"></i>
			{{this.user_detail.book_pays}}
			<span class="ville">{{this.user_detail.book_ville}}</span>
			</div>
		</h6>
		{{/if}}

		<p>
			{{this.user_legende}}
		</p>

		<p>
		<div class="ui left labeled mini button disponible">
			{{#is this.user_detail.book_dispo 'true'}}
			  <a class="ui olive right pointing label">
			    <i class="coffee icon "></i>
			  </a>
			    <div class="ui button">
					Disponible			  </div>
			{{/is}}
			{{#is this.user_detail.book_dispo 'false'}}
			  <a class="ui orange right pointing label">
			    <i class="plane icon "></i>
			  </a>
			    <div class="ui button">
				    Indisponible			  </div>
			{{/is}}
		</div>
		</p>

</script>

<script id="tpl_book_open" type="text/x-handlebars">

	<!-- book open -->
	<div id="book_open">


	<div id="book_fd_top"></div>

	{{#if this.book_id_next}}
    <div id="mfp-book_suivant">
	    <a id="mfp-book_suivant_lien">
	    <div class="suivant_legende">{{msg_book_suivant}}</div>
	    <div class="suivant"></div>
	    </a>
    </div>
	{{/if}}

	<div class="ui container no-margin">

		<h2 class="ui header">

		  <img src="{{book_info_recup_img_vign}}" class="ui circular image" alt="{{this.user_detail.book_prenom_nom}}">

		  <div class="content">
		    {{this.user_detail.book_prenom_nom}}

		    <div class="sub header">
		    {{this.user_detail.book_type}}
		    <span>
		    {{this.user_detail.book_statut}}
		    </span>
		    </div>



		  </div>



		<div class="ui list">

			{{#if this.user_detail.book_ville }}
			<div class="item localise_">
				<i class="map marker alternate icon"></i>
				<div class="ville">{{this.user_detail.book_ville}}</div>
				<div class="pays">{{this.user_detail.book_pays}}</div>
			</div>
			{{/if}}

            <div class="item link_goto_book_">
             <a href="{{this.user_book_url}}" target="_blank"><i class="icon folder outline"></i>Book complet</a>
            </div>


            <div class="item action_">

                <div    id="msg_send" class="ui left labeled button ubdf_bouton link_ultra-book_contact  cursor_effect"
                        data-user="{{this.user_id}}"
                        data-content="Je souhaiterais vous contacter">
                    <div class="ui grey right pointing label">
                        <i class="envelope icon"></i>
                    </div>
                    <div class="ui basic inverted button" >
                        Contacter                    </div>
                </div>
                <br/>
                <div    id="work_similary" class="ui left labeled button ubdf_bouton popup_link hidden  cursor_effect"
                        data-content="Je suis intéressé pour commander un travail similaire">
                    <div class="ui grey right pointing label">
                        <i class="clone icon"></i> Commander                    </div>
                    <div class="ui basic inverted  button">
                        un projet similaire                        <!--<span class="img_offre_similaire_min"></span> à <span class="img_offre_similaire_max"></span>-->
                    </div>
                </div>
                <br/>
                <div    id="work_buy" class="ui left labeled button ubdf_bouton popup_link hidden  cursor_effect"
                        data-content="Je voudrais acheter cette image">
                    <div class="ui grey right pointing label">
                        <i class="shopping bag icon"></i> Acheter                    </div>
                    <div class="ui basic inverted button" >
                        cette image                        <!--<span class="img_offre_vente"></span>-->
                    </div>
                </div>

			</div>




			<div class="item motscles">
				{{mcles this.us_pf_css}}
			</div>

		</div>

		</h2>



		<div class="actions">
			<div class="ui horizontal list">

				<div class="item" id="dispo">
					{{#is this.user_detail.book_dispo 'true'}}
					<button class="ui olive icon button popup_link" data-content="Je suis disponible actuellement pour une commande">
						<i class="coffee icon "></i>
					</button>
					{{/is}}
					{{#is this.user_detail.book_dispo 'false'}}
					<button class="ui orange icon button popup_link" data-content="Je ne suis pas disponible pour le moment">
						<i class="plane icon "></i>
					</button>
					{{/is}}
				</div>

				<div class="item">
					  <div class="ui inverted basic button memobook_add  cursor_effect" data-user="{{this.user_id}}">
					     <i class="heart icon"></i><span> Mémoriser</span>
					  </div>
				</div>

			</div>

		</div>


		 <div class="ub_img_action">
                      <div class="ub_icone precedent mfp-btn_prev  cursor_effect "></div>
                      <div class="ub_img_nb">
                            <strong>1</strong> / <span class="ub_img_nb_total">
                            {{this.slider_data_nb_total}}</span>
                        </div>
                      <div class="ub_icone suivant big mfp-btn_next  cursor_effect"></div>
         </div>

</div>

<!-- #end -->
</script>

<script id="tpl_book" type="text/x-handlebars">

{{#books}}
<!-- item tpl -->
	<div
	class="ui card {{toUrl this.us_type}} {{this.us_filtre}} newitem_hide ptf_index_{{ptf_index}}  cursor_effect"
	id="user_{{this.us_dir}}"
		data-user="{{this.us_dir}}"
		data-us_="us_prenom_nom"

		data-user_detail='{"book_type":"{{toUrl this.us_type}}",
			"book_type_titre":"{{this.us_type_titre}}",
			"book_prenom_nom":"{{this.us_prenom_nom}}",
			"book_ville":"{{this.us_ville}}",
			"book_pays":"{{this.us_pays}}",
			"book_lat":"{{this.us_lat}}",
			"book_lng":"{{this.us_lng}}",
			"book_statut":"{{this.us_statut}}",
			"book_type":"{{this.us_type}}",
			"book_bio":"{{this.img_bio}}",
			"book_dispo":"{{this.us_pf_diff_dispo}}"}'

		data-slider='{"book_img": [{{this.slider}}]}'

		data-motcles='{{this.us_pf_css}}'

		>


		<a class="ui fluid image dimmable" href="#">

			<div class="ui dimmer">
				<div class="content">
					<div class="center">
						<div class="ui inverted button">
							ZOOM
						</div>
					</div>
				</div>
			</div>

			<img
				src="{{img_book.[0].img_fichier}}"
			    alt="{{img_book.[0].img_alt}}">
		</a>



		<div class="content center aligned">

			<img class="ui avatar image"
			     src="{{this.us_vign}}"
			     alt="{{this.us_prenom_nom}}">

			<div class="header">
				{{this.us_prenom_nom}}
			</div>
			<div class="meta">
				<a class="group  coul_{{toUrl this.us_type}}">{{toTraduction this.us_type}}</a>
			</div>


		</div>
		<div class="extra content">

			<div class="left floated">
				<div class="vues">
					<i class="fonticon-eye3 icon"></i><span class="stats_vue"></span>
				</div>
			</div>
			<div class="right floated">
				<div class="like">
					<i class="fonticon-heart2 icon"></i><span class="stats_sel"> </span>
				</div>
			</div>

		</div>

	</div>




	 {{#if this.us_stats_book}}
	 <div class="us_stats_book  memob_data hide" data-us_="us_stats_st_cles" data-stats_st_cles="{{stats_st_cles}}" data-src="https://www.extra-book.com/2012_stats/st_action.php?action=add&st_champ={{this.stats_champ}}&us_login={{this.us_dir}}&st_cles={{this.stats_st_cles}}&r=1"></div>
	 {{/if}}

	<!-- item #end-->

{{/books}}

</script>
@endverbatim
