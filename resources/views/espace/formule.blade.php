@extends('layouts.espace')

@section('title', __('Formule'))

@section('content')
    <h1 class="text-2xl font-light">{{ __('Formule') }}</h1>

    <div class="mt-6 max-w-xl rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
        @if ($creatif->plan)
            <p>{{ __('Formule :marque', ['marque' => $marque->nom]) }}</p>
            @if ($echeance)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $echeance->isPast() ? __('Expirée le :date', ['date' => $echeance->format('d/m/Y')]) : __('Valable jusqu’au :date', ['date' => $echeance->format('d/m/Y')]) }}
                </p>
            @endif
        @else
            <p>{{ __('Formule gratuite') }}</p>
        @endif
    </div>

    <h2 class="mt-10 text-lg font-light">{{ $creatif->plan ? __('Prolonger ma formule') : __('Passer à la formule :marque', ['marque' => $marque->nom]) }}</h2>
    <div class="mt-3 grid max-w-3xl gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($options as $numero => $o)
            <form method="post" action="{{ route('espace.formule.payer', $numero) }}" class="flex flex-col rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                @csrf
                @isset($o['promo'])
                    <span class="mb-1 w-fit rounded-full bg-red-600 px-2 py-0.5 text-xs text-white">{{ $o['promo'] === 'blackfriday' ? 'Black Friday' : __('Promotion du jour') }}</span>
                @endisset
                <span class="font-medium">{{ __($o['libelle']) }}</span>
                <span class="mt-2 text-2xl font-light">
                    {{ number_format($o['ttc'], 2, ',', ' ') }} €
                    @isset($o['barre']) <s class="text-sm text-gray-400">{{ number_format($o['barre'], 2, ',', ' ') }} €</s> @endisset
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __(':n mois, TTC', ['n' => $o['mois']]) }}</span>
                <div class="mt-4"><x-espace.bouton>{{ __('Payer par carte') }}</x-espace.bouton></div>
            </form>
        @endforeach
    </div>
    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Paiement sécurisé par Payplug. Une formule en cours est prolongée à partir de son échéance.') }}</p>

    <livewire:espace.parrainage />

    <h2 class="mt-10 text-lg font-light">{{ __('Factures') }}</h2>
    <ul class="mt-3 max-w-xl divide-y divide-gray-200 rounded-lg bg-white shadow-sm dark:divide-gray-600 dark:bg-gray-800">
        @forelse ($factures as $f)
            <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                <span>{{ $f->issued_at?->format('d/m/Y') }} — {{ $f->designation ?: $f->label }}</span>
                <span class="whitespace-nowrap">{{ number_format((float) $f->amount, 2, ',', ' ') }} €</span>
                <a href="{{ route('espace.facture', $f) }}" target="_blank" class="underline">{{ $f->numero() }}</a>
                <a href="{{ route('espace.facture.pdf', $f) }}" class="underline">PDF</a>
            </li>
        @empty
            <li class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Aucune facture.') }}</li>
        @endforelse
    </ul>
@endsection
