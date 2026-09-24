{{--
 | Un seul composant pour deux blocs, parce qu'un seul champ sert aux
 | deux sortes de codes : le service reconnait un code de parrainage a sa
 | forme (« AR-46969 ») et traite le reste en code promo.
 |
 | Dessus, le bloc d'activation, visible, repris de la page tarifs des
 | Illustrateurs ; dessous, le parrainage, replie comme les factures.
--}}
<div>

    {{-- Activation d'un code formule. Le chat deborde en bas du bloc,
         comme sur la maquette : le conteneur le rogne. --}}
    <div class="mt-12 overflow-hidden rounded-ub-carte bg-[#e4e4e6]">
        <div class="grid items-end gap-6 md:grid-cols-[1fr_1.2fr]">

            <img src="{{ asset('img_front/chat-code-formule.png') }}" alt=""
                 class="mx-auto -mb-2 w-[320px] max-w-full self-end md:mx-0 md:ml-10">

            <div class="px-6 pb-10 pt-8 md:px-0 md:pr-10">
                <h3 class="text-[22px] font-semibold text-ub-texte">{{ __('Activer un code formule') }}</h3>

                <form wire:submit="utiliser" class="mt-4 max-w-sm">
                    <input type="text" wire:model="code" placeholder="{{ __('Entrez votre code') }}"
                           class="h-12 w-full rounded-full border border-ub-bord bg-white px-5 text-[15px] text-ub-texte outline-none placeholder:text-ub-texte4 focus:border-ub-accent">

                    <button type="submit" wire:target="utiliser" wire:loading.attr="disabled"
                            class="mt-4 inline-flex items-center gap-2 rounded-full bg-black px-7 py-3 text-[16px] font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-60">
                        {{-- L'eclair de la maquette. --}}
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z"/>
                        </svg>
                        {{ __('Activer') }}
                    </button>
                </form>

                @error('code') <p class="mt-2 text-[14px] text-ub-danger">{{ $message }}</p> @enderror
                @if ($resultat) <p class="mt-3 max-w-sm text-[15px] text-ub-texte">{{ $resultat }}</p> @endif

                <h4 class="mt-8 text-[17px] font-semibold text-ub-texte2">{{ __('Comment obtenir un code formule ?') }}</h4>
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

    {{-- Le parrainage : replie, comme les factures. --}}
    <div class="mt-10">
        <x-espace.section-pliante :titre="__('Parrainage')">
            <p class="text-[15px] text-ub-texte2">
                {{ __('Votre code de parrainage :') }}
                <strong class="font-mono text-ub-texte">{{ $monCode }}</strong>.
                {{ __('Quand un créatif avec une formule payante l’utilise, vous gagnez chacun 1 à 3 mois de formule.') }}
                {{ __('Il s’active dans le champ ci-dessus.') }}
            </p>

            @if ($filleuls->isNotEmpty())
                <ul class="mt-3 space-y-1 text-[14px] text-ub-texte2">
                    @foreach ($filleuls as $f)
                        <li>{{ $f->referred?->fullName() }} — {{ $f->confirmed_at?->format('d/m/Y') }}</li>
                    @endforeach
                </ul>
            @endif
        </x-espace.section-pliante>
    </div>
</div>
