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

    <div class="flex items-center gap-3 border-t border-ub-filet p-4">
        <div class="min-w-0 flex-1">
            <div class="text-[15px] font-bold leading-snug text-ub-texte">{{ $nom }}</div>
            <div class="text-[11px] font-semibold uppercase tracking-[.06em] text-ub-texte3">{{ __('Responsive') }}</div>
        </div>

        @if ($cle === $theme)
            <span class="shrink-0 text-[13px] font-semibold text-ub-accent-texte">{{ __('Modèle actif') }}</span>
        @else
            <button type="button" wire:click="choisirTheme('{{ $cle }}')"
                    class="bouton-espace-petit inline-flex shrink-0 items-center justify-center border border-ub-bord bg-white px-3.5 text-[13px] font-semibold text-ub-texte hover:border-ub-accent hover:text-ub-accent-texte">
                {{ __('Activer') }}
            </button>
        @endif
    </div>
</div>
