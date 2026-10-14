<div id="appareils">
    <x-espace.carte :titre="__('Appareils connectés')"
                    :sous-titre="__('Applications mobile et desktop qui accèdent à votre compte.')">

        @forelse ($jetons as $jeton)
            <div wire:key="jeton-{{ $jeton->id }}" class="flex items-center justify-between gap-4 border-b border-ub-filet py-3.5 last:border-b-0">
                <div class="min-w-0">
                    <div class="truncate text-[15px] font-semibold text-ub-texte">{{ $jeton->name }}</div>
                    <div class="text-[13px] text-ub-texte3">
                        {{ $jeton->last_used_at
                            ? __('Utilisé :quand', ['quand' => $jeton->last_used_at->diffForHumans()])
                            : __('Jamais utilisé') }}
                        · {{ __('Connecté le :date', ['date' => $jeton->created_at->translatedFormat('j F Y')]) }}
                    </div>
                </div>

                <x-espace.info-bulle :texte="__('Déconnecter cet appareil')">
                    <button type="button" wire:click="revoquer({{ $jeton->id }})"
                            wire:confirm="{{ __('Déconnecter :appareil ?', ['appareil' => $jeton->name]) }}"
                            class="p-1 text-ub-texte3 hover:text-ub-danger">
                        <x-espace.picto nom="poubelle" class="h-5 w-5" />
                    </button>
                </x-espace.info-bulle>
            </div>
        @empty
            <p class="py-2 text-[14px] text-ub-texte3">{{ __('Aucune application connectée.') }}</p>
        @endforelse

        {{-- Connexion d'une application desktop : le code s'affiche dans l'application. --}}
        <form wire:submit="verifier" class="mt-5 border-t border-ub-filet pt-5">
            <label for="code-appareil" class="block text-[15px] font-semibold">{{ __('Connecter une application') }}</label>
            <span class="mt-1 block text-[13px] text-ub-texte2 text-pretty">
                <span class="block">{{ __('Saisissez le code affiché par l’application.') }}</span>
                <span class="block">{{ __('Il reste valable 10 minutes.') }}</span>
            </span>

            @if ($demande)
                {{-- Confirmation : jamais d'autorisation sans voir ce qui est demande. --}}
                <div class="mt-3 border border-ub-texte/50 p-4">
                    <span class="block text-[14px] text-ub-texte text-pretty">
                        <span class="block">{{ __('« :appareil » demande l’accès à votre compte.', ['appareil' => $demande['appareil']]) }}</span>
                        <span class="block text-ub-texte3">{{ __('N’autorisez que votre propre appareil.') }}</span>
                    </span>
                    <ul class="mt-2 text-[13px] text-ub-texte2">
                        @foreach ($demande['droits'] as $droit)
                            <li>{{ $droit }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-3 flex flex-wrap gap-3">
                        <button type="button" wire:click="autoriser" wire:loading.attr="disabled" wire:target="autoriser"
                                class="bouton-espace bouton-espace-grand px-5">{{ __('Autoriser') }}</button>
                        <button type="button" wire:click="annuler"
                                class="bouton-espace bouton-espace-grand border border-ub-bord bg-white px-5 text-ub-texte hover:bg-ub-fond">{{ __('Annuler') }}</button>
                    </div>
                </div>
            @else
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <input id="code-appareil" type="text" wire:model="code" autocomplete="off" autocapitalize="characters"
                           placeholder="ABCD-2345" maxlength="20"
                           class="champ-espace w-44 font-mono uppercase tracking-widest">
                    <button type="submit" wire:loading.attr="disabled" wire:target="verifier"
                            class="bouton-espace bouton-espace-grand px-5">{{ __('Continuer') }}</button>
                </div>
            @endif

            @error('code') <span class="mt-2 block text-[13px] text-ub-danger">{{ $message }}</span> @enderror

            @if ($autorise)
                <span class="mt-2 block text-[13px] text-ub-succes">{{ __(':appareil est connecté.', ['appareil' => $autorise]) }}</span>
            @endif
        </form>
    </x-espace.carte>
</div>
