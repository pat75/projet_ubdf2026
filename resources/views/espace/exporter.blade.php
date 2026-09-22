@extends('layouts.espace')

@section('title', __('Exporter'))

@php($code = '<iframe src="'.route('microbook', ['admin' => 0, 'pied' => 1, 'login' => auth()->user()->login]).'" scrolling="no" width="270" height="600" style="border:none" title="'.e(auth()->user()->fullName()).'"></iframe>')

@section('content')
    <h1 class="text-2xl font-light">{{ __('Exporter son book sur d’autres sites') }}</h1>
    <p class="mt-2 max-w-xl text-sm text-gray-600 dark:text-gray-400">{{ __('Copiez ce code dans votre site ou votre blog : il affiche une vignette de votre book avec vos derniers visuels.') }}</p>

    <div class="mt-6 flex flex-col gap-6 md:flex-row" x-data="{ copie: false }">
        <div class="max-w-md flex-1">
            <textarea readonly rows="6" x-ref="code" x-on:focus="$el.select()"
                      class="w-full rounded-md border border-gray-300 px-3 py-2 font-mono text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">{{ $code }}</textarea>
            <button type="button" class="mt-2 text-sm underline" x-on:click="navigator.clipboard.writeText($refs.code.value); copie = true; setTimeout(() => copie = false, 1500)">
                <span x-show="!copie">{{ __('Copier le code') }}</span><span x-show="copie" x-cloak>{{ __('Copié') }}</span>
            </button>
            @unless (auth()->user()->bookSetting?->diffuse_web)
                <p class="mt-2 text-sm text-red-600">{{ __('Votre book est hors ligne : la vignette ne s’affichera pas.') }}</p>
            @endunless
        </div>
        {!! $code !!}
    </div>
@endsection
