<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Connexion d'une application desktop sans saisir le mot de passe dans
 * l'application (principe du « device flow » OAuth) :
 *
 *   1. l'application demande un code : elle recoit un code court a
 *      afficher (ABCD-2345) et un code secret qu'elle garde ;
 *   2. le creatif, connecte sur le site, saisit le code court dans
 *      Mon compte > Appareils connectes ;
 *   3. l'application, qui interroge le site en boucle avec son code
 *      secret, recoit alors son jeton.
 *
 * Tout vit en cache, 10 minutes : rien a nettoyer en base.
 */
class ConnexionParCode
{
    public const DUREE = 600;

    /** Sans 0/O ni 1/I : le code se recopie a la main. */
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * @param  list<string>  $droits
     * @return array{code: string, code_appareil: string, expire_dans: int}
     */
    public function demarrer(string $appareil, array $droits): array
    {
        $code = $this->codeCourt();
        $secret = Str::random(64);

        Cache::put($this->cleSecret($secret), ['code' => $code, 'appareil' => $appareil, 'droits' => $droits, 'user_id' => null], self::DUREE);
        Cache::put($this->cleCode($code), $secret, self::DUREE);

        return ['code' => $code, 'code_appareil' => $secret, 'expire_dans' => self::DUREE];
    }

    /**
     * Ce que demande l'application derriere un code, sans le consommer :
     * le creatif le voit avant de confirmer (un code transmis par un tiers
     * donnerait sinon son compte a l'appareil de ce tiers).
     *
     * @return array{appareil: string, droits: list<string>}|null
     */
    public function consulter(string $code): ?array
    {
        $secret = Cache::get($this->cleCode($this->normaliser($code)));
        $demande = $secret ? Cache::get($this->cleSecret($secret)) : null;

        return $demande ? ['appareil' => $demande['appareil'], 'droits' => $demande['droits']] : null;
    }

    /** Le creatif valide le code affiche par l'application. Rend le nom de l'appareil, ou null si le code est inconnu ou expire. */
    public function autoriser(User $creatif, string $code): ?string
    {
        $code = $this->normaliser($code);
        $secret = Cache::pull($this->cleCode($code));
        $demande = $secret ? Cache::get($this->cleSecret($secret)) : null;

        if (! $demande) {
            return null;
        }

        Cache::put($this->cleSecret($secret), ['user_id' => $creatif->id] + $demande, self::DUREE);

        return $demande['appareil'];
    }

    /**
     * @return array{jeton: string, droits: list<string>}|'en_attente'|'expire'
     */
    public function echanger(string $secret): array|string
    {
        $demande = Cache::get($this->cleSecret($secret));

        if (! $demande) {
            return 'expire';
        }

        if (! $demande['user_id']) {
            return 'en_attente';
        }

        Cache::forget($this->cleSecret($secret));
        $creatif = User::find($demande['user_id']);

        if (! $creatif || $creatif->estBloque()) {
            return 'expire';
        }

        return [
            'jeton' => $creatif->createToken($demande['appareil'], $demande['droits'])->plainTextToken,
            'droits' => $demande['droits'],
        ];
    }

    private function codeCourt(): string
    {
        $code = '';

        for ($i = 0; $i < 8; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return substr($code, 0, 4).'-'.substr($code, 4);
    }

    /** « abcd 2345 », « ABCD2345 » ou « abcd-2345 » : meme code. */
    private function normaliser(string $code): string
    {
        $brut = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));

        return substr($brut, 0, 4).'-'.substr($brut, 4, 4);
    }

    private function cleCode(string $code): string
    {
        return 'api-code:'.$code;
    }

    private function cleSecret(string $secret): string
    {
        return 'api-code-appareil:'.hash('sha256', $secret);
    }
}
