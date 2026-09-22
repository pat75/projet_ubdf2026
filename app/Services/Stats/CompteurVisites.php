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
    public function compter(User $book, ?int $visiteurId, string $ip, ?string $navigateur): void
    {
        $proprietaire = $visiteurId === $book->id;
        $cle = 'visite:'.$book->id.':'.sha1($ip.'|'.$navigateur);

        if (! Cache::add($cle, 1, now()->addMinutes(30))) {
            return;
        }

        $colonne = $proprietaire ? 'admin_views' : 'public_views';

        DB::table('visit_stats')->upsert(
            [['user_id' => $book->id, 'date' => now()->toDateString(), $colonne => 1, 'created_at' => now(), 'updated_at' => now()]],
            ['user_id', 'date'],
            [$colonne => DB::raw($colonne.' + 1'), 'updated_at' => now()],
        );
    }
}
