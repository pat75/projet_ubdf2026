<?php

namespace App\Services\Book;

use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;

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
     * Etat d'un fichier du book pour ce visiteur. Un book sans portfolio
     * protege ne coute qu'une requete, sur un index.
     *
     * @return self::LIBRE|self::OUVERT|self::FERME
     */
    public function fichier(string $login, string $fichier): string
    {
        $book = User::where('login', $login)->first(['id']);
        $proteges = $book?->galleries()->whereNotNull('password')->get() ?? collect();

        if ($proteges->isEmpty()) {
            return self::LIBRE;
        }

        $galerie = Media::whereIn('gallery_id', $proteges->modelKeys())->where('filename', $fichier)->value('gallery_id');

        return match (true) {
            $galerie === null => self::LIBRE,
            $this->ouvert($proteges->find($galerie)) => self::OUVERT,
            default => self::FERME,
        };
    }
}
