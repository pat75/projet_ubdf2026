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

    {{--
     | Trois cartes, et trois seulement : la gratuite, le six mois et le
     | douze mois. Le Pack Luxe et le Pack Site ne se vendent plus en
     | ligne — ils sont ecartes dans la grille, pas ici.
     |
     | La carte de gauche n'est pas une offre a payer : elle rappelle ce
     | dont dispose un compte gratuit, et sert de point de comparaison aux
     | deux autres. Elle est donc en `div`, pas en `form`.
    --}}
    @php($limites = config('formules.limites'))

    <div class="mt-5 grid items-stretch gap-4 md:grid-cols-3">

        <div class="flex flex-col rounded-ub-carte border border-ub-bord-carte bg-white p-6">
            <span class="text-[15px] font-semibold">{{ __('Formule gratuite') }}</span>

            <span class="mt-3 text-[32px] font-light leading-none">0 €</span>
            <span class="mt-1 text-[13px] text-ub-texte3">{{ __('sans limite de durée') }}</span>

            <ul class="mt-5 space-y-1.5 text-[14px] text-ub-texte2">
                <li>{{ __(':n visuels', ['n' => $limites['gratuite']['visuels']]) }}</li>
                <li>{{ __(':n pages', ['n' => $limites['gratuite']['pages']]) }}</li>
                <li>{{ __(':n Mo d’espace', ['n' => round($limites['gratuite']['poids_ko'] / 1000)]) }}</li>
            </ul>

            <span class="mt-auto pt-6 text-[14px] text-ub-texte3">
                {{ $creatif->plan ? __('Votre formule précédente') : __('Votre formule actuelle') }}
            </span>
        </div>

        @foreach ($options as $numero => $o)
            <form method="post" action="{{ route(nom_route('espace.formule.payer'), $numero) }}"
                  class="flex flex-col rounded-ub-carte border-2 border-ub-accent bg-white p-6">
                @csrf
                @isset($o['promo'])
                    <span class="mb-2 w-fit rounded-full bg-ub-rouge px-2.5 py-0.5 text-[11px] font-semibold text-white">{{ $o['promo'] === 'blackfriday' ? 'Black Friday' : __('Promotion du jour') }}</span>
                @endisset

                <span class="text-[15px] font-semibold">{{ __($o['libelle']) }}</span>

                <span class="mt-3 flex items-baseline gap-2">
                    <span class="text-[32px] font-light leading-none text-ub-accent-texte">{{ number_format($o['ttc'], 2, ',', ' ') }} €</span>
                    @isset($o['barre']) <s class="text-[15px] text-ub-texte4">{{ number_format($o['barre'], 2, ',', ' ') }} €</s> @endisset
                </span>

                <span class="mt-1 text-[13px] text-ub-texte3">{{ __(':n mois, TTC', ['n' => $o['mois']]) }}</span>

                <ul class="mt-5 space-y-1.5 text-[14px] text-ub-texte2">
                    <li>{{ __(':n visuels', ['n' => $limites['payante']['visuels']]) }}</li>
                    <li>{{ __('pages illimitées') }}</li>
                    <li>{{ __(':n Mo d’espace', ['n' => round($limites['payante']['poids_ko'] / 1000)]) }}</li>
                </ul>

                <div class="mt-auto pt-6">
                    <button type="submit" class="w-full rounded-ub bg-ub-accent px-4 py-2.5 text-[15px] font-semibold text-white hover:bg-ub-accent-fonce">
                        {{ __('Payer par carte') }}
                    </button>
                </div>
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
