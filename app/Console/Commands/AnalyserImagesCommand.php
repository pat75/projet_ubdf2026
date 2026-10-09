<?php

namespace App\Console\Commands;

use App\Actions\Recherche\LancerAnalyseLot;
use Illuminate\Console\Command;

/**
 * Analyse IA en tache de fond : un lot par minute (routes/console.php).
 * Lot de 10 a ~5 s par image : sous la limite NVIDIA de 40 requetes/minute.
 */
class AnalyserImagesCommand extends Command
{
    protected $signature = 'ubdf:analyser-images {--lot='.LancerAnalyseLot::TAILLE.'}';

    protected $description = 'Analyse IA du prochain lot de visuels en attente';

    public function handle(LancerAnalyseLot $lancer): int
    {
        ['ok' => $ok, 'erreurs' => $erreurs] = $lancer((int) $this->option('lot'));
        $this->info("{$ok} analysee(s), {$erreurs} erreur(s)");

        return self::SUCCESS;
    }
}
