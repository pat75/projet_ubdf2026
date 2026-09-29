<?php

namespace App\Http\Controllers\Memo;

use App\Http\Controllers\Controller;
use App\Services\Memo\MemoBooks;
use App\Services\Memo\PdfMemo;
use Illuminate\Http\Response;

/** Export PDF du memo book de la personne connectee (visiteur ou creatif). */
class PdfMemoController extends Controller
{
    public function __invoke(MemoBooks $memo, PdfMemo $pdf): Response
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);

        return response($pdf->generer($proprietaire), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="memo-book_'.now()->format('j-m-Y').'.pdf"',
        ]);
    }
}
