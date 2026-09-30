<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChiffresCles extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $payantes = User::where('plan', '>', 0)->count();
        $echues = User::where('plan', '>', 0)->where('plan_expires_at', '<', now())->count();

        return [
            Stat::make('Créatifs', number_format(User::count(), 0, ',', ' '))
                ->description(number_format(User::where('created_at', '>=', now()->subDays(30))->count(), 0, ',', ' ').' ce mois-ci'),
            Stat::make('Formules payantes', number_format($payantes, 0, ',', ' '))
                ->description($echues.' échues')
                ->color($echues > 0 ? 'warning' : 'success'),
            Stat::make('Demandes reçues (30 j)', Conversation::where('is_spam', false)
                ->where('created_at', '>=', now()->subDays(30))->count()),
        ];
    }
}
