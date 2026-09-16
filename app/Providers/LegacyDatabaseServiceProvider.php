<?php

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

/**
 * Garde-fou sur les connexions vers les bases du projet 2019.
 *
 * Les bases d'origine ne doivent jamais etre modifiees : toute requete
 * autre qu'une lecture leve une exception avant d'atteindre MySQL.
 */
class LegacyDatabaseServiceProvider extends ServiceProvider
{
    /** Verbes SQL autorises sur les connexions d'origine. */
    private const READ_ONLY = ['select', 'show', 'describe', 'desc', 'explain', 'set'];

    /** Connexion => base protegee, pour le message d'erreur. */
    private const PROTEGEES = [
        'legacy' => 'ub2020',
        'legacy_wp' => 'WordPress du magazine',
    ];

    public function boot(): void
    {
        foreach (self::PROTEGEES as $connexion => $base) {
            DB::connection($connexion)->beforeExecuting(
                function (string $query) use ($connexion, $base): void {
                    $verb = strtolower(strtok(ltrim($query, "( \t\n\r"), " \t\n\r("));

                    if (! in_array($verb, self::READ_ONLY, true)) {
                        throw new RuntimeException(
                            "Ecriture interdite sur la connexion {$connexion} ({$base}) : [{$verb}]. ".
                            'Cette base est en lecture seule.'
                        );
                    }
                }
            );
        }
    }
}
