<?php

namespace App\Http\Controllers;

use App\Services\Captcha\Captcha;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Image du captcha d'un formulaire (App\Services\Captcha\Captcha).
 * Chaque affichage tire un nouveau code : recharger l'image en donne un autre.
 */
class CaptchaController extends Controller
{
    public function __invoke(Captcha $captcha, Request $request): Response
    {
        // Par son nom : sur un sous-domaine de book, {login} precede.
        $formulaire = (string) ($request->route('formulaire') ?? 'contact');

        abort_unless(in_array($formulaire, Captcha::FORMULAIRES, true), 404);

        return response($captcha->svg($captcha->generer($formulaire)), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
