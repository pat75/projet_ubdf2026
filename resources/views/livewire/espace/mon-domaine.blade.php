@php
    $libelles = [
        'a_verifier' => __('En attente de vérification'),
        'paye' => __('Paiement reçu, commande en cours'),
        'commande' => __('Commandé, configuration en cours'),
        'en_attente' => __('Configuration en cours'),
        'dns_ok' => __('Configuration en cours'),
        'actif' => __('Actif'),
        'suspendu' => __('Suspendu'),
        'resilie' => __('Résilié : actif jusqu’à l’échéance'),
        'transfert' => __('Transfert en cours'),
    ];
@endphp

<div class="dev_only">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-[13px] font-semibold uppercase tracking-[.08em] text-ub-accent-texte">{{ __('Votre adresse sur le web') }}</div>
            <h1 class="mt-1.5 font-titre text-[34px] font-light leading-tight tracking-tight text-ub-texte">{{ __('Nom de domaine') }}</h1>
        </div>
        <a href="https://{{ $domaine->nom }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 text-[21px] font-bold text-ub-accent">
            {{ $domaine->nom }}
            <svg class="h-4 w-4 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>
            </svg>
        </a>
    </div>

    <x-espace.carte :titre="__('Informations')">
        <x-espace.ligne :libelle="__('Statut')">{{ $libelles[$domaine->statut] ?? $domaine->statut }}</x-espace.ligne>
        @if ($domaine->source === 'externe')
            <x-espace.ligne :libelle="__('Gestion')">{{ __('Chez votre registrar (renouvellement, facturation)') }}</x-espace.ligne>
        @else
        <x-espace.ligne :libelle="__('Réservé le')">{{ $domaine->reserve_le?->translatedFormat('j F Y') ?? '—' }}</x-espace.ligne>
        <x-espace.ligne :libelle="__('Échéance')">{{ $domaine->expire_le?->translatedFormat('j F Y') ?? '—' }}</x-espace.ligne>
        @if ($domaine->resilie_le)
            <x-espace.ligne :libelle="__('Résilié le')">{{ $domaine->resilie_le->translatedFormat('j F Y') }}</x-espace.ligne>
        @endif
        <x-espace.ligne :libelle="__('Prix')">{{ __(':prix € TTC / an', ['prix' => number_format((float) $domaine->prix_ttc, 2, ',', ' ')]) }}</x-espace.ligne>
        @if ($domaine->facture)
            <x-espace.ligne :libelle="__('Facture')">
                <a href="{{ route(nom_route('espace.facture'), $domaine->facture) }}" target="_blank" class="underline">{{ $domaine->facture->numero() }}</a>
            </x-espace.ligne>
        @endif
        @endif
    </x-espace.carte>

    @if ($domaine->source === 'externe')
        @php($txt = $domaine->enregistrementTxt())
        @php($ip = config('services.ovh.ip_vps'))
        @php($ip6 = config('services.ovh.ip6_vps'))
        <x-espace.carte :titre="__('Configuration chez votre registrar')" class="mt-6">
            <span class="block text-[14px] text-ub-texte2 text-pretty">
                <span class="block">{{ __('Dans l’espace DNS de votre registrar (Gandi, IONOS, OVH…), créez les enregistrements ci-dessous.') }}</span>
                <span class="block">{{ __('Nous vérifions toutes les heures ; la propagation peut prendre quelques heures.') }}</span>
            </span>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-[14px]">
                    <thead class="text-[12px] uppercase tracking-[.06em] text-ub-texte3">
                        <tr><th class="py-2 pr-4">{{ __('Type') }}</th><th class="py-2 pr-4">{{ __('Nom') }}</th><th class="py-2 pr-4">{{ __('Valeur') }}</th><th class="py-2">{{ __('État') }}</th></tr>
                    </thead>
                    <tbody class="font-mono text-[13px]">
                        <tr class="border-t border-ub-filet">
                            <td class="py-2.5 pr-4">TXT</td>
                            <td class="py-2.5 pr-4 break-all">{{ $txt['nom'] }}</td>
                            <td class="py-2.5 pr-4 break-all">{{ $txt['valeur'] }}</td>
                            <td class="py-2.5 font-sans">{{ $domaine->statut === 'a_verifier' ? __('À créer') : __('Vérifié') }}</td>
                        </tr>
                        <tr class="border-t border-ub-filet">
                            <td class="py-2.5 pr-4">A</td>
                            <td class="py-2.5 pr-4 break-all">{{ $domaine->nom }}</td>
                            <td class="py-2.5 pr-4">{{ $ip ?: '—' }}</td>
                            <td class="py-2.5 font-sans">{{ in_array($domaine->statut, \App\Models\Domaine::SERVIS, true) ? __('Vérifié') : __('À créer') }}</td>
                        </tr>
                        @if ($ip6)
                            <tr class="border-t border-ub-filet">
                                <td class="py-2.5 pr-4">AAAA</td>
                                <td class="py-2.5 pr-4 break-all">{{ $domaine->nom }}</td>
                                <td class="py-2.5 pr-4 break-all">{{ $ip6 }}</td>
                                <td class="py-2.5 font-sans">{{ __('Conseillé') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <span class="mt-3 block text-[13px] text-ub-texte3 text-pretty">
                <span class="block">{{ __('Supprimez les autres enregistrements A ou AAAA de :nom, sinon une partie des visiteurs n’arrivera pas sur votre book.', ['nom' => $domaine->nom]) }}</span>
            </span>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                @if ($domaine->statut === 'a_verifier')
                    <button type="button" wire:click="verifierMaintenant" wire:loading.attr="disabled" wire:target="verifierMaintenant"
                            class="bouton-espace bouton-espace-grand px-5">{{ __('Vérifier maintenant') }}</button>
                @endif
                <button type="button" wire:click="retirer" wire:confirm="{{ __('Retirer :nom de votre book ? Le domaine reste à vous chez votre registrar.', ['nom' => $domaine->nom]) }}"
                        class="inline-flex h-[43px] items-center border border-ub-bord bg-white px-5 text-[14px] hover:bg-ub-fond">{{ __('Retirer ce domaine') }}</button>
            </div>
        </x-espace.carte>
    @endif

    @if ($domaine->source !== 'externe')
    <x-espace.carte :titre="__('Titulaire')" :sous-titre="__('Propriétaire du domaine auprès du registre.')" class="mt-6">
        @php($t = $domaine->titulaire ?? [])
        <x-espace.ligne :libelle="__('Nom')">{{ trim(($t['prenom'] ?? '').' '.($t['nom'] ?? '')) }}@if (! empty($t['societe'])) — {{ $t['societe'] }}@endif</x-espace.ligne>
        <x-espace.ligne :libelle="__('Adresse mail')">{{ $t['email'] ?? '' }}</x-espace.ligne>
        <x-espace.ligne :libelle="__('Téléphone')">{{ $t['telephone'] ?? '' }}</x-espace.ligne>
        <x-espace.ligne :libelle="__('Adresse')">{{ ($t['adresse'] ?? '').', '.($t['code_postal'] ?? '').' '.($t['ville'] ?? '').' ('.($t['pays'] ?? '').')' }}</x-espace.ligne>
    </x-espace.carte>
    @endif

    @if ($erreur)
        <p class="mt-4 text-[14px] text-ub-danger">{{ $erreur }}</p>
    @endif

    @if ($domaine->source !== 'externe')
    <x-espace.carte :titre="__('Transférer mon domaine')" class="mt-6">
        <span class="block text-[14px] text-ub-texte2 text-pretty">
            <span class="block">{{ __('Pour confier votre domaine à un autre registrar, demandez son code de transfert.') }}</span>
            <span class="block">{{ __('Votre nouveau registrar vous le demandera.') }}</span>
        </span>

        @if ($authCode)
            <div x-data="{ copie: false }" class="mt-4 flex flex-wrap items-center gap-3">
                <code class="border border-ub-filet bg-ub-fond px-3 py-2 font-mono text-[15px]">{{ $authCode }}</code>
                <button type="button" @click="navigator.clipboard.writeText(@js($authCode)); copie = true"
                        class="bouton-espace bouton-espace-petit px-4" x-text="copie ? @js(__('Copié')) : @js(__('Copier'))"></button>
            </div>
        @elseif ($domaine->statut !== 'paye')
            <button type="button" wire:click="transferer" wire:confirm="{{ __('Obtenir le code de transfert de :nom ?', ['nom' => $domaine->nom]) }}"
                    class="bouton-espace bouton-espace-grand mt-4 px-5">{{ __('Obtenir le code de transfert') }}</button>
        @endif
    </x-espace.carte>

    @endif

    @if ($domaine->source !== 'externe' && in_array($domaine->statut, ['commande', 'en_attente', 'dns_ok', 'actif', 'transfert'], true))
        <section class="mt-6 rounded-ub-carte border border-ub-danger-bord bg-ub-danger-fond px-7 py-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <span class="text-pretty">
                    <span class="block text-[17px] font-semibold text-ub-danger">{{ __('Résilier mon domaine') }}</span>
                    <span class="block text-[14px] text-[#8a5a5a]">{{ __('Il ne sera pas renouvelé et reste actif jusqu’au :date.', ['date' => $domaine->expire_le?->translatedFormat('j F Y') ?? '—']) }}</span>
                </span>
            </div>
            <form wire:submit="resilier" x-data="{ mot: '' }" class="mt-4 flex flex-wrap items-center gap-3">
                <label class="text-[14px] text-[#8a5a5a]" for="confirmation-resiliation">{!! __('Recopiez :mot pour confirmer :', ['mot' => '<strong class="text-ub-danger">'.e(__('résilier')).'</strong>']) !!}</label>
                <input id="confirmation-resiliation" type="text" wire:model="confirmation" x-model="mot" autocomplete="off"
                       class="w-40 border border-ub-danger-bord bg-white px-3 py-1.5 text-[14px] focus:border-ub-danger focus:outline-none">
                <button type="submit" :disabled="mot.trim().toLowerCase() !== @js(mb_strtolower(__('résilier')))"
                        class="bouton-espace bouton-espace-petit px-4">{{ __('Résilier') }}</button>
                @error('confirmation') <span class="w-full text-[13px] text-ub-danger">{{ $message }}</span> @enderror
            </form>
        </section>
    @endif
</div>
