@extends('layouts.espace')

@section('title', __('Tableau de bord'))

@section('content')
    <x-espace.titre>{{ __('Tableau de bord') }}</x-espace.titre>

    {{-- Les deux entrees en matiere : charger des images, personnaliser le
         portfolio. Ce sont les deux gestes que le createur vient faire. --}}
    <x-espace.hero
        :illustration="asset('img_admin/int-tab-download.svg')"
        :alt="__('Charger vos images')"
        :lien="route(nom_route('espace.galeries'))"
        :titre="__('Charger')"
        :suite="__('mes images')"
        class="mb-14">
        <span class="fonticon-plus-square text-[28px] text-ub-turquoise" aria-hidden="true"></span>
        {{ __('Dossier images') }}
    </x-espace.hero>

    <x-espace.hero
        :illustration="asset('img_admin/int-tab-conf.svg')"
        :alt="__('Personnalisez votre portfolio')"
        :lien="route(nom_route('espace.design'))"
        :titre="__('Personnaliser')"
        :suite="__('mon portfolio')"
        class="mb-14">
        <span class="fonticon-share text-[28px] text-ub-turquoise" aria-hidden="true"></span>
        {{ __('Menu apparence') }}
    </x-espace.hero>

    {{-- La carte du book, puis les deux codes QR : celui du mini-book sur
         le portail, celui du book sur son sous-domaine. --}}
    <div class="grid gap-8 md:grid-cols-[220px_1fr]">

        <div class="overflow-hidden rounded-ub border border-ub-gris-clair bg-white">
            <a href="{{ $creatif->portfolioUrl() }}" class="block">
                @php($couverture = $creatif->media()->published()->whereNot('filename', '')->orderBy('position')->first())

                @if ($couverture)
                    <img src="{{ $couverture->url('front_desk') }}" alt="{{ $creatif->fullName() }}" class="h-40 w-full object-cover">
                @else
                    <div class="flex h-40 items-center justify-center bg-ub-gris-clair text-[13px] text-ub-gris-fonce">
                        {{ __('Aucun visuel') }}
                    </div>
                @endif
            </a>

            <div class="relative px-3 pb-4 pt-8 text-center">
                <x-espace.vignette :creatif="$creatif" class="absolute -top-6 left-1/2 h-12 w-12 -translate-x-1/2 border-2 border-white" />

                <div class="font-titre text-[17px] lowercase">{{ $creatif->fullName() }}</div>
                <div class="mt-1 text-[11px] uppercase tracking-wide text-ub-gris-fonce">{{ $creatif->category?->name }}</div>
            </div>

            <div class="flex items-center justify-around border-t border-ub-gris-clair px-3 py-2 text-[13px] text-ub-gris-fonce">
                <span class="inline-flex items-center gap-1">
                    <x-espace.icone nom="vues" class="h-4 w-4" />{{ number_format($visites['total'], 0, ',', ' ') }}
                </span>
                <span class="inline-flex items-center gap-1">
                    <x-espace.icone nom="coeur" class="h-4 w-4" />{{ $creatif->memoCount ?? 0 }}
                </span>
            </div>
        </div>

        <div class="space-y-6">
            <div class="flex items-center gap-5 rounded-ub border border-ub-gris-clair p-4">
                <div class="shrink-0">{!! $qrMinibook !!}</div>
                <div>
                    <div class="font-titre text-[19px] font-light">{{ __('Mon') }} <strong class="font-bold">{{ __('mini-book') }}</strong></div>
                    <a href="{{ $lienMinibook }}" class="mt-1 inline-flex items-center gap-2 text-[13px] text-ub-texte hover:text-ub-rouge">
                        <x-espace.icone nom="lien" class="h-4 w-4" />{{ $lienMinibook }}
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-5 p-4">
                <div class="shrink-0">{!! $qrBook !!}</div>
                <div>
                    <div class="font-titre text-[19px] font-light">{{ __('Mon') }} <strong class="font-bold">book</strong></div>
                    <a href="{{ $creatif->bookUrl() }}" target="_blank" rel="noopener"
                       class="mt-1 inline-flex items-center gap-2 text-[13px] text-ub-texte hover:text-ub-rouge">
                        <x-espace.icone nom="lien" class="h-4 w-4" />{{ $creatif->bookUrl() }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Les visites : le total, puis leur repartition par support. --}}
    <section class="mt-16">
        <h2 class="font-titre text-[28px] font-light text-black">{{ __('Stats') }}</h2>

        <div class="mt-2 flex flex-wrap items-baseline justify-between gap-4">
            <span class="inline-flex items-center gap-2 text-[17px]">
                {{ __('Nombre total de visites') }}
                <x-espace.icone nom="graphe" class="h-4 w-4 text-ub-gris-fonce" />
            </span>
            <span class="font-titre text-[42px] font-light leading-none">{{ number_format($visites['total'], 0, ',', ' ') }}</span>
        </div>

        <x-espace.anneau :parts="$visites['parts']" class="mt-10" />
    </section>

    {{-- Les trois lignes de bas de page que l'original affiche en tout
         petit : elles servent au support pour identifier un compte. --}}
    <div class="mt-14 border-t border-dotted border-[#aaa] pt-5 text-[11px] leading-5 text-ub-gris-fonce">
        {{ __('Version book : ') }}{{ $creatif->bookSetting?->theme }}<br>
        {{ __('id : ') }}{{ $creatif->id }}<br>
        {{ __('identifiant de connexion : ') }}{{ $creatif->login }}
    </div>
@endsection
