<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Admin\PriseIdentiteController;
use App\Http\Controllers\Admin\PriseIdentiteVisiteurController;
use App\Models\Reglage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ferme le portail au public quand un administrateur a coche « Mettre le
 * site en maintenance » (App\Filament\Pages\AccueilPage, cle
 * App\Models\Reglage::MAINTENANCE).
 *
 * Le portail entier repond alors la page de maintenance en 503 : ni
 * accueil, ni recherche, ni connexion, ni creation de book. Restent
 * servis :
 *
 *  - les books creatifs sur leur sous-domaine, et les images qu'ils
 *    tirent du portail (routes `book.*`) : fermer le portail
 *    ne doit pas casser les portfolios en ligne ;
 *  - le back-office et sa page de connexion, sans quoi personne ne
 *    pourrait rouvrir le site ;
 *  - une prise d'identite en cours, pour qu'un administrateur puisse
 *    continuer a regarder un compte de l'interieur ;
 *  - la deconnexion, et la notification de paiement Payplug, qui vient
 *    de serveur a serveur et n'a pas de page a lire.
 *
 * Les sessions creatif et visiteur deja ouvertes sont refermees : la
 * maintenance ne laisse personne dedans. La session est lue directement,
 * sans resoudre de garde, pour la meme raison que dans
 * RefuserComptesBloques : resoudre une garde emet un `Login` qui ferme
 * l'autre. `hasUser()` complete la lecture sans rien restaurer : il ne
 * repond que d'une garde deja resolue plus tot dans la requete.
 */
class Maintenance
{
    /** Routes servies malgre la maintenance (nom exact ou prefixe `x.`). */
    private const AUTORISEES = [
        'book.',
        'deconnexion',
        'payplug.notification',
    ];

    public function handle(Request $requete, Closure $suite): Response
    {
        if (! Reglage::enMaintenance() || $this->autorisee($requete)) {
            return $suite($requete);
        }

        foreach (['web', 'visitor'] as $garde) {
            $ouverte = filled($requete->session()?->get(Auth::guard($garde)->getName()))
                || Auth::guard($garde)->hasUser();

            if ($ouverte) {
                Auth::guard($garde)->logout();
            }
        }

        $message = __('Le site est en maintenance. Il revient très vite.');

        if ($requete->expectsJson()) {
            return response()->json(['message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->view('errors.maintenance', ['message' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    private function autorisee(Request $requete): bool
    {
        /*
         | Back-office seulement, pas le portail : un administrateur
         | connecte doit voir la page de maintenance comme le public, sans
         | quoi il croit le site ouvert depuis le navigateur ou il vient
         | justement de le fermer.
         */
        if ($requete->is('admin_', 'admin_/*')) {
            return true;
        }

        if ($this->appelLivewireDuBackOffice($requete)) {
            return true;
        }

        if ($requete->session()?->has(PriseIdentiteController::SESSION)
            || $requete->session()?->has(PriseIdentiteVisiteurController::SESSION)) {
            return true;
        }

        $nom = (string) $requete->route()?->getName();

        return $nom !== '' && Str::startsWith($nom, self::AUTORISEES);
    }

    /**
     * Le back-office parle a ses composants par `livewire-<hash>/update`,
     * hors de /admin_ : sans cette exception, le bouton « Enregistrer » de
     * la page d'accueil recevait lui aussi la 503, et un site mis en
     * maintenance ne pouvait plus etre rouvert. Idem pour le formulaire de
     * connexion du back-office.
     *
     * Seul un appel dont **tous** les composants sont ceux de Filament
     * passe : les composants Livewire de l'espace creatif restent fermes.
     */
    private function appelLivewireDuBackOffice(Request $requete): bool
    {
        if (! $requete->hasHeader('X-Livewire')) {
            return false;
        }

        $noms = collect($requete->input('components', []))
            ->map(fn ($composant) => json_decode((string) ($composant['snapshot'] ?? ''), true)['memo']['name'] ?? null);

        return $noms->isNotEmpty() && $noms->every(fn ($nom) => is_string($nom) && Str::startsWith(
            $nom,
            ['App\\Filament\\', 'Filament\\', 'app.filament.', 'filament.'],
        ));
    }
}
