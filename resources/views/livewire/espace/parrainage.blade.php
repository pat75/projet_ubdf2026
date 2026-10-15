{{--
 | Le bloc d'activation, repris de la page tarifs des Illustrateurs.
 |
 | Un seul champ pour les deux sortes de codes : le service reconnait un
 | code de parrainage a sa forme (« AR-46969 ») et traite le reste en
 | code promo. Le code du createur et ses filleuls sont dans le meme bloc.
--}}
<div>

    {{-- Activation d'un code formule : meme cartouche que les volets
         Factures, CGV… de x-espace.section-pliante, juste au-dessus. Le
         chat deborde en bas du bloc : le conteneur le rogne. --}}
    <div class="overflow-hidden rounded-ub-carte bg-[#f3f3f1] px-6" x-data="{ ouvert: @js((bool) ($resultat || $errors->has('code'))) }">
        <button type="button" @click="ouvert = ! ouvert" :aria-expanded="ouvert"
                class="flex w-full items-center justify-between py-4 text-left">
            <span class="font-titre text-[20px] font-light text-[#070707]">{{ __('Activer un code formule ou de parrainage') }}</span>
            <x-espace.picto nom="angle-droite" x-show="! ouvert" class="h-5 w-5 shrink-0 text-ub-texte" />
            <x-espace.picto nom="angle-bas" x-show="ouvert" x-cloak class="h-5 w-5 shrink-0 text-ub-texte" />
        </button>

        <div x-show="ouvert" x-cloak x-collapse class="grid items-end gap-6 md:grid-cols-[1fr_1.2fr]">

            <img src="{{ asset('img_front/chat-code-formule.webp') }}" alt=""
                 class="mx-auto -mb-2 w-[320px] max-w-full self-end md:mx-0">

            <div class="pb-8 pt-2">
                <p class="text-[15px] text-pretty text-ub-texte2">
                    <span class="block">{{ __('Saisissez un code promo ou le code de parrainage d’un créatif (ex. AR-46969).') }}</span>
                </p>

                <form wire:submit="utiliser" class="mt-4 max-w-sm">
                    <input type="text" wire:model="code" placeholder="{{ __('Code promo ou de parrainage') }}"
                           class="h-12 w-full rounded-full border border-ub-bord bg-white px-5 text-[15px] text-ub-texte outline-none placeholder:text-ub-texte4 focus:border-ub-accent">

                    <button type="submit" wire:target="utiliser" wire:loading.attr="disabled"
                            class="bouton-espace bouton-espace-grand mt-4 px-7">
                        {{-- L'eclair de la maquette. --}}
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z"/>
                        </svg>
                        {{ __('Activer') }}
                    </button>
                </form>

                @error('code') <p class="mt-2 text-[14px] text-ub-danger">{{ $message }}</p> @enderror
                @if ($resultat) <p class="mt-3 max-w-sm text-[15px] text-ub-texte">{{ $resultat }}</p> @endif

                <div x-data="{ code: false }" class="mt-8">
                <button type="button" @click="code = ! code" :aria-expanded="code"
                        class="flex w-full max-w-sm items-center justify-between text-left text-[17px] font-semibold text-ub-texte2">
                    {{ __('Mon code de parrainage') }}
                    <x-espace.picto nom="angle-droite" x-show="! code" class="h-5 w-5 shrink-0 text-ub-texte" />
                    <x-espace.picto nom="angle-bas" x-show="code" x-cloak class="h-5 w-5 shrink-0 text-ub-texte" />
                </button>
                <div x-show="code" x-cloak x-collapse>
                <p class="mt-0.5 text-[15px] text-pretty text-ub-texte2">
                    <span class="block">{{ __('Votre code :') }} <strong class="font-mono text-ub-texte">{{ $monCodeParrain }}</strong></span>
                    <span class="block">{{ __('Quand un créatif avec une formule payante l’utilise, vous gagnez chacun 1 à 3 mois de formule.') }}</span>
                </p>

                @if ($filleuls->isNotEmpty())
                    <ul class="mt-2 space-y-1 text-[14px] text-ub-texte2">
                        @foreach ($filleuls as $f)
                            <li>{{ $f->referred?->fullName() }} — {{ $f->confirmed_at?->format('d/m/Y') }}</li>
                        @endforeach
                    </ul>
                @endif
                </div>
                </div>

                <div x-data="{ aide: false }" class="mt-6">
                <button type="button" @click="aide = ! aide" :aria-expanded="aide"
                        class="flex w-full max-w-sm items-center justify-between text-left text-[17px] font-semibold text-ub-texte2">
                    {{ __('Comment obtenir un code formule ?') }}
                    <x-espace.picto nom="angle-droite" x-show="! aide" class="h-5 w-5 shrink-0 text-ub-texte" />
                    <x-espace.picto nom="angle-bas" x-show="aide" x-cloak class="h-5 w-5 shrink-0 text-ub-texte" />
                </button>
                <div x-show="aide" x-cloak x-collapse>
                <p class="mt-0.5 text-[15px] text-ub-texte2">
                    {{ __('suivez l’Instagram :marque, des codes y sont régulièrement postés.', ['marque' => $marque->nom]) }}
                </p>

                <a href="https://www.instagram.com/ultra.book/" target="_blank" rel="noopener"
                   class="mt-2 inline-flex text-[22px] text-ub-texte2 hover:text-ub-texte">
                    <span class="fonticon-uniF05E" aria-hidden="true"></span>
                    <span class="sr-only">Instagram</span>
                </a>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
