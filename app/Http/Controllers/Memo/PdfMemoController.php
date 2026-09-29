<?php

namespace App\Http\Controllers\Memo;

use App\Http\Controllers\Controller;
use App\Services\Memo\MemoBooks;
use App\Services\Memo\PdfMemo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Export PDF du memoBook de la personne connectee (visiteur ou creatif). `?qr=1` : codes QR des books. */
class PdfMemoController extends Controller
{
    public function __invoke(Request $requete, MemoBooks $memo, PdfMemo $pdf): Response
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);

        return response($pdf->generer($proprietaire, $requete->boolean('qr')), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="memo-book_'.now()->format('j-m-Y').'.pdf"',
        ]);
    }
}
