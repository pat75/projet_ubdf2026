<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Services\Espace\PdfBook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** PDF du book, reserve a son proprietaire comme dans le legacy. */
class PdfController extends Controller
{
    public function __invoke(Request $request, PdfBook $pdf): Response
    {
        $nom = ($request->user()->brand === 'df' ? 'dustfolio' : 'ultra-book').'_'.now()->format('j-m-Y').'.pdf';

        // Interrupteurs de la page Exporter : ?titres=0 masque le nom des
        // rubriques, ?legendes=1 ajoute le titre des visuels, ?proteges=1
        // inclut les portfolios proteges par mot de passe.
        $contenu = $pdf->generer($request->user(), $request->boolean('titres', true), $request->boolean('legendes'), $request->boolean('proteges'));

        return response($contenu, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nom.'"',
        ]);
    }
}
