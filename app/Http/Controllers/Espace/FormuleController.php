<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Referral;
use App\Services\Espace\CodeDiffusion;
use App\Services\Paiement\Parrainage;
use App\Services\Paiement\Souscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Formule et factures (ubaction__user_pref_formule du legacy). */
class FormuleController extends Controller
{
    public function index(Request $request, Souscription $souscription, CodeDiffusion $diffusion, Parrainage $parrainage): View
    {
        $creatif = $request->user();

        return view('espace.formule', [
            'creatif' => $creatif,
            'echeance' => $creatif->echeanceFormule(),
            'options' => $souscription->options($creatif),
            'reabonnement' => $souscription->dejaAbonne($creatif),
            'factures' => $creatif->invoices()->where('status', 'paid')->latest('issued_at')->get(),
            'codeDiffusion' => $diffusion->pour($creatif),
            // Le parrainage est un depliant de la page, a la suite des
            // factures ; la saisie du code, elle, reste au composant.
        ]);
    }

    /** Croix du bloc « Félicitations » : masque jusqu'a la prochaine formule. */
    public function masquerMerci(Request $request): Response
    {
        $request->user()->update(['merci_masque_le' => now()]);

        return response()->noContent();
    }

    /** Fleche d'un bloc repliable (offre couplee, domaine) : retient son etat. */
    public function replier(Request $request, string $bloc): Response
    {
        $colonne = ['offre' => 'offre_repliee', 'domaine' => 'domaine_replie'][$bloc];
        $request->user()->update([$colonne => $request->boolean('repliee')]);

        return response()->noContent();
    }

    public function facture(Invoice $facture): View
    {
        $this->autoriser($facture);

        return view('espace.facture', $this->donneesFacture($facture));
    }

    /** Meme facture, en PDF : c'est celle-la qu'un comptable demande. */
    public function facturePdf(Invoice $facture): Response
    {
        $this->autoriser($facture);

        return Pdf::loadView('espace.facture', $this->donneesFacture($facture) + ['pdf' => true])
            ->setPaper('a4')
            ->download($facture->numero().'.pdf');
    }

    /** Le proprietaire de la facture, ou un administrateur connecte. */
    private function autoriser(Invoice $facture): void
    {
        if (Auth::guard('admin')->check()) {
            return;
        }

        Gate::authorize('view', $facture);
    }

    /** @return array<string, mixed> */
    private function donneesFacture(Invoice $facture): array
    {
        return [
            'facture' => $facture,
            'client' => $facture->user,
            'editeur' => config('ubdf.editeur'),
        ];
    }
}
