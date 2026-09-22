@extends('layouts.espace')

@section('title', __('Tableau de bord'))

@section('content')
    <h1 class="text-2xl font-light">{{ __('Bonjour :prenom', ['prenom' => $creatif->firstname ?: $creatif->login]) }}</h1>

    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Votre book :') }}
        <a href="{{ $creatif->bookUrl() }}" class="underline" target="_blank" rel="noopener">{{ $creatif->bookUrl() }}</a>
    </p>

    <dl class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ($chiffres as $libelle => $valeur)
            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $libelle }}</dt>
                <dd class="mt-1 text-2xl font-light">{{ $valeur }}</dd>
            </div>
        @endforeach
    </dl>
@endsection
