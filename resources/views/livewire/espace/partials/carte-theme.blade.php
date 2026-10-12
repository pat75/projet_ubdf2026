{{-- Une carte de modele de book : vignette, nom, action. Attend $cle, $nom,
     $theme (theme actif) et $vignettes (voir habillage.blade.php). --}}
@php([$fichier, $mode] = $vignettes[$cle] ?? [null, null])
@php($reglable = $cle === $theme && in_array($cle, ['mdl_2020_ultra_frais', 'mdl_2020_ultra_zen'], true))
<div wire:key="theme-{{ $cle }}" @class([
        'carte-espace relative overflow-hidden border-2',
        'cursor-pointer transition-shadow hover:shadow-md' => $reglable,
        'border-ub-accent' => $cle === $theme,
        'border-transparent' => $cle !== $theme,
    ])
    @if ($reglable) x-data @click="window.open(@js(route('espace.edition-book')), '_blank', 'noopener')" @endif>
    <div class="relative aspect-video bg-ub-fond"
         @if ($fichier)
             style="background-image:url('{{ asset('img_front/motifs/'.$fichier) }}');
                    background-repeat:{{ $mode === 'repeat' ? 'repeat' : 'no-repeat' }};
                    background-position:{{ $mode === 'repeat' ? '0 0' : '50% 0' }};
                    @if ($mode === 'couvrir') background-size:cover; @endif"
         @endif>
        @if ($cle === $theme)
            <span class="absolute left-3 top-3 rounded-sm bg-ub-accent px-2.5 py-1 text-[11px] font-bold text-white">{{ __('Activé ✓') }}</span>
        @endif
    </div>

    <div class="flex items-start gap-3 border-t border-ub-filet p-4">
        <div class="min-w-0 flex-1">
            {{-- Nom, puis les trois ecrans servis (modeles responsive). --}}
            <div class="flex items-center gap-2">
                <span class="text-[15px] font-bold leading-snug text-black">{{ __($nom) }}</span>
                <span class="flex items-center gap-0.5 text-ub-texte3" title="{{ __('Mobile, tablette et ordinateur') }}">
                    <x-espace.icone nom="mobile" class="h-3.5 w-3.5" />
                    <x-espace.icone nom="tablette" class="h-3.5 w-3.5" />
                    <x-espace.icone nom="ordinateur" class="h-3.5 w-3.5" />
                </span>
            </div>
        </div>

        @if ($cle === $theme)
            <span class="shrink-0 text-[13px] font-semibold leading-[1.375rem] text-ub-accent-texte">{{ __('Modèle actif') }}</span>
        @endif
    </div>

    {{-- Action au centre absolu de la carte, en surimpression. --}}
    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
        @if ($reglable)
            <a href="{{ route('espace.edition-book') }}" target="_blank" rel="noopener" @click.stop
               class="pointer-events-auto bouton-espace bouton-espace-grand inline-flex items-center gap-2 px-5 shadow-lg">
                <x-espace.icone nom="reglage" class="h-4 w-4" />
                {{ __('Réglages du book') }}
            </a>
        @elseif ($cle !== $theme)
            <button type="button" wire:click="choisirTheme('{{ $cle }}')"
                    class="pointer-events-auto bouton-espace-grand inline-flex items-center justify-center border border-gray-400 bg-white px-5 font-semibold text-ub-texte shadow-lg hover:border-ub-accent hover:text-ub-accent-texte">
                {{ __('Activer') }}
            </button>
        @endif
    </div>
</div>
