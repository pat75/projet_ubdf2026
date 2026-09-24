@extends('layouts.espace')

@section('title', __('Ma formule'))

@section('content')
    @if ($creatif->plan)
        {{-- Formule en cours : on remercie, on rappelle l'echeance, et on
             laisse la grille des offres pour la prolongation, plus bas. --}}
        <x-espace.hero
            :illustration="asset('img_admin/budget.svg')"
            :alt="__('Ma formule')"
            :titre="__('Merci')"
            :suite="__('pour votre soutien')"
            :suite-dessous="true"
            class="mb-10" />

        <div class="mb-10 flex items-center gap-6 rounded bg-[#eceadb] px-6 py-5">
            <img src="{{ asset('img_admin/avion.png') }}" alt="" class="h-20 w-20 shrink-0 rounded-full object-cover">

            <div class="text-[#3d6d8e]">
                <h2 class="font-titre text-[21px] font-bold">{{ __('Vous êtes actuellement en formule PREMIUM') }}</h2>

                @if ($echeance)
                    <p class="mt-1">{{ __('Vous pouvez la renouveler avant le :date', ['date' => $echeance->format('d-m-Y')]) }}</p>
                @endif

                <p class="mt-1 text-[13px]">{{ __('Le restant des jours de votre formule actuelle s’accumulera avec la nouvelle formule.') }}</p>
            </div>
        </div>

        {{-- Le remerciement de l'annee. --}}
        <div class="mb-10 rounded bg-gradient-to-br from-[#25c1b4] to-[#12a5e0] px-6 py-10 text-center text-white">
            <img src="{{ asset('img_front/thank-you.png') }}" alt="" class="mx-auto w-[200px]">

            <h2 class="mt-6 font-titre text-[26px] font-bold">{{ __(':annee : Merci pour votre soutien !', ['annee' => now()->year]) }}</h2>

            <p class="mx-auto mt-5 max-w-xl">
                {{ __('Votre contribution est essentielle pour nous permettre de continuer à améliorer nos services et vous offrir la meilleure expérience possible.') }}
            </p>

            <div class="mx-auto mt-6 max-w-2xl space-y-4 rounded bg-white/15 p-6 text-[15px]">
                <p>{!! __('Grâce à votre <strong>formule :marque</strong>, vous bénéficiez d’un espace professionnel optimisé pour mettre en valeur votre travail.', ['marque' => e($marque->nom)]) !!}</p>
                <p>{{ __('Notre équipe reste à votre disposition pour tout accompagnement ou question concernant votre portfolio.') }}</p>

                <div class="rounded bg-white/25 p-4 text-[14px] font-semibold">
                    {{ __('N’oubliez pas : vous pouvez à tout moment nous contacter') }}<br>
                    {{ __('via') }} <a href="mailto:{{ $marque->email }}" class="underline">{{ $marque->email }}</a> {{ __('pour obtenir de l’aide.') }}
                </div>
            </div>
        </div>

        {{-- Offre couplee avec le site de diffusion : le code se recalcule
             des deux cotes, il n'est stocke nulle part. --}}
        <div class="mb-10 rounded-lg border border-[#dee2e6] bg-[#f8f9fa] p-7 text-[#495057]">
            <h2 class="mb-3 flex items-center gap-2 font-titre text-[19px] font-bold">
                <x-espace.icone nom="horloge" class="h-5 w-5 text-[#17b7bf]" />
                {{ __('Offre couplée :marque Classique et Diffusion', ['marque' => $marque->nom]) }}
            </h2>

            <p>
                {{ __('Donnez plus de visibilité à votre travail sur') }}
                <a href="{{ config('services.diffusion.url') }}" target="_blank" rel="noopener" class="text-[#17b7bf] underline">{{ parse_url(config('services.diffusion.url'), PHP_URL_HOST) }}</a>
            </p>

            <p class="mt-1">{!! __('Profitez d’une <strong class="text-ub-rouge">formule optimisée</strong> avec votre code personnel') !!}</p>

            <dl class="mt-4 rounded border border-[#e3e6e8] bg-white px-6 py-4 text-[14px]">
                <div class="flex items-center gap-6 py-1.5">
                    <dt class="w-32">{{ __('Identifiant :') }}</dt>
                    <dd><code class="rounded bg-[#eef0f2] px-3 py-1 font-mono">{{ $creatif->login }}</code></dd>
                </div>
                <div class="flex items-center gap-6 py-1.5">
                    <dt class="w-32">{{ __('Code promo :') }}</dt>
                    <dd><code class="rounded bg-[#eef0f2] px-3 py-1 font-mono">{{ $codeDiffusion }}</code></dd>
                </div>
            </dl>

            <details class="mt-4 text-[14px]">
                <summary class="cursor-pointer text-[#3d6d8e]">{{ __('Comment utiliser ce code ?') }}</summary>
                <ol class="mt-3 list-decimal space-y-1 pl-6">
                    <li>{{ __('Créez votre compte sur') }} <a href="{{ config('services.diffusion.url') }}" target="_blank" rel="noopener" class="font-semibold underline">{{ parse_url(config('services.diffusion.url'), PHP_URL_HOST) }}</a></li>
                    <li>{!! __('Accédez à <strong>« Formules et factures »</strong>') !!}</li>
                    <li>{{ __('Ajoutez votre identifiant et votre code') }}</li>
                    <li>{{ __('Visualisez vos nouveaux prix réduits') }}</li>
                </ol>
            </details>
        </div>
    @else
        <x-espace.titre>{{ __('Ma formule') }}</x-espace.titre>

        <p class="mb-8 text-[17px]">{{ __('Vous êtes en formule gratuite.') }}</p>
    @endif

    {{-- Les deux compteurs, rapportes au plafond de la formule. --}}
    <div class="border-t border-ub-gris-clair">
        <x-espace.quota :libelle="__('Nombre total d’images :')" :quota="$quotas['images']" :lien="route(nom_route('espace.galeries'))" />
        <x-espace.quota :libelle="__('Poids total des images :')" :quota="$quotas['poids']" :lien="route(nom_route('espace.galeries'))" />
    </div>

    {{-- Prolonger ou souscrire. --}}
    <h2 class="mt-12 font-titre text-[22px] font-light">
        {{ $creatif->plan ? __('Prolonger ma formule') : __('Passer à la formule :marque', ['marque' => $marque->nom]) }}
    </h2>

    {{-- Le createur qui a deja paye une fois ne souscrit plus : il
         renouvelle. La page le dit, et le douze mois porte alors le tarif
         de reabonnement. --}}
    @if ($reabonnement)
        <p class="mt-1 text-[15px] text-ub-texte2">{{ __('Vos tarifs de renouvellement, réservés aux créatifs déjà abonnés.') }}</p>
    @endif

    {{--
     | Trois cartes, et trois seulement : la gratuite, le six mois et le
     | douze mois. Le Pack Luxe et le Pack Site ne se vendent plus en
     | ligne — ils sont ecartes dans la grille, pas ici.
     |
     | Presentation reprise de la page tarifs des Illustrateurs : la
     | gratuite en retrait a gauche, en gris, chevauchee par les deux
     | offres payantes ; la derniere — le douze mois — est la vedette, sur
     | fond sombre. Les couleurs sont celles de l'espace : le turquoise a
     | la place de l'indigo, et pas de mode sombre, l'espace n'en a pas.
     |
     | La carte de gauche n'est pas une offre a payer : elle rappelle ce
     | dont dispose un compte gratuit, et sert de point de comparaison aux
     | deux autres. Elle est donc en `div`, pas en `form`.
    --}}
    @php
        $limites = config('formules.limites');
        $dernier = array_key_last($options);
    @endphp

    <div class="mt-6 grid grid-cols-1 items-center gap-y-6 sm:gap-y-0 lg:grid-cols-[4fr_5fr_5fr]">

        {{-- La gratuite : en retrait, sans ombre, elle passe dessous. --}}
        <div class="relative z-0 rounded-3xl bg-[#e4e4e1] px-8 py-14 ring-1 ring-black/5 sm:mx-8 sm:px-12 lg:mx-0 lg:-mr-6 lg:self-center lg:px-8 lg:py-23">
            <h3 class="text-[22px] text-ub-texte2">{{ __('Formule gratuite') }}</h3>
            <p class="text-[16px] font-semibold text-ub-texte3">{{ __('Sans limite de durée') }}</p>

            <p class="mt-4 flex items-baseline gap-x-2">
                <span class="text-[44px] font-semibold leading-none tracking-tight text-ub-texte2">0 €</span>
            </p>

            <div class="my-4 h-px bg-black/10"></div>

            <ul role="list" class="space-y-3 text-[15px] text-ub-texte2">
                <li class="flex gap-x-3"><x-espace.puce class="text-ub-texte4" />{{ __(':n visuels', ['n' => $limites['gratuite']['visuels']]) }}</li>
                <li class="flex gap-x-3"><x-espace.puce class="text-ub-texte4" />{{ __(':n pages', ['n' => $limites['gratuite']['pages']]) }}</li>
                <li class="flex gap-x-3"><x-espace.puce class="text-ub-texte4" />{{ __(':n Mo d’espace', ['n' => round($limites['gratuite']['poids_ko'] / 1000)]) }}</li>
            </ul>

            <p class="mt-6 text-[14px] text-ub-texte3">
                {{ $creatif->plan ? __('Votre formule précédente.') : __('Votre formule actuelle.') }}
            </p>
        </div>

        @foreach ($options as $numero => $o)
            @php
                // La derniere offre de la grille — le douze mois — est mise
                // en avant : fond sombre, et elle passe par-dessus.
                $vedette = $numero === $dernier;
                $parMois = $o['ttc'] / $o['mois'];
            @endphp

            <form method="post" action="{{ route(nom_route('espace.formule.payer'), $numero) }}"
                  class="relative rounded-3xl px-8 py-10 shadow-xl transition duration-300 ease-out hover:-translate-y-2 sm:mx-8 sm:px-12 lg:mx-0 lg:px-8
                         {{ $vedette ? 'z-2 bg-[#22292f] text-white ring-1 ring-white/10' : 'z-1 bg-white ring-1 ring-black/10 lg:-mr-6' }}">
                @csrf

                @if ($o['promo'] ?? false)
                    <span class="absolute -top-3.5 right-8 rounded-full bg-ub-rouge px-4 py-1.5 text-[13px] font-semibold text-white shadow">
                        {{ $o['promo'] === 'blackfriday' ? 'Black Friday' : __('Promotion du jour') }}
                    </span>
                @elseif ($vedette)
                    <span class="absolute -top-3.5 right-8 rounded-full bg-ub-accent px-4 py-1.5 text-[13px] font-semibold text-white shadow">
                        {{ $reabonnement ? __('Offre renouvellement') : __('Meilleure offre') }}
                    </span>
                @endif

                <h3 class="text-[22px] font-semibold {{ $vedette ? 'text-white' : 'text-ub-texte' }}">{{ __($o['libelle']) }}</h3>
                <p class="text-[16px] font-semibold text-ub-accent">{{ __(':n mois', ['n' => $o['mois']]) }}</p>

                <p class="mt-4 flex items-baseline gap-x-2">
                    <span class="text-[44px] font-semibold leading-none tracking-tight {{ $vedette ? 'text-white' : 'text-ub-texte' }}">{{ number_format($parMois, 2, ',', ' ') }} €</span>
                    <span class="text-[18px] text-ub-accent">{{ __('/ mois') }}</span>
                </p>

                <p class="mt-2 text-[15px] {{ $vedette ? 'text-white/70' : 'text-ub-texte2' }}">
                    {{ __('Facturé :montant € en une seule fois', ['montant' => number_format($o['ttc'], 2, ',', ' ')]) }}
                    @isset($o['barre'])
                        <span class="ml-1 font-semibold text-ub-accent">{{ __('au lieu de') }} <s>{{ number_format($o['barre'], 2, ',', ' ') }} €</s></span>
                    @endisset
                </p>

                <ul role="list" class="mt-6 space-y-3 text-[15px] {{ $vedette ? 'text-white/85' : 'text-ub-texte2' }}">
                    <li class="flex gap-x-3"><x-espace.puce class="text-ub-accent" />{{ __(':n visuels', ['n' => $limites['payante']['visuels']]) }}</li>
                    <li class="flex gap-x-3"><x-espace.puce class="text-ub-accent" />{{ __('Pages illimitées') }}</li>
                    <li class="flex gap-x-3"><x-espace.puce class="text-ub-accent" />{{ __(':n Mo d’espace', ['n' => round($limites['payante']['poids_ko'] / 1000)]) }}</li>
                    <li class="flex gap-x-3"><x-espace.puce class="text-ub-accent" />{{ __('Paiement unique, sans reconduction automatique') }}</li>
                </ul>

                <button type="submit"
                        class="mt-8 block w-full cursor-pointer rounded-full px-8 py-3.5 text-center text-[17px] font-bold transition-opacity duration-150 hover:opacity-90
                               {{ $vedette ? 'bg-ub-accent text-white' : 'bg-ub-texte text-white' }}">
                    {{ __('Sélectionner') }}
                </button>
            </form>
        @endforeach
    </div>

    <p class="mt-3 text-[12px] text-ub-gris-fonce">{{ __('Paiement sécurisé par Payplug. Une formule en cours est prolongée à partir de son échéance.') }}</p>

    <livewire:espace.parrainage />

    <div class="mt-10">
        <x-espace.section-pliante :titre="__('Factures')" icone="factures">
            @forelse ($factures as $f)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ub-gris-clair py-2 text-[14px] last:border-b-0">
                    <span class="w-24">{{ $f->numero() }}</span>
                    <span class="w-24">{{ $f->issued_at?->format('d/m/Y') }}</span>
                    <span class="flex-1">{{ $f->designation ?: $f->label }}</span>
                    <span class="whitespace-nowrap">{{ number_format((float) $f->amount, 2, ',', ' ') }} €</span>
                    <a href="{{ route(nom_route('espace.facture'), $f) }}" target="_blank" class="underline">{{ __('Voir') }}</a>
                    <a href="{{ route(nom_route('espace.facture.pdf'), $f) }}" class="underline">PDF</a>
                </div>
            @empty
                <p class="py-3 text-[14px] text-ub-gris-fonce">{{ __('Aucune facture.') }}</p>
            @endforelse
        </x-espace.section-pliante>

        <x-espace.section-pliante :titre="__('Conditions générales de vente')">
            <p class="text-[14px]">
                <a href="{{ asset('pdf/Conditions_generales_de_vente_'.$marque->nom.'.pdf') }}" target="_blank" rel="noopener" class="underline">
                    <strong>{{ __('Conditions générales de vente :marque', ['marque' => $marque->nom]) }}</strong> (PDF)
                </a>
            </p>
        </x-espace.section-pliante>

        <x-espace.section-pliante :titre="__('Certification et sécurité des paiements sur :marque', ['marque' => $marque->nom])">
            <div class="grid gap-8 text-[14px] md:grid-cols-3">
                <div class="space-y-4">
                    <img src="{{ $marque->logo }}" alt="{{ $marque->nom }}" class="w-[100px]">

                    {{-- Le logo Payplug etait charge depuis payplug.com :
                         une image tierce sur une page de paiement, et un
                         lien mort le jour ou elle bouge. Son nom suffit. --}}
                    <p class="font-titre text-[20px] font-light">Payplug</p>
                </div>

                <div>
                    <h3 class="mb-2 font-semibold">{{ __('Choix de la solution de paiement') }}</h3>
                    <p>{{ __('Nous avons supprimé le paiement Paypal, car il posait des problèmes de validation de l’achat : les formules n’étaient pas activées malgré le paiement.') }}</p>
                    <p class="mt-3">{!! __('<strong>Nous utilisons désormais Payplug</strong> depuis plusieurs années, et nous en sommes très satisfaits.') !!}</p>
                </div>

                <div>
                    <h3 class="mb-2 font-semibold">{{ __('Sécurité, certification et agrément') }}</h3>
                    <p>{!! __('<strong>Toutes vos données bancaires sont chiffrées</strong> lors de l’envoi au serveur Payplug : elles ne transitent pas par le serveur :marque, la fenêtre de paiement lui étant extérieure.', ['marque' => e($marque->nom)]) !!}</p>
                </div>
            </div>
        </x-espace.section-pliante>
    </div>
@endsection
