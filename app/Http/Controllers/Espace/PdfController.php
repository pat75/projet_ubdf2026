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

        return response($pdf->generer($request->user()), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nom.'"',
        ]);
    }
}
