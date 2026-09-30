<x-filament-panels::page>
    {{-- Le CSS de Filament est precompile : les utilitaires Tailwind ecrits ici
         ne seraient pas generes. La mise en page est donc en CSS explicite. --}}
    @push('styles')
        <style>
            .accueil-deux-colonnes {
                display: grid;
                grid-template-columns: minmax(0, 26rem) minmax(0, 1fr);
                align-items: start;
                gap: 1.5rem;
            }

            .accueil-apercu {
                overflow: hidden;
                border: 1px solid rgb(228 228 231);
                border-radius: 0.75rem;
                background: #fff;
            }

            .dark .accueil-apercu {
                border-color: rgb(63 63 70);
                background: rgb(24 24 27);
            }

            .accueil-apercu__barre {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.5rem 1rem;
                border-bottom: 1px solid rgb(228 228 231);
                font-size: 0.875rem;
            }

            .dark .accueil-apercu__barre {
                border-bottom-color: rgb(63 63 70);
            }

            .accueil-apercu__cadre {
                display: block;
                width: 100%;
                height: 75vh;
                border: 0;
                background: #fff;
            }

            @media (max-width: 1023px) {
                .accueil-deux-colonnes {
                    grid-template-columns: minmax(0, 1fr);
                }

                .accueil-apercu__cadre {
                    height: 60vh;
                }
            }
        </style>
    @endpush

    <div class="accueil-deux-colonnes">
        <div>
            {{ $this->content }}
        </div>

        {{-- Apercu de la page d'accueil du portail, recharge a chaque enregistrement. --}}
        <div
            class="accueil-apercu"
            x-data="{ cle: 0 }"
            x-on:accueil-enregistree.window="cle++"
        >
            <div class="accueil-apercu__barre">
                <span class="fi-color-gray">{{ __('Aperçu') }}</span>
                <span style="display:flex; gap:0.75rem;">
                    <button type="button" x-on:click="cle++" class="fi-link">{{ __('Rafraîchir') }}</button>
                    <a href="{{ $this->urlApercu() }}" target="_blank" rel="noopener" class="fi-link">{{ __('Ouvrir') }}</a>
                </span>
            </div>

            <template x-for="i in [cle]" :key="i">
                <iframe
                    src="{{ $this->urlApercu() }}"
                    title="{{ __('Aperçu de la page d’accueil') }}"
                    class="accueil-apercu__cadre"
                ></iframe>
            </template>
        </div>
    </div>
</x-filament-panels::page>
