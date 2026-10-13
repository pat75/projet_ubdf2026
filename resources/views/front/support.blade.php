@extends('layouts.portail')

@section('title', __('Nous contacter').' | '.$marque->nom)
@section('body_class', 'page_support')

{{-- Presentation reprise du formulaire de contact des books
     (book/commun/_formulaire-contact), couleurs des books par defaut. --}}
@php
    $champ = 'w-full border-0 border-b border-[#767676] bg-transparent px-0 py-2 text-[16px] font-light text-[#111] outline-none placeholder:text-[#666] focus:border-[#111] focus:ring-0';
@endphp

@section('content')
    <div class="ui container" style="padding:90px 0 140px">
        {{-- Deux colonnes : visuel sur un tiers, formulaire sur deux tiers ;
             visuel a droite, au-dessus sur mobile. --}}
        <div class="grid grid-cols-1 items-start gap-10 md:grid-cols-3">
        <div class="md:order-2 md:col-span-1">
            <img src="/_img_visuels/image-from-rawpixel-id-15710113-png.png" alt="" class="block h-auto w-full">
        </div>
        <article class="max-w-xl text-left md:order-1 md:col-span-2">
            <h1 class="mb-8 text-[26px]">{{ __('Nous contacter') }}</h1>

            <form method="post" action="{{ lien('support.envoyer') }}" enctype="multipart/form-data" novalidate class="flex flex-col gap-6">
                @csrf
                <input type="hidden" name="page_origine" value="{{ old('page_origine', $pageOrigine) }}">

                @if ($errors->any())
                    <ul class="border-l-2 border-red-600 pl-4 text-[14px] text-red-600">
                        @foreach ($errors->all() as $erreur)
                            <li>{{ $erreur }}</li>
                        @endforeach
                    </ul>
                @endif

                @if ($compte)
                    {{-- Creatif connecte : il est identifie, rien a saisir. --}}
                    @php($profil = app(\App\Services\Espace\AffichageProfil::class))
                    <div class="mb-7 flex items-center gap-4">
                        <img src="{{ $profil->photoUrl($compte) ?: $profil->medaillonCreatif($compte) }}" alt="" class="size-14 rounded-full object-cover">
                        <div class="text-[16px] font-light">
                            <strong class="font-semibold">{{ $compte->fullName() }}</strong><br>
                            <a href="{{ $compte->bookUrl() }}" target="_blank" rel="noopener">{{ str_replace('https://', '', $compte->bookUrl()) }}</a>
                        </div>
                    </div>
                @endif

                <div>
                    <textarea id="contact-message" name="message" aria-label="{{ __('Message') }}" rows="3" placeholder="{{ __('Mon message') }}"
                              oninput="this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px'"
                              class="{{ $champ }} resize-none">{{ old('message') }}</textarea>
                </div>

                @if ($compte)
                    {{-- Piece jointe : createur connecte uniquement, rangee dans son book. --}}
                    <div>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-[14px] text-[#333] hover:text-[#111]">
                            <span class="inline-flex h-[43.5px] items-center gap-2 border border-[#bbb] px-4">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m21 12-8.5 8.5a5 5 0 0 1-7-7L14 5a3.5 3.5 0 0 1 5 5l-8.5 8.5a2 2 0 0 1-3-3L15 8"/></svg>
                                <span>{{ __('Joindre une image ou un PDF') }}</span>
                            </span>
                            <input type="file" name="piece" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" class="sr-only"
                                   onchange="this.nextElementSibling.textContent = this.files[0]?.name ?? ''">
                            <span class="font-light text-[#666]"></span>
                        </label>
                        @error('piece')<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endif

                @guest('web')
                    <div>
                        <input id="contact-mail" name="email" aria-label="{{ __('Mail') }}" type="email" value="{{ old('email') }}" placeholder="{{ __('Mon e-mail') }}" autocomplete="email" class="{{ $champ }}">
                    </div>

                    {{-- Captcha local (App\Services\Captcha\Captcha). --}}
                    <div class="mt-2 flex items-center gap-3">
                        <img id="contact-captcha-img" src="{{ route('captcha.formulaire', 'support') }}"
                             width="158" height="53" alt="{{ __('Code à recopier') }}" class="shrink-0 bg-white">
                        <button type="button" class="cursor-pointer border-0 bg-transparent p-1 text-[#666] shadow-none outline-none hover:text-[#111]" title="{{ __('Autre code') }}" aria-label="{{ __('Autre code') }}"
                                onclick="const i = document.getElementById('contact-captcha-img'); i.src = i.src.split('?')[0] + '?' + Date.now()">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 12a8 8 0 0 1 14-5.3M20 4v5h-5M20 12a8 8 0 0 1-14 5.3M4 20v-5h5"/></svg>
                        </button>
                        <input id="contact-captcha" name="captcha" aria-label="{{ __('Code à recopier') }}" type="text" maxlength="4" autocomplete="off" autocapitalize="characters" spellcheck="false"
                               placeholder="{{ __('Recopiez le code') }}" class="{{ $champ }} w-40 min-w-0">
                    </div>
                @endguest

                <div>
                    <button type="submit" onclick="this.disabled = true; this.form.submit()"
                            class="inline-flex cursor-pointer items-center gap-2 border-0 bg-[#111] px-8 py-3 text-[13px] uppercase tracking-[1.3px] text-white transition hover:opacity-80 disabled:opacity-60">
                        {{ __('Envoyer') }}
                    </button>
                </div>
            </form>
        </article>
        </div>
    </div>
@endsection
