{{-- Popups et modales du portail (recherche, connexion, inscription,
     memo book, contact). Le comportement est porte par js2019. --}}
<!-- All popup and modal !-->
<!-- Menu -top - mobile -->

<div class="ui fixed secondary mobile only menu " id="menu-top-fixed-mobile">
    <div class="ui container" >

        <div class="item btn_burger mode_accueil">
            <div class="menu-burger mobile-hidden">☰</div>
        </div>

        <div class="item logo mode_accueil  cursor_effect">
			                <img  class="logo_normal" src="{{ $marque->logo }}" alt="{{ $marque->nom }}">
			        </div>

        <div class="item right btn_rechercher mode_accueil">
            <i class="search icon"></i>
        </div>


        <div class="item recherche mode_rechercher" id="bloc_rechercher_top2_mobile">
            <form action="/recherche" class="form_rechercher2018">
                <div class="ui action input search rech2018">
                    <div class="ui menu ">

                        <!-- domaine -->
                        <input type="hidden" name="page_domaine" value="tous">

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

                                <button type="submit" class="ui small grey button submit_rechercher">
                                    <i class="search icon"></i>
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="item right btn_rechercher_close mode_rechercher">
            <i class="close icon"></i>
        </div>


    </div>
</div>




<!-- Menu -top - desktop -->
<div class="ui fixed secondary mobile menu  light light_permanent " id="menu-top-fixed">

    <div class="ui  container ">

		            <div class="item btn_burger ">
                <div class="menu-burger cursor_effect">☰</div>
            </div>
		
        <div class="item logo">
            <a href="/" class=" cursor_effect">
				                    <img class="logo_normal" src="{{ $marque->logo }}" alt="{{ $marque->nom }}" style="width:120px;">
				            </a>
        </div>

        <div class="item logo_light">
			                <img src="/img_front/UB-logo_2018_light.svg" alt="Ultra-book">
			
        </div>


        <div class="right item">
            <div class="ui right">
                <div class="ui  right secondary  menu">
                    <div class="right item">

						


                        <!-- bloc menu filtre -->
                        <div class="item link_menu_top_ptf icon_domaine  cursor_effect">
                            <svg height="393pt" viewBox="-4 0 393 393.99003" width="393pt" xmlns="http://www.w3.org/2000/svg">
                                <path d="m368.3125 0h-351.261719c-6.195312-.0117188-11.875 3.449219-14.707031 8.960938-2.871094 5.585937-2.3671875 12.3125 1.300781 17.414062l128.6875 181.28125c.042969.0625.089844.121094.132813.183594 4.675781 6.3125 7.203125 13.957031 7.21875 21.816406v147.796875c-.027344 4.378906 1.691406 8.582031 4.777344 11.6875 3.085937 3.105469 7.28125 4.847656 11.65625 4.847656 2.226562 0 4.425781-.445312 6.480468-1.296875l72.3125-27.574218c6.480469-1.976563 10.78125-8.089844 10.78125-15.453126v-120.007812c.011719-7.855469 2.542969-15.503906 7.214844-21.816406.042969-.0625.089844-.121094.132812-.183594l128.683594-181.289062c3.667969-5.097657 4.171875-11.820313 1.300782-17.40625-2.832032-5.511719-8.511719-8.9726568-14.710938-8.960938zm-131.53125 195.992188c-7.1875 9.753906-11.074219 21.546874-11.097656 33.664062v117.578125l-66 25.164063v-142.742188c-.023438-12.117188-3.910156-23.910156-11.101563-33.664062l-124.933593-175.992188h338.070312zm0 0"/>
                            </svg>
                        </div>


                        <!-- bloc menu seach -->
                        <div class="item recherche_menu_top link_rechercher  cursor_effect">
                            <div class="open" id="search-menu">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                     width="620.692px" height="620.692px" viewBox="0 0 451 451" style="enable-background:new 0 0 451 451;"
                                     xml:space="preserve">
                                    <path d="M447.05,428l-109.6-109.6c29.4-33.8,47.2-77.9,47.2-126.1C384.65,86.2,298.35,0,192.35,0C86.25,0,0.05,86.3,0.05,192.3
                                                s86.3,192.3,192.3,192.3c48.2,0,92.3-17.8,126.1-47.2L428.05,447c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4
                                                C452.25,441.8,452.25,433.2,447.05,428z M26.95,192.3c0-91.2,74.2-165.3,165.3-165.3c91.2,0,165.3,74.2,165.3,165.3
                                                s-74.1,165.4-165.3,165.4C101.15,357.7,26.95,283.5,26.95,192.3z"/>
                                </svg>
                            </div>
                        </div>


                        <!-- bloc memobook -->
                        <div class="item memobook link_memobook  cursor_effect" id="nav_memobook">
                            <a href="/memobook" title="S'election de book">
                                <span class="memo_nb hidden"></span>
                                <span class="fonticon-heart_white fonticon_w22"></span>
                            </a>
                        </div>

                        <x-dev.switch-marque />

                        <!-- bloc connexion-->
						
                            <div class="item btn_connection_ mobile-hidden  cursor_effect">
                                <a class="ui black basic button btn_connection">Connexion</a>
                            </div>
                            <!-- bloc creer un book-->
                            <div class="item btn_connection_signin mobile-hidden  cursor_effect">
								                                    <a class="ui black button btn_modal_creerbook">Créer un book</a>
								                            </div>

						                        <!-- bloc connexion - end -->


                    </div>
                </div>
                <div class="ui right secondary menu menu_top_droite mobile-hidden">
                    <div class="item link_menu_top_ptf  icon_domaine_secondary">METIERS</div>

                    <!-- Popup domaine/metiers !-->
                    <div class="ui fluid inverted popup transition hidden popup_ptf">
                        <div class="ui one column grid">
                            <div class="left aligned  column">
                                <h4 class="ui header">Filtres par métiers</h4>
                                <div class="ui link list" id="bloc_menu_contant_metiers">
                                    <div id="nav_metiers">
                                        <nav>

                                            <ul>
																																							
                                                        <a href="/illustrateur"
                                                           class="coul_illustrateur"
                                                           title="Illustration">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_illustrateur "></div>
																Illustration                                                            </li>
                                                        </a>


																																																				
                                                        <a href="/illustrateur-jeunesse"
                                                           class="coul_illustrateur_jeunesse"
                                                           title="Illustration jeunesse">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_illustrateur_jeunesse "></div>
																Illustration jeunesse                                                            </li>
                                                        </a>


																																																				
                                                        <a href="/graphiste"
                                                           class="coul_graphiste"
                                                           title="Graphisme">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_graphiste "></div>
																Graphisme                                                            </li>
                                                        </a>


																																																				
                                                        <a href="/directeur-artistique"
                                                           class="coul_directeur_artistique"
                                                           title="Direction artistique">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_directeur_artistique "></div>
																Direction artistique                                                            </li>
                                                        </a>


																																																				
                                                        <a href="/digital"
                                                           class="coul_digital"
                                                           title="Digital & développement">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_digital "></div>
																Digital & développement                                                            </li>
                                                        </a>


																																																				
                                                        <a href="/plasticien"
                                                           class="coul_plasticien"
                                                           title="Art">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_plasticien "></div>
																Art                                                            </li>
                                                        </a>


																																																				
                                                        <a href="/photographe"
                                                           class="coul_photographe"
                                                           title="Photographie">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_photographe "></div>
																Photographie                                                            </li>
                                                        </a>


																																																				
                                                        <a href="/design"
                                                           class="coul_design"
                                                           title="Design objet">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_design "></div>
																Design objet                                                            </li>
                                                        </a>


																																																				
                                                        <a href="/architecte"
                                                           class="coul_architecte"
                                                           title="Architecture">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_architecte "></div>
																Architecture                                                            </li>
                                                        </a>


																																																																																																																													                                            </ul>

                                        </nav>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- Popup domaine/metiers -end  !-->

                    <!-- Popup Mémo book !-->
                    <div class="ui fluid+ inverted popup transition hidden popup_memobook">
                        <div class="ui one column grid">
                            <div class="left aligned  column">
                                <h4 class="ui header">Mémo book</h4>
								Mes sélections de books                            </div>

                        </div>
                    </div>
                    <!-- Popup Mémo book -end  !-->

                    <!-- Popup popup_rechercher !-->
                    <div class="ui fluid+ inverted popup transition hidden popup_rechercher">
                        <div class="ui one column grid">
                            <div class="left aligned  column">
                                <h4 class="ui header">Recherchez un book</h4>
								Par mots-clés ou par nom                            </div>

                        </div>
                    </div>
                    <!-- Popup popup_rechercher -end  !-->



                    <div class="item btn_zoom">ZOOM</div>
                    <div class="item btn_actu">TENDANCES</div>
                    <div class="item btn_actu">COWORKING</div>


                </div>

            </div>
        </div>


    </div>

</div>



<!-- Popup rechercher menu_top !-->
<div class="ui large modal" id="bloc_rechercher_top_menu_modal">
    <div class="content">


       <span class="close">
           <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" viewBox="0 0 10 10">
               <path d="M10.012,9.296 L9.296,10.012 L5.000,5.716 L0.704,10.012 L-0.012,9.296 L4.284,5.000 L-0.012,0.704 L0.704,-0.012 L5.000,4.284 L9.296,-0.012 L10.012,0.704 L5.716,5.000 L10.012,9.296 Z" class="cls-1"></path>
           </svg>
       </span>

        <form action="/recherche" class="form_rechercher2018">
            <div class="ui action input search rech2018">
                <div class="ui menu ">

                    <div class="item">
                        <div class="ui left input">

                            <input type="hidden" name="page_domaine" value="tous">
                            <input type="hidden" name="type_recherche" value="pseudo">

                            <input class="prompt" type="text" placeholder="Indiquez un mot clé, un domaine ou un nom" name="q" value="" required>

                            <button type="submit" class="ui small  button submit_rechercher ">
                                <i class="search icon"></i>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </form>


    </div>
</div>




<!-- Popup rechercher options !-->
<div class="ui popup bottom left transition hidden popup_rechercher_options">
    <div class="ui one column grid">
        <div class="left aligned  column">
            <h4 class="ui header">Options de recherche</h4>
            <div class="ui link list" id="bloc_menu_contant_metiers">
                <div class="inline field">
                    <div class="ui toggle checkbox flt_sel">
                        <input type="checkbox" name="flt_sel" tabindex="0">
                        <label>Sélections</label>
                    </div>
                    <br/><br/>
                    <div class="ui toggle checkbox flt_pro">
                        <input type="checkbox" name="flt_pro" tabindex="0">
                        <label>Ultra-book</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Menu -top contant - normal -->
<div id="overlay-menu">
    <div class="ui container" style="width:1227px!important;">
        <div class="ui aligned stackable  three column grid">

            <div class="sixteen wide column head">
                <div class="ui center aligned basic space1 segment ">

					                        <img class="logo_normal" src="{{ $marque->estDefaut() ? '/img_front/ultra-book_logo_nb-light.svg' : $marque->logo }}" alt="{{ $marque->nom }}">
					
                    <h3>Une mine de créatifs</h3>
                    <h5>Trouver et contacter les meilleurs créatifs freelances !</h5>



                    <!-- principle -->
                    <div class="ui huge inverted relaxed horizontal animated list principle_menu">
                        <div class="item">
                            <a class="ui big red circular label icon">1</a>
                            <div class="middle aligned content">
                                <div class="header">Sélectionnez un créatif</div>
                            </div>
                        </div>
                        <div class="item">
                            <a class="ui big red circular label icon">2</a>
                            <div class="middle aligned content">
                                <div class="header">Envoyez votre demande</div>
                            </div>
                        </div>
                        <div class="item">
                            <a class="ui big red circular label icon">3</a>
                            <div class="middle aligned content">
                                <div class="header">Validez et démarrer un projet</div>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
            <div class="row">
                <div class="column ">

                    <div class="title mobile-hidden">
						Plateforme                    </div>

                    <div class="ui huge inverted  animated list selections">


                        <a class="item btn_modal_creerbook">
                            <div class="middle aligned content">
                                <div class="header">
                                    <span class="fonticon-plus2 fonticon_w22"></span>&nbsp;Créer un portfolio</div>
                            </div>
                        </a>

                        <a href="/memobook" class="item ">
                            <div class="header">
                                <span class="memo_nb"></span>
                                <span class="fonticon-heart_white fonticon_w22"></span>&nbsp;Ma sélection                             </div>
                        </a>

                    </div>


                    <div class="title mobile-hidden">
						Contact / Aide                    </div>
                    <div class="ui huge inverted relaxed divided+  animated list mobile-hidden">

                        <a class="item small_item" href="mailto:contact2019&#64;ultra-book.net?subject=Aide Ultra-book&body=Indiquez l’adresse de votre portfolio, merci.">
                            <div class="middle aligned content">
                                <div class="tiny  header">
                                    contact2019&#64;ultra-book.net
                                </div>
                            </div>
                        </a>
                        <!--
                        <a class="item small_item" href="/doc/">
                            <div class="middle aligned content">
                                <div class="tiny  header">Documentation <span
                                            class="et">&amp;</span> Tuto</div>
                            </div>
                        </a>
                        -->

                    </div>





                    <div class="title newsletter_title">
						Newsletter                    </div>
                    <div class="newsletter">
                        <div>Les dernières sélections du mois</div>
                        <div class="no-spam mobile-hidden">Confidentialité, sécurité et absence de spam</div>

                        <form class="ui form form_newsletter_2018" action="/front/action_ajax_2.php">

                            <input type="hidden" name="action" value="add">

                            <div class="ui  action mini input">
                                <input type="text" name="mail" placeholder="Mail...">
                                <button class="ui inverted+ icon button">
                                    <i class="envelope outline icon"></i>
                                </button>
                            </div>
                        </form>
                        <div class="retour"></div>
                        <!--<a href="/newsletters" class="fade mobile-hidden">Archives des sélections <span
                                    class="fonticon-arrow-right icon"></span></a>-->
                    </div>




                </div>
                <div class="column domaines mobile-hidden">
                    <div class="title">
						Rechercher par catégories                    </div>
                    <div id="nav_metiers">
                        <nav>
                            <ul>
																	                                        <a href="/illustrateur-freelance" class="coul_illustrateur"
                                           title="Illustration">
                                            <li class="no_selected">

                                                <div class="nuancier coul_illustrateur "></div>
												Illustration
                                            </li>
                                        </a>
																										                                        <a href="/meilleurs-illustrateurs-jeunesse" class="coul_illustrateur_jeunesse"
                                           title="Illustration jeunesse">
                                            <li class="no_selected">

                                                <div class="nuancier coul_illustrateur_jeunesse "></div>
												Illustration jeunesse
                                            </li>
                                        </a>
																										                                        <a href="/graphistes-freelance" class="coul_graphiste"
                                           title="Graphisme">
                                            <li class="no_selected">

                                                <div class="nuancier coul_graphiste "></div>
												Graphisme
                                            </li>
                                        </a>
																										                                        <a href="/directeur-artistique" class="coul_directeur_artistique"
                                           title="Direction artistique">
                                            <li class="no_selected">

                                                <div class="nuancier coul_directeur_artistique "></div>
												Direction artistique
                                            </li>
                                        </a>
																										                                        <a href="/webdesigner-developpeur-freelance" class="coul_digital"
                                           title="Digital & développement">
                                            <li class="no_selected">

                                                <div class="nuancier coul_digital "></div>
												Digital & développement
                                            </li>
                                        </a>
																										                                        <a href="/plasticien" class="coul_plasticien"
                                           title="Art">
                                            <li class="no_selected">

                                                <div class="nuancier coul_plasticien "></div>
												Art
                                            </li>
                                        </a>
																										                                        <a href="/photographe" class="coul_photographe"
                                           title="Photographie">
                                            <li class="no_selected">

                                                <div class="nuancier coul_photographe "></div>
												Photographie
                                            </li>
                                        </a>
																										                                        <a href="/design" class="coul_design"
                                           title="Design objet">
                                            <li class="no_selected">

                                                <div class="nuancier coul_design "></div>
												Design objet
                                            </li>
                                        </a>
																										                                        <a href="/architecte" class="coul_architecte"
                                           title="Architecture">
                                            <li class="no_selected">

                                                <div class="nuancier coul_architecte "></div>
												Architecture
                                            </li>
                                        </a>
																																																																																					                            </ul>
                        </nav>
                    </div>

                </div>

                <div class="column ">

                    <div class="title mobile-hidden">
						Autres rubriques                    </div>
                    <div class="ui huge inverted relaxed divided  animated list mobile-hidden">


						
                            <a class="item " href="https://www.ultra-book.info">
                                <div class="ui huge circular icon button">
                                    <i class="icon bolt"></i>
                                </div>
                                <div class="middle aligned content">
                                    <div class="header">Tendances, Actualités, Zoom</div>
                                    <div class="description">Voir tous les actus<span class="fonticon-arrow-right icon"></span></div>
                                </div>
                            </a>

						


                        <a class="item" href="https://www.ultrabook.pro/?utm_source=ubaccueil" target="_blank">
                            <div class="ui huge circular icon button">
                                <i class="icon university "></i>
                            </div>
                            <div class="middle aligned content">
                                <div class="header">Installer mon site PRO</div>
                                <div class="description">ultrabook.pro<span class="fonticon-arrow-right icon"></span></div>
                            </div>
                            <div class="middle aligned content">
                                <img src="https://www.ultrabook.pro/img/enplusconstruction.svg" alt="Ultra-book site PRO" style="max-width:100px">
                            </div>
                        </a>



                    </div>




                </div>

            </div>
        </div>
    </div>
</div>


<!-- Modal ajax 2018 !-->
<div class="ui modal large modal_content_ajax" id="fullscreenModal">
    <div class="scrolling content">
        <div class="ui active dimmer">
            <div class="ui medium loader"></div>
        </div>
        <br><br><br><br><br><br>
    </div>
</div>
<!-- Modal #end !-->


<!-- Modal ajax 2018 contact card !-->
<div class="ui modal large modal_content_ajax_contact">
    <div class="content">

        <div class="btn_close outbox">
            <div></div>
        </div>

        <div class="ui very relaxed grid two column middle center aligned stackable">

            <div class="column middle aligned" id="header_creatif"></div>

            <div class="column middle aligned">
                <div class="segment basic space1">
                    <div class="ui left aligned header">Contacter</div>

                    <div class="content_ajax">
                        <div class="ui active inverted dimmer">
                            <div class="ui medium loader"></div>
                        </div>
                        <br><br><br>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- Modal #end !-->














<!-- Modal ajax 2019 intermediate card - bloc tpl_bloc_modal_content_ajax_intermediate_header_creatif -->


<!-- Modal ajax 2019 intermediate card - bloc tpl_bloc_modal_content_ajax_intermediate_form_creatif -->



<!-- Modal ajax 2019 intermediate card !-->
<div class="ui modal large modal_content_ajax_intermediate">
    <div class="content ">

        <div class="btn_close outbox">
            <div></div>
        </div>

        <div class="ui very relaxed grid two column middle center aligned stackable">


            <div class="column middle aligned segment_header hidden" ></div>

            <!-- content -->
            <div class="column middle aligned loading_segment hidden">
                <div class="ui active inverted dimmer ">
                    <div class="ui medium loader"></div>
                </div>
            </div>

            <div class="column middle aligned segment_content hidden"></div>


        </div>
    </div>

</div>
<!-- Modal #end !-->




<!-- Modal connection 2018 !-->
<div class="ui modal modal_connection">


    <div class="content">
        <div class="btn_close outbox">
            <div></div>
        </div>


        <div class="ui container ">
            <div class="ui grid three column middle center aligned stackable">


                <div class="2-3 wide column space2 ui middle aligned">
                    <div class="segment basic space1">

						                            <div class="logo">
                                <img class="logo_normal" src="{{ $marque->logo }}" alt="{{ $marque->nom }}" >
                            </div>
                            <h3>Une mine de créatifs</h3>
						

                        <!--  mdp forget form show ok+error -->
                        <div class="ui segment basic space1 hidden" id="segment_mdp_showOk">
                            <div class="ui left aligned basic small segment">

                                <div class="ui info message hidden">
                                    <div class="header"></div>
                                    <p></p>
                                    <ul class="list"><ul>
                                </div>

                                <div class="ui negative message hidden">
                                    <div class="header"></div>
                                    <p></p>
                                </div>

                                <button class="ui button icon btn_back_mdp"><i class="angle left icon"></i></button>

                            </div>
                        </div>



                    </div>
                </div>

                <div class="one  column space2">



                    <!-- mdp loader -->
                    <div class="ui inverted dimmer" id="segment_loader_mdp">
                        <div class="ui loader"></div>
                    </div>


                    <!--  mdp forget form -->
                    <div class="ui segment basic space1 hidden" id="segment_mdp">

                        <div class="ui left aligned basic small segment">
                            <h4>Récupérer mon mot de passe</h4>

                            <form id="mdp_form" class="ui form">

                                @csrf


                                <input type="hidden" name="form_action" value="form_valide" >
                                <input type="hidden" name="form_id" value="form_mdpoublie">
                                <input type="hidden" name="action" value="form">
                                <input type="hidden" name="g-recaptcha-response">

                                <div class="grouped fields">
                                    <div class="ui  field">
                                        <div class="ui left icon input">
                                            <input id="us_mail_mdp" type="text" name="us_mail"  value=""
                                                   placeholder="Indiquer votre mail">
                                            <i class="mail icon"></i>
                                        </div>
                                    </div>
                                </div>
                                <button class="ui button icon btn_back_mdptologin"><i class="angle left icon"></i></button>

                                <button class="ui teal button valider_submit_mdp"
                                        type="submit">Valider</button>
                            </form>

                        </div>
                    </div>



                    <!-- connect form -->
                    <div class="ui segment basic space1" id="segment_connect">

						
                        <div class="ui horizontal divider">
							Déja un compte                        </div>


                        <div class="ui left aligned basic small segment">

                            <form action="/ubaction__user_open" id="login_form" method="post" class="ui form ">

                                @csrf


                                <input type="hidden" name="g-recaptcha-response">

                                <div class="grouped fields">
                                    <div class="ui small input field">
                                        <input id="login"
                                               type="text"
                                               name="login"
                                               value=""
                                               autocomplete="username"
                                               placeholder="Identifiant">
                                    </div>
                                </div>

                                <div class="grouped fields">
                                    <div class="ui field">
                                        <input
                                                id="pass"
                                                type="password"
                                                name="pass"
                                                value=""
                                                autocomplete="current-password"
                                                placeholder="Mot de passe">
                                    </div>
                                </div>

                                <input class="ui small button valider_submit_login" type="submit" value="Connexion">

                            </form>

                            <br>
                            <a id="btn_mdp_forget" href="#">Mot de passe ou pseudo oublié ?</a>
                        </div>
                    </div>
                </div>


            </div>
        </div>


    </div>
</div>
<!-- Modal #end !-->




<!-- Modal creer un book 2019 ! -->

<!-- reCAPTCHA v3 -->
<div class="ui modal modal_creerbook">

    <div class="content">
        <div class="btn_close outbox">
            <div></div>
        </div>


        <div class="ui container ">
            <div class="ui grid middle center aligned stackable">


                <div class="ten wide column space2 ui middle aligned">
                    <div class="segment basic space1" id="inscription_segment_gauche">

                        <img class="ui centered medium image  cursor_effect" src="/img_admin/diffusion-b.svg" alt="Créez un book">

                        <!-- presentation -->
                        <div id="inscription_segment_presentation">
                            <h2>Créez un book</h2>
                            <h4>Rejoignez les 50.000 créatifs.<br/>Créez, diffusez et proposez vos services</h4>
                            <!--<p>La plateforme est gratuite par défaut.<br/></p>-->

							                                <div class="logo">
                                    <img class="logo_normal" src="{{ $marque->logo }}" alt="{{ $marque->nom }}" >
                                </div>
                                <h3>Une mine de créatifs</h3>
							

                        </div>


                        <!-- validation -->
                        <div class="hidden" id="inscription_segment_validation">

                            <canvas id="make_progress_canvas"></canvas>

                            <div class="inscription_segment_bravo hidden" >
                                <h2 class="btn_acceder_espace">Bravo, maintenant vous avez votre portfolio !</h2>
                                <h4 class="btn_acceder_espace">Pour rejoindre la sélection placez au minimum une douzaine d’images</h4>
                                <h3 class="btn_acceder_espace">Accédez à votre espace<i class="ui arrow right teal icon"></i></h3>
                            </div>

                        </div>

                    </div>
                </div>


                <div class="six wide column space2">


                    <!-- inscription -->
                    <div class="ui segment basic space1" id="inscription_segment_">


                        <!-- loader -->
                        <div class="ui inverted dimmer" id="inscription_segment_loader">
                            <div class="ui loader"></div>
                        </div>


                        <!-- inscription form - error
						<div id="user_add_error" class="hidden"></div>
						-->

                        <!-- inscription form error -->
                        <div id="display_error_segment" class="hidden">
                            <div class="ui error message" >
                                <div class="header"><i class="cogs icon"></i> Erreurs lors de l’enregistrement.</div>
                            </div>
                            <button class="ui button icon return_first hidden"><i class="angle left icon"></i></button>
                        </div>


                        <!-- inscription form - google after -->
                        <div id="inscription_segment_google_form_after" class="hidden">
                            <div class="ui left aligned basic small segment ">

                                <div class="ui raised segment  data_google">
                                    <div id="type_connection">connection via  <span class="data_type_connection"></span></div>
                                    <a class="ui teal ribbon label"><i class="icon" id="icon_to_change"></i></a>
                                    <h2 class="ui header">
                                        <img src="" class="ui circular image data_photo_url">
                                        <div class="content">
                                            <span class="data_displayName"></span>
                                            <div class="sub header data_email"></div>
                                        </div>

                                    </h2>

                                </div>

                                <form class="ui form " id="inscription_google">

                                @csrf


                                    <input type="hidden" name="action" value="form">
                                    <input type="hidden" name="form_id" value="">
                                    <input type="hidden" value="form_valide" name="form_action">
                                    <input type="hidden" name="g-recaptcha-response">

                                    <!-- 1 volet-->
                                    <div class="volet_1">

                                        <div class="grouped fields">
                                            <div class="ui field">
                                                <div class="ui right labeled small input us_login_">
                                                    <div class="ui label us_login_affhttp"> https://</div>
                                                    <input name="us_login"
                                                           type="text"
                                                           autocomplete="username"
                                                           placeholder="Identifiant"
                                                           id="us_login">
                                                    <div
                                                            class="ui label us_login_affub">.ubdf2020ssl.localhost:4433</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grouped fields">
                                            <div class="field">
                                                <div class="ui selection dropdown dropdown_nav_metiers_">
                                                    <input type="hidden" name="us_type">
                                                    <i class="dropdown icon"></i>
                                                    <div class="default text">Métier ou domaine</div>

                                                    <div class="menu dropdown_nav_metiers" >
																													                                                                <div class="item" data-value="illustrateur">
                                                                    <div class="nuancier coul_illustrateur"></div>
																	Illustration                                                                </div>
																																												                                                                <div class="item" data-value="illustrateur-jeunesse">
                                                                    <div class="nuancier coul_illustrateur_jeunesse"></div>
																	Illustration jeunesse                                                                </div>
																																												                                                                <div class="item" data-value="graphiste">
                                                                    <div class="nuancier coul_graphiste"></div>
																	Graphisme                                                                </div>
																																												                                                                <div class="item" data-value="directeur-artistique">
                                                                    <div class="nuancier coul_directeur_artistique"></div>
																	Direction artistique                                                                </div>
																																												                                                                <div class="item" data-value="digital">
                                                                    <div class="nuancier coul_digital"></div>
																	Digital & développement                                                                </div>
																																												                                                                <div class="item" data-value="plasticien">
                                                                    <div class="nuancier coul_plasticien"></div>
																	Art                                                                </div>
																																												                                                                <div class="item" data-value="photographe">
                                                                    <div class="nuancier coul_photographe"></div>
																	Photographie                                                                </div>
																																												                                                                <div class="item" data-value="design">
                                                                    <div class="nuancier coul_design"></div>
																	Design objet                                                                </div>
																																												                                                                <div class="item" data-value="architecte">
                                                                    <div class="nuancier coul_architecte"></div>
																	Architecture                                                                </div>
																																																																																																																																																	                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grouped fields">
                                            <div class="field">
                                                <div class="ui toggle checkbox us_licence ">
                                                    <input name="us_licence" type="checkbox" tabindex="0" class="hidden">
                                                    <label>
														J’accepte les                                                        <a href="/doc/conditions-dutilisations"
                                                           target="_blank">conditions d’utilisation</a>
														{{ __('de la plateforme :marque', ['marque' => $marque->nom]) }}                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <button class="ui button icon return_first hidden"><i class="angle left icon"></i></button>

                                        <button class="ui teal button valider_submit"
                                                type="submit">Valider</button>

                                    </div>
                                </form>

                            </div>
                        </div>

                        <!-- inscription form - classic -->
                        <div id="inscription_segment">

                            <div id="inscription_segment_social_connect">
                                <h4 style="margin-top:50px;text-align:left;color:#5f5f5f;margin-bottom: 8px;margin-left: 6px;">
                                    <!--Sobre, efficace et gratuit-->
									Inscrivez-vous gratuitement                                </h4>

								
                            </div>

                            <!-- classic -->
                            <div class="ui left aligned basic small segment">

                                <form class="ui form " id="inscription_classic">

                                @csrf


                                    <input type="hidden" name="action" value="form">
                                    <input type="hidden" name="form_id" value="form_adduser">
                                    <input type="hidden" value="form_valide" name="form_action">
                                    <input type="hidden"  name="g-recaptcha-response">


                                    <!-- 1 volet-->
                                    <div class="volet_1">

                                        <div class="grouped fields">
                                            <div class="ui  field ">
                                                <div class="ui right labeled small input us_login_">
                                                    <div class="ui label us_login_affhttp"> https://</div>
                                                    <input name="us_login"
                                                           type="text"
                                                           autocomplete="username"
                                                           placeholder="Identifiant"
                                                           id="us_login">
                                                    <div
                                                            class="ui label us_login_affub">.ubdf2020ssl.localhost:4433</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grouped fields">
                                            <div class="field">
                                                <div class="ui selection dropdown dropdown_nav_metiers_">
                                                    <input type="hidden" name="us_type">
                                                    <i class="dropdown icon"></i>
                                                    <div class="default text">Métier ou domaine</div>

                                                    <div class="menu dropdown_nav_metiers" >
																													                                                                <div class="item" data-value="illustrateur">
                                                                    <div class="nuancier coul_illustrateur"></div>
																	Illustration                                                                </div>
																																												                                                                <div class="item" data-value="illustrateur-jeunesse">
                                                                    <div class="nuancier coul_illustrateur_jeunesse"></div>
																	Illustration jeunesse                                                                </div>
																																												                                                                <div class="item" data-value="graphiste">
                                                                    <div class="nuancier coul_graphiste"></div>
																	Graphisme                                                                </div>
																																												                                                                <div class="item" data-value="directeur-artistique">
                                                                    <div class="nuancier coul_directeur_artistique"></div>
																	Direction artistique                                                                </div>
																																												                                                                <div class="item" data-value="digital">
                                                                    <div class="nuancier coul_digital"></div>
																	Digital & développement                                                                </div>
																																												                                                                <div class="item" data-value="plasticien">
                                                                    <div class="nuancier coul_plasticien"></div>
																	Art                                                                </div>
																																												                                                                <div class="item" data-value="photographe">
                                                                    <div class="nuancier coul_photographe"></div>
																	Photographie                                                                </div>
																																												                                                                <div class="item" data-value="design">
                                                                    <div class="nuancier coul_design"></div>
																	Design objet                                                                </div>
																																												                                                                <div class="item" data-value="architecte">
                                                                    <div class="nuancier coul_architecte"></div>
																	Architecture                                                                </div>
																																																																																																																																																	                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button class="ui icon teal button valider_volet_1"><i class="angle right icon"></i></button>

                                    </div>


                                    <!-- 2 volet-->
                                    <div class="volet_2 hidden">

                                        <div class="ui Large label  id_connection UItooltip" data-content="Utilisez cet identifiant pour vous connecter et administrer votre book" data-position="top center" data-variation="inverted" >
                                            <i class="user icon"></i>
                                            <span id="show_id_connection"></span>
                                        </div>

                                        <div class="grouped fields">
                                            <div class="ui small input field">
                                                <input id="mdp-desac" type="password" name="us_pass" value="" autocomplete="new-password"
                                                       placeholder="Mot de passe">
                                            </div>
                                        </div>


                                        <div class="grouped fields">
                                            <div class="ui small input field">
                                                <input type="mail" name="us_nom" value=""
                                                       placeholder="Nom / Prénom">
                                            </div>
                                        </div>

                                        <div class="grouped fields">
                                            <div class="ui small input field">
                                                <input id="mail" type="mail" name="us_mail" value=""
                                                       placeholder="Mail">
                                            </div>
                                        </div>

                                        <div class="grouped fields">
                                            <div class="field">
                                                <div class="ui toggle checkbox us_licence ">
                                                    <input name="us_licence" type="checkbox" tabindex="0"
                                                           class="hidden">
                                                    <label>
														J’accepte les                                                        <a href="/doc/conditions-dutilisations"
                                                           target="_blank">conditions d’utilisation</a>
														{{ __('de la plateforme :marque', ['marque' => $marque->nom]) }}                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <button class="ui button icon valider_volet_2"><i class="angle left icon"></i></button>
                                        <button class="ui teal button valider_submit"
                                                type="submit">Valider</button>

                                    </div>

                                </form>


                            </div>



                        </div>


                    </div>
                </div>


            </div>
        </div>


    </div>
</div>
<!-- Modal #end !-->







<!-- All template !-->





<!-- bloc books open -->



<!-- bloc tpl_bloc_portfolios for api -->




<!-- bloc tpl_bloc_portfolios -->








<!-- bloc memobooks - vide -->



<!-- bloc books open - contact -->



<!-- bloc books open -->




<!-- bloc books single -->

<!-- bloc books single -fin

<!-- cboxOverlay !-->
<div id="cboxOverlay"></div>

<!-- cursor !-->
<div id="cursor_follower">
    <div id="circle1"></div>
    <div id="circle2"></div>
</div>



<!-- ex Google Analytics -->


	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChcd_aICKMtRG81CImIt9c6an6z9Jc8wc" type="text/javascript"></script>



<!-- stats book en JS -->
<div id="ub_us_stats_book" class="hide"></div>


	<script type="application/ld+json">
		{
			"@@context" : "https://schema.org",
		    "@@type" : "Organization",
		    "name" : "Ultra-book",
		    "url" : "https://www.ultra-book.com",
		    "sameAs" : [ "https://www.facebook.com/ultrabook.fr/",
		    "https://twitter.com/ultra_book",
		    "https://www.pinterest.com/ultrabook001/",
		    "https://plus.google.com/+Ultrabook01"]
		}
	</script>















