<?php

namespace App\Services\Book;

use App\Models\Gallery;

/**
 * Portfolios proteges par mot de passe, cote visiteur du book.
 *
 * Le deverrouillage vaut pour un portfolio et dure la session du
 * navigateur. Le createur connecte voit toujours tout son book.
 */
class AccesPortfolios
{
    private const CLE = 'book.portfolios_ouverts';

    public function ouvert(Gallery $galerie): bool
    {
        return ! $galerie->estProtegee()
            || auth()->id() === $galerie->user_id
            || in_array($galerie->id, session(self::CLE, []), true);
    }

    public function deverrouiller(Gallery $galerie, string $motDePasse): bool
    {
        if (! $galerie->estProtegee() || ! hash_equals((string) $galerie->password, $motDePasse)) {
            return false;
        }

        session()->push(self::CLE, $galerie->id);

        return true;
    }

    /** Fichier hors de tout portfolio protege. */
    public const LIBRE = 'libre';

    /** Fichier d'un portfolio protege, deverrouille pour ce visiteur. */
    public const OUVERT = 'ouvert';

    /** Fichier d'un portfolio protege, encore ferme a ce visiteur. */
    public const FERME = 'ferme';

    /**
     * Etat d'un fichier du book pour ce visiteur. Une seule requete par
     * image servie : le portfolio protege qui contient ce fichier, s'il y
     * en a un.
     *
     * @return self::LIBRE|self::OUVERT|self::FERME
     */
    public function fichier(string $login, string $fichier): string
    {
        $galerie = Gallery::query()
            ->whereNotNull('password')
            ->whereHas('user', fn ($q) => $q->where('login', $login))
            ->whereHas('media', fn ($q) => $q->where('filename', $fichier))
            ->first();

        return match (true) {
            $galerie === null => self::LIBRE,
            $this->ouvert($galerie) => self::OUVERT,
            default => self::FERME,
        };
    }
}
