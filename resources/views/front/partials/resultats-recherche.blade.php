{{-- Resultats d'une recherche du portail : rendus dans /recherche, et
     renvoyes seuls a l'ajax de /search (RechercheController::search). --}}
<div class="ui container bloc_portfolios">

    <div class="bloc_titre">
        <h1>
            @if ($recherche->q === '')
                {{ __('Rechercher un portfolio') }}
            @else
                {{ $recherche->q }}
            @endif
        </h1>

        @if ($recherche->exploitable())
            <div class="sub_title">
                <strong>{{ number_format($total, 0, ',', ' ') }}</strong>
                {{ trans_choice('portfolio|portfolios', $total) }}
                {{ $recherche->mode === 'pseudo' ? __('au nom recherché') : __('sur ces mots-clés') }}
            </div>
        @endif
    </div>

    @if (! $recherche->exploitable())
        {{-- Regle reprise du legacy : en deca de trois caracteres, la
             requete balaierait la table sans rien discriminer. --}}
        <div class="ui basic segment center aligned recherche_mini">
            {{ __('Indiquez au moins trois caractères.') }}
        </div>
    @elseif ($books->isEmpty() && ($images ?? collect())->isEmpty())
        <div class="ui basic segment center aligned recherche_vide">
            <img src="/img_front/recherche-vide.png" alt="" width="343" height="400" style="display:block;margin:0 auto 1em;max-width:60%;height:auto">
            {{ __('Aucun portfolio ne correspond à cette recherche.') }}
        </div>
    @endif

    @if (($images ?? collect())->isNotEmpty())
        {{-- Visuels reperes par l'analyse IA (mots-cles, titre). --}}
        <div class="bloc_titre resultats_images">
            <h2>{{ trans_choice(':n image|:n images', $images->count(), ['n' => $images->count()]) }}</h2>
        </div>
        <div class="ui six doubling cards" id="resultats_images">
            @foreach ($images as $image)
                <a class="ui card" href="{{ $image->pageUrl() }}" title="{{ $image->ai_title }}">
                    <div class="image">
                        <img src="{{ $image->url('carre_183') }}" alt="{{ $image->ai_title }}" width="183" height="183" loading="lazy">
                    </div>
                    <x-portail.legende-createur :creatif="$image->user" />
                </a>
            @endforeach
        </div>

        @if ($books->isNotEmpty())
            <div class="bloc_titre resultats_books">
                <h2>{{ trans_choice(':n portfolio|:n portfolios', $total, ['n' => $total]) }}</h2>
            </div>
        @endif
    @endif

    <div class="visibility infinite" x-data="defilementInfini">
        <div class="ui five doubling cards" id="accueil_portfolio">
            @foreach ($books as $book)
                <x-book-card :book="$book" />
            @endforeach
            <div id="position_card_last" x-ref="fin"></div>
        </div>

        <div class="ui basic segment">
            <div class="ui grid result_message"></div>
        </div>

        <div class="ui horizontal icon divider result_end" :class="{ show: termine && page > 0 }">
            <i class="circular large angle up icon"></i>
        </div>

        <div class="ui large centered inline text loader" :class="{ active: enCours }">{{ __('Chargement...') }}</div>
    </div>
</div>
