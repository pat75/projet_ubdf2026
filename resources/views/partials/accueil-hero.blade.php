{{-- Blocs d'en-tete propres a la page d'accueil, repris du front 2018 :
     video et titre, bloc de recherche, derniers mots-cles, « Creer votre
     portfolio », « Une selection de qualite », « Installer mon site
     internet pro » et la banniere des disponibilites.

     Ils n'apparaissent pas sur les pages de metier ni sur l'annuaire :
     ce sont des accroches d'accueil, qui feraient doublon ailleurs. --}}

<!-- Slides UB -->
    <style>

            .bloc_slide_video {
                height: 480px !important;
                background-image: linear-gradient(to right, rgba(0, 129, 199, 0.65), rgba(0, 49, 114, 0.82));
            }

            .video_header {
                display: block;
                height: 480px;
                overflow-x: hidden;
                overflow-y: hidden;
                position: relative;
            }
            .video_source {
                position: absolute;
                /* Recadree pour couvrir tout le bloc plutot que d'etre
                   decalee de -260px avec une hauteur libre, qui la laissait
                   coupee sur une partie du fond bleu. */
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .video_bg {
                background-size: cover;
                position: absolute;
                width:100%;
                height: 480px;
                overflow-x: hidden;
                overflow-y: hidden;
                background-image: linear-gradient(to right, rgba(1, 143, 220, 0.65), rgba(0, 88, 150, 0.82));
                background-image: linear-gradient(to right, rgba(0, 129, 199, 0.65), rgba(0, 49, 114, 0.82));
            }
            /*
             | Dustfolio : illustration fixe et accroche a la place de la
             | video.
             |
             | L'illustration est posee a droite, sans recadrage — c'est un
             | dessin, le couper le mutile, contrairement a une video de
             | fond. Le degrade du conteneur reste visible derriere elle.
             */
            .illustration_header {
                object-fit: contain;
                object-position: right center;
                left: auto;
                right: 0;
                width: 58%;
            }
            .accroche_header {
                /* Le degrade est deja porte par .bloc_slide_video : le
                   repeter ici masquerait l'illustration. */
                background-image: none;
                display: flex;
                flex-direction: column;
                justify-content: center;
                max-width: 960px;
                left: 0;
                right: 0;
                margin: auto;
                padding: 0 24px;
                align-items: flex-start;
                text-align: left;
            }
            .accroche_surtitre {
                color: #fff;
                font-family: 'Lato', sans-serif;
                font-size: 22px;
                font-weight: 400;
                letter-spacing: 0.22em;
                text-transform: uppercase;
                opacity: 0.85;
            }
            .accroche_titre {
                color: #fff;
                font-family: 'Lato', sans-serif;
                font-size: 52px;
                font-weight: 700;
                line-height: 1.12;
                margin: 14px 0 0 0;
                max-width: 560px;
            }
            .accroche_soustitre {
                color: #fff;
                font-family: 'Lato', sans-serif;
                font-size: 26px;
                font-weight: 300;
                margin-top: 12px;
                max-width: 560px;
            }
            .accroche_bouton {
                margin-top: 34px !important;
                background-color: #fff !important;
                color: #02187C !important;
            }
            /* L'en-tete de Dustfolio porte quatre elements au lieu de deux :
               il lui faut plus de hauteur qu'a celui d'Ultra-book. */
            body.marque_df .bloc_slide_video,
            body.marque_df .video_header,
            body.marque_df .video_bg {
                height: 560px !important;
            }
            /* core.css peint un fond presque noir sur .video_header. La
               video le recouvrait entierement ; l'illustration, elle, est
               ajustee sans recadrage et le laissait apparaitre. Le fond
               redevient transparent pour laisser voir le degrade de la
               marque porte par le conteneur. */
            body.marque_df .video_header {
                background-color: transparent !important;
            }
            body.marque_df #bloc_rechercher {
                margin-top: 40px;
                margin-bottom: 40px;
            }

            @@media only screen and (max-width: 980px) {
                /* Sous cette largeur l'illustration ne laisse plus de place
                   au texte : l'accroche passe seule, sur le degrade. */
                .illustration_header {
                    display: none;
                }
                .accroche_titre { font-size: 34px; }
                .accroche_soustitre { font-size: 19px; }
                .accroche_header { text-align: center; align-items: center; }
            }

            .video_titre {
                max-width:960px;
                padding:90px 0 0 0;
                color: white;
                z-index: 10;
                margin: auto;
                font-size:78px;
                font-family: 'Lato',sans-serif;
                font-weight: 400;
            }
            .video_soustitre {
                max-width:960px;
                margin: auto;
                padding:48px 0 0 0;
                font-size:24px;
                font-family: 'Lato',sans-serif;
                font-weight: 400;
                color: white;
                z-index: 10;
                opacity: 0.8;
            }



             #bloc_rechercher h1 {
                font-size: 30px!important;
                text-align: left;
                margin-left:38px;

            }

            /* La phrase de definition demarre exactement sous le titre. */
             #bloc_rechercher .accueil_definition {
                margin-left: 38px;
            }

             #bloc_rechercher {
                margin-left: 20px;
                margin-right: 20px;
            }

            /* Accueil : bloc "Trouvez les meilleurs portfolios de creatifs"
               - remonte pour venir juste sous la ligne "Illustration, graphisme,
                 design, photo et plasticien" du header video
               - fond blanc a 80% (20% de transparence) : uniquement la couleur de
                 fond, le contenu (titre, champ de recherche) reste opaque
               - le remontage (-100px) est compense par un margin-bottom de +100px
                 pour que le bloc des mots-cles (illustrateur, bande dessinee...)
                 reste a sa place, sous le bloc video du header
               Desktop uniquement : en dessous de 980px le header video est reduit
               et les titres sont masques (voir media query plus bas). */
            @@media only screen and (min-width: 981px) {
                /*
                 | Remontee reservee a Ultra-book, dont l'en-tete ne porte
                 | qu'un titre et un sous-titre : le bloc de recherche vient
                 | s'y superposer.
                 |
                 | Sur Dustfolio l'en-tete porte une accroche **et** un
                 | bouton d'appel a l'action. Les superposer masquerait le
                 | bouton — c'est ce que la premiere capture montrait.
                 */
                body.marque_ub #bloc_rechercher {
                    /* Mesure dans le navigateur : le bloc video occupe
                       480px, le sous-titre se termine a 269px. Un remontage
                       de 260px place le haut du bloc a 305px, soit 36px sous
                       le sous-titre, et sa base 31px avant la fin du fond
                       bleu — le calage de la maquette de reference.
                       C'est la valeur du front 2018, qui ne s'appliquait pas
                       tant que la media query etait invalide. */
                    /* -290px : remonte de 30px de plus que le calage d'origine. */
                    margin-top: -290px !important;
                    /* Compense le remontage pour que le bloc des mots-cles
                       reste sous l'en-tete video. */
                    margin-bottom: 100px !important;
                    /* Opacite de 80 % sur la seule couleur de fond : elle
                       porte sur le canal alpha du blanc, pas sur la
                       propriete « opacity », qui aurait aussi affaibli le
                       titre et le champ de recherche. */
                    background-color: rgba(255, 255, 255, 0.8) !important;
                    /* 10 % plus etroit que la largeur d'origine (conteneur
                       moins 2 x 20px), centre. */
                    width: calc((100% - 40px) * 0.9);
                    margin-left: auto !important;
                    margin-right: auto !important;
                }

                /* Bloc 10 % moins haut (265px -> 239px) : 26px pris sur le
                   vide sous le champ de recherche. */
                body.marque_ub #bloc_rechercher > .row:last-child {
                    padding-bottom: 0 !important;
                }
                body.marque_ub #bloc_rechercher > .row:last-child > .column > .segment {
                    padding-bottom: 2px !important;
                }

                /* En-tete video 10 % plus haut : 480px -> 528px. Le bloc de
                   recherche garde son remontage de 260px, il reste donc cale
                   au meme endroit par rapport au bas de la video. */
                body.marque_ub .bloc_slide_video,
                body.marque_ub .video_header,
                body.marque_ub .video_bg,
                body.marque_ub .video_source {
                    height: 528px !important;
                }
            }

            /* Accueil : barre de recherche (champ + bouton "Rechercher")
               - suppression du filet entre le champ et le bouton : l'arrondi
                 complet du champ (border-radius 7000px) et la bordure blanche
                 de 6px du bouton laissaient voir le fond entre les deux
               - le fond blanc et l'arrondi sont portes par le conteneur
                 .ui.action, le champ devient transparent
               - champ et bouton etires sur toute la hauteur du conteneur :
                 meme hauteur et meme alignement vertical
               Desktop uniquement, comme le bloc ci-dessus. */
            @@media only screen and (min-width: 981px) {

                #bloc_rechercher .form_rechercher2018 .ui.action {
                    background-color: #fff;
                    align-items: stretch;
                    /* arrondi de 8px sur les quatre coins de la barre */
                    border-radius: 8px !important;
                }

                #bloc_rechercher .form_rechercher2018 .ui.action .ui.input {
                    align-items: stretch;
                }

                #bloc_rechercher .form_rechercher2018 .ui.action input.prompt {
                    margin-top: 0 !important;
                    /* 8px en haut, 0 en bas : le texte saisi descend de 4px
                       (box-sizing border-box, hauteur fixe). */
                    padding-top: 8px !important;
                    padding-bottom: 0 !important;
                    height: 68px;
                    /* arrondi de 8px sur le bord gauche du champ */
                    border-radius: 8px 0 0 8px !important;
                    background: transparent !important;
                }

                #bloc_rechercher .form_rechercher2018 .ui.action .ui.button {
                    border: 0 !important;
                    /* arrondi de 8px sur le bord droit du bouton */
                    border-radius: 0 8px 8px 0 !important;
                    align-self: stretch;
                    height: 68px;
                }

                /* le libelle flottant suit la nouvelle hauteur du champ */
                #bloc_rechercher .form_rechercher2018 .ui.action .floating-label {
                    top: 50%;
                    transform: translateY(-50%);
                }
                #bloc_rechercher .form_rechercher2018 .ui.action input:focus ~ .floating-label,
                #bloc_rechercher .form_rechercher2018 .ui.action input:not(:focus):valid ~ .floating-label {
                    /* 6px (et non 12px) : le libelle remonte pour ne pas
                       chevaucher le texte saisi. */
                    top: 6px;
                    transform: none;
                }
            }


            @@media only screen and (max-width: 1280px) {
                .video_source {
                    /* Corrigeait le decalage de -260px du front 2018.
                       La video etant desormais recadree en couverture,
                       elle reste calee sur le haut du bloc. */
                    top: 0 !important;
                }
            }


            @@media only screen and (max-width: 980px) {

                [class*="mobile_hidden"] {
                    display: none !important;
                }

                .bloc_slide_video {
                    margin-bottom: -30px!important;
                    height: 320px!important;
                }

                .video_header {
                    height: 380px!important;
                }

                .video_source {
                    /* Corrigeait le decalage de -260px du front 2018.
                       La video etant desormais recadree en couverture,
                       elle reste calee sur le haut du bloc. */
                    top: 0 !important;
                }
                .video_titre, .video_soustitre {
                    display:none;
                }
            }

        </style>

    <div class="ui container bloc_slide mobile bloc_slide_video ">

		<!-- Slider main container -->
		<div class="ui active loader hidden"></div>

        <header class="video_header">
            @if ($marque->estDefaut())
                {{-- Ultra-book : la video d'origine du front 2018. --}}
                <video autoplay loop muted playsinline webkit-playsinline
                       class="video_source" id="myVideo">
                        <source src="/_video/crea3.mov" type="video/mp4">
                </video>

                <div class="video_bg">
                    <div class="video_titre" x-apparition>Une mine de créatifs</div>
                    <div class="video_soustitre" x-apparition.150>Illustration, graphisme, design, photo et plasticien</div>
                </div>
            @else
                {{-- Dustfolio : une illustration fixe a la place de la video.
                     Elle pese 150 Ko contre 2,9 Mo pour la video, se
                     redimensionne sans perte, et n'impose pas un
                     telechargement automatique au visiteur. --}}
                <img class="video_source illustration_header"
                     src="{{ $marque->asset('accueil-freelance.svg') }}" alt="">

                <div class="video_bg accroche_header">
                    <div class="accroche_surtitre" x-apparition>{{ __('freelance') }}</div>
                    <h2 class="accroche_titre" x-apparition.100>{{ __('Créez gratuitement votre portfolio') }}</h2>
                    <div class="accroche_soustitre" x-apparition.200>{{ __('Diffusez-le et proposez vos services') }}</div>

                    <a x-apparition:zoom.300 class="ui huge right labeled icon button accroche_bouton cursor_effect btn_modal_creerbook_mdl" href="{{ lien('inscription.page') }}">
                        <i class="right arrow icon"></i>
                        {{ __('Créer un book') }}
                    </a>
                </div>
            @endif
        </header>

	</div>


@include('partials.bloc-recherche', ['definition' => $marque->estDefaut()])

    


@if ($accueilBlocs['mots_cles'] ?? true)
	<!-- Last recherche -->


    <div class="ui container bloc_last_recherche mobile_hidden" x-apparition.100>
        <div class="ui grid">
	                <div class="row">

                <div class="two wide column">
                    <p class="t-h4">illustration</p>
                </div>

                <div class="fourteen wide column">
                                    <a class="ui large basic label cursor_effect  coul_illustration" data-slug="illustration,illustration" href="{{ lien('recherche', ['q' => 'illustration,illustration', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>illustrateur</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_illustration" data-slug="illustration,bande dessinée" href="{{ lien('recherche', ['q' => 'illustration,bande dessinée', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>bande dessinée</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_illustration" data-slug="illustration,jeunesse" href="{{ lien('recherche', ['q' => 'illustration,jeunesse', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>jeunesse</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_illustration" data-slug="illustration,comics" href="{{ lien('recherche', ['q' => 'illustration,comics', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>comics</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_illustration" data-slug="illustration,presse" href="{{ lien('recherche', ['q' => 'illustration,presse', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>presse</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_illustration" data-slug="illustration,publicité" href="{{ lien('recherche', ['q' => 'illustration,publicité', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>publicité</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_illustration" data-slug="illustration,personnages" href="{{ lien('recherche', ['q' => 'illustration,personnages', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>personnages</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_illustration" data-slug="illustration,iso" href="{{ lien('recherche', ['q' => 'illustration,iso', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>isométrie</strong>
                    </a>

                                </div>

            </div>
	                <div class="row">

                <div class="two wide column">
                    <p class="t-h4">graphisme</p>
                </div>

                <div class="fourteen wide column">
                                    <a class="ui large basic label cursor_effect  coul_graphisme" data-slug="graphisme,graphisme" href="{{ lien('recherche', ['q' => 'graphisme,graphisme', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>graphistes</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_graphisme" data-slug="graphisme,da" href="{{ lien('recherche', ['q' => 'graphisme,da', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>directeur artistique</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_graphisme" data-slug="graphisme,print" href="{{ lien('recherche', ['q' => 'graphisme,print', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>print</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_graphisme" data-slug="graphisme,communication" href="{{ lien('recherche', ['q' => 'graphisme,communication', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>communication</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_graphisme" data-slug="graphisme,logo" href="{{ lien('recherche', ['q' => 'graphisme,logo', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>logo</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_graphisme" data-slug="graphisme,flyer" href="{{ lien('recherche', ['q' => 'graphisme,flyer', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>flyer</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_graphisme" data-slug="graphisme,brochure" href="{{ lien('recherche', ['q' => 'graphisme,brochure', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>brochure</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_graphisme" data-slug="graphisme,maquettiste" href="{{ lien('recherche', ['q' => 'graphisme,maquettiste', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>maquettiste</strong>
                    </a>

                                </div>

            </div>
	                <div class="row">

                <div class="two wide column">
                    <p class="t-h4">digital</p>
                </div>

                <div class="fourteen wide column">
                                    <a class="ui large basic label cursor_effect  coul_digital" data-slug="digital,web" href="{{ lien('recherche', ['q' => 'digital,web', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>web, site internet</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_digital" data-slug="digital,UX designer" href="{{ lien('recherche', ['q' => 'digital,UX designer', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>UX designer</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_digital" data-slug="digital,digital UI Designer" href="{{ lien('recherche', ['q' => 'digital,digital UI Designer', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>UI designer</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_digital" data-slug="digital,directeur artistique digital" href="{{ lien('recherche', ['q' => 'digital,directeur artistique digital', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>directeur artistique web</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_digital" data-slug="digital,développeur wordpress" href="{{ lien('recherche', ['q' => 'digital,développeur wordpress', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>développeur wordpress</strong>
                    </a>

                                </div>

            </div>
	                <div class="row">

                <div class="two wide column">
                    <p class="t-h4">photo</p>
                </div>

                <div class="fourteen wide column">
                                    <a class="ui large basic label cursor_effect  coul_photo" data-slug="photo,presse" href="{{ lien('recherche', ['q' => 'photo,presse', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>presse</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_photo" data-slug="photo,portrait" href="{{ lien('recherche', ['q' => 'photo,portrait', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>portrait</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_photo" data-slug="photo,architecture" href="{{ lien('recherche', ['q' => 'photo,architecture', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>architecture</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_photo" data-slug="photo,culinaire" href="{{ lien('recherche', ['q' => 'photo,culinaire', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>culinaire</strong>
                    </a>

                                    <a class="ui large basic label cursor_effect  coul_photo" data-slug="photo,corporate" href="{{ lien('recherche', ['q' => 'photo,corporate', 'type_recherche' => 'mcles']) }}">
                        <i class="chevron right icon"></i><strong>corporate / entreprise</strong>
                    </a>

                                </div>

            </div>
	    

        </div>
    </div>


@endif
@if ($accueilBlocs['creer_portfolio'] ?? true)
    <!-- Modeles 2020 -->
    <div class="ui container bloc_slide mobile bloc_accueil_modele2020">
        <div class="ui middle aligned two column stackable grid">
                <div class="right aligned column " x-apparition:gauche>
                    <p class="t-h5">{{ __('Freelances') }}</p>
                    <h2>{{ __('Créer votre portfolio') }}</h2>
                    <p class="t-h4">{{ __('Chargez vos images par glisser-poser') }}</p>
                    <p class="t-h4">{{ __('Modifiez l’apparence, et diffusez') }}</p>
                    <a class="ui black basic  right labeled icon button mobile-hidden cursor_effect btn_modal_creerbook_mdl" href="{{ lien('inscription.page') }}">
                        <i class="right arrow icon"></i>
                        {{ mb_strtoupper(__('Créez un book')) }}</a>
                </div>
                <div class=" center aligned olive+ column col_visuel_mdl_book" x-apparition:droite.150>
                    <div class="accueil_mdl_book_visuel"></div>
                </div>
        </div>
    </div>
    <!-- Modeles 2020 #end -->






@endif
@if ($accueilBlocs['selection_qualite'] ?? true)
    <!-- entreprises 2020-->
    <div class="ui container bloc_slide mobile bloc_accueil_entreprise2020_stats">

        <div class="ui middle aligned two column stackable grid">

            <div class="right aligned olive+ column " x-apparition:gauche>
                <img src="/img_front/accueil_entreprise_consult-b.svg" alt="freelance, entreprise">
            </div>

            <div class="left aligned olive+ column" x-apparition:droite.150>
                <p class="t-h5">{{ __('Entreprises') }}</p>
                <h2>{{ __('Une sélection de qualité') }}</h2>

                <div class="accueil_stats ">

                    <div class="stats_nbselection">
                        <p class="t-h3">58 133</p>
                        <p class="t-h6">{{ __('Books actifs') }}</p>
                    </div>

                    <div class="stats_nbbook">
                        <p class="t-h3">4 325</p>
                        <p class="t-h6">{{ __('Books sélectionnés') }}</p>
                    </div>

                </div>


                <p class="t-h4">{{ __('Trouver et contacter les meilleurs créatifs freelances !') }}</p>

                <div class="ui black  buttons ubdf_principes">
                    <div class="ui button right pointing label">
                        <i class="heart icon"></i>
                        {{ __('Sélectionnez un créatif') }}</div>
                    <div class="ui button right pointing label">
                        <i class="paper plane icon"></i>
                        {{ __('Envoyez votre demande') }}</div>
                    <div class="ui button label">
                        <i class="rocket icon"></i>
                        {{ __('Validez et démarrez un projet') }}</div>
                </div>

            </div>

        </div>
    </div>
    <!-- entreprises 2020 #end -->




	



@endif
@if ($marque->estDefaut())
    @if ($accueilBlocs['site_pro'] ?? true)
    {{-- Reserve a Ultra-book : ce bloc renvoie vers ultrabook.pro et
         les-illustrateurs.com, deux services de la marque Ultra-book qui
         n'ont pas d'equivalent chez Dustfolio. --}}
    <!-- ubsite Pro 2022 -->
    <a href="https://www.ultrabook.pro/?utm_source=ubaccueil" target="_blank">
        <div class="ui container bloc_slide mobile bloc_accueil_ubsitepro">
            <div class="ui middle aligned two column stackable grid">
                <div class="right aligned column " x-apparition:gauche>
                    <h5>Mon site web PRO</h5>

                    <h2>Installer <strong>mon site internet PRO</strong></h2>
                    <h4>Un vrai site complet, extensible et illimité, </h4>
                    <h4>installé sur votre nom de domaine et votre hébergement.</h4>
                    <h6 style="    margin-bottom: 8px;">Forfait installation, configuration et licence illimitée.</h6>

                    <button class="ui inverted basic right labeled icon button mobile-hidden cursor_effect">
                        <i class="right arrow icon" style="    background-color: blueviolet;"></i> Détail de l’offre                    </button>
                    <br/>
                    <br/>
                    <img src="https://www.ultrabook.pro/img/ub_logo_web_wp_2022.svg" class="visuel_avantages" alt="Ultra-book site PRO" loading="lazy">
                </div>

                <div class=" center aligned column col_visuel_mdl_book" x-apparition:droite.150>
                    <img src="https://www.ultrabook.pro/img/enplusconstruction.svg" alt="Ultra-book site PRO">
                </div>

            </div>
        </div>
    </a>
    <!-- #end -->
    @endif
@endif




@if ($marque->estDefaut())
    @if ($accueilBlocs['disponibilites'] ?? true)
    {{-- Reserve a Ultra-book : ce bloc renvoie vers ultrabook.pro et
         les-illustrateurs.com, deux services de la marque Ultra-book qui
         n'ont pas d'equivalent chez Dustfolio. --}}
    <!-- banniere dispo -->
    <style>
        /* Styles CSS */
        .pt-8 { padding-top: 2rem; }
        .pb-8 { padding-bottom: 2rem; }
        .max-lg\\:pb-1 { padding-bottom: 0.25rem; }
        .max-lg\\:pt-2 { padding-top: 0.5rem; }
        .relative { position: relative; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .max-w-7xl { max-width: 80rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .sm\\:static { position: static; }
        .sm\\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .lg\\:px-8 { padding-left: 2rem; padding-right: 2rem; }
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        .md\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .md\\:col-span-1 { grid-column: span 1 / span 1; }
        .md\\:col-span-2 { grid-column: span 2 / span 2; }
        .max-lg\\:ml-6 { margin-left: 1.5rem; }
        .max-h-32 { max-height: 8rem; }
        .md\\:max-h-60 { max-height: 15rem; }
        .max-lg\\:ml-4 { margin-left: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .max-lg\\:mb-4 { margin-bottom: 1rem; }
        .text-6xl { font-size: 3.75rem; }
        .max-lg\\:text-3xl { font-size: 1.875rem; }
        .font-thin { font-weight: 100; }
        .max-lg\\:font-normal { font-weight: 400; }
        .tracking-tight { letter-spacing: -0.025em; }
        .text-gray-900 { color: #111827; }
        .text-white { color: #fff; }
        .font-light { font-weight: 300; }
        .text-4xl { font-size: 2.25rem; }
        .leading-10 { line-height: 2.5rem; }
        .max-lg\\:text-lg { font-size: 1.125rem; }
        .text-black { color: #000; }
        .hidden { display: none; }
        .mt-2 { margin-top: 0.5rem; }
        .text-xl { font-size: 1.25rem; }
        .max-lg\\:text-base { font-size: 1rem; }

        a.dispo_link { color: black;  font-size: 24px;   }
        a.dispo_link:hover { color: white; }

        .dispo_new { color: #9dfff4; margin-bottom:-10px;line-height:10px; font-weight: 600;font-size: 32px}

        .dispo_bouton_arrondi {
            display: inline-block;
            padding: 6px 28px;
            border-radius: 28px;
            background-color: black;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 20px;
            margin-right: 10px;
        }

        .dispo_bouton_arrondi:hover {
            background-color: #444;
            color: white;
            transform: scale(1.01);
        }
        .dispo_desc {
            font-size: 20px;
        }
    </style>
    <header class="ui pt-4 md:pt-10 pb-10 mb-12 max-lg:mb-8 ubd-header shadow" style="padding: 90px 0 ;background-image: url('https://les-illustrateurs.com/_img/degrade.svg'); background-size: cover;">
        <div class="ui container">
            <div class="ui grid">
                <div class="ui four wide computer sixteen wide mobile column" x-apparition:gauche>
                    <img src="https://les-illustrateurs.com/_img/diffusion.svg" class="ui image" alt="Les illustrateurs/rices disponibles aujourd’hui">
                </div>
                <div class="ui twelve wide computer sixteen wide mobile column" x-apparition:droite.150>

                    <div class="dispo_new" >NOUVEAU !</div>
                    <h2  class="mb-6 max-lg:mb-4 text-6xl max-lg:text-3xl font-thin max-lg:font-normal tracking-tight text-gray-900">
                        Retrouver les illustrateurs/rices<div style="line-height: 42px;" class="text-white font-normal">disponibles aujourd’hui</div>
                    </h2>
                    <h2   class="text-4xl leading-10 max-lg:text-lg font-light max-lg:font-light text-black">
                        <a href="https://www.les-illustrateurs.com" class="dispo_bouton_arrondi">CONTACTER LES DISPOS</a>
                        <a href="https://www.les-illustrateurs.com" class="dispo_link">www.les-illustrateurs.com</a>

                        <h3 class="dispo_desc">
                            Le meilleur de la sélection Ultra-book des illustrateurs et illustratrices en fonction de leurs disponibilités
                        </h3>
                </div>
            </div>
        </div>
    </header>
    <!-- banniere dispo #end-->
    @endif
@endif
