{{-- Une carte de modele de book : vignette, nom, action. Attend $cle, $nom,
     $theme (theme actif) et $vignettes (voir habillage.blade.php). --}}
@php([$fichier, $mode] = $vignettes[$cle] ?? [null, null])
<div wire:key="theme-{{ $cle }}" @class([
        'carte-espace overflow-hidden border-2',
        'border-ub-accent' => $cle === $theme,
        'border-transparent' => $cle !== $theme,
    ])>
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
                <span class="text-[15px] font-bold leading-snug text-ub-texte">{{ $nom }}</span>
                <span class="flex items-center gap-0.5 text-ub-texte3" title="{{ __('Mobile, tablette et ordinateur') }}">
                    <x-espace.icone nom="mobile" class="h-3.5 w-3.5" />
                    <x-espace.icone nom="tablette" class="h-3.5 w-3.5" />
                    <x-espace.icone nom="ordinateur" class="h-3.5 w-3.5" />
                </span>
            </div>
            {{-- Modele actif Ultra-frais / Ultra-zen : reglages visuels sur le book lui-meme. --}}
            @if ($cle === $theme && in_array($cle, ['mdl_2020_ultra_frais', 'mdl_2020_ultra_zen'], true))
                <a href="{{ route('espace.edition-book') }}" target="_blank" rel="noopener"
                   class="bouton-espace bouton-espace-petit mt-3 inline-flex items-center gap-1.5 px-3.5">
                    <x-espace.icone nom="reglage" class="h-4 w-4" />
                    {{ __('Réglages visuels') }}
                </a>
            @endif
        </div>

        @if ($cle === $theme)
            <span class="shrink-0 text-[13px] font-semibold leading-[1.375rem] text-ub-accent-texte">{{ __('Modèle actif') }}</span>
        @else
            <button type="button" wire:click="choisirTheme('{{ $cle }}')"
                    class="bouton-espace-petit inline-flex shrink-0 self-center items-center justify-center border border-ub-bord bg-white px-3.5 text-[13px] font-semibold text-ub-texte hover:border-ub-accent hover:text-ub-accent-texte">
                {{ __('Activer') }}
            </button>
        @endif
    </div>
</div>
