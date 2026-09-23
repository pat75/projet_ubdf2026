@extends('layouts.espace')

@section('title', __('Statistiques'))

@php($max = max(1, $parJour->max()))
@php($maxMois = max(1, $parMois->max()))

@section('content')
    <x-espace.titre>{{ __('Mes statistiques') }}</x-espace.titre>

    <dl class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3">
        @foreach ([__('Aujourd’hui') => $parJour->last(), __('30 derniers jours') => $parJour->sum(), __('Depuis l’ouverture') => $total] as $libelle => $valeur)
            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $libelle }}</dt>
                <dd class="mt-1 text-2xl font-light">{{ number_format($valeur, 0, ',', ' ') }}</dd>
            </div>
        @endforeach
    </dl>

    <h2 class="mt-10 text-lg font-light">{{ __('Visites des 30 derniers jours') }}</h2>
    <div class="mt-3 flex h-40 items-end gap-1 rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
        @foreach ($parJour as $date => $n)
            <div class="flex-1 rounded-t bg-gray-800 dark:bg-gray-300" style="height: {{ max(2, round($n / $max * 100)) }}%"
                 title="{{ \Illuminate\Support\Carbon::parse($date)->format('d/m') }} : {{ $n }}"></div>
        @endforeach
    </div>

    <h2 class="mt-10 text-lg font-light">{{ __('Par mois') }}</h2>
    <ul class="mt-3 max-w-xl space-y-1 text-sm">
        @foreach ($parMois as $mois => $n)
            <li class="flex items-center gap-3">
                <span class="w-20 text-gray-600 dark:text-gray-400">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $mois)->translatedFormat('M Y') }}</span>
                <span class="h-3 rounded bg-gray-800 dark:bg-gray-300" style="width: {{ max(1, round($n / $maxMois * 70)) }}%"></span>
                <span>{{ number_format($n, 0, ',', ' ') }}</span>
            </li>
        @endforeach
    </ul>
    <p class="mt-6 text-xs text-gray-500 dark:text-gray-400">{{ __('Vos propres visites ne sont pas comptées.') }}</p>
@endsection
