<?php

namespace App\Console\Commands;

use App\Services\Paiement\Relances;
use Illuminate\Console\Command;

class RelancerFormulesCommand extends Command
{
    protected $signature = 'ubdf:relancer-formules';

    protected $description = 'Prévient les créatifs dont la formule arrive à échéance (J-5 puis le jour même)';

    public function handle(Relances $relances): int
    {
        foreach ($relances->envoyer() as $jours => $nombre) {
            $this->components->info($jours > 0
                ? "J-{$jours} : {$nombre} relance(s) en file"
                : "Jour de l’échéance : {$nombre} relance(s) en file");
        }

        return self::SUCCESS;
    }
}
