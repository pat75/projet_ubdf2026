<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\IdentifierCompte;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\JetonRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Jetons d'acces des applications : un par appareil, revocable.
 *
 * Meme identification que le formulaire du portail (IdentifierCompte),
 * reservee aux creatifs : un visiteur n'a pas d'espace a piloter.
 */
class JetonController extends Controller
{
    public function store(JetonRequest $requete, IdentifierCompte $identifier): JsonResponse
    {
        $compte = $identifier->executer($requete->validated('identifiant'), $requete->validated('mot_de_passe'));

        abort_if($compte === IdentifierCompte::BLOQUE, 403, __('Ce compte a été suspendu. Contactez-nous pour en connaître la raison.'));

        if ($compte === IdentifierCompte::AMBIGU) {
            throw ValidationException::withMessages([
                'identifiant' => __('Plusieurs books utilisent cette adresse : connectez-vous avec votre identifiant.'),
            ]);
        }

        if (! $compte instanceof User) {
            throw ValidationException::withMessages(['identifiant' => __('Identifiant ou mot de passe incorrect.')]);
        }

        $droits = $requete->droits();

        return response()->json([
            'jeton' => $compte->createToken($requete->validated('appareil'), $droits)->plainTextToken,
            'droits' => $droits,
        ], 201);
    }

    public function destroy(Request $requete): Response
    {
        $requete->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
