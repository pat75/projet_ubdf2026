<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use App\Models\Invoice;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChiffresCles extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $payantes = User::where('plan', '>', 0)->count();
        $caMois = Invoice::where('status', 'paid')->whereBetween('issued_at', [now()->startOfMonth(), now()])->sum('amount');
        $echues = User::where('plan', '>', 0)
            ->whereRaw('DATE_ADD(plan_started_at, INTERVAL plan_months MONTH) < NOW()')->count();

        return [
            Stat::make('Créatifs', number_format(User::count(), 0, ',', ' '))
                ->description(number_format(User::where('created_at', '>=', now()->subDays(30))->count(), 0, ',', ' ').' ce mois-ci'),
            Stat::make('Formules payantes', number_format($payantes, 0, ',', ' '))
                ->description($echues.' échues')
                ->color($echues > 0 ? 'warning' : 'success'),
            Stat::make('Encaissé ce mois', number_format((float) $caMois, 2, ',', ' ').' €'),
            Stat::make('Demandes reçues (30 j)', Conversation::where('is_spam', false)
                ->where('created_at', '>=', now()->subDays(30))->count()),
        ];
    }
}
