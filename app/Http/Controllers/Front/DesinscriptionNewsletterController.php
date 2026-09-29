<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Newsletter\Desabonnement;
use Illuminate\View\View;

/**
 * Desinscription et reabonnement depuis le lien signe des newsletters.
 *
 * Un clic suffit : pas de formulaire, pas de connexion. La page offre le
 * retour en arriere, pour un clic donne par erreur.
 */
class DesinscriptionNewsletterController extends Controller
{
    public function __construct(private readonly Desabonnement $desabonnement) {}

    public function desinscrire(string $adresse): View
    {
        $email = $this->desabonnement->decoder($adresse);

        return view('front.newsletter.desinscription', [
            'etat' => $this->desabonnement->desabonner($email) ? 'desinscrit' : 'inconnu',
            'jeton' => $adresse
        ]);
    }

    public function reabonner(string $adresse): View
    {
        $email = $this->desabonnement->decoder($adresse);

        return view('front.newsletter.desinscription', [
            'etat' => $this->desabonnement->reabonner($email) ? 'reabonne' : 'inconnu',
            'jeton' => $adresse
        ]);
    }
}
