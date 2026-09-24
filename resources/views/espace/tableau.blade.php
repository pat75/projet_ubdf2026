@extends('layouts.espace')

@section('title', __('Tableau de bord'))

@section('content')
    {{-- En-tete : salutation, titre, et l'acces direct au book publie. --}}
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-texte3">
                {{ __('Bonjour :prenom', ['prenom' => $creatif->firstname ?: $creatif->login]) }}
            </div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Tableau de bord') }}</h1>
        </div>

        <a href="{{ $creatif->portfolioUrl() }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 rounded-ub bg-ub-texte px-4.5 py-2.5 text-[14px] font-bold text-white hover:bg-black">
            {{ __('Voir mon book ↗') }}
        </a>
    </div>

    {{-- Les deux entrees en matiere : charger des images, personnaliser le
         portfolio. Ce sont les deux gestes que le createur vient faire. --}}
    <div class="grid gap-5 sm:grid-cols-2">
        <a href="{{ route(nom_route('espace.galeries')) }}"
           class="carte-espace flex items-center gap-5 p-5.5 transition hover:shadow-[0_6px_24px_rgba(22,169,181,.14)]">
            <img src="{{ asset('img_admin/int-tab-download.svg') }}" alt="{{ __('Charger vos images') }}" class="h-[108px] w-[108px] shrink-0 object-contain">

            <div class="min-w-0">
                <span class="text-[22px] font-light leading-[1.15] text-ub-accent-texte"><strong class="font-bold">{{ __('Charger') }}</strong> {{ __('mes images') }}</span>
                <p class="mt-1.5 text-[14px] text-ub-texte2 text-pretty">{{ __('Ajoutez de nouveaux projets depuis votre dossier images.') }}</p>
                <span class="mt-1.5 inline-flex items-center gap-1.5 text-[14px] font-bold text-ub-texte">
                    <span class="fonticon-plus-square text-[16px] text-ub-accent" aria-hidden="true"></span>
                    {{ __('Dossier images →') }}
                </span>
            </div>
        </a>

        <a href="{{ route(nom_route('espace.design')) }}"
           class="carte-espace flex items-center gap-5 p-5.5 transition hover:shadow-[0_6px_24px_rgba(22,169,181,.14)]">
            <img src="{{ asset('img_admin/int-tab-conf.svg') }}" alt="{{ __('Personnalisez votre portfolio') }}" class="h-[108px] w-[108px] shrink-0 object-contain">

            <div class="min-w-0">
                <span class="text-[22px] font-light leading-[1.15] text-ub-accent-texte"><strong class="font-bold">{{ __('Personnaliser') }}</strong> {{ __('mon portfolio') }}</span>
                <p class="mt-1.5 text-[14px] text-ub-texte2 text-pretty">{{ __('Couleurs, typographies et mise en page de votre book.') }}</p>
                <span class="mt-1.5 inline-flex items-center gap-1.5 text-[14px] font-bold text-ub-texte">
                    <span class="fonticon-share text-[16px] text-ub-accent" aria-hidden="true"></span>
                    {{ __('Menu apparence →') }}
                </span>
            </div>
        </a>
    </div>

    {{-- La carte du book, puis les deux liens a partager, chacun avec son
         code QR : celui du mini-book sur le portail, celui du book sur
         son sous-domaine. --}}
    <div class="mt-6 flex flex-wrap items-stretch gap-5">

        {{-- Exactement la carte que les visiteurs voient sur la page
             d'accueil : voir x-espace.carte-portail. --}}
        <x-espace.carte-portail :creatif="$creatif" :visites="$visites['total']" class="mx-auto shrink-0 sm:mx-0" />

        {{-- Les liens : un bouton copie, un bouton ouvre. Alpine tient
             juste lequel des deux vient d'etre copie, pour le message. --}}
        <div class="flex min-w-0 flex-[999] basis-105 flex-col gap-3.5"
             x-data="{ copie: null, copier(cle, valeur) {
                 navigator.clipboard?.writeText(valeur);
                 this.copie = cle;
                 clearTimeout(this.t); this.t = setTimeout(() => this.copie = null, 1500);
             } }">
            <h2 class="text-[13px] font-bold uppercase tracking-[.08em] text-ub-texte3">{{ __('Partager mes liens') }}</h2>

            <div class="carte-espace flex flex-wrap items-center gap-5 p-4.5">
                <div class="shrink-0">{!! $qrMinibook !!}</div>

                <div class="flex min-w-50 flex-1 flex-col gap-2.5">
                    <span class="text-[19px] font-light">{{ __('Mon') }} <strong class="font-bold">{{ __('mini-book') }}</strong></span>

                    <div class="flex flex-wrap items-center gap-2">
                        <code class="min-w-0 flex-1 truncate rounded-md bg-ub-fond px-2.5 py-2 font-mono text-[12px] text-ub-texte2">{{ $lienMinibook }}</code>

                        <button type="button" @click="copier('mini', @js($lienMinibook))"
                                class="shrink-0 rounded-md border border-ub-bord px-3.5 py-2 text-[13px] font-bold text-ub-texte hover:border-ub-accent hover:text-ub-accent-texte">
                            <span x-text="copie === 'mini' ? @js(__('Copié ✓')) : @js(__('Copier'))">{{ __('Copier') }}</span>
                        </button>

                        <a href="{{ $lienMinibook }}" class="shrink-0 rounded-md bg-ub-accent px-3.5 py-2 text-[13px] font-bold text-white hover:bg-ub-accent-fonce">
                            {{ __('Ouvrir ↗') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="carte-espace flex flex-wrap items-center gap-5 p-4.5">
                <div class="shrink-0">{!! $qrBook !!}</div>

                <div class="flex min-w-50 flex-1 flex-col gap-2.5">
                    <span class="text-[19px] font-light">{{ __('Mon') }} <strong class="font-bold">book</strong></span>

                    <div class="flex flex-wrap items-center gap-2">
                        <code class="min-w-0 flex-1 truncate rounded-md bg-ub-fond px-2.5 py-2 font-mono text-[12px] text-ub-texte2">{{ $creatif->bookUrl() }}</code>

                        <button type="button" @click="copier('book', @js($creatif->bookUrl()))"
                                class="shrink-0 rounded-md border border-ub-bord px-3.5 py-2 text-[13px] font-bold text-ub-texte hover:border-ub-accent hover:text-ub-accent-texte">
                            <span x-text="copie === 'book' ? @js(__('Copié ✓')) : @js(__('Copier'))">{{ __('Copier') }}</span>
                        </button>

                        <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"
                           class="shrink-0 rounded-md bg-ub-accent px-3.5 py-2 text-[13px] font-bold text-white hover:bg-ub-accent-fonce">
                            {{ __('Ouvrir ↗') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Les visites : le total, puis leur repartition par support. --}}
    <section class="carte-espace mt-6 p-7">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-titre text-[24px] font-light text-ub-texte">{{ __('Statistiques') }}</h2>
                <p class="mt-1 text-[14px] text-ub-texte3">{{ __('Nombre total de visites, tous formats confondus') }}</p>
            </div>

            <span class="font-titre text-[48px] font-light leading-none tracking-tight text-ub-texte">{{ number_format($visites['total'], 0, ',', ' ') }}</span>
        </div>

        <x-espace.anneau :parts="$visites['parts']" class="mt-7" />
    </section>

    {{-- Les deux compteurs de la formule. Ils etaient sur « Ma formule »,
         ou ils coupaient la page en deux entre le remerciement et la
         grille des offres ; leur place est ici, avec les autres chiffres
         du compte. --}}
    <section class="carte-espace mt-6 p-7">
        <h2 class="font-titre text-[24px] font-light text-ub-texte">{{ __('Mes images') }}</h2>

        <div class="mt-3 border-t border-ub-filet">
            <x-espace.quota :libelle="__('Nombre total d’images :')" :quota="$quotas['images']" :lien="route(nom_route('espace.galeries'))" />
            <x-espace.quota :libelle="__('Poids total des images :')" :quota="$quotas['poids']" :lien="route(nom_route('espace.galeries'))" />
        </div>
    </section>

    {{-- Les trois lignes de bas de page que l'original affiche en tout
         petit : elles servent au support pour identifier un compte. --}}
    <div class="mt-8 flex flex-wrap gap-x-6 gap-y-1 px-1 text-[12px] text-ub-texte3">
        <span>{{ __('Version book : :v', ['v' => $creatif->bookSetting?->theme]) }}</span>
        <span>{{ __('id : :id', ['id' => $creatif->id]) }}</span>
        <span>{{ __('Identifiant de connexion : :login', ['login' => $creatif->login]) }}</span>
    </div>
@endsection
