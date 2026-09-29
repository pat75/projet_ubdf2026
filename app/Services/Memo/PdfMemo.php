<?php

namespace App\Services\Memo;

use App\Models\User;
use App\Models\Visitor;
use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use App\Support\DossierBook;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * PDF du memo book : une fiche par book (couverture, nom, metier, adresse
 * du book). Les couvertures sont lues sur disque, en declinaison
 * `front_desk` : dompdf ne va pas chercher d'image sur le reseau.
 */
class PdfMemo
{
    public function __construct(
        private readonly MemoBooks $memo,
        private readonly GenerateurImages $images,
    ) {}

    public function generer(User|Visitor $proprietaire): string
    {
        $declinaison = Declinaison::nommee('front_desk');

        $fiches = $this->memo->books($proprietaire)->map(function (User $book) use ($declinaison) {
            $couverture = $book->media->first();
            $fichier = $couverture && $declinaison
                ? $this->images->produire(DossierBook::chemin($book->login, $couverture->filename), $declinaison)
                : null;

            return [
                'nom' => $book->fullName(),
                'metier' => $book->category?->name,
                'url' => $book->bookUrl(),
                'image' => $fichier ? 'data:'.(mime_content_type($fichier) ?: 'image/jpeg').';base64,'.base64_encode((string) file_get_contents($fichier)) : null,
            ];
        });

        return Pdf::loadView('memo.pdf', ['fiches' => $fiches])->setPaper('a4')->output();
    }
}
