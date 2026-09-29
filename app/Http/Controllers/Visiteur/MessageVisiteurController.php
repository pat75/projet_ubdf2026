<?php

namespace App\Http\Controllers\Visiteur;

use App\Http\Controllers\Controller;
use App\Services\Messagerie\Intermediation;
use Illuminate\Http\RedirectResponse;

/**
 * Ouvre, depuis le tableau de bord, un fil envoye par le visiteur.
 *
 * Le fil reste celui de la messagerie par lien (FilController) : un
 * jeton emetteur neuf est emis et le visiteur y est conduit. Seuls les
 * fils de son adresse confirmee lui sont accessibles (Visitor::demandes).
 */
class MessageVisiteurController extends Controller
{
    public function __invoke(int $conversation, Intermediation $intermediation): RedirectResponse
    {
        $fil = auth('visitor')->user()->demandes()->findOrFail($conversation);

        return redirect()->to($intermediation->renouveler($fil, Intermediation::EMETTEUR));
    }
}
