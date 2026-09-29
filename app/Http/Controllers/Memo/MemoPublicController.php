<?php

namespace App\Http\Controllers\Memo;

use App\Http\Controllers\Controller;
use App\Models\MemoPartage;
use App\Models\User;
use App\Models\Visitor;
use App\Services\Memo\MemoBooks;
use App\Services\Memo\PdfMemo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Version publique d'un memoBook partage : la liste des books seule, sans
 * messages ni retrait. Un partage coupe rend 404, comme un jeton inconnu.
 */
class MemoPublicController extends Controller
{
    public function page(string $jeton, MemoBooks $memo): View
    {
        [$partage, $proprietaire] = $this->partage($jeton);

        return view('memo.public', [
            'partage' => $partage,
            'books' => $memo->books($proprietaire),
            'auteur' => $proprietaire instanceof User ? $proprietaire->fullName() : null,
            'createur' => $proprietaire instanceof User ? $proprietaire : null,
        ]);
    }

    /** Le meme PDF que celui du proprietaire, titre « La selection de … ». `?qr=1` : codes QR. */
    public function pdf(Request $requete, string $jeton, PdfMemo $pdf): Response
    {
        [, $proprietaire] = $this->partage($jeton);

        $titre = $proprietaire instanceof User
            ? __('La sélection de :nom', ['nom' => $proprietaire->fullName()])
            : __('Une sélection de books');

        return response($pdf->generer($proprietaire, $requete->boolean('qr'), $titre), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="selection-books_'.now()->format('j-m-Y').'.pdf"',
        ]);
    }

    /** @return array{0: MemoPartage, 1: User|Visitor} */
    private function partage(string $jeton): array
    {
        $partage = MemoPartage::query()->where('jeton', $jeton)->where('actif', true)->firstOrFail();

        return [$partage, $partage->proprietaire() ?? abort(404)];
    }
}
