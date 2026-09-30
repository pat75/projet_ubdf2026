{{-- Badge rouge pose a cote de « Ultra-book classique V3 » quand le site
     est ferme au public (App\Models\Reglage::MAINTENANCE). Le CSS de
     Filament etant precompile, les regles sont ecrites en clair : des
     utilitaires Tailwind ajoutes ici ne seraient pas generes. --}}
@if (\App\Models\Reglage::enMaintenance())
    <a href="{{ \App\Filament\Pages\AccueilPage::getUrl() }}" class="ub-badge-maintenance" title="{{ __('Rouvrir le site depuis la page d’accueil') }}">
        <span class="ub-badge-maintenance__point"></span>
        {{ __('Site en maintenance') }}
    </a>

    <style>
        .ub-badge-maintenance {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            background: rgb(220 38 38);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.4;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .ub-badge-maintenance:hover {
            background: rgb(185 28 28);
        }

        .ub-badge-maintenance__point {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background: #fff;
        }
    </style>
@endif
