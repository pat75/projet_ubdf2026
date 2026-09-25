{{-- Bloc de recherche de l'accueil (repris du front 2018), reutilise en tete de la page /recherche : x-data="recherche" (resources/js/portail/recherche.js). --}}
<!-- Recherche  -->
	<div class="ui  container bloc_rechercher " x-apparition>
		<div class="ui two column stackable center aligned grid segment" id="bloc_rechercher">

			<div class="row one column ">
				<div class="column aligned">
					<div class="ui segment basic ">
						<h1>{{ __('Trouvez les meilleurs portfolios de créatifs.') }}</h1>
					</div>
				</div>
			</div>

			<div class="row one column ">
				<div class="column recherche_nom">

					<div class="ui segment basic left aligned">
						<form action="/recherche" class="form_rechercher2018" x-data="recherche" @submit.prevent="envoyer($el)">
							<div class="ui action input search category rech2018" @click.outside="resultats = []">
								<div class="ui left icon input">
									<i class="search big icon"></i>
									<input class="prompt" type="text" name="q" value="" required autocomplete="off" x-model="requete" @input.debounce.50ms="chercher()" @keydown.escape="resultats = []">
									<span class="floating-label mobile-hidden">{{ __('Essayez : "Métier : illustration" ou "Mots clés : publicité" ou "Nom"...') }}</span>
                                    <span class="floating-label mobile only">{{ __('Métier, mots clés ou Nom...') }}</span>
                                    <input type="hidden" name="type_recherche" value="">
									<button type="submit" class="ui huge button submit_rechercher_accueil cursor_effect">
										{{ __('Rechercher') }}									</button>
								</div>

								<x-portail.resultats-recherche />
							</div>
							<div class="ui basic red pointing prompt label transition error_prompt " :class="{ show: erreurVide }">Indiquez un mot clé ou un nom</div>

						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
