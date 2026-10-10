<?php

namespace App\Services\Book;

use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

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
     * Etat d'un fichier du book pour ce visiteur.
     *
     * Appele pour chaque image servie : la liste des fichiers proteges du
     * book est gardee en cache, pour qu'une image libre ne touche pas
     * MySQL. Sans cela, un book charge d'un coup ouvrait autant de
     * connexions que d'images, et depassait `max_user_connections` en
     * prod. Le cache est vide par Gallery et Media a chaque ecriture.
     *
     * @return self::LIBRE|self::OUVERT|self::FERME
     */
    public function fichier(string $login, string $fichier): string
    {
        $id = self::fichiersProteges($login)[$fichier] ?? null;
        $galerie = $id ? Gallery::find($id) : null;

        return match (true) {
            $galerie === null => self::LIBRE,
            $this->ouvert($galerie) => self::OUVERT,
            default => self::FERME,
        };
    }

    /** @return array<string, int> nom de fichier => id du portfolio protege */
    private static function fichiersProteges(string $login): array
    {
        return Cache::remember(self::cleCache($login), 3600, fn () => Media::query()
            ->whereHas('user', fn ($q) => $q->where('login', $login))
            ->whereHas('gallery', fn ($q) => $q->whereNotNull('password'))
            ->pluck('gallery_id', 'filename')
            ->all());
    }

    public static function oublier(?int $userId): void
    {
        $login = $userId ? User::query()->whereKey($userId)->value('login') : null;

        if ($login) {
            Cache::forget(self::cleCache($login));
        }
    }

    private static function cleCache(string $login): string
    {
        return 'book.fichiers_proteges.'.$login;
    }
}
