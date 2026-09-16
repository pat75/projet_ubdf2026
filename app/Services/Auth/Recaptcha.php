<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verification reCAPTCHA v3.
 *
 * Le front de 2019 pose deja un jeton dans `g-recaptcha-response` sur les
 * trois formulaires (connexion, inscription, mot de passe oublie) ; cette
 * classe en fait la contrepartie serveur.
 *
 * Deux ecarts avec le legacy, volontaires :
 *
 * 1. Sans secret configure, la verification est **desactivee** et le
 *    signale dans les logs. Le legacy, lui, ecrivait `$error = false;`
 *    juste apres l'appel dans le cas « mot de passe oublie » (voir
 *    front/ajax_2010.php ligne 660) : le captcha y etait donc neutralise en
 *    production sans que rien ne l'indique.
 *
 * 2. Une erreur reseau ne bloque pas la requete. Google indisponible ne doit
 *    pas empecher un creatif de se connecter ; l'incident est journalise.
 */
class Recaptcha
{
    private const URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function actif(): bool
    {
        return (bool) config('services.recaptcha.secret');
    }

    /**
     * @param  string|null  $jeton  Valeur de `g-recaptcha-response`.
     * @param  string|null  $action  Action attendue, quand le front en pose une.
     */
    public function valide(?string $jeton, ?string $action = null): bool
    {
        if (! $this->actif()) {
            Log::debug('reCAPTCHA non configure : verification ignoree.');

            return true;
        }

        if (blank($jeton)) {
            return false;
        }

        try {
            $reponse = Http::asForm()
                ->timeout(5)
                ->post(self::URL, [
                    'secret' => config('services.recaptcha.secret'),
                    'response' => $jeton,
                    'remoteip' => request()->ip(),
                ])
                ->json();
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA injoignable, requete laissee passer.', [
                'exception' => $e->getMessage(),
            ]);

            return true;
        }

        if (! ($reponse['success'] ?? false)) {
            return false;
        }

        if ($action !== null && ($reponse['action'] ?? $action) !== $action) {
            return false;
        }

        return ($reponse['score'] ?? 0) >= config('services.recaptcha.score_minimum');
    }
}
