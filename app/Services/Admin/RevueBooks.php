<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Revue en serie des books depuis le back-office : l'administrateur ouvre
 * d'un coup tous les books de la page courante, chacun dans son onglet,
 * et met en selection ceux qui le meritent sans revenir a la liste.
 *
 * Pourquoi un jeton signe plutot que la session administrateur :
 * `SESSION_DOMAIN` est vide, la session du portail ne couvre donc pas les
 * sous-domaines des books (meme constat que pour le mode edition d'un
 * createur, voir EditionBookController). Le bandeau ne peut pas savoir par
 * un cookie qu'un administrateur regarde.
 *
 * Plutot que d'ouvrir une session sur chacun des sous-domaines — une par
 * onglet, cinquante sessions pour une revue —, le droit de basculer la
 * selection tient dans un jeton signe, porte par l'adresse. Il est lie a
 * un seul book, expire, et n'autorise que ce geste-la : ni lecture de
 * donnees, ni acces au back-office.
 */
class RevueBooks
{
    /** Nom du parametre d'adresse qui porte le jeton. */
    public const PARAMETRE = 'ub_revue';

    /**
     * Duree de validite.
     *
     * Deux heures : une revue de cinquante books se fait en une fois, mais
     * on la commence, on repond au telephone, on la reprend. Plus court
     * ferait expirer des onglets encore ouverts ; plus long laisserait
     * trainer des adresses actives dans un historique.
     */
    private const DUREE_HEURES = 2;

    /** Jeton pour ce book, au nom de cet administrateur. */
    public function jeton(User $book, int $administrateur): string
    {
        $expire = Carbon::now()->addHours(self::DUREE_HEURES)->getTimestamp();
        $charge = $book->login.'|'.$expire.'|'.$administrateur;

        return $charge.'|'.$this->signature($charge);
    }

    /**
     * Administrateur porte par ce jeton, ou null s'il est absent, expire,
     * falsifie, ou emis pour un autre book.
     */
    public function administrateur(?string $jeton, User $book): ?int
    {
        if (blank($jeton) || substr_count($jeton, '|') !== 3) {
            return null;
        }

        [$login, $expire, $administrateur, $signature] = explode('|', $jeton);

        $attendue = $this->signature($login.'|'.$expire.'|'.$administrateur);

        // Comparaison a temps constant : une comparaison ordinaire laisse
        // deviner la signature octet par octet.
        if (! hash_equals($attendue, $signature)) {
            return null;
        }

        if ($login !== $book->login || (int) $expire < Carbon::now()->getTimestamp()) {
            return null;
        }

        return (int) $administrateur;
    }

    /** Adresse du book a ouvrir pour la revue, jeton compris. */
    public function url(User $book, int $administrateur): string
    {
        return $book->bookUrl().'/portfolio?'.http_build_query([
            self::PARAMETRE => $this->jeton($book, $administrateur),
        ]);
    }

    private function signature(string $charge): string
    {
        return hash_hmac('sha256', $charge, (string) config('app.key'));
    }
}
