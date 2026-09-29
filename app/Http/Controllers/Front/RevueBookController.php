<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Admin;
use App\Models\User;
use App\Services\Admin\RevueBooks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Bascule de la selection depuis le bandeau de revue, sur le
 * sous-domaine du book.
 *
 * Sans session ici (voir App\Services\Admin\RevueBooks) : c'est le jeton
 * signe qui autorise le geste, et lui seul. Il est lie a un book precis,
 * donc un jeton valide pour l'un ne selectionne pas l'autre.
 *
 * La route est hors CSRF : le jeton joue ce role — il n'est pas dans un
 * cookie, un site tiers ne peut donc pas le forger ni le rejouer sans
 * l'avoir deja. C'est la meme exemption, pour la meme raison, que celle
 * du formulaire de contact du front (bootstrap/app.php).
 */
class RevueBookController extends Controller
{
    public function basculer(Request $requete, RevueBooks $revue, string $login): RedirectResponse
    {
        $book = User::where('login', $login)->firstOrFail();

        $administrateur = $revue->administrateur(
            (string) $requete->input(RevueBooks::PARAMETRE),
            $book,
        );

        abort_if($administrateur === null, 403);

        $book->update(['in_home_selection' => ! $book->in_home_selection]);

        $this->journaliser($requete, $book, $administrateur);

        // Retour sur le book, jeton compris : le bandeau se reaffiche avec
        // le nouvel etat, sans que l'onglet perde sa place.
        return redirect()->to($book->bookUrl().'/portfolio?'.http_build_query([
            RevueBooks::PARAMETRE => $requete->input(RevueBooks::PARAMETRE),
        ]));
    }

    /**
     * Le journal du back-office ne voit pas passer ce changement :
     * l'observateur JournalAdmin ne retient que ce qui vient d'une session
     * `admin`, et il n'y en a pas sur ce sous-domaine. On note donc
     * l'action ici, avec le meme format.
     */
    private function journaliser(Request $requete, User $book, int $administrateur): void
    {
        AdminActivity::create([
            'admin_id' => $administrateur,
            'admin_name' => Admin::find($administrateur)?->name,
            'action' => 'updated',
            'subject_type' => User::class,
            'subject_id' => (string) $book->getKey(),
            'subject_label' => $book->login,
            'changes' => ['in_home_selection' => [
                'avant' => ! $book->in_home_selection,
                'apres' => $book->in_home_selection,
            ]],
            'ip' => $requete->ip(),
        ]);
    }
}
