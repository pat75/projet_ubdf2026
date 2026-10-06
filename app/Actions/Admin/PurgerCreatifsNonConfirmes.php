<?php

namespace App\Actions\Admin;

use App\Models\User;
use App\Support\DossierBook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Supprime definitivement les comptes jamais confirmes
 * (book_supp_nonvalide.php du legacy) : compte, donnees liees et
 * fichiers. Seuls ceux inscrits depuis plus de DELAI_MOIS mois sont
 * touches, quelle que soit la selection recue.
 */
class PurgerCreatifsNonConfirmes
{
    public const DELAI_MOIS = 2;

    public static function purgeables(Builder $query): Builder
    {
        return $query->whereNull('email_verified_at')
            ->where('created_at', '<', now()->subMonths(self::DELAI_MOIS));
    }

    /** @param  Collection<int, User>  $creatifs */
    public function __invoke(Collection $creatifs): int
    {
        $ids = $creatifs->pluck('id');
        $purges = 0;

        self::purgeables(User::withTrashed()->whereKey($ids))
            ->each(function (User $creatif) use (&$purges) {
                $dossier = DossierBook::chemin($creatif->login);

                $creatif->forceDelete();
                File::deleteDirectory($dossier);
                $purges++;
            });

        return $purges;
    }
}
