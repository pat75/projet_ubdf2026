<?php

namespace App\Services\Stats;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Compte les vues d'un book, agregees par jour dans visit_stats.
 *
 * Remplace le pixel du serveur de statistiques du legacy
 * (www.extra-book.com/2012_stats). Comme lui, le comptage passe par une
 * image : les robots qui ne chargent pas les images ne sont pas comptes.
 * Un meme visiteur (IP + navigateur) n'est compte qu'une fois par
 * demi-heure et par book.
 */
class CompteurVisites
{
    /**
     * Les trois surfaces ou un book s'affiche. Le tableau de bord en
     * donne la repartition ; une valeur inconnue retombe sur `book`.
     */
    public const SURFACES = ['book', 'minibook', 'memobook'];

    public function compter(User $book, ?int $visiteurId, string $ip, ?string $navigateur, string $surface = 'book'): void
    {
        $surface = in_array($surface, self::SURFACES, true) ? $surface : 'book';

        $proprietaire = $visiteurId === $book->id;
        $cle = 'visite:'.$book->id.':'.$surface.':'.sha1($ip.'|'.$navigateur);

        if (! Cache::add($cle, 1, now()->addMinutes(30))) {
            return;
        }

        $colonne = $proprietaire ? 'admin_views' : 'public_views';

        DB::table('visit_stats')->upsert(
            [['user_id' => $book->id, 'date' => now()->toDateString(), 'surface' => $surface,
                $colonne => 1, 'created_at' => now(), 'updated_at' => now()]],
            ['user_id', 'date', 'surface'],
            [$colonne => DB::raw($colonne.' + 1'), 'updated_at' => now()],
        );
    }
}
