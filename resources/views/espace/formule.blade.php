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

    <h2 class="mt-10 text-lg font-light">{{ __('Factures') }}</h2>
    <ul class="mt-3 max-w-xl divide-y divide-gray-200 rounded-lg bg-white shadow-sm dark:divide-gray-600 dark:bg-gray-800">
        @forelse ($factures as $f)
            <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                <span>{{ $f->issued_at?->format('d/m/Y') }} — {{ $f->designation ?: $f->label }}</span>
                <span class="whitespace-nowrap">{{ number_format((float) $f->amount, 2, ',', ' ') }} €</span>
                <a href="{{ route('espace.facture', $f) }}" target="_blank" class="underline">{{ $f->numero() }}</a>
            </li>
        @empty
            <li class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">{{ __('Aucune facture.') }}</li>
        @endforelse
    </ul>
@endsection
