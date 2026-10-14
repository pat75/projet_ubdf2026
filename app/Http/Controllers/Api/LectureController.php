<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RubriqueResource;
use App\Models\User;
use App\Services\Espace\PagesBook;
use App\Services\Memo\MemoBooks;
use App\Services\Stats\TableauVisites;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/** Gestes simples sans controleur dedie : lecture des pages, memoBook (lecture et ajout/retrait), statistiques. */
class LectureController extends Controller
{
    public function pages(Request $requete, PagesBook $pages): AnonymousResourceCollection
    {
        return RubriqueResource::collection($pages->rubriques($requete->user()));
    }

    public function memo(Request $requete, MemoBooks $memo): JsonResponse
    {
        $recherche = mb_substr((string) $requete->query('recherche', ''), 0, 100);

        return response()->json(['data' => $memo->books($requete->user(), $recherche)->map(fn (User $book) => [
            'login' => $book->login,
            'nom' => $book->fullName(),
            'societe' => $book->company,
            'url_book' => $book->bookUrl(),
            'vignette' => $book->media->first()?->url('adm_medium'),
            'memorise_le' => $book->memorise_le ? \Illuminate\Support\Carbon::parse($book->memorise_le)->toIso8601String() : null,
        ])->values()]);
    }

    public function statistiques(Request $requete, TableauVisites $tableau): JsonResponse
    {
        return response()->json(['data' => $tableau->pour($requete->user())]);
    }

    public function memoriser(Request $requete, MemoBooks $memo): Response
    {
        $login = $requete->validate(['login' => ['required', 'string', 'max:100']])['login'];
        $book = User::where('login', mb_strtolower($login))->firstOrFail();

        $memo->ajouter($requete->user(), $book);

        return response()->noContent();
    }

    public function oublier(Request $requete, MemoBooks $memo, string $login): Response
    {
        $memo->retirer($requete->user(), User::where('login', $login)->firstOrFail());

        return response()->noContent();
    }
}
