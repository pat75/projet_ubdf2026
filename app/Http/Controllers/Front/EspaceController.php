<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Espace\CodeQr;
use App\Services\Espace\Quotas;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tableau de bord de l'espace creatif : destination de la connexion, de
 * l'inscription et de la reinitialisation du mot de passe.
 */
class EspaceController extends Controller
{
    public function __invoke(Request $requete, CodeQr $qr, Quotas $quotas): View
    {
        /** @var User $creatif */
        $creatif = $requete->user();

        $lienMinibook = rtrim(url('/'), '/').'/#'.$creatif->login;

        return view('espace.tableau', [
            'creatif' => $creatif,
            'lienMinibook' => $lienMinibook,
            'qrMinibook' => $qr->svg($lienMinibook),
            'qrBook' => $qr->svg($creatif->bookUrl()),
            'visites' => $this->visites($creatif),
            'quotas' => $quotas->pour($creatif),
        ]);
    }

    /**
     * Les vues du book, par surface.
     *
     * Le legacy allait les chercher sur un serveur de statistiques
     * exterieur (extra-book.com), avec une cle privee dans le gabarit.
     * Elles sont maintenant comptees ici, par `CompteurVisites`.
     *
     * @return array{total: int, parts: array<string, int>}
     */
    private function visites(User $creatif): array
    {
        $parSurface = $creatif->visitStats()
            ->selectRaw('surface, SUM(public_views) as vues')
            ->groupBy('surface')
            ->pluck('vues', 'surface');

        /*
         | MemoBook n'a pas de pixel qui le compte (seul le book en envoie
         | un, voir CompteurVisites) : le montrer a zero en permanence
         | n'apportait rien qu'un stat vide.
         */
        $libelles = [
            'book' => __('Book'),
            'minibook' => __('MiniBook'),
        ];

        $parts = [];

        foreach ($libelles as $code => $libelle) {
            $parts[$libelle] = (int) ($parSurface[$code] ?? 0);
        }

        return ['total' => array_sum($parts), 'parts' => $parts];
    }
}
