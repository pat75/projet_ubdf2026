<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Espace\CodeDiffusion;
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
    public function index(Request $request, Souscription $souscription, CodeDiffusion $diffusion): View
    {
        $creatif = $request->user();

        return view('espace.formule', [
            'creatif' => $creatif,
            'echeance' => $creatif->echeanceFormule(),
            'options' => $souscription->options($creatif),
            'reabonnement' => $souscription->dejaAbonne($creatif),
            'factures' => $creatif->invoices()->where('status', 'paid')->latest('issued_at')->get(),
            'codeDiffusion' => $diffusion->pour($creatif),
            'quotas' => $this->quotas($creatif),
        ]);
    }

    /**
     * Les deux compteurs de la page : nombre de visuels et poids total,
     * chacun rapporte au plafond de la formule.
     *
     * @return array<string, array{valeur: int, plafond: int, unite: string}>
     */
    private function quotas(User $creatif): array
    {
        $limites = config('formules.limites.'.($creatif->plan ? 'payante' : 'gratuite'));

        return [
            'images' => [
                'valeur' => (int) $creatif->media_count,
                'plafond' => (int) $limites['visuels'],
                'unite' => '',
            ],
            'poids' => [
                'valeur' => (int) $creatif->storage_used,
                'plafond' => (int) $limites['poids_ko'],
                'unite' => 'Ko',
            ],
        ];
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
