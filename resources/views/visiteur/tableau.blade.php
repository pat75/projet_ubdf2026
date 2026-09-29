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
                {{ __('Confirmez votre adresse :email pour retrouver ici les messages envoyés aux créatifs.', ['email' => $visiteur->email]) }}
            </p>
            <form method="post" action="{{ lien('visiteur.confirmation') }}">
                @csrf
                <button type="submit" class="bouton-espace bouton-espace-petit px-4">{{ __('Renvoyer le mail') }}</button>
            </form>
        </div>
    @endunless

    {{-- Memo book --}}
    <x-espace.carte :titre="__('Mon mémoBook')" :sous-titre="trans_choice(':n book enregistré|:n books enregistrés', $memoTotal, ['n' => $memoTotal])">
        @if ($memo->isEmpty())
            <p class="text-[15px] text-ub-texte3">{{ __('Cliquez sur le cœur d’un book, sur le portail, pour le garder ici.') }}</p>
        @else
            <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @foreach ($memo as $book)
                    @php($couverture = $book->media->first())
                    <li>
                        <a href="{{ $book->bookUrl() }}" target="_blank" rel="noopener" class="group block">
                            <span class="block h-24 overflow-hidden rounded-ub bg-ub-fond">
                                @if ($couverture)
                                    <img src="{{ $couverture->url('front_desk') }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                @endif
                            </span>
                            <span class="mt-2 block truncate text-[15px] font-semibold text-ub-texte group-hover:underline">{{ $book->fullName() }}</span>
                            <span class="block truncate text-[12px] uppercase tracking-wide text-ub-texte3">{{ $book->category?->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-5">
            <a href="{{ lien('memobook') }}" class="bouton-espace bouton-espace-grand px-6">{{ __('Voir mon mémoBook') }}</a>
        </div>
    </x-espace.carte>

    {{-- Derniers messages --}}
    <x-espace.carte :titre="__('Mes derniers messages')">
        @if (! $visiteur->email_verified_at)
            <p class="text-[15px] text-ub-texte3">{{ __('Vos messages apparaîtront ici une fois votre adresse confirmée.') }}</p>
        @elseif ($messages->isEmpty())
            <p class="text-[15px] text-ub-texte3">{{ __('Aucun message envoyé depuis cette adresse.') }}</p>
        @else
            <ul>
                @foreach ($messages as $fil)
                    <li class="border-b border-ub-filet last:border-b-0">
                        <a href="{{ lien('memobook.message', ['conversation' => $fil->id]) }}" class="flex items-center gap-3 py-3">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[15px] font-semibold text-ub-texte">{{ $fil->user?->fullName() }}</span>
                                <span class="block truncate text-[13px] text-ub-texte3">{{ $fil->objet() }} · {{ $fil->last_message_at?->translatedFormat('j F Y') }}</span>
                            </span>
                            @if ($fil->non_lus > 0)
                                <span class="rounded-full bg-ub-accent px-2 py-px text-[12px] font-bold text-white">{{ $fil->non_lus }}</span>
                            @endif
                            <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-espace.carte>

    {{-- Dernieres visites --}}
    <x-espace.carte :titre="__('Mes dernières visites')">
        @if ($visites->isEmpty())
            <p class="text-[15px] text-ub-texte3">{{ __('Les books que vous consultez, connecté, s’afficheront ici.') }}</p>
        @else
            <ul>
                @foreach ($visites as $book)
                    <li class="border-b border-ub-filet last:border-b-0">
                        <a href="{{ $book->bookUrl() }}" target="_blank" rel="noopener" class="flex items-center gap-3 py-3">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[15px] font-semibold text-ub-texte">{{ $book->fullName() }}</span>
                                <span class="block truncate text-[13px] text-ub-texte3">{{ $book->category?->name }} · {{ \Illuminate\Support\Carbon::parse($book->pivot->visited_at)->diffForHumans() }}</span>
                            </span>
                            <x-espace.picto nom="angle-droite" class="h-5 w-5 shrink-0 text-ub-texte" />
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-espace.carte>
@endsection
