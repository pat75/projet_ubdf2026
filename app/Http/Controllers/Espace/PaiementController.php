<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Services\Paiement\Souscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaiementController extends Controller
{
    public function __construct(private readonly Souscription $souscription) {}

    /** Envoie le createur sur la page de paiement Payplug. */
    public function payer(Request $request, int $option): RedirectResponse|JsonResponse
    {
        $url = $this->souscription->commencer($request->user(), $option);

        // Fenetre flottante Payplug : le script de la page recoit l'adresse.
        return $request->expectsJson() ? response()->json(['url' => $url]) : redirect()->away($url);
    }

    /**
     * Retour de Payplug. La formule n'est prolongee qu'a la notification,
     * qui peut arriver quelques secondes apres.
     */
    public function retour(Request $request): RedirectResponse
    {
        $facture = $this->souscription->confirmer($request->user());

        return redirect()->route('espace.formule')->with('paiement_ok', (bool) $facture)->with('paiement', $facture
            ? __('Votre paiement est validé. Votre formule vient d’être activée.')
            : __('Merci ! Votre paiement est en cours de validation, la formule sera active dans quelques instants.'));
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
