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
                    <span class="bg-linear-to-br from-[#22c1c3] to-[#16a6d9] bg-clip-text text-transparent">{{ __('Merci pour votre soutien, :prenom', ['prenom' => $creatif->fullName()]) }}</span>
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
             | La barre mesure les jours restants sur une annee (maquette :
             | 182 jours restants = barre a moitie). L'echelle est fixe, quelle
             | que soit la duree de la formule : une 6 mois neuve remplit la
             | moitie, une 12 mois neuve la remplit. Au-dela d'un an (jours
             | reportes), la barre reste pleine.
             */
            $restant = $echeance
                ? max(0, min(100, round(now()->diffInDays($echeance, false) / 365 * 100)))
                : 0;
        @endphp

        {{-- Le remerciement. La croix le masque (memorise sur le compte)
             jusqu'a ce qu'une nouvelle formule demarre. --}}
        @unless ($creatif->merci_masque_le && (! $creatif->plan_started_at || $creatif->merci_masque_le->gte($creatif->plan_started_at)))
        <section x-data="{ vu: true }" x-show="vu" x-transition.opacity
                 class="relative mt-6 grid items-center overflow-hidden rounded-ub-carte bg-gradient-to-br from-[#22c1c3] to-[#16a6d9] text-white sm:grid-cols-[243px_minmax(0,1fr)]">
            <div class="absolute top-3 right-3 z-10">
            <x-espace.info-bulle :texte="__('Ne plus afficher')">
                <button type="button" aria-label="{{ __('Fermer') }}"
                        @click="vu = false; fetch(@js(route(nom_route('espace.formule.merci.masquer'))), { method: 'POST', headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' } })"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-white/80 transition hover:bg-white/15 hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </x-espace.info-bulle>
            </div>

            <div class="flex items-center justify-center p-6 sm:pr-3.75">
                <img src="{{ asset('img_front/thank-you.png') }}" alt="" class="w-[188px] mix-blend-multiply">
            </div>

            <div class="px-8 py-[75px] sm:pl-1.5">
                <h2 class="mb-2 text-[28.6px] font-bold">{{ __('Félicitations !') }}</h2>

                <p class="mb-5 text-[15px] leading-[1.5] text-pretty">
                    {{ __('Votre contribution est essentielle pour nous permettre de continuer à améliorer nos services et vous offrir la meilleure expérience possible.') }}
                </p>

                <a href="{{ lien('support') }}" class="bouton-espace bouton-espace-petit px-3.5">
                    <x-espace.icone nom="enveloppe" class="h-4 w-4" />{{ __('Nous contacter') }}
                </a>
                <span class="ml-3 text-[14px]">{{ __('Équipe disponible pour toute question sur votre portfolio.') }}</span>
            </div>
        </section>
        @endunless

        {{-- L'etat de la formule, et les deux boutons vers la grille. --}}
        <section class="mt-6 grid items-center gap-6 rounded-ub-carte bg-white px-8 py-[58px] md:grid-cols-[minmax(0,1fr)_auto]">
            <div>
                <div class="mb-2.5 flex items-center gap-2.5">
                    <span class="rounded-full bg-[#1b1b1b] px-2.5 py-1 text-[12px] font-bold uppercase tracking-[.1em] text-white">{{ __('Premium') }}</span>

                    <span class="flex items-center gap-1.5 text-[14px] font-semibold text-[#1a8a4a]">
                        <span class="h-2 w-2 rounded-full bg-[#22a45d]"></span>{{ __('Active') }}
                    </span>
                </div>

                <h2 class="mb-1 text-[26.5px] font-bold">{{ __('Vous êtes actuellement en formule Premium') }}</h2>

                @if ($echeance)
                    <p class="text-[16px] text-ub-texte2">
                        {{ __('Renouvelable jusqu’au') }}
                        <b class="text-[#1b1b1b]">{{ $echeance->translatedFormat('j F Y') }}</b>
                        @if ($echeance->isFuture())
                            · {{ trans_choice(':n jour restant|:n jours restants', $n = (int) now()->diffInDays($echeance), ['n' => $n]) }}
                        @endif
                    </p>
                @endif

                <div class="mt-4.5 h-2.5 overflow-hidden rounded-full bg-[#eef0f0]">
                    <div class="h-full rounded-full bg-ub-accent" style="width: {{ $restant }}%"></div>
                </div>

                <p class="mt-2.5 text-[14px] text-pretty text-[#6a6a6a]">
                    {{ __('Les jours restants de votre formule actuelle s’ajoutent à la nouvelle formule : vous ne perdez rien en renouvelant en avance.') }}
                </p>
            </div>

            {{-- Le bouton mene a la grille, plus bas sur la page. Rien a
                 charger, c'est une ancre. --}}
            <div class="flex min-w-[180px] flex-col gap-2">
                <a href="#offres" class="bouton-espace bouton-espace-grand gap-2 px-4.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.64-6.36M21 3v6h-6"/></svg>
                    {{ __('Renouveler') }}
                </a>
            </div>
        </section>

        {{-- Offre couplee avec le site de diffusion : le code se recalcule
             des deux cotes, il n'est stocke nulle part. La fleche replie le
             bloc sur son surtitre et son titre ; le choix est retenu sur le
             compte. --}}
        <section class="relative mt-6 rounded-ub-carte bg-gradient-to-br from-[#fde4ec] to-[#f08080] px-8 py-7"
                 x-data="{ copie: null, aide: false, repliee: @js($creatif->offre_repliee),
                     basculer() {
                         this.repliee = ! this.repliee;
                         fetch(@js(route(nom_route('espace.formule.replier'), 'offre')), { method: 'POST',
                             headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                             body: JSON.stringify({ repliee: this.repliee }) });
                     },
                     copier(cle, valeur) {
                     navigator.clipboard?.writeText(valeur);
                     this.copie = cle;
                     clearTimeout(this.t); this.t = setTimeout(() => this.copie = null, 1500);
                 } }">

            <x-espace.replier-bloc />

            <div class="text-[13px] font-semibold uppercase tracking-[.12em] text-ub-formule">{{ __('Offre réservée') }}</div>

            <h2 class="mt-1.5 mb-1.5 pr-10 text-[22px] font-bold">{{ __('Offre couplée :marque Classique + Diffusion', ['marque' => $marque->nom]) }}</h2>

            <div x-show="! repliee" x-collapse @if ($creatif->offre_repliee) x-cloak @endif>

            <p class="max-w-[520px] text-[16px] text-pretty text-ub-texte2">
                {{ __('Donnez plus de visibilité à votre travail sur') }}
                <a href="{{ config('services.diffusion.url') }}" target="_blank" rel="noopener" class="text-ub-accent-texte underline">{{ parse_url(config('services.diffusion.url'), PHP_URL_HOST) }}</a>
                {!! __('et profitez d’une <b class="text-ub-formule">formule optimisée</b> avec votre code personnel.') !!}
            </p>

            <div class="mt-5 grid max-w-[520px] gap-2.5 sm:grid-cols-2">
                @foreach ([['id', __('Identifiant'), $creatif->login], ['code', __('Code promo'), $codeDiffusion]] as [$cle, $libelle, $valeur])
                    <div class="flex items-center justify-between gap-3 rounded-ub-bandeau bg-white border border-[#e3e5e5] px-3 py-2">
                        <div>
                            <div class="text-[11px] text-ub-texte3">{{ $libelle }}</div>
                            <div class="font-mono text-[15px] tracking-[.04em]">{{ $valeur }}</div>
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
            </div>
        </section>
    @else
        {{-- Label : l'etat du compte, et l'ancre vers la grille. --}}
        {{-- Le premier label reprend le fond et la typo de la carte « Formule gratuite » de la grille. --}}
        <a href="#offres" class="group mt-6 inline-flex flex-wrap items-center gap-3 text-[16px]">
            <span class="rounded-full bg-[#d8d8d4] px-5.25 py-[11.5px] font-semibold text-ub-texte2">{{ __('Vous êtes en formule gratuite') }}</span>
            <span aria-hidden="true" class="text-[26px] leading-none text-black">→</span>
            <span class="rounded-full bg-black px-5.25 py-[11.5px] font-bold text-white transition group-hover:bg-[#333]">{{ __('Passer à la formule :marque', ['marque' => $marque->nom]) }}</span>
        </a>
    @endif

    {{-- Prolonger ou souscrire. --}}
    {{-- En formule gratuite, le label ci-dessus tient lieu de titre. --}}
    {{-- Le createur qui a deja paye une fois ne souscrit plus : il
         renouvelle. La page le dit, et le douze mois porte alors le tarif
         de reabonnement. La bascule affiche les prix par mois ou au total. --}}
    <div x-data="{ total: false }">
    <div id="offres" class="mt-[58px] flex scroll-mt-6 flex-wrap items-end justify-between gap-4">
        <div>
            @if ($creatif->plan)
                <h2 class="font-titre text-[35px] font-normal">{{ __('Prolonger ma formule') }}</h2>
            @endif
            <p class="mt-1 text-[16.5px] text-pretty text-ub-texte2">
                @if ($reabonnement)
                    <span class="block">{{ __('Tarifs de renouvellement réservés aux créatifs déjà abonnés.') }}</span>
                @endif
                <span class="block">{{ __('Paiement unique, sans reconduction.') }}</span>
            </p>
        </div>

        <div class="flex rounded-full bg-white p-1 text-[14px] font-semibold">
            <button type="button" @click="total = false" :class="total ? 'text-ub-texte2' : 'bg-black text-white'" class="rounded-full px-4 py-1.5">{{ __('Par mois') }}</button>
            <button type="button" @click="total = true" :class="total ? 'bg-black text-white' : 'text-ub-texte2'" class="rounded-full px-4 py-1.5">{{ __('Total') }}</button>
        </div>
    </div>

    {{--
     | Trois cartes, et trois seulement : la gratuite, le six mois et le
     | douze mois. Le Pack Luxe et le Pack Site ne se vendent plus en
     | ligne — ils sont ecartes dans la grille, pas ici.
     |
     | Maquette « Ma formule » : la gratuite, plus etroite et en retrait,
     | rappelle ce dont dispose un compte gratuit (une `div`, pas un
     | `form`). Le douze mois est la vedette, sur fond sombre. Un clic sur
     | une carte payante la selectionne (contour turquoise) ; son bouton
     | ouvre le paiement.
    --}}
    @php
        $limites = config('formules.limites');
        $dernier = array_key_last($options);
        $avantages = [
            __(':n visuels', ['n' => $limites['payante']['visuels']]),
            __('Pages illimitées'),
            __(':n Mo d’espace', ['n' => round($limites['payante']['poids_ko'] / 1000)]),
            __('Référencement IA de vos images'),
        ];
    @endphp

    <div x-data="{ choisie: @js($dernier) }"
         class="mt-4 grid grid-cols-1 items-center gap-1 pt-3.5 lg:grid-cols-[minmax(0,.8fr)_minmax(0,1.1fr)_minmax(0,1.1fr)]">

        {{-- La gratuite : en retrait, sans ombre. --}}
        <div class="flex flex-col rounded-xl bg-[#f6f5f3] px-4.5 py-[38px] text-ub-texte opacity-75">
            <h3 class="text-[17px] font-bold">{{ __('Formule gratuite') }}</h3>
            <p class="text-[13px] text-[#6b7178]">{{ __('Sans limite de durée') }}</p>

            <p class="mt-4 mb-0.5 text-[36px] font-bold leading-none tracking-[-.02em]">0 €</p>

            <p class="min-h-9 text-[13px] text-[#6b7178]">
                {{ $creatif->plan ? __('Votre formule précédente') : __('Votre formule actuelle') }}
            </p>

            <div class="my-3.5 border-t border-[#eceae6]"></div>

            <ul role="list" class="flex flex-1 flex-col gap-2.25 text-[14px]">
                <li class="flex gap-2"><span class="font-bold text-ub-accent" aria-hidden="true">✓</span>{{ __(':n visuels', ['n' => $limites['gratuite']['visuels']]) }}</li>
                <li class="flex gap-2"><span class="font-bold text-ub-accent" aria-hidden="true">✓</span>{{ __(':n pages', ['n' => $limites['gratuite']['pages']]) }}</li>
                <li class="flex gap-2"><span class="font-bold text-ub-accent" aria-hidden="true">✓</span>{{ __(':n Mo d’espace', ['n' => round($limites['gratuite']['poids_ko'] / 1000)]) }}</li>
                <li class="flex gap-2 opacity-45"><span class="font-bold text-ub-accent" aria-hidden="true">—</span>{{ __('Référencement IA') }}</li>
            </ul>

            <p class="mt-5 py-2.75 text-center text-[14px] font-semibold text-[#7a8087]">{{ __('Formule actuelle de base') }}</p>
        </div>

        @foreach ($options as $numero => $o)
            @php
                $vedette = $numero === $dernier;
                $parMois = $o['ttc'] / $o['mois'];
                $sous = $vedette ? 'text-[#9fd9df]' : 'text-[#6b7178]';
            @endphp

            <form data-payplug method="post" action="{{ route(nom_route('espace.formule.payer'), $numero) }}"
                  @click="choisie = @js($numero)"
                  :class="choisie === @js($numero) ? 'outline-2 -outline-offset-2 outline-ub-accent' : ''"
                  class="relative flex cursor-pointer flex-col rounded-xl px-6.5 py-[54px]
                         {{ $vedette ? 'bg-[#1a1d21] text-white shadow-[0_24px_48px_-16px_rgba(20,30,40,.45)]' : 'bg-white text-ub-texte shadow-[0_18px_40px_-18px_rgba(20,30,40,.25)]' }}">
                @csrf

                @if ($o['promo'] ?? false)
                    <span class="absolute -top-2.75 left-5.5 rounded-full bg-ub-rouge px-2.5 py-0.75 text-[11px] font-bold text-white">
                        {{ $o['promo'] === 'blackfriday' ? 'Black Friday' : __('Promotion du jour') }}
                    </span>
                @elseif ($vedette)
                    <span class="absolute -top-2.75 left-5.5 rounded-full bg-ub-accent px-2.5 py-0.75 text-[11px] font-bold text-white">
                        {{ __('Meilleur prix') }}@isset($o['barre']) · −{{ round((1 - $o['ttc'] / $o['barre']) * 100) }} %@endisset
                    </span>
                @endif

                <h3 class="text-[17px] font-bold">{{ __($o['mois'] === 12 ? 'Formule 12 mois' : 'Formule 6 mois') }}</h3>
                <p class="text-[13px] {{ $sous }}">
                    {{ ($o['reabonnement'] ?? false) ? __('Réabonnement · :n mois', ['n' => $o['mois']]) : __(':n mois', ['n' => $o['mois']]) }}
                </p>

                <p class="mt-4 mb-0.5 flex items-baseline gap-1.5">
                    <span class="text-[36px] font-bold leading-none tracking-[-.02em]"
                          x-text="total ? @js(number_format($o['ttc'], 2, ',', ' ').' €') : @js(number_format($parMois, 2, ',', ' ').' €')">{{ number_format($parMois, 2, ',', ' ') }} €</span>
                    <span class="text-[13px] {{ $sous }}" x-text="total ? @js(__('au total')) : @js(__('/ mois'))">{{ __('/ mois') }}</span>
                </p>

                <p class="min-h-9 text-[13px] {{ $sous }}">
                    {{ __('Facturé :montant € en une fois', ['montant' => number_format($o['ttc'], 2, ',', ' ')]) }}
                    @isset($o['barre'])
                        {{ __('au lieu de') }} <s>{{ number_format($o['barre'], 2, ',', ' ') }} €</s>
                    @endisset
                </p>

                <div class="my-3.5 border-t {{ $vedette ? 'border-[#33383d]' : 'border-[#eceae6]' }}"></div>

                <ul role="list" class="flex flex-1 flex-col gap-2.25 text-[14px]">
                    @foreach ($avantages as $a)
                        <li class="flex gap-2"><span class="font-bold text-ub-accent" aria-hidden="true">✓</span>{{ $a }}</li>
                    @endforeach
                </ul>

                {{-- Carte choisie : bouton turquoise. Sinon noir, ou blanc
                     sur la carte sombre. --}}
                <button type="submit"
                        :class="choisie === @js($numero) ? '!bg-ub-accent !text-white' : @js($vedette ? '!bg-white !text-[#1a1d21]' : '')"
                        class="bouton-espace bouton-espace-grand mt-5 w-full !rounded-sm">
                    <span x-text="choisie === @js($numero) ? @js(__('Sélectionnée ✓')) : @js(__('Sélectionner'))">{{ __('Sélectionner') }}</span>
                </button>
            </form>
        @endforeach
    </div>

    <p class="mt-3 mb-10 text-[13.8px] text-ub-texte2">{{ __('SAS POLYGUN — paiements sécurisés par PayPlug Enterprise SAS, établissement de paiement agréé par l’ACPR (CIB : 16778), 110 avenue de France, 75013 Paris.') }}</p>
    </div>

    {{-- Option nom de domaine : developpement seulement pour l'instant. --}}
    @if (\App\Models\Domaine::achatOuvert())
        <livewire:espace.nom-de-domaine />
    @endif

    {{-- Activation de code, puis factures, CGV et securite : une pile de
         cartouches identiques. --}}
    <div class="mt-10">
        <livewire:espace.parrainage />

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

{{-- Paiement Payplug dans une fenetre flottante (formules et nom de domaine).
     Sans le script Payplug ou en cas d'echec : redirection classique. --}}
<style>
    /* Fenetre flottante Payplug. Son script etend l'iframe a toute la page
       (transparente) et y dessine la carte de paiement : on la ramene a la
       taille de cette carte, centree, avec 20px de blanc autour et l'arrondi
       des formules. Styles poses en ligne par form.latest.js : !important. */
    /* --pp-rogne : la page Payplug (dans l'iframe, autre domaine, non
       stylable) ouvre sur une marge de 100px. On en rogne 90px par le
       haut (clip-path) pour n'en garder que 10px, et on remonte la fenetre
       de la moitie pour qu'elle reste centree. */
    :root { --pp-h: min(634px, calc(100vh - 162px)); --pp-rogne: 90px; }
    #iframe-payplug {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, calc(-50% - var(--pp-rogne) / 2));
        clip-path: inset(var(--pp-rogne) 0 0 0 round 1.5rem);
        width: min(400px, calc(100vw - 92px)) !important;
        height: var(--pp-h) !important;
        margin: 0 !important;
        border: 30px solid #fff !important;
        border-bottom-width: 100px !important; /* place de la mention legale */
        background: #fff !important;
        border-radius: 1.5rem !important;
        box-sizing: content-box !important;
    }
    /* Croix de fermeture : dans le coin haut droit de la fenetre. */
    #wrapper-close-3ds {
        position: fixed !important;
        top: calc(50% - (var(--pp-h) + 130px) / 2 + var(--pp-rogne) / 2 + 6px) !important;
        left: calc(50% + min(200px, 50vw - 46px) - 9px) !important;
        z-index: 10000;
    }
    /* Mention legale, dans la bande blanche sous le formulaire Payplug. */
    #mention-payplug {
        position: fixed;
        z-index: 9999;
        left: 50%;
        top: calc(50% - (var(--pp-h) + 130px) / 2 + var(--pp-h) + 38px - var(--pp-rogne) / 2);
        transform: translateX(-50%);
        width: min(400px, calc(100vw - 92px));
        font: 12.65px/1.4 system-ui, sans-serif;
        color: #474747;
        text-align: center;
        text-wrap: pretty;
    }
</style>
<script src="https://api.payplug.com/js/1/form.latest.js"></script>
<script>
(() => {
    const ouvrir = (url) => {
        if (! window.Payplug) return (window.location.href = url);
        Payplug.showPayment(url);
        // Disparait avec la fenetre : Payplug retire tout son conteneur a la fermeture.
        const mention = Object.assign(document.createElement('p'), { id: 'mention-payplug', textContent: {{ Js::from(__('SAS POLYGUN — paiements sécurisés par PayPlug Enterprise SAS, établissement de paiement agréé par l’ACPR (CIB : 16778), 110 avenue de France, 75013 Paris.')) }} });
        document.getElementById('wrapper-payplug-iframe')?.appendChild(mention);
    };

    window.addEventListener('payplug-ouvrir', (e) => ouvrir(e.detail.url));

    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (! form.matches || ! form.matches('form[data-payplug]')) return;
        e.preventDefault();
        try {
            const r = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (! r.ok) throw new Error(r.status);
            ouvrir((await r.json()).url);
        } catch (err) {
            form.submit();
        }
    });
})();
</script>
@endsection
