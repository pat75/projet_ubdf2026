<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Admin\PriseIdentiteController;
use App\Http\Controllers\Admin\PriseIdentiteVisiteurController;
use App\Models\User;
use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Referme la session d'un compte bloque depuis le back-office.
 *
 * Pose en global plutot que sur les groupes `auth:web` / `auth:visitor` :
 * le blocage doit valoir pour une session deja ouverte au moment ou
 * l'administrateur l'a ferme, sans quoi le compte resterait actif tant
 * qu'il ne se deconnecte pas.
 *
 * Il lit l'identifiant **directement dans la session**, sans passer par
 * `Auth::guard(...)->user()`. Resoudre une garde restaure au besoin la
 * session depuis le cookie « se souvenir de moi », ce qui emet un
 * evenement `Login` — et un `Login` sur une garde ferme l'autre
 * (AppServiceProvider). Interroger les deux gardes a chaque requete se
 * deconnectait donc tout seul. Ici, rien n'est resolu ni restaure.
 *
 * Un compte restaure par le seul cookie « se souvenir de moi » passe
 * entre les mailles a sa toute premiere requete — la session ne porte
 * pas encore sa cle — et est ferme a la suivante. L'entree par le
 * formulaire, elle, est refusee d'emblee (IdentifierCompte).
 */
class RefuserComptesBloques
{
    /** Garde => modele consulte. */
    private const GARDES = ['web' => User::class, 'visitor' => Visitor::class];

    public function handle(Request $requete, Closure $suite): Response
    {
        /*
         | Une prise d'identite echappe au blocage : c'est justement sur un
         | compte ferme qu'un administrateur a besoin d'entrer pour voir ce
         | qui s'y passe. Les temoins ne sont poses que par les controleurs
         | de prise d'identite, sous garde `admin`.
         */
        if ($requete->session()?->has(PriseIdentiteController::SESSION)
            || $requete->session()?->has(PriseIdentiteVisiteurController::SESSION)) {
            return $suite($requete);
        }

        $bloque = false;

        foreach (self::GARDES as $garde => $modele) {
            if ($this->estBloque($requete, $garde, $modele)) {
                Auth::guard($garde)->logout();
                $bloque = true;
            }
        }

        if (! $bloque) {
            return $suite($requete);
        }

        $requete->session()?->invalidate();
        $requete->session()?->regenerateToken();

        return redirect()->to(lien('home'))
            ->with('connexion_ouverte', true)
            ->withErrors(['login' => __('Ce compte a été suspendu. Contactez-nous pour en connaître la raison.')]);
    }

    /**
     * @param  class-string<User|Visitor>  $modele
     */
    private function estBloque(Request $requete, string $garde, string $modele): bool
    {
        // Cle de session de la garde (« login_web_<hash> ») : lue telle
        // quelle, sans demander a la garde de resoudre quoi que ce soit.
        $id = $requete->session()?->get(Auth::guard($garde)->getName());

        if (blank($id)) {
            return false;
        }

        return $modele::withTrashed()->whereKey($id)->whereNotNull('blocked_at')->exists();
    }
}
