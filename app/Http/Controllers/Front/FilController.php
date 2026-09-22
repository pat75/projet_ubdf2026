<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Messagerie\Intermediation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Fil de discussion ouvert par lien signe, sans compte ni session.
 *
 * Les deux parties voient le meme echange, chacune par son propre lien.
 * Aucune adresse electronique n'est affichee : c'est la plateforme qui
 * relaie, et c'est ce qui distingue cette messagerie d'un simple
 * formulaire de contact.
 */
class FilController extends Controller
{
    public function __construct(private readonly Intermediation $intermediation) {}

    public function show(string $role, string $selector, string $jeton): View
    {
        ['conversation' => $conversation, 'role' => $role] = $this->acces($role, $selector, $jeton);

        $conversation->load(['messages', 'user']);

        // Marquer lu ce que l'autre partie a ecrit, pas ses propres messages.
        $conversation->messages()
            ->where('from_owner', $role !== Intermediation::PROPRIETAIRE)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('front.messagerie.fil', [
            'conversation' => $conversation,
            'role' => $role,
            'jeton' => $jeton,
        ]);
    }

    public function repondre(Request $request, string $role, string $selector, string $jeton): RedirectResponse
    {
        ['conversation' => $conversation, 'role' => $role] = $this->acces($role, $selector, $jeton);

        $valide = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
        ], [
            'message.required' => 'Écrivez votre réponse.',
        ]);

        $this->intermediation->repondre($conversation, $role, $valide['message'], $request->ip());

        $this->intermediation->notifierAutrePartie($conversation, $role);

        return redirect()
            ->route('messagerie.fil', compact('role', 'selector', 'jeton'))
            ->with('envoye', true);
    }

    /**
     * Previent la partie qui n'a pas ecrit, sur son propre lien.
     *
     * Un nouveau jeton lui est attribue a cette occasion : le precedent a
     * circule par courriel depuis l'ouverture du fil, parfois depuis des
     * mois. Le lien envoye est donc toujours le plus recent.
     */

    /**
     * @return array{conversation: \App\Models\Conversation, role: string}
     */
    private function acces(string $role, string $selector, string $jeton): array
    {
        $acces = $this->intermediation->ouvrirDepuisLien($role, $selector, $jeton);

        if ($acces === null) {
            // Un lien invalide et un lien expire rendent la meme reponse :
            // rien ne doit permettre de distinguer les deux cas.
            throw new NotFoundHttpException('Ce lien de discussion n’est plus valable.');
        }

        return $acces;
    }
}
