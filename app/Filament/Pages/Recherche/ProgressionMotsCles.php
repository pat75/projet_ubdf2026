<?php

namespace App\Filament\Pages\Recherche;

use App\Models\Tag;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/** Nombre total de mots-cles distincts, jour par jour, sur 60 jours. */
class ProgressionMotsCles extends ChartWidget
{
    protected ?string $heading = 'Progression des mots-clés (60 jours)';

    protected ?string $maxHeight = '260px';

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $debut = now()->subDays(59)->startOfDay();
        $parJour = Tag::where('created_at', '>=', $debut)
            ->select(DB::raw('DATE(created_at) as jour'), DB::raw('COUNT(*) as n'))
            ->groupBy('jour')
            ->pluck('n', 'jour');

        $cumul = Tag::where('created_at', '<', $debut)->count();
        $libelles = $valeurs = [];

        for ($jour = $debut->copy(); $jour->lte(now()); $jour->addDay()) {
            $cumul += (int) ($parJour[$jour->toDateString()] ?? 0);
            $libelles[] = $jour->format('d/m');
            $valeurs[] = $cumul;
        }

        return [
            'datasets' => [['label' => 'Mots-clés', 'data' => $valeurs, 'borderColor' => '#2563eb', 'fill' => false, 'tension' => 0.2]],
            'labels' => $libelles,
        ];
    }
}
