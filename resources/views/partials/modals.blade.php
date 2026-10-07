{{-- Popups et modales du portail (recherche, connexion,
     memo book, contact). Le comportement est porte par js2019. --}}
<!-- All popup and modal !-->
{{-- Menus du haut : absents des pages plein ecran (creer un book). --}}
@unless (View::hasSection('plein_ecran'))
<!-- Menu -top - mobile -->

<div class="ui fixed secondary mobile only menu " id="menu-top-fixed-mobile" x-data="barreMobile">
    <div class="ui container" >

        @if ($marque->estDefaut())
        <div class="item btn_burger mode_accueil" x-show="! recherche">
            <div class="menu-burger mobile-hidden" @click="$store.menu.basculer()" x-text="$store.menu.ouvert ? '✕' : '☰'">☰</div>
        </div>
        @endif

        <div class="item logo mode_accueil  cursor_effect" x-show="! recherche">
			                <img  class="logo_normal" src="{{ $marque->logo }}" alt="{{ $marque->nom }}" @unless ($marque->estDefaut()) style="width:155px;" @endunless>
			        </div>

        <x-portail.selecteur-langue class="right mode_accueil" x-show="! recherche" />

        <a class="item {{ $marque->multilingue() ? '' : 'right' }} btn_rechercher mode_accueil" x-show="! recherche" href="{{ lien('search') }}" aria-label="{{ __('Rechercher') }}">
            <i class="search icon"></i>
        </a>


        <div class="item recherche mode_rechercher" id="bloc_rechercher_top2_mobile" x-show="recherche" x-cloak>
            <form action="/recherche" class="form_rechercher2018" x-data="recherche" @submit.prevent="envoyer($el)">
                <div class="ui action input search category rech2018" @click.outside="resultats = []">
                    <div class="ui menu ">

                        <!-- domaine -->
                        <input type="hidden" name="page_domaine" value="tous">

                        <!-- input -->
                        <div class="item">
                            <div class="ui left input">
                                <input class="prompt" type="text" name="q" value="" required autocomplete="off" x-model="requete" @input.debounce.50ms="chercher()" @keydown.escape="resultats = []">

                                <div class="option link_rechercher_options" x-infobulle="'.popup_rechercher_options'" :class="{ show_on: $store.optionsRecherche.selection || $store.optionsRecherche.abonnes }">
                                    <div class="border"></div>
                                    <i class="toggle on icon"></i>
                                    <i class="toggle off icon"></i>
                                </div>

                                <input type="hidden" name="type_recherche" value="mcles">

                                <button type="submit" class="ui small grey button submit_rechercher" :class="{ loading: enCours }">
                                    <i class="search icon"></i>
                                </button>

                                <x-portail.resultats-recherche />

                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="item right btn_rechercher_close mode_rechercher" x-show="recherche" x-cloak @click="recherche = false">
            <i class="close icon"></i>
        </div>


    </div>
</div>




<!-- Menu -top - desktop -->
<div class="ui fixed secondary mobile menu  light light_permanent " id="menu-top-fixed">

    <div class="ui  container ">

            {{-- Pas de menu burger sur Dustfolio. --}}
            @if ($marque->estDefaut())
            <div class="item btn_burger ">
                <div class="menu-burger cursor_effect" x-data @click="$store.menu.basculer()" x-text="$store.menu.ouvert ? '✕' : '☰'">☰</div>
            </div>
            @endif
		
        <div class="item logo">
            <a href="/" class=" cursor_effect">
				                    <img class="logo_normal" src="{{ $marque->logo }}" alt="{{ $marque->nom }}" style="width:{{ $marque->estDefaut() ? 114 : 160 }}px;">
				            </a>
        </div>

        {{-- Bascule UB / DF (developpement seulement), a droite du logo. --}}
        <x-dev.switch-marque />

        <div class="item logo_light">
			                {{-- Charte : sur fond sombre, meme logo que partout, inverse. --}}
			                <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" style="filter: invert(1)">
			
        </div>


        <div class="right item">
            <div class="ui right">
                <div class="ui  right secondary  menu">
                    <div class="right item">

						


                        <x-portail.selecteur-langue />

                        <!-- bloc menu filtre -->
                        <div class="item link_menu_top_ptf icon_domaine show cursor_effect" x-data x-infobulle.lent="'.popup_ptf'">
                            <svg height="393pt" viewBox="-4 0 393 393.99003" width="393pt" xmlns="http://www.w3.org/2000/svg">
                                <path d="m368.3125 0h-351.261719c-6.195312-.0117188-11.875 3.449219-14.707031 8.960938-2.871094 5.585937-2.3671875 12.3125 1.300781 17.414062l128.6875 181.28125c.042969.0625.089844.121094.132813.183594 4.675781 6.3125 7.203125 13.957031 7.21875 21.816406v147.796875c-.027344 4.378906 1.691406 8.582031 4.777344 11.6875 3.085937 3.105469 7.28125 4.847656 11.65625 4.847656 2.226562 0 4.425781-.445312 6.480468-1.296875l72.3125-27.574218c6.480469-1.976563 10.78125-8.089844 10.78125-15.453126v-120.007812c.011719-7.855469 2.542969-15.503906 7.214844-21.816406.042969-.0625.089844-.121094.132812-.183594l128.683594-181.289062c3.667969-5.097657 4.171875-11.820313 1.300782-17.40625-2.832032-5.511719-8.511719-8.9726568-14.710938-8.960938zm-131.53125 195.992188c-7.1875 9.753906-11.074219 21.546874-11.097656 33.664062v117.578125l-66 25.164063v-142.742188c-.023438-12.117188-3.910156-23.910156-11.101563-33.664062l-124.933593-175.992188h338.070312zm0 0"/>
                            </svg>
                        </div>


                        <!-- bloc menu seach -->
                        <div class="item recherche_menu_top link_rechercher  cursor_effect" x-data x-infobulle="'.popup_rechercher'">
                            <a class="open" id="search-menu" href="{{ lien('search') }}" aria-label="{{ __('Rechercher') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                     width="620.692px" height="620.692px" viewBox="0 0 451 451" style="enable-background:new 0 0 451 451;"
                                     xml:space="preserve">
                                    <path d="M447.05,428l-109.6-109.6c29.4-33.8,47.2-77.9,47.2-126.1C384.65,86.2,298.35,0,192.35,0C86.25,0,0.05,86.3,0.05,192.3
                                                s86.3,192.3,192.3,192.3c48.2,0,92.3-17.8,126.1-47.2L428.05,447c2.6,2.6,6.1,4,9.5,4s6.9-1.3,9.5-4
                                                C452.25,441.8,452.25,433.2,447.05,428z M26.95,192.3c0-91.2,74.2-165.3,165.3-165.3c91.2,0,165.3,74.2,165.3,165.3
                                                s-74.1,165.4-165.3,165.4C101.15,357.7,26.95,283.5,26.95,192.3z"/>
                                </svg>
                            </a>
                        </div>


                        <!-- bloc connexion-->
                        {{-- Connecte, le createur remplace les deux boutons
                             par sa vignette, son nom et son metier : le meme
                             bloc que dans la barre de son espace. --}}
                        @auth('web')
                            <div class="item">
                                <x-barre.createur :creatif="auth('web')->user()" />
                            </div>
                        @elseauth('visitor')
                            {{-- Visiteur connecte : le coeur mene au memo book, le texte a
                                 son compte. Ecart entre domaine, recherche et coeur :
                                 portail.css (.coeur_menu_top). --}}
                            <div class="item coeur_menu_top">
                                <a href="{{ lien('memobook') }}" aria-label="{{ __('Mémo book') }}" title="{{ __('Mémo book') }}" style="display:flex;align-items:center;color:#444">
                                    <span class="fonticon-heart_white" aria-hidden="true"></span>
                                </a>
                            </div>
                            <div class="item">
                                <a href="{{ lien('visiteur.tableau') }}" class="barre_visiteur"
                                   style="display:flex;align-items:center;gap:10px;color:#1b1b1b;text-decoration:none;font-family:'Source Sans 3','Source Sans Pro',sans-serif;font-size:16px;font-weight:600">
                                    <x-visiteur.avatar :visiteur="auth('visitor')->user()" :taille="36" />
                                    {{ __('Mon compte') }}
                                </a>
                            </div>
                        @else
                            {{-- Mobile seulement (portail.css .icone_menu_mobile) : les
                                 deux boutons texte ci-dessous y sont masques. --}}
                            <div class="item icone_menu_mobile">
                                <button type="button" x-data @click="$store.modale.ouvrir('connexion')" aria-label="{{ __('Connexion') }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
                                </button>
                            </div>
                            <div class="item icone_menu_mobile">
                                <a href="{{ lien('inscription.page') }}" aria-label="{{ __('Créer un book') }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9.5"/><path d="M12 7.5v9M7.5 12h9"/></svg>
                                </a>
                            </div>
                            <div class="item btn_connection_ mobile-hidden  cursor_effect">
                                <a class="ui black basic button btn_connection" x-data @click="$store.modale.ouvrir('connexion')">{{ __('Connexion') }}</a>
                            </div>
                            <!-- bloc creer un book-->
                            <div class="item btn_connection_signin mobile-hidden  cursor_effect">
                                <a class="ui black button btn_modal_creerbook" href="{{ lien('inscription.page') }}">{{ __('Créer un book') }}</a>
                            </div>
                        @endauth
                        <!-- bloc connexion - end -->


                    </div>
                </div>
                <div class="ui right secondary menu menu_top_droite mobile-hidden">
                    <div class="item link_menu_top_ptf  icon_domaine_secondary" x-data x-infobulle.lent="'.popup_ptf'">{{ mb_strtoupper(__('Métiers')) }}</div>

                    <!-- Popup domaine/metiers !-->
                    <div class="ui fluid inverted popup transition hidden popup_ptf">
                        <div class="ui one column grid">
                            <div class="left aligned  column">
                                <h4 class="ui header">{{ __('Filtres par métiers') }}</h4>
                                <div class="ui link list" id="bloc_menu_contant_metiers">
                                    <div id="nav_metiers">
                                        <nav>

                                            <ul>
																																							
                                                        <a href="{{ lien_metier('illustrateur') }}"
                                                           class="coul_illustrateur"
                                                           title="{{ __('Illustration') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_illustrateur "></div>
																{{ __('Illustration') }}                                                            </li>
                                                        </a>


																																																				
                                                        <a href="{{ lien_metier('illustrateur-jeunesse') }}"
                                                           class="coul_illustrateur_jeunesse"
                                                           title="{{ __('Illustration jeunesse') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_illustrateur_jeunesse "></div>
																{{ __('Illustration jeunesse') }}                                                            </li>
                                                        </a>


																																																				
                                                        <a href="{{ lien_metier('graphiste') }}"
                                                           class="coul_graphiste"
                                                           title="{{ __('Graphisme') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_graphiste "></div>
																{{ __('Graphisme') }}                                                            </li>
                                                        </a>


																																																				
                                                        <a href="{{ lien_metier('directeur-artistique') }}"
                                                           class="coul_directeur_artistique"
                                                           title="{{ __('Direction artistique') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_directeur_artistique "></div>
																{{ __('Direction artistique') }}                                                            </li>
                                                        </a>


																																																				
                                                        <a href="{{ lien_metier('digital') }}"
                                                           class="coul_digital"
                                                           title="{{ __('Digital & développement') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_digital "></div>
																{{ __('Digital & développement') }}                                                            </li>
                                                        </a>


																																																				
                                                        <a href="{{ lien_metier('plasticien') }}"
                                                           class="coul_plasticien"
                                                           title="{{ __('Art') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_plasticien "></div>
																{{ __('Art') }}                                                            </li>
                                                        </a>


																																																				
                                                        <a href="{{ lien_metier('photographe') }}"
                                                           class="coul_photographe"
                                                           title="{{ __('Photographie') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_photographe "></div>
																{{ __('Photographie') }}                                                            </li>
                                                        </a>


																																																				
                                                        <a href="{{ lien_metier('design') }}"
                                                           class="coul_design"
                                                           title="{{ __('Design objet') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_design "></div>
																{{ __('Design objet') }}                                                            </li>
                                                        </a>


																																																				
                                                        <a href="{{ lien_metier('architecte') }}"
                                                           class="coul_architecte"
                                                           title="{{ __('Architecture') }}">
                                                            <li class="no_selected">
                                                                <div class="nuancier coul_architecte "></div>
																{{ __('Architecture') }}                                                            </li>
                                                        </a>


																																																																																																																													                                            </ul>

                                        </nav>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- Popup domaine/metiers -end  !-->


                    <!-- Popup popup_rechercher !-->
                    <div class="ui fluid+ inverted popup transition hidden popup_rechercher">
                        <div class="ui one column grid">
                            <div class="left aligned  column">
                                <h4 class="ui header">{{ __('Recherchez un book') }}</h4>
								{{ __('Par mots-clés ou par nom') }}</div>

                        </div>
                    </div>
                    <!-- Popup popup_rechercher -end  !-->



                    <div class="item btn_zoom">{{ mb_strtoupper(__('Zoom')) }}</div>
                    <div class="item btn_actu">{{ mb_strtoupper(__('Tendances')) }}</div>
                    <div class="item btn_actu">COWORKING</div>


                </div>

            </div>
        </div>


    </div>

</div>



@endunless

<!-- Popup rechercher menu_top !-->
<x-portail.modale nom="recherche" class="large" id="bloc_rechercher_top_menu_modal">
    <div class="content">


       <span class="close" @click="$store.modale.fermer()">
           <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" viewBox="0 0 10 10">
               <path d="M10.012,9.296 L9.296,10.012 L5.000,5.716 L0.704,10.012 L-0.012,9.296 L4.284,5.000 L-0.012,0.704 L0.704,-0.012 L5.000,4.284 L9.296,-0.012 L10.012,0.704 L5.716,5.000 L10.012,9.296 Z" class="cls-1"></path>
           </svg>
       </span>

        <form action="/recherche" class="form_rechercher2018" x-data="recherche" @submit.prevent="envoyer($el)">
            <div class="ui action input search category rech2018" @click.outside="resultats = []">
                <div class="ui menu ">

                    <div class="item">
                        <div class="ui left input">

                            <input type="hidden" name="page_domaine" value="tous">
                            <input type="hidden" name="type_recherche" value="mcles">

                            <input class="prompt" type="text" placeholder="Indiquez un mot clé, un domaine ou un nom" name="q" value="" required autocomplete="off" x-model="requete" @input.debounce.50ms="chercher()" @keydown.escape="resultats = []">

                            <button type="submit" class="ui small  button submit_rechercher " :class="{ loading: enCours }">
                                <i class="search icon"></i>
                            </button>

                            <x-portail.resultats-recherche />

                        </div>
                    </div>
                </div>
            </div>
        </form>


    </div>
</x-portail.modale>




<!-- Popup rechercher options !-->
<div class="ui popup bottom left transition hidden popup_rechercher_options">
    <div class="ui one column grid">
        <div class="left aligned  column">
            <h4 class="ui header">{{ __('Options de recherche') }}</h4>
            <div class="ui link list" id="bloc_menu_contant_metiers">
                <div class="inline field">
                    <div class="ui toggle checkbox flt_sel" x-data="caseACocher" x-bind="racine" @change="$store.optionsRecherche.selection = $event.target.checked">
                        <input type="checkbox" name="flt_sel" tabindex="0">
                        <label>{{ __('Sélections') }}</label>
                    </div>
                    <br/><br/>
                    <div class="ui toggle checkbox flt_pro" x-data="caseACocher" x-bind="racine" @change="$store.optionsRecherche.abonnes = $event.target.checked">
                        <input type="checkbox" name="flt_pro" tabindex="0">
                        <label>{{ $marque->nom }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Menu plein ecran (burger) -->
<x-portail.menu-plein-ecran />


<!-- Modal ajax 2018 !-->
<x-portail.modale nom="contenu" class="large modal_content_ajax" id="fullscreenModal">
    <div class="scrolling content">
        <div class="ui active dimmer">
            <div class="ui medium loader"></div>
        </div>
        <br><br><br><br><br><br>
    </div>
</x-portail.modale>
<!-- Modal #end !-->


<!-- Modal ajax 2018 contact card !-->
<x-portail.modale nom="contact" class="large modal_content_ajax_contact">
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

</x-portail.modale>
<!-- Modal #end !-->















<!-- Modal connexion -->
{{-- Ecran de connexion plein ecran, sur le modele de la modale de Tesli :
     illustration a gauche (masquee sur mobile), formulaire a droite.
     Etats : resources/js/portail/connexion.js. --}}
<x-portail.modale nom="connexion" class="connexion_plein" :ouverte="session('connexion_ouverte', false)">
    <div class="connexion" x-data="connexion">

        <button type="button" class="btn_close connexion_fermer" aria-label="{{ __('Fermer') }}">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>

        <div class="connexion_visuel">
            <img src="/img_admin/diffusion-b.svg" alt="" width="400" height="400" loading="lazy">
        </div>

        <div class="connexion_panneau">
            <div class="connexion_colonne">
                <img class="connexion_logo" src="{{ $marque->logo }}" alt="{{ $marque->nom }}">
                {{-- Pas de <h1> : la fenetre est incluse dans toutes les pages. --}}
                <p class="connexion_titre" role="heading" aria-level="2">{{ __('Connexion') }}</p>
                <p class="connexion_accroche">{{ __('Retrouvez votre book, vos messages et vos statistiques.') }}</p>

                <!-- connexion -->
                <div id="segment_connect" x-show="vue === 'connexion'">
                    {{-- Echec de connexion (ConnexionController, GoogleController) :
                         la fenetre est rouverte avec le message. --}}
                    @error('login')
                        <div class="ui negative message connexion_alerte" role="alert">
                            <div class="header">{{ __('Connexion impossible') }}</div>
                            <p>{{ $message }}</p>
                            {{-- Compte suspendu : le message, et rien d'autre.
                                 Reinitialiser son mot de passe ne debloque
                                 rien, et proposer « Créer un book » invite a
                                 contourner la suspension. --}}
                            @unless (session('connexion_suspendue'))
                                <p>
                                    <a href="#" @click.prevent="vue = 'mdp'">{{ __('Réinitialiser mon mot de passe') }}</a>
                                    ·
                                    <a href="{{ lien('inscription.page') }}">{{ __('Créer un book') }}</a>
                                </p>
                            @endunless
                        </div>
                    @enderror

                    @if (\App\Models\Reglage::googleActif())
                        <x-portail.bouton-google class="creer_book_google" />

                        <div class="creer_book_ou"><span>{{ __('ou avec votre mot de passe') }}</span></div>
                    @endif

                    <form action="/ubaction__user_open" id="login_form" method="post" class="ui form creer_book_form" @submit.prevent="connecter($el)" novalidate>
                        @csrf
                        <input type="hidden" name="g-recaptcha-response">
                        <div class="field" :class="{ error: erreurLogin }">
                            <input id="login" type="text" name="login" value="{{ old('login') }}"
                                   placeholder="{{ __('Identifiant ou e-mail') }}" aria-label="{{ __('Identifiant ou e-mail') }}"
                                   autocomplete="username" @input="erreurLogin = ''">
                            <div class="ui basic red pointing prompt label" :class="{ show: erreurLogin }" x-text="erreurLogin"></div>
                        </div>
                        <div class="field" x-data="{ visible: false }">
                            <div class="ui icon input">
                                <input id="pass" :type="visible ? 'text' : 'password'" type="password" name="pass" value=""
                                       placeholder="{{ __('Mot de passe') }}" aria-label="{{ __('Mot de passe') }}"
                                       autocomplete="current-password">
                                <i class="link icon" :class="visible ? 'eye slash' : 'eye'" @click="visible = ! visible"
                                   :title="visible ? @js(__('Masquer')) : @js(__('Afficher'))"></i>
                            </div>
                        </div>
                        <div class="connexion_actions">
                            <button class="ui black button valider_submit_login" type="submit">{{ __('Se connecter') }}</button>
                            <a id="btn_mdp_forget" href="#" @click.prevent="vue = 'mdp'">{{ __('Mot de passe oublié ?') }}</a>
                        </div>
                    </form>
                </div>

                <!-- mot de passe oublie : envoi en cours -->
                <div class="ui inverted dimmer" id="segment_loader_mdp" :class="{ active: chargement }">
                    <div class="ui loader"></div>
                </div>

                <!-- mot de passe oublie : formulaire -->
                <div id="segment_mdp" x-show="vue === 'mdp'" x-cloak>
                    <h4>{{ __('Récupérer mon mot de passe') }}</h4>
                    <p class="connexion_accroche">{{ __('Indiquez l’adresse mail de votre compte : vous recevrez un lien pour choisir un nouveau mot de passe.') }}</p>
                    <form id="mdp_form" class="ui form" @submit.prevent="demanderMotDePasse($el)" novalidate>
                        @csrf
                        <input type="hidden" name="form_action" value="form_valide">
                        <input type="hidden" name="form_id" value="form_mdpoublie">
                        <input type="hidden" name="action" value="form">
                        <div class="field" :class="{ error: erreurMail }">
                            <div class="ui left icon input">
                                <input id="us_mail_mdp" type="email" name="us_mail" value=""
                                       placeholder="{{ __('Indiquer votre mail') }}" @input="erreurMail = ''">
                                <i class="mail icon"></i>
                            </div>
                            <div class="ui basic red pointing prompt label" :class="{ show: erreurMail }" x-text="erreurMail"></div>
                        </div>
                        <div class="connexion_actions">
                            <button class="ui black button valider_submit_mdp" type="submit">{{ __('Valider') }}</button>
                            <a href="#" class="btn_back_mdptologin" @click.prevent="vue = 'connexion'">{{ __('Retour à la connexion') }}</a>
                        </div>
                    </form>
                </div>

                <!-- mot de passe oublie : resultat -->
                <div id="segment_mdp_showOk" x-show="vue === 'resultat'" x-cloak>
                    <div class="ui info message" x-show="! resultat.erreur">
                        <div class="header" x-text="resultat.titre"></div>
                        <p x-text="resultat.texte"></p>
                    </div>
                    <div class="ui negative message" x-show="resultat.erreur">
                        <div class="header" x-text="resultat.titre"></div>
                    </div>
                    <a href="#" class="btn_back_mdp" @click.prevent="vue = 'connexion'">{{ __('Retour à la connexion') }}</a>
                </div>
            </div>

            {{-- Tout en bas de la colonne, comme sur « Creer un book ». --}}
            <p class="connexion_inscription" x-show="vue === 'connexion'" x-cloak>
                {{ __('Pas encore de book ?') }}
                <a href="{{ lien('inscription.page') }}">{{ __('Créer un book') }}</a>
            </p>
        </div>
    </div>
</x-portail.modale>

{{-- Proposition de compte visiteur, ouverte au premier coeur d'un anonyme
     ($store.memo.proposerCompte). Adresse + mot de passe : le compte est
     cree, connecte, et la selection du navigateur y est versee.
     Etats : resources/js/portail/memo-compte.js. --}}
@if (! auth('web')->check() && ! auth('visitor')->check())
<x-portail.modale nom="memo-compte" class="connexion_plein">
    <div class="connexion" x-data="memoCompte">

        <button type="button" class="btn_close connexion_fermer" aria-label="{{ __('Fermer') }}">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>

        <div class="connexion_visuel">
            <img src="/img_admin/diffusion-b.svg" alt="" width="400" height="400" loading="lazy">
        </div>

        <div class="connexion_panneau">
            <div class="connexion_colonne">
                <img class="connexion_logo" src="{{ $marque->logo }}" alt="{{ $marque->nom }}">
                <p class="connexion_titre" role="heading" aria-level="2">{{ __('Gardez votre mémoBook') }}</p>
                <p class="connexion_accroche">{{ __('Book ajouté à votre sélection. Indiquez votre e-mail et un mot de passe pour la retrouver sur tous vos appareils et l’exporter en PDF.') }}</p>

                <div class="ui negative message connexion_alerte" role="alert" x-show="erreur" x-cloak>
                    <p x-text="erreur"></p>
                    <p x-show="existe"><a href="#" @click.prevent="$store.modale.ouvrir('connexion')">{{ __('Me connecter') }}</a></p>
                </div>

                <form class="ui form creer_book_form" @submit.prevent="creer($el)" novalidate>
                    <div class="field">
                        <input type="email" name="email" placeholder="{{ __('Adresse e-mail') }}" aria-label="{{ __('Adresse e-mail') }}"
                               autocomplete="email" required @input="erreur = ''">
                    </div>
                    <div class="field" x-data="{ visible: false }">
                        <div class="ui icon input">
                            <input :type="visible ? 'text' : 'password'" type="password" name="password"
                                   placeholder="{{ __('Mot de passe (8 caractères minimum)') }}" aria-label="{{ __('Mot de passe') }}"
                                   autocomplete="new-password" minlength="8" required @input="erreur = ''">
                            <i class="link icon" :class="visible ? 'eye slash' : 'eye'" @click="visible = ! visible"
                               :title="visible ? @js(__('Masquer')) : @js(__('Afficher'))"></i>
                        </div>
                    </div>
                    <div class="connexion_actions">
                        <button class="ui black button" type="submit" :class="{ loading: envoi }" :disabled="envoi">{{ __('Enregistrer mon mémoBook') }}</button>
                        <a href="#" @click.prevent="$store.modale.fermer()">{{ __('Plus tard') }}</a>
                    </div>
                </form>

                <p class="connexion_accroche">
                    {{ __('Déjà un compte ?') }}
                    <a href="#" @click.prevent="$store.modale.ouvrir('connexion')">{{ __('Connexion') }}</a>
                </p>
            </div>
        </div>
    </div>
</x-portail.modale>
@endif
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

{{-- Google Maps n'est utilise par aucune vue servie (seuls les anciens
     gabarits morts de book/model_old y faisaient appel) : ni cle
     configuree, ni carte affichee. Charger ce script sur chaque page du
     portail causait une erreur bloquee par les bloqueurs de pub
     (gen_204?csp_test=true). A retirer completement une fois model_old
     supprime, ou a reintroduire seulement sur la page qui en aura besoin. --}}



<!-- stats book en JS -->
<div id="ub_us_stats_book" class="hide"></div>


	{{-- Donnees structurees communes a tout le portail : l'organisation et le
	     site, avec sa recherche interne (SearchAction). Les pages y ajoutent
	     les leurs (@stack('jsonld')). --}}
	@php
	    $racine = rtrim($marque->canonique, '/');
	    $graphe = [
	        '@context' => 'https://schema.org',
	        '@graph' => [
	            [
	                '@type' => 'Organization',
	                '@id' => $racine.'/#organisation',
	                'name' => $marque->nom,
	                'url' => $racine.'/',
	                'logo' => $racine.$marque->logo,
	                'email' => $marque->email,
	                'description' => $marque->description(),
	                'sameAs' => $marque->estDefaut() ? [
	                    'https://www.facebook.com/ultrabook.fr/',
	                    'https://www.instagram.com/ultra.book/',
	                    'https://twitter.com/ultra_book',
	                    'https://www.pinterest.com/ultrabook001/',
	                ] : [],
	            ] + ($marque->estDefaut() ? ['foundingDate' => '2007'] : []),
	            [
	                '@type' => 'WebSite',
	                '@id' => $racine.'/#site',
	                'name' => $marque->nom,
	                'url' => $racine.'/',
	                'inLanguage' => $marque->langues,
	                'publisher' => ['@id' => $racine.'/#organisation'],
	                'potentialAction' => [
	                    '@type' => 'SearchAction',
	                    'target' => [
	                        '@type' => 'EntryPoint',
	                        'urlTemplate' => $racine.parse_url(lien('recherche'), PHP_URL_PATH).'?q={search_term_string}&type_recherche=mcles',
	                    ],
	                    'query-input' => 'required name=search_term_string',
	                ],
	            ],
	        ],
	    ];
	@endphp
	<script type="application/ld+json">{!! json_encode($graphe, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
	@stack('jsonld')















