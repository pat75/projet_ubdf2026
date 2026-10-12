<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\NoteSuppressionRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * Page affichee juste apres la suppression d'un book (Livewire\Espace\Compte) :
 * confirmation, puis une note de 1 a 5 etoiles, visible du back-office.
 * Le createur n'est plus connecte : l'acces passe par un lien signe.
 */
class SuppressionBookController extends Controller
{
    public static function lien(User $compte): string
    {
        return URL::temporarySignedRoute(nom_route('book.supprime'), now()->addDay(), ['user' => $compte->id]);
    }

    public function afficher(User $user): View
    {
        abort_unless($user->trashed(), 404);

        return view('front.book-supprime', [
            'compte' => $user,
            'action' => URL::temporarySignedRoute(nom_route('book.supprime.noter'), now()->addDay(), ['user' => $user->id]),
        ]);
    }

    public function noter(NoteSuppressionRequest $requete, User $user): RedirectResponse
    {
        abort_unless($user->trashed(), 404);

        $user->forceFill(['deletion_rating' => $requete->integer('note')])->saveQuietly();

        return redirect()->to(self::lien($user))->with('note_enregistree', true);
    }
}
