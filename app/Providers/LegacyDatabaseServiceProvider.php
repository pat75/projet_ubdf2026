<?php

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

/**
 * Garde-fou sur la connexion « legacy » (base ub2020 du projet 2019).
 *
 * La base d'origine ne doit jamais etre modifiee : toute requete autre
 * qu'une lecture leve une exception avant d'atteindre MySQL.
 */
class LegacyDatabaseServiceProvider extends ServiceProvider
{
    /** Verbes SQL autorises sur la connexion legacy. */
    private const READ_ONLY = ['select', 'show', 'describe', 'desc', 'explain', 'set'];

    public function boot(): void
    {
        DB::connection('legacy')->beforeExecuting(function (string $query): void {
            $verb = strtolower(strtok(ltrim($query, "( \t\n\r"), " \t\n\r("));

            if (! in_array($verb, self::READ_ONLY, true)) {
                throw new RuntimeException(
                    "Ecriture interdite sur la connexion legacy (ub2020) : [{$verb}]. ".
                    'Cette base est en lecture seule.'
                );
            }
        });
    }
}
