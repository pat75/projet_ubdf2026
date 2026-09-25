{{--
    Page contact (ex-contact.tlp.php) : titre et pied saisis par le createur,
    formulaire envoye a BookController::envoyer (x-data="contactBook",
    resources/js/book/contact.js).
--}}
@extends('book.ultra2020.layout')

@section('contenu')
    @php
        $champ = 'w-full border-0 border-b border-book-filet bg-transparent px-0 py-2 text-[16px] font-light text-book-texte outline-none placeholder:text-book-texte3 focus:border-book-texte focus:ring-0';
        $libelle = 'mb-1 block text-[13px] font-semibold uppercase tracking-[1.2px] text-book-texte2';
    @endphp

    <article @class(['mx-auto max-w-xl text-left', 'md:mx-0' => $vue->zen()])>
        @if ($vue->texte('contact_titre') !== '' || $vue->edition())
            <h1 class="mb-8 font-titre text-[26px] font-semibold leading-tight text-book-texte2 md:text-[32px]"
                @if ($vue->edition()) x-data="texteBook('contact_titre')" data-editable @endif>{!! $vue->texte('contact_titre') !!}</h1>
        @endif

        <div x-data="contactBook"
             data-msg-message="{{ __('Votre message doit contenir au moins 10 caractères.') }}"
             data-msg-mail="{{ __('Il ne s’agit pas d’un mail') }}"
             data-msg-captcha="{{ __('Recopiez les 4 caractères de l’image.') }}"
             data-msg-envoi="{{ __('Envoi impossible pour le moment.') }}">

            <div x-show="vue === 'merci'" x-cloak x-transition.opacity class="py-10">
                <p class="flex items-center gap-3 font-titre text-[22px] font-semibold text-book-texte">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                    {{ __('Merci') }}
                </p>
                <p class="mt-2 text-[16px] font-light">{{ __('Votre demande vient d’être envoyée') }}</p>
            </div>

            <form x-show="vue !== 'merci'" action="{{ route('book.contact.envoyer', ['login' => $b->us_dir]) }}" method="post"
                  @submit.prevent="envoyer($el)" novalidate class="flex flex-col gap-6">
                @csrf

                <template x-if="serveur.length">
                    <ul class="border-l-2 border-red-600 pl-4 text-[14px] text-red-600">
                        <template x-for="erreur in serveur"><li x-text="erreur"></li></template>
                    </ul>
                </template>

                <div>
                    <label for="contact-message" class="{{ $libelle }}">{{ __('Message') }}</label>
                    <textarea id="contact-message" name="fm_contact_message" rows="3" placeholder="{{ __('Mon message') }}"
                              x-data x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                              class="{{ $champ }} resize-none"></textarea>
                    <p x-show="erreurs.message" x-text="erreurs.message" x-cloak class="mt-1 text-[13px] text-red-600"></p>
                </div>

                <div>
                    <label for="contact-nom" class="{{ $libelle }}">{{ __('Prénom, nom') }}</label>
                    <input id="contact-nom" name="fm_contact_nom_prenom" type="text" placeholder="{{ __('Mon nom et prénom') }}" autocomplete="name" class="{{ $champ }}">
                </div>

                <div>
                    <label for="contact-mail" class="{{ $libelle }}">{{ __('Mail') }}</label>
                    <input id="contact-mail" name="fm_contact_mail" type="email" placeholder="{{ __('Mon e-mail') }}" autocomplete="email" class="{{ $champ }}">
                    <p x-show="erreurs.mail" x-text="erreurs.mail" x-cloak class="mt-1 text-[13px] text-red-600"></p>
                </div>


                {{-- Captcha local (App\Services\Captcha\Captcha), servi par ce sous-domaine. --}}
                <div class="mt-2">
                    <div class="flex items-center gap-3">
                        <img x-ref="captcha" src="{{ route('book.captcha', ['login' => $b->us_dir, 'formulaire' => 'contact_book']) }}"
                             data-src="{{ route('book.captcha', ['login' => $b->us_dir, 'formulaire' => 'contact_book']) }}"
                             width="158" height="53" alt="{{ __('Code à recopier') }}" class="shrink-0 bg-white">
                        <button type="button" @click="nouveauCode()" class="p-1 text-book-texte3 hover:text-book-texte" title="{{ __('Autre code') }}" aria-label="{{ __('Autre code') }}" data-curseur>
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 12a8 8 0 0 1 14-5.3M20 4v5h-5M20 12a8 8 0 0 1-14 5.3M4 20v-5h5"/></svg>
                        </button>
                        <input id="contact-captcha" name="captcha" aria-label="{{ __('Code à recopier') }}" type="text" maxlength="4" autocomplete="off" autocapitalize="characters" spellcheck="false"
                               placeholder="{{ __('Recopiez le code') }}" class="{{ $champ }} w-40 min-w-0">
                    </div>
                    <p x-show="erreurs.captcha" x-text="erreurs.captcha" x-cloak class="mt-1 text-[13px] text-red-600"></p>
                </div>

                <div>
                    <button type="submit" :disabled="vue === 'envoi'" data-curseur
                            class="inline-flex items-center gap-2 bg-book-texte px-8 py-3 font-titre text-[13px] uppercase tracking-[1.3px] text-book-fond transition hover:opacity-80 disabled:opacity-60">
                        <svg x-show="vue === 'envoi'" x-cloak class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ __('Envoyer') }}
                    </button>
                </div>

                {{-- Information sur les donnees, au survol ou au toucher. --}}
                <div x-data="{ info: false }" class="relative text-[13px] text-book-texte3" @mouseenter="info = true" @mouseleave="info = false">
                    <button type="button" class="flex items-center gap-2 hover:text-book-texte" @click="info = ! info" :aria-expanded="info">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                        {{ __('Protection des données et suivi du message') }}
                    </button>
                    <p x-show="info" x-cloak x-transition.opacity
                       class="absolute bottom-full left-0 z-20 mb-2 max-w-sm bg-book-texte p-3 text-[12px] leading-relaxed text-book-fond shadow-lg">
                        {{ __('Les informations indiquées dans ce formulaire ne seront pas diffusées à des tiers, autres que le destinataire du message et la plateforme Ultra-book. Un mail vous permettra de suivre l’évolution de votre message et surtout de vérifier s’il a été lu par le destinataire.') }}
                    </p>
            </form>
        </div>

        @if ($vue->texte('contact_footer') !== '')
            <div class="contenu-page mt-12">{!! $vue->texte('contact_footer') !!}</div>
        @endif
    </article>
@endsection
