<?php

namespace App\Console\Commands;

use App\Services\Legacy\LegacyCms;
use Illuminate\Console\Command;

class ImportCmsCommand extends Command
{
    protected $signature = 'ubdf:import-cms {--skip-medias : ne pas recopier les images}';

    protected $description = 'Importe les pages et actualites de l’ancien WordPress du magazine';

    public function handle(LegacyCms $cms): int
    {
        $this->info('Import du CMS depuis la base WordPress (lecture seule)…');

        $resultat = $cms->importer(avecMedias: ! $this->option('skip-medias'));

        $this->newLine();

        foreach ($resultat['comptes'] as $quoi => $combien) {
            $this->line(sprintf('  %-12s %d', $quoi, $combien));
        }

        foreach ($resultat['avertissements'] as $avertissement) {
            $this->warn('  '.$avertissement);
        }

        $this->newLine();
        $this->info('Import termine.');

        return self::SUCCESS;
    }
}
