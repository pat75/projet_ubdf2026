@extends('layouts.visiteur')

@section('title', __('Tableau de bord'))

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Bonjour') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Tableau de bord') }}</h1>
        </div>
    </div>

    @unless ($visiteur->email_verified_at)
        <div class="carte-espace flex flex-wrap items-center justify-between gap-3 p-5">
            <p class="text-[15px] text-ub-texte2">
                {{ __('Confirmez votre adresse :email pour retrouver aussi les messages envoyés aux créatifs avant l’ouverture de votre compte.', ['email' => $visiteur->email]) }}
            </p>
            <form method="post" action="{{ lien('visiteur.confirmation') }}">
                @csrf
                <button type="submit" class="bouton-espace bouton-espace-petit px-4">{{ __('Renvoyer le mail') }}</button>
            </form>
        </div>
    @endunless

    {{-- Memo book --}}
    <x-espace.carte :titre="__('Memo Books')" :sous-titre="trans_choice(':n book enregistré|:n books enregistrés', $memoTotal, ['n' => $memoTotal])">
        <x-slot:action>
            <a href="{{ lien('memobook') }}" class="bouton-espace bouton-espace-petit px-4">{{ __('Voir mon mémoBook') }}</a>
        </x-slot:action>

        @if ($memo->isEmpty())
            <p class="text-[15px] text-ub-texte3">{{ __('Cliquez sur le cœur d’un book, sur le portail, pour le garder ici.') }}</p>
        @else
            {{-- Les dix derniers books du memo, un avatar chacun, superposes
                 comme dans « Mes derniers messages ». --}}
            @php($profil = app(\App\Services\Espace\AffichageProfil::class))
            <ul class="flex flex-wrap pl-[19px]">
                @foreach ($memo as $book)
                    @php($photo = $profil->photoUrl($book))
                    <li class="group/avatar -ml-[19px] hover:z-10 focus-within:z-10 relative">
                        <a href="{{ $book->bookUrl() }}" target="_blank" rel="noopener"
                           class="relative block h-14 w-14 rounded-full ring-2 ring-white transition hover:-translate-y-0.5">
                            @if ($photo)
                                <img src="{{ $photo }}" alt="{{ $book->fullName() }}" class="h-14 w-14 rounded-full object-cover">
                            @else
                                <span class="flex h-14 w-14 items-center justify-center rounded-full text-[16px] font-bold text-white"
                                      style="background: {{ $profil->couleur($book) }}">{{ $profil->initiales($book) }}</span>
                                <span class="sr-only">{{ $book->fullName() }}</span>
                            @endif
                        </a>
                        <x-espace.bulle-avatar>{{ $book->fullName() }}</x-espace.bulle-avatar>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-espace.carte>

    {{-- Derniers messages --}}
    <x-espace.carte :titre="__('Mes derniers messages')">
        <x-slot:action>
            <a href="{{ lien('visiteur.messages') }}" class="bouton-espace bouton-espace-petit px-4">{{ __('Tous mes messages') }}</a>
        </x-slot:action>

        @if ($messages->isEmpty())
            <p class="text-[15px] text-ub-texte3">{{ __('Aucun message envoyé depuis cette adresse.') }}</p>
        @else
            {{-- Les huit derniers echanges, un avatar du createur chacun :
                 le clic ouvre l'echange deplie dans Mes messages. --}}
            @php($profil = app(\App\Services\Espace\AffichageProfil::class))
            {{-- Avatars superposes d'un tiers (19px sur 56), le survol passe devant. --}}
            <ul class="flex flex-wrap pl-[19px]">
                @foreach ($messages as $fil)
                    @continue(! $fil->user)
                    @php($photo = $profil->photoUrl($fil->user))
                    <li class="group/avatar -ml-[19px] hover:z-10 focus-within:z-10 relative">
                        <a href="{{ lien('visiteur.messages', ['fil' => $fil->id]) }}"
                           class="relative block h-14 w-14 rounded-full ring-2 ring-white transition hover:-translate-y-0.5">
                            @if ($photo)
                                <img src="{{ $photo }}" alt="{{ $fil->user->fullName() }}" class="h-14 w-14 rounded-full object-cover">
                            @else
                                <span class="flex h-14 w-14 items-center justify-center rounded-full text-[16px] font-bold text-white"
                                      style="background: {{ $profil->couleur($fil->user) }}">{{ $profil->initiales($fil->user) }}</span>
                                <span class="sr-only">{{ $fil->user->fullName() }}</span>
                            @endif
                            @if ($fil->non_lus > 0)
                                <span class="absolute -left-1 -top-1 min-w-5 rounded-full bg-ub-accent px-1.5 text-center text-[11px] font-bold leading-5 text-white">{{ $fil->non_lus }}</span>
                            @endif
                        </a>
                        <x-espace.bulle-avatar>{{ $fil->user->fullName() }}</x-espace.bulle-avatar>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-espace.carte>

    {{-- Dernieres visites --}}
    <x-espace.carte :titre="__('Mes dernières visites')">
        <livewire:visiteur.dernieres-visites />
    </x-espace.carte>
@endsection
