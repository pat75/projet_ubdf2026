<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Services\Paiement\Souscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaiementController extends Controller
{
    public function __construct(private readonly Souscription $souscription) {}

    /** Envoie le createur sur la page de paiement Payplug. */
    public function payer(Request $request, int $option): RedirectResponse
    {
        return redirect()->away($this->souscription->commencer($request->user(), $option));
    }

    /**
     * Retour de Payplug. La formule n'est prolongee qu'a la notification,
     * qui peut arriver quelques secondes apres.
     */
    public function retour(): RedirectResponse
    {
        return redirect()->route('espace.formule')
            ->with('statut', __('Merci ! Votre paiement est en cours de validation, la formule sera active dans quelques instants.'));
    }

    /** Notification serveur a serveur de Payplug. */
    public function notification(Request $request): Response
    {
        try {
            $this->souscription->traiter($request->getContent());
        } catch (\Payplug\Exception\PayplugException $e) {
            report($e);

            return response('', 400);
        }

        return response('', 200);
    }
}
