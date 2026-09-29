<?php

namespace App\Http\Controllers\Visiteur;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Visiteur\JournalVisites;
use Illuminate\Http\Response;

/**
 * Pixel pose sur chaque book (book/commun/_pixel) et appele par la
 * visionneuse du portail : note la visite d'un visiteur connecte. Sans
 * session visiteur, ne fait rien.
 */
class VisiteBookController extends Controller
{
    private const GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function __invoke(string $login, JournalVisites $journal): Response
    {
        $visiteur = auth('visitor')->user();

        if ($visiteur && ($book = User::query()->where('login', mb_strtolower($login))->first())) {
            $journal->noter($visiteur, $book);
        }

        return response(base64_decode(self::GIF), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
