<?php

namespace App\Console\Commands;

use App\Models\DataExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Efface les archives « Mes donnees » arrivees a expiration : elles pesent
 * le poids d'un book entier, le disque ne doit pas les accumuler.
 */
class PurgerExportsCommand extends Command
{
    protected $signature = 'ubdf:purger-exports';

    protected $description = 'Efface les archives « Mes données » expirées';

    public function handle(): int
    {
        $n = 0;

        DataExport::query()->where('status', DataExport::PRET)->where('expire_at', '<', now())
            ->each(function (DataExport $export) use (&$n) {
                if ($export->fichier) {
                    Storage::disk(DataExport::DISQUE)->delete($export->fichier);
                }
                $export->update(['status' => DataExport::EXPIRE, 'fichier' => null]);
                $n++;
            });

        $this->info("{$n} archive(s) effacée(s).");

        return self::SUCCESS;
    }
}
