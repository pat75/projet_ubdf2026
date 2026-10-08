<?php

namespace App\Filament\Pages\Recherche;

use App\Actions\Recherche\LancerAnalyseLot;
use App\Models\Media;
use App\Models\Tag;
use App\Services\IA\Nvidia;
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

        // Cadence du cron : mesuree sur la derniere heure, sinon theorique
        // (un lot par minute).
        $attente = LancerAnalyseLot::enAttente()->count();
        $parHeure = Media::where('analysed_at', '>=', now()->subHour())->count() ?: LancerAnalyseLot::TAILLE * 60;
        $heures = $attente / $parHeure;
        $fin = $attente
            ? '≈ '.number_format($heures, 1, ',', ' ').' h, soit '.number_format($heures / 24, 1, ',', ' ').' j (à '.$nombre($parHeure).' images/h)'
            : 'Rien en attente';

        // API prevue (NVIDIA, sinon OpenRouter en secours) et modele
        // reellement employe a la derniere analyse.
        $probleme = Nvidia::actif() ? Nvidia::probleme() : null;
        $dernier = Media::whereNotNull('analysed_at')->latest('analysed_at')->value('ai_model');
        $derniereNvidia = Media::whereIn('ai_model', [...Nvidia::MODELES_VISION, Nvidia::modele()])->max('analysed_at');
        $depuis = $derniereNvidia ? ' · dernière opération NVIDIA '.\Illuminate\Support\Carbon::parse($derniereNvidia)->locale('fr')->diffForHumans() : '';

        return [
            $probleme
                ? Stat::make('API', 'NVIDIA injoignable')
                    ->description('Depuis le '.date('d/m H:i', $probleme['quand']).' : '.mb_substr($probleme['motif'], 0, 120).' — analyses en pause, nouvel essai toutes les '.Nvidia::PAUSE.' min'.$depuis)
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger')
                : Stat::make('API', Nvidia::actif() ? 'NVIDIA' : 'OpenRouter')
                    ->description((Nvidia::actif() ? 'Modèle : '.Nvidia::modele() : 'Sélection automatique (vision)')
                        .($dernier ? ' · dernier utilisé : '.$dernier : '').$depuis)
                    ->color(Nvidia::actif() ? 'success' : 'warning'),
            Stat::make('En attente (cron)', $nombre($attente))
                ->description($fin)
                ->color($attente ? 'info' : 'success'),
            Stat::make('Images analysées', $nombre((clone $analyses)->count()))
                ->description($nombre($eligibles).' images éligibles'.($erreurs ? ', '.$erreurs.' en erreur' : ''))
                ->color($erreurs ? 'warning' : 'success'),
            Stat::make('Books analysés', $nombre((clone $analyses)->distinct()->count('user_id'))),
            Stat::make('Mots-clés', $nombre(Tag::count()))
                ->description($nombre(Tag::where('created_at', '>=', now()->subDays(7))->count()).' nouveaux cette semaine'),
        ];
    }
}
