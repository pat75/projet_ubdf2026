{{-- Pied de page du portail. --}}
<!-- footer -->
	

<!-- Footer -->
<div class="bloc_footer">
	<div class="ui container ">

		<div class="ui top attached button+">
			<div class="logo ">

				                    <img class="logo_normal" src="{{ $marque->logo }}" alt="{{ $marque->nom }}">
				
			</div>
		</div>

		<div class="ui grid left aligned stackable">

			<div class="row {{ $marque->estDefaut() ? 'five' : 'four' }} column space1">

				<div class="one olive+ column space2">
					<div class="ui segment basic space1">
						<div class="intro">
							Depuis 2007 {{ $marque->nom }} vous permet de créer votre portfolio, d’y ajouter vos images,
							légendes, liens web, textes de présentation, et surtout de personnaliser votre espace
							book. Les books sont classés par domaine, une selection est faite tous les trois mois
							par des professionnels.						</div>
					</div>
				</div>
				<div class="one green+ column space2">
					<div class="ui segment basic space1">
						<h5>Plateforme portfolio</h5>
						<div class="ui link list">

                            <!--<a class="item" href="https://www.ultra-book.pro/contact/" >Contact / Aide</a>-->
							                                <a class="item " href="mailto:{{ $marque->email }}?subject={{ rawurlencode('Aide '.$marque->nom) }}&body={{ rawurlencode('Indiquez l’adresse de votre portfolio, merci.') }}">
									Contact/aide<br/>{{ $marque->email }}
                                </a>
                            
                            <a class="item" href="/doc/">Documentation / Tuto</a>
							<a class="item" href="/doc/les-formules-ultra-book">Tarifs</a>
							<a class="item" href="/doc/questions-frequentes-2">Questions fréquentes</a>
							<a class="item" href="/doc/qui-sommes-nous">Qui sommes nous ?</a>
							<a class="item" href="/doc/mentions-legales">Mentions légales</a>
						</div>

					</div>
				</div>
								<div class="one olive+ column space2">
					<div class="ui segment basic space1">
						<h5>Rubriques</h5>
						<div class="ui link list">
							<a class="item  btn_zoom" >Zoom</a>
                            <a class="item  btn_actu" >Tendances, Actualités</a>
							<a class="item" href="/accueil#bloc_ultrabook_href" >Derniers Ultra-book</a>

                            <a class="item" href="http://www.ultra-book.fr/ecoles/"><span class="fonticon-arrow-right icon"></span> Annuaire des écoles</a>

                            <a class="item" href="https://www.les-illustrateurs.com" title="Les Illustrateurs"><span class="fonticon-arrow-right icon"></span>Illustrateurs freelances</a>
                            <a class="item" href="/meilleurs-graphistes"><span class="fonticon-arrow-right icon"></span>Graphistes freelances</a>
                            <a class="item" href="/webdesigner-freelance"><span class="fonticon-arrow-right icon"></span>Webdesigners freelances</a>
                            <a class="item" href="/developpeur-freelance"><span class="fonticon-arrow-right icon"></span>Développeurs freelances</a>
            			</div>
					</div>
				</div>
			@if ($marque->estDefaut())
					{{-- Reserve a Ultra-book : les autres sites de la societe,
						 sans equivalent chez Dustfolio. --}}
					<div class="one column space2">
						<div class="ui segment basic space1">
							<h5>Nos autres sites</h5>
							<div class="ui link list">
								<a class="item" href="https://www.tesli.fr" target="_blank" rel="noopener">Tesli<br/>Créez votre site en un clic, par IA</a>
								<a class="item" href="https://www.la-belle-illustration.fr" target="_blank" rel="noopener">La Belle Illustration<br/>La boutique d’illustrations à vendre</a>
								<a class="item" href="https://www.les-illustrateurs.fr" target="_blank" rel="noopener">UB-diffusion<br/>La plateforme créative pour vendre vos créations</a>
							</div>
						</div>
					</div>
			@endif
                				<div class="one column space2">
					<div class="ui segment basic space1">
						<h5>Newsletter</h5>

						<div class="newsletter" x-data="newsletter">
							<div>Les dernières sélections du mois</div>
							<div class="no-spam">Confidentialité, sécurité et absence de spam</div>

							<form class="ui form form_newsletter_2018" action="{{ route('newsletter.inscription') }}" @submit.prevent="envoyer($el)">

								@csrf

								<div class="ui  action mini input">
									<input type="text" name="mail" placeholder="Mail...">
									<button class="ui inverted+ icon button">
										<i class="envelope outline icon"></i>
									</button>
								</div>
							</form>
							<div class="retour" x-show="message" x-transition.opacity.duration.300ms x-cloak><span :style="{ color: erreur ? 'red' : 'lightgreen' }" x-text="message"></span></div>
						</div>

												<!-- partage -->
						<div class="partage ">
							<a href="https://www.instagram.com/ultra.book/"><span class="fonticon-uniF05E "></span></a>
							<a href="https://www.facebook.com/ultrabook.fr"><span class="fonticon-uniF051 "></span></a>
							<a href="https://twitter.com/ultra_book"><span class="fonticon-uniF057 "></span></a>
							<a target="_blank" href="https://www.pinterest.com/ultrabook001/"
							   onclick="ga('send', 'pageview', '/link_pinterest');">
								<span class="fonticon-pinterest "></span>
							</a>
						</div>
                        <br/>
                        <a href="https://www.dustfolio.com/accueil" style="color:white"><span class="fonticon-arrow-right icon"></span> Dustfolio</a>
                        <br/>
                        <a href="https://www.creer-un-book.com" title="Comment créer un book" style="color:white"><span class="fonticon-arrow-right icon"></span> Créez un book</a>
                        
					</div>
				</div>

			</div>

		</div>
	</div>
</div>
<!-- Footer #end-->




	<!-- cookies -->
	<div id="cookie-policy" x-data="bandeauCookies" :class="{ show: visible }" @click="accepter">
		<div class="btn_close close">
			<div></div>
		</div>
		<div class="cookiepolicy-message">
			<p>Nous utilisons des cookies pour améliorer notre site et votre expérience de navigation.
				En utilisant notre site, vous acceptez notre politique de cookies.				<span><a target="_blank" href="#">En savoir plus</a></span>
			</p>
		</div>
	</div>


</div>







