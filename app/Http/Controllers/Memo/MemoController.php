<?php

namespace App\Http\Controllers\Memo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Memo\FusionMemoRequest;
use App\Http\Requests\Memo\MemoRequest;
use App\Models\User;
use App\Services\Memo\MemoBooks;
use Illuminate\Http\JsonResponse;

/**
 * Le memo en base, appele par le store Alpine `memo` du portail
 * (resources/js/portail/visionneuse.js) pour un visiteur ou un creatif
 * connecte. Chaque reponse rend la liste a jour des logins.
 */
class MemoController extends Controller
{
    public function __construct(private readonly MemoBooks $memo) {}

    public function ajouter(MemoRequest $requete): JsonResponse
    {
        $proprietaire = $this->proprietaire();
        $this->memo->ajouter($proprietaire, $this->book($requete));

        return $this->etat($proprietaire);
    }

    public function retirer(MemoRequest $requete): JsonResponse
    {
        $proprietaire = $this->proprietaire();
        $this->memo->retirer($proprietaire, $this->book($requete));

        return $this->etat($proprietaire);
    }

    public function fusionner(FusionMemoRequest $requete): JsonResponse
    {
        $proprietaire = $this->proprietaire();
        $this->memo->fusionner($proprietaire, $requete->validated('logins'));

        return $this->etat($proprietaire);
    }

    private function etat($proprietaire): JsonResponse
    {
        return response()->json(['logins' => $this->memo->logins($proprietaire)]);
    }

    private function proprietaire()
    {
        return $this->memo->proprietaire() ?? abort(401);
    }

    private function book(MemoRequest $requete): User
    {
        return User::query()->where('login', mb_strtolower($requete->validated('login')))->firstOrFail();
    }
}
