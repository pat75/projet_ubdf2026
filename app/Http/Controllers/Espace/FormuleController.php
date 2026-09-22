<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Paiement\Souscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Formule et factures (ubaction__user_pref_formule du legacy). */
class FormuleController extends Controller
{
    public function index(Request $request, Souscription $souscription): View
    {
        $creatif = $request->user();
        $echeance = $creatif->echeanceFormule();

        return view('espace.formule', [
            'creatif' => $creatif,
            'echeance' => $echeance,
            'options' => $souscription->options($creatif),
            'factures' => $creatif->invoices()->where('status', 'paid')->latest('issued_at')->get(),
        ]);
    }

    public function facture(Invoice $facture): View
    {
        Gate::authorize('view', $facture);

        return view('espace.facture', [
            'facture' => $facture,
            'client' => $facture->user,
            'editeur' => config('ubdf.editeur'),
        ]);
    }
}
