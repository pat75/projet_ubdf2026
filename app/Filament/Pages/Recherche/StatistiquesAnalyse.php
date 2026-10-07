<?php

namespace App\Filament\Pages\Recherche;

use App\Actions\Recherche\LancerAnalyseLot;
use App\Models\Media;
use App\Models\Tag;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Chiffres en tete de la page Analyse IA. Hors de Filament/Widgets : pas sur le tableau de bord. */
class StatistiquesAnalyse extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $nombre = fn (int $n) => number_format($n, 0, ',', ' ');
        $analyses = Media::whereNotNull('analysed_at');
        $eligibles = LancerAnalyseLot::eligibles()->count();
        $erreurs = Media::where('ai_status', 'erreur')->count();

        return [
            Stat::make('Images analysées', $nombre((clone $analyses)->count()))
                ->description($nombre($eligibles).' images éligibles'.($erreurs ? ', '.$erreurs.' en erreur' : ''))
                ->color($erreurs ? 'warning' : 'success'),
            Stat::make('Books analysés', $nombre((clone $analyses)->distinct()->count('user_id'))),
            Stat::make('Mots-clés', $nombre(Tag::count()))
                ->description($nombre(Tag::where('created_at', '>=', now()->subDays(7))->count()).' nouveaux cette semaine'),
        ];
    }
}
