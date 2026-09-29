<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Services\Stats\CompteurVisites;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Visites du book, sur le modele du tableau de bord analytique de
 * les-illustrateurs (/stats) : periode au choix (7, 30, 90 jours), courbe
 * des visites, repartition par surface, puis les 12 derniers mois.
 *
 * Les 90 jours sont envoyes d'un coup, par jour et par surface : le
 * changement de periode se fait dans le navigateur, sans rechargement.
 */
class StatistiquesController extends Controller
{
    public const JOURS = 90;

    public function __invoke(Request $request): View
    {
        $stats = $request->user()->visitStats();

        // Plusieurs lignes par jour (une par surface) : on les regroupe.
        $lignes = $stats->clone()
            ->where('date', '>=', now()->subDays(self::JOURS - 1)->toDateString())
            ->selectRaw('date, surface, SUM(public_views) as vues')
            ->groupBy('date', 'surface')
            ->get()
            ->groupBy(fn ($l) => $l->date->toDateString());

        $parJour = collect(range(self::JOURS - 1, 0))->mapWithKeys(function ($i) use ($lignes) {
            $date = now()->subDays($i)->toDateString();
            $surfaces = collect(CompteurVisites::SURFACES)->mapWithKeys(fn ($s) => [$s => 0]);

            foreach ($lignes[$date] ?? [] as $ligne) {
                $surfaces[$ligne->surface] = (int) $ligne->vues;
            }

            return [$date => $surfaces->all()];
        });

        $mois = $stats->clone()
            ->where('date', '>=', now()->startOfMonth()->subMonths(11)->toDateString())
            ->get(['date', 'public_views'])
            ->groupBy(fn ($s) => $s->date->format('Y-m'))
            ->map->sum('public_views');

        $parMois = collect(range(11, 0))->mapWithKeys(function ($i) use ($mois) {
            $debut = now()->startOfMonth()->subMonths($i);

            return [$debut->translatedFormat('M Y') => (int) ($mois[$debut->format('Y-m')] ?? 0)];
        });

        return view('espace.statistiques', [
            'parJour' => $parJour,
            'parMois' => $parMois,
            'total' => (int) $stats->clone()->sum('public_views'),
            'surfaces' => [
                'book' => __('Book'),
                'minibook' => __('MiniBook'),
            ],
        ]);
    }
}
