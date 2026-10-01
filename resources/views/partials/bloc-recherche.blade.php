{{-- Bloc de recherche de l'accueil (repris du front 2018), reutilise en tete des pages /recherche et /search ($ajax : resultats charges sous le bloc) : x-data="recherche" (resources/js/portail/recherche.js). --}}
<!-- Recherche  -->
	<div class="ui  container bloc_rechercher " x-apparition>
		<div class="ui two column stackable center aligned grid segment" id="bloc_rechercher">

			<div class="row one column ">
				<div class="column aligned">
					<div class="ui segment basic ">
						<{{ $niveauTitre ?? 'h1' }}>{{ __('Trouvez les meilleurs portfolios de créatifs.') }}</{{ $niveauTitre ?? 'h1' }}>
						@if ($definition ?? false)
							{{-- Phrase de definition sous le h1 : le passage que les moteurs
							     et les assistants IA reprennent pour presenter le site. --}}
							<p class="accueil_definition">{{ __('Depuis 2007, Ultra-book réunit les portfolios de plus de 58 000 illustrateurs, graphistes, photographes et designers indépendants. Chaque trimestre, des professionnels en sélectionnent les meilleurs : parcourez leurs books et contactez directement le créatif qui vous correspond.') }}</p>
						@endif
					</div>
				</div>
			</div>

			<div class="row one column ">
				<div class="column recherche_nom">

					<div class="ui segment basic left aligned">
						<form action="{{ empty($ajax) ? '/recherche' : lien('search') }}" class="form_rechercher2018" x-data="recherche" @submit.prevent="envoyer($el)" @if (! empty($ajax)) data-ajax @endif>
							<div class="ui action input search category rech2018" @click.outside="resultats = []">
								<div class="ui left icon input">
									<i class="search big icon"></i>
									<input class="prompt" x-ref="champ" type="text" name="q" value="{{ $recherche->q ?? '' }}" required autocomplete="off" x-init="requete = $el.value" x-model="requete" @input.debounce.50ms="chercher()" @keydown.escape="resultats = []" @scroll="placerVider()">
									<span class="floating-label mobile-hidden">{{ __('Essayez : "Métier : illustration" ou "Mots clés : publicité" ou "Nom"...') }}</span>
                                    <span class="floating-label mobile only">{{ __('Métier, mots clés ou Nom...') }}</span>
                                    <input type="hidden" name="type_recherche" value="">
                                    {{-- Vide le champ ; place a 20 px apres le dernier caractere saisi (placerVider, recherche.js). --}}
                                    <button type="button" class="vider_recherche" x-ref="vider" x-show="requete" x-cloak x-effect="requete; $nextTick(() => placerVider())"
                                            @click="vider($el.closest('form'))" aria-label="{{ __('Effacer la recherche') }}">
                                        <svg viewBox="0 0 14 14" width="14" height="14" aria-hidden="true"><path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                    </button>
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
