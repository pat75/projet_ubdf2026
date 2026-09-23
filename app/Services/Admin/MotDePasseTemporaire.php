<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Reinitialisation d'un mot de passe depuis le back-office.
 *
 * Le legacy renvoyait au createur son mot de passe **en clair**, qu'il
 * stockait tel quel. Ici rien n'est conserve : le mot de passe tire est
 * hache aussitot, et sa forme lisible ne vit que le temps de la reponse
 * qui l'affiche a l'administrateur.
 */
class MotDePasseTemporaire
{
    /**
     * Alphabet de 8 caracteres alphanumeriques, prive des signes qui se
     * confondent a l'oral ou a la lecture : 0 et O, 1, I et l, 5 et S.
     */
    private const ALPHABET = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public const LONGUEUR = 8;

    public function generer(): string
    {
        $alphabet = self::ALPHABET;
        $dernier = strlen($alphabet) - 1;

        $mot = '';
        for ($i = 0; $i < self::LONGUEUR; $i++) {
            $mot .= $alphabet[random_int(0, $dernier)];
        }

        return $mot;
    }

    /**
     * Tire un mot de passe, l'applique au compte et le rend en clair.
     *
     * Les sessions ouvertes du createur sont invalidees : sans cela, un
     * navigateur reste connecte avec l'ancien mot de passe.
     */
    public function appliquer(User $creatif): string
    {
        $clair = $this->generer();

        $creatif->forceFill([
            'password' => $clair,          // le cast `hashed` fait le reste
            'remember_token' => null,
        ])->save();

        $this->fermerLesSessions($creatif);

        return $clair;
    }

    private function fermerLesSessions(User $creatif): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $creatif->id)
            ->delete();
    }
}
