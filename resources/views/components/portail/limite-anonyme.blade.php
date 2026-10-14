{{-- Fin d'une liste bornee pour un anonyme (App\Support\LimiteAnonyme) :
     une ligne fantome, floutee, signale la suite sans rien en reveler (aucune
     donnee de book), puis l'invitation a se connecter ou a ouvrir un compte
     visiteur. Les deux fenetres existent deja (partials/modals) ; la page est
     memorisee en session pour y revenir apres inscription. --}}
@props(['total', 'visibles' => \App\Support\LimiteAnonyme::BOOKS])

<div class="limite_anonyme">
    <div class="ui five doubling cards limite_anonyme_fantomes" aria-hidden="true">
        @for ($i = 0; $i < \App\Support\LimiteAnonyme::BOOKS; $i++)
            <div class="ui card"><div class="image"></div><div class="content"></div></div>
        @endfor
    </div>

    <div class="limite_anonyme_message">
        <h2>{{ trans_choice(':n autre portfolio vous attend|:n autres portfolios vous attendent', $total - $visibles, ['n' => number_format($total - $visibles, 0, ',', ' ')]) }}</h2>
        <p class="text-pretty">
            <span class="block"><strong>{{ __('Créez votre compte visiteur gratuit') }}</strong> {{ __('pour consulter tous les résultats.') }}</span>
            <span class="block">{{ __('Déjà inscrit ? Connectez-vous.') }}</span>
        </p>
        <div class="limite_anonyme_boutons" x-data>
            <button type="button" class="ui huge black button" @click="$store.modale.ouvrir('memo-compte')">{{ __('Créer un compte visiteur') }}</button>
            <button type="button" class="ui huge black basic button" @click="$store.modale.ouvrir('connexion')">{{ __('Me connecter') }}</button>
        </div>
    </div>
</div>
