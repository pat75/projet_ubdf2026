<?php

namespace App\Http\Controllers\Visiteur;

use App\Http\Controllers\Controller;
use App\Services\Memo\MemoBooks;
use Illuminate\View\View;

/**
 * Tableau de bord d'un visiteur connecte : ses derniers messages, ses
 * dernieres visites de books et les derniers books de son memo.
 */
class TableauVisiteurController extends Controller
{
    public function __invoke(MemoBooks $memo): View
    {
        $visiteur = auth('visitor')->user();

        return view('visiteur.tableau', [
            'visiteur' => $visiteur,
            'messages' => $memo->conversations($visiteur)
                ->with('user')
                ->withCount(['messages as non_lus' => fn ($q) => $q->where('from_owner', true)->whereNull('read_at')])
                ->orderByDesc('last_message_at')
                ->limit(5)
                ->get(),
            'visites' => $visiteur->visites()->with('category')->limit(8)->get(),
            'memo' => $memo->books($visiteur, limite: 6),
            'memoTotal' => $memo->compter($visiteur),
        ]);
    }
}
