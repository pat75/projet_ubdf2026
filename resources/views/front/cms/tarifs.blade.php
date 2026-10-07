{{--
 | Grille tarifaire publique (/doc/les-formules-ultra-book), reprise de la
 | page « Ma formule » de l'espace : la gratuite en retrait, puis le six et
 | le douze mois, ce dernier en vedette. Pas de paiement ici : le visiteur
 | cree son book, le createur connecte va a son espace.
--}}
@php
    $dernier = array_key_last($tarifs['options']);
    $gratuite = $tarifs['limites']['gratuite'];
    $payante = $tarifs['limites']['payante'];
    $prix = fn (float $v) => number_format($v, 2, ',', ' ').' €';
    $connecte = auth('web')->check();
    $cible = $connecte ? lien('espace.formule') : lien('inscription.page');
@endphp

<section class="tarifs_cms" aria-labelledby="titre_tarifs">
    <h2 id="titre_tarifs">{{ __('Comparer les formules') }}</h2>

    <div class="tarifs_grille">
        <div class="tarif tarif_gratuit">
            <h3>{{ __('Formule gratuite') }}</h3>
            <p class="tarif_duree">{{ __('Sans limite de durée') }}</p>
            <p class="tarif_prix">0 €</p>
            <ul>
                <li>{{ __(':n visuels', ['n' => $gratuite['visuels']]) }}</li>
                <li>{{ __(':n pages', ['n' => $gratuite['pages']]) }}</li>
                <li>{{ __(':n Mo d’espace', ['n' => round($gratuite['poids_ko'] / 1000)]) }}</li>
                <li>{{ __('Adresse personnelle, sans publicité') }}</li>
            </ul>
            <a href="{{ $cible }}" class="tarif_bouton">{{ $connecte ? __('Mon espace') : __('Créer mon book') }}</a>
        </div>

        @foreach ($tarifs['options'] as $numero => $o)
            <div class="tarif {{ $numero === $dernier ? 'tarif_vedette' : '' }}">
                @if ($numero === $dernier)
                    <span class="tarif_etiquette">{{ __('Meilleure offre') }}</span>
                @endif
                <h3>{{ __($o['libelle']) }}</h3>
                <p class="tarif_duree">{{ __(':n mois', ['n' => $o['mois']]) }}</p>
                <p class="tarif_prix">{{ $prix($o['ttc'] / $o['mois']) }} <small>{{ __('/ mois') }}</small></p>
                <p class="tarif_total">
                    {{ __('Facturé :montant en une seule fois', ['montant' => $prix($o['ttc'])]) }}
                    @isset($o['barre'])
                        {{ __('au lieu de') }} <s>{{ $prix($o['barre']) }}</s>
                    @endisset
                </p>
                <ul>
                    <li>{{ __(':n visuels', ['n' => $payante['visuels']]) }}</li>
                    <li>{{ __('Pages illimitées') }}</li>
                    <li>{{ __('Référencement automatique de vos images par IA') }}</li>
                    <li>{{ __(':n Mo d’espace', ['n' => round($payante['poids_ko'] / 1000)]) }}</li>
                    <li>{{ __('Paiement unique, sans reconduction automatique') }}</li>
                </ul>
                <a href="{{ $cible }}" class="tarif_bouton">{{ $connecte ? __('Choisir cette formule') : __('Commencer gratuitement') }}</a>
            </div>
        @endforeach
    </div>

    {{-- Le compte visiteur (recruteur, directeur artistique, client) :
         gratuit, sans formule. Anonyme : la modale memo-compte du portail. --}}
    <div class="tarif_visiteur">
        <div>
            <h3>{{ __('Vous recherchez un créatif ? Le compte visiteur est gratuit') }}</h3>
            <p>{{ __('Sans book à publier, créez gratuitement un compte visiteur : enregistrez vos portfolios préférés dans votre memoBook, partagez-le ou exportez-le en PDF, et échangez directement avec les créatifs par la messagerie.') }}
                <a href="{{ lien('cms.doc', 'compte-visiteur') }}" class="tarif_lien">{{ __('Tout sur le compte visiteur') }}</a></p>
        </div>
        @if (auth('visitor')->check())
            <a href="{{ lien('memobook') }}" class="tarif_bouton">{{ __('Mon memoBook') }}</a>
        @elseif (! auth('web')->check())
            <button type="button" class="tarif_bouton" @click="$store.modale.ouvrir('memo-compte')">{{ __('Créer un compte visiteur') }}</button>
        @endif
    </div>

    <p class="tarifs_note">
        {{ __('Prix TTC. Paiement sécurisé par Payplug : vos données bancaires ne transitent pas par :marque.', ['marque' => $marque->nom]) }}
        @if ($tarifs['reabonnement'])
            {{ __('Renouvellement 12 mois pour les créatifs déjà abonnés : :montant.', ['montant' => $prix($tarifs['reabonnement']['ttc'])]) }}
        @endif
        {{ __('Une formule en cours est prolongée à partir de son échéance : renouveler en avance ne fait rien perdre.') }}
    </p>
</section>

@push('scripts')
    @php
        $offres = [[
            '@type' => 'Offer', 'name' => __('Formule gratuite'), 'price' => '0', 'priceCurrency' => 'EUR',
        ]];
        foreach ($tarifs['options'] as $o) {
            $offres[] = [
                '@type' => 'Offer', 'name' => __($o['libelle']), 'price' => number_format($o['ttc'], 2, '.', ''),
                'priceCurrency' => 'EUR', 'url' => lien('cms.doc', $page->slug),
            ];
        }
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => __('Portfolio en ligne :marque', ['marque' => $marque->nom]),
            'brand' => ['@type' => 'Brand', 'name' => $marque->nom],
            'description' => texte_seo($page->excerpt ?: $page->body, 300),
            'offers' => $offres,
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
