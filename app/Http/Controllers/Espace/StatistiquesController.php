<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Visites du book : 30 derniers jours, 12 derniers mois, total. */
class StatistiquesController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = $request->user()->visitStats();

        $jours = $stats->clone()->where('date', '>=', now()->subDays(29)->toDateString())
            ->pluck('public_views', 'date')->mapWithKeys(fn ($v, $d) => [Carbon::parse($d)->toDateString() => $v]);

        $parJour = collect(range(29, 0))->mapWithKeys(function ($i) use ($jours) {
            $date = now()->subDays($i)->toDateString();

            return [$date => (int) ($jours[$date] ?? 0)];
        });

        $mois = $stats->clone()->where('date', '>=', now()->startOfMonth()->subMonths(11)->toDateString())->get()
            ->groupBy(fn ($s) => $s->date->format('Y-m'))->map->sum('public_views');

        $parMois = collect(range(11, 0))->mapWithKeys(function ($i) use ($mois) {
            $cle = now()->startOfMonth()->subMonths($i)->format('Y-m');

            return [$cle => (int) ($mois[$cle] ?? 0)];
        });

        return view('espace.statistiques', [
            'parJour' => $parJour,
            'parMois' => $parMois,
            'total' => (int) $stats->clone()->sum('public_views'),
        ]);
    }
}
