<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

/** Lien signe des mails de coaching : coupe l'interrupteur « Conseils pour mon book » de Diffusion. */
class DesabonnementCoachController extends Controller
{
    public function __invoke(User $user): View
    {
        $user->bookSetting()->updateOrCreate([], ['coaching' => false]);

        return view('front.desabonnement-coach', ['creatif' => $user]);
    }
}
