@extends('layouts.espace')

@section('title', __('Ma formule'))

@section('content')

    {{-- En-tete : le surtitre et le remerciement nomme. --}}
    <div class="pb-2">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.12em] text-ub-accent-texte">{{ __('Ma formule') }}</div>

            <h1 class="mt-1.5 mb-1 text-[38px] font-bold leading-[1.1] text-[#1b1b1b]">
                @if ($creatif->plan)
                    {{-- Meme degrade que le fond du bloc « merci pour votre soutien ». --}}
                    <span class="bg-linear-to-br from-[#22c1c3] to-[#16a6d9] bg-clip-text text-transparent">{{ __('Merci pour votre soutien, :prenom', ['prenom' => $creatif->firstname ?: $creatif->login]) }}</span>
                @else
                    {{ __('Votre formule, :prenom', ['prenom' => $creatif->firstname ?: $creatif->login]) }}
                @endif
            </h1>

            <p class="text-[17px] text-ub-texte2">{{ __('Votre formule, vos avantages et vos offres partenaires au même endroit.') }}</p>
        </div>
    </div>

    @if ($creatif->plan)
        @php
            /*
             | La barre mesure la part ecoulee de la formule en cours : du
             | jour ou elle a commence a son echeance. Une formule prolongee
             | plusieurs fois peut afficher une part infime — c'est le cas
             | des comptes dont l'echeance part loin dans le temps.
             */
            $debut = $creatif->plan_started_at;
            $ecoule = 0;

            if ($debut && $echeance && $echeance->greaterThan($debut)) {
                $total = $debut->diffInSeconds($echeance);
                $ecoule = max(0, min(100, round($debut->diffInSeconds(now()) / $total * 100)));
            }
        @endphp

        {{-- L'etat de la formule, et les deux boutons vers la grille. --}}
        <section class="mt-6 grid items-center gap-6 rounded-ub-carte bg-white px-8 py-7 md:grid-cols-[minmax(0,1fr)_auto]">
            <div>
                <div class="mb-2.5 flex items-center gap-2.5">
                    <span class="rounded-full bg-[#1b1b1b] px-2.5 py-1 text-[12px] font-bold uppercase tracking-[.1em] text-white">{{ __('Premium') }}</span>

                    <span class="flex items-center gap-1.5 text-[14px] font-semibold text-[#1a8a4a]">
                        <span class="h-2 w-2 rounded-full bg-[#22a45d]"></span>{{ __('Active') }}
                    </span>
                </div>

                <h2 class="mb-1 text-[22px] font-bold">{{ __('Vous êtes actuellement en formule Premium') }}</h2>

                @if ($echeance)
                    <p class="text-[16px] text-ub-texte2">
                        {{ __('Renouvelable jusqu’au') }}
                        <b class="text-[#1b1b1b]">{{ $echeance->translatedFormat('j F Y') }}</b>
                    </p>
                @endif

                <div class="mt-4.5 h-1.5 overflow-hidden rounded bg-[#eef0f0]">
                    <div class="h-full bg-ub-accent" style="width: {{ $ecoule }}%"></div>
                </div>

                <p class="mt-2.5 text-[14px] text-pretty text-[#6a6a6a]">
                    {{ __('Les jours restants de votre formule actuelle s’ajoutent à la nouvelle formule : vous ne perdez rien en renouvelant en avance.') }}
                </p>
            </div>

            {{-- Les deux boutons menent au meme endroit : la grille, plus
                 bas sur la page. Rien a charger, c'est une ancre. --}}
            <div class="flex min-w-[180px] flex-col gap-2">
                <a href="#offres" class="bouton-espace bouton-espace-grand px-4.5">
                    {{ __('Renouveler') }}
                </a>
                <a href="#offres" class="bouton-espace bouton-espace-grand px-4.5">
                    {{ __('Comparer les formules') }}
                </a>
            </div>
        </section>

        {{-- Le remerciement de l'annee. --}}
        <section class="mt-6 grid overflow-hidden rounded-ub-carte bg-gradient-to-br from-[#22c1c3] to-[#16a6d9] text-white sm:grid-cols-[180px_minmax(0,1fr)]">
            <div class="flex items-center justify-center p-6">
                <img src="{{ asset('img_front/thank-you.png') }}" alt="" class="w-[140px] mix-blend-multiply">
            </div>

            <div class="p-8 sm:pl-1.5">
                <h2 class="mb-2.5 text-[26px] font-bold">{{ __(':annee : merci pour votre soutien !', ['annee' => now()->year]) }}</h2>

                <p class="mb-5 text-[16px] leading-[1.55] text-pretty">
                    {{ __('Votre contribution est essentielle pour nous permettre de continuer à améliorer nos services et vous offrir la meilleure expérience possible.') }}
                </p>

                <div class="mb-5 grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(200px,1fr))]">
                    <div class="rounded-ub-bandeau bg-white/15 px-4 py-3.5 text-[15px] leading-[1.45]">
                        <b class="mb-1 block">{{ __('Un espace professionnel') }}</b>
                        {{ __('Grâce à votre formule :marque, votre travail est mis en valeur sur un portfolio optimisé.', ['marque' => $marque->nom]) }}
                    </div>
                    <div class="rounded-ub-bandeau bg-white/15 px-4 py-3.5 text-[15px] leading-[1.45]">
                        <b class="mb-1 block">{{ __('Un accompagnement') }}</b>
                        {{ __('Notre équipe reste disponible pour toute question concernant votre portfolio.') }}
                    </div>
                </div>

                @php
                    $sujet = __('Question sur mon compte :book', ['book' => $creatif->login]);
                    $mailto = 'mailto:'.$marque->email.'?subject='.rawurlencode($sujet);
                @endphp

                <a href="{{ $mailto }}"
                   class="bouton-espace bouton-espace-grand px-4.5">
                    <x-espace.icone nom="enveloppe" class="h-4 w-4" />{{ __('Écrire à :email', ['email' => $marque->email]) }}
                </a>
            </div>
        </section>

        {{-- Offre couplee avec le site de diffusion : le code se recalcule
             des deux cotes, il n'est stocke nulle part. --}}
        <section class="mt-6 rounded-ub-carte bg-white px-8 py-7"
                 x-data="{ copie: null, aide: false, copier(cle, valeur) {
                     navigator.clipboard?.writeText(valeur);
                     this.copie = cle;
                     clearTimeout(this.t); this.t = setTimeout(() => this.copie = null, 1500);
                 } }">

            <div class="text-[13px] font-semibold uppercase tracking-[.12em] text-ub-formule">{{ __('Offre réservée') }}</div>

            <h2 class="mt-1.5 mb-1.5 text-[22px] font-bold">{{ __('Offre couplée :marque Classique + Diffusion', ['marque' => $marque->nom]) }}</h2>

            <p class="max-w-[520px] text-[16px] text-pretty text-ub-texte2">
                {{ __('Donnez plus de visibilité à votre travail sur') }}
                <a href="{{ config('services.diffusion.url') }}" target="_blank" rel="noopener" class="text-ub-accent-texte underline">{{ parse_url(config('services.diffusion.url'), PHP_URL_HOST) }}</a>
                {!! __('et profitez d’une <b class="text-ub-formule">formule optimisée</b> avec votre code personnel.') !!}
            </p>

            <div class="mt-5.5 grid gap-3 sm:grid-cols-2">
                @foreach ([['id', __('Identifiant'), $creatif->login], ['code', __('Code promo'), $codeDiffusion]] as [$cle, $libelle, $valeur])
                    <div class="flex items-center justify-between gap-3 rounded-ub-bandeau border border-[#e3e5e5] px-4 py-3.5">
                        <div>
                            <div class="mb-0.5 text-[13px] text-ub-texte3">{{ $libelle }}</div>
                            <div class="font-mono text-[18px] tracking-[.04em]">{{ $valeur }}</div>
                        </div>

                        <button type="button" @click="copier('{{ $cle }}', @js($valeur))"
                                class="bouton-espace bouton-espace-petit px-3.5">
                            <span x-text="copie === '{{ $cle }}' ? @js(__('Copié ✓')) : @js(__('Copier'))">{{ __('Copier') }}</span>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-5">
                <a href="{{ config('services.diffusion.url') }}" target="_blank" rel="noopener"
                   class="bouton-espace bouton-espace-grand px-5">
                    {{ __('Profiter de l’offre ↗') }}
                </a>

                <button type="button" @click="aide = ! aide" :aria-expanded="aide" class="text-[15px] text-ub-accent-texte">
                    <span x-text="aide ? '▾' : '▸'">▸</span> {{ __('Comment utiliser ce code ?') }}
                </button>
            </div>

            <ol x-show="aide" x-cloak x-collapse
                class="mt-5 list-decimal rounded-ub-bandeau bg-[#f6f7f7] py-4.5 pl-10 pr-4.5 text-[15px] leading-[1.7] text-[#444]">
                <li>{{ __('Rendez-vous sur') }}
                    <a href="{{ config('services.diffusion.url') }}" target="_blank" rel="noopener" class="underline">{{ parse_url(config('services.diffusion.url'), PHP_URL_HOST) }}</a>
                    {{ __('et choisissez la formule Diffusion.') }}</li>
                <li>{!! __('Connectez-vous avec votre identifiant <b>:login</b>.', ['login' => e($creatif->login)]) !!}</li>
                <li>{!! __('Saisissez le code promo <b>:code</b> au moment du paiement.', ['code' => e($codeDiffusion)]) !!}</li>
            </ol>
        </section>
    @else
        <p class="mt-6 text-[17px]">{{ __('Vous êtes en formule gratuite.') }}</p>
    @endif

    {{-- Prolonger ou souscrire. --}}
    <h2 id="offres" class="mt-12 scroll-mt-6 font-titre text-[22px] font-light">
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
                        class="bouton-espace bouton-espace-grand mt-8 w-full px-8 {{ $vedette ? 'ring-1 ring-white/40' : '' }}">
                    {{ __('Sélectionner') }}
                </button>
            </form>
        @endforeach
    </div>

    <p class="mt-3 text-[12px] text-ub-gris-fonce">{{ __('Paiement sécurisé par Payplug. Une formule en cours est prolongée à partir de son échéance.') }}</p>

    <livewire:espace.parrainage />

    <div class="mt-10">
        <x-espace.section-pliante :titre="__('Factures')" icone="factures" cartouche clair>
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

        {{-- Le parrainage, a la suite des factures et dans le meme
             depliant : son code et ses filleuls. La saisie, elle, est
             dans le bloc d'activation ci-dessus — un seul champ sert aux
             deux sortes de codes. --}}
        <x-espace.section-pliante :titre="__('Parrainage')" cartouche>
            <p class="text-[15px] text-ub-texte2">
                {{ __('Votre code de parrainage :') }}
                <strong class="font-mono text-ub-texte">{{ $monCodeParrain }}</strong>.
                {{ __('Quand un créatif avec une formule payante l’utilise, vous gagnez chacun 1 à 3 mois de formule.') }}
                {{ __('Il s’active dans le champ ci-dessus.') }}
            </p>

            @if ($filleuls->isNotEmpty())
                <ul class="mt-3 space-y-1 text-[14px] text-ub-texte2">
                    @foreach ($filleuls as $f)
                        <li>{{ $f->referred?->fullName() }} — {{ $f->confirmed_at?->format('d/m/Y') }}</li>
                    @endforeach
                </ul>
            @endif
        </x-espace.section-pliante>

        <x-espace.section-pliante :titre="__('Conditions générales de vente')" cartouche>
            <p class="text-[14px]">
                <a href="{{ asset('pdf/Conditions_generales_de_vente_'.$marque->nom.'.pdf') }}" target="_blank" rel="noopener" class="underline">
                    <strong>{{ __('Conditions générales de vente :marque', ['marque' => $marque->nom]) }}</strong> (PDF)
                </a>
            </p>
        </x-espace.section-pliante>

        <x-espace.section-pliante :titre="__('Certification et sécurité des paiements sur :marque', ['marque' => $marque->nom])" cartouche>
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
