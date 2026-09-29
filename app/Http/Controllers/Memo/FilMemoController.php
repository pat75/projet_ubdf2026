<?php

namespace App\Http\Controllers\Memo;

use App\Http\Controllers\Controller;
use App\Services\Memo\MemoBooks;
use App\Services\Messagerie\Intermediation;
use Illuminate\Http\RedirectResponse;

/**
 * Ouvre, pour y repondre, un fil envoye par le visiteur ou le creatif
 * connecte (tableau de bord visiteur, fenetre des messages du memoBook).
 *
 * Le fil reste celui de la messagerie par lien (FilController) : un
 * jeton emetteur neuf est emis et la personne y est conduite. Seuls les
 * fils de son adresse confirmee lui sont accessibles.
 */
class FilMemoController extends Controller
{
    public function __invoke(int $conversation, MemoBooks $memo, Intermediation $intermediation): RedirectResponse
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);
        $fil = $memo->conversations($proprietaire)->findOrFail($conversation);

        return redirect()->to($intermediation->renouveler($fil, Intermediation::EMETTEUR));
    }
}
