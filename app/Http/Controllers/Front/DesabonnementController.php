<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

/**
 * Desabonnement de la newsletter, depuis le lien signe des e-mails
 * (nl_unsubscribe.php du legacy).
 *
 * Un clic suffit : pas de formulaire, pas de connexion. La signature du
 * lien tient lieu de preuve.
 */
class DesabonnementController extends Controller
{
    public function __invoke(User $user): View
    {
        $user->bookSetting()->updateOrCreate([], ['diffuse_newsletter' => false]);

        return view('front.desabonnement', ['creatif' => $user]);
    }
}
