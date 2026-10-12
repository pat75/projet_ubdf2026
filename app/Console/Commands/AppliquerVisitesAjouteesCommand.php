<?php

namespace App\Console\Commands;

use App\Services\Stats\VisitesAjoutees;
use Illuminate\Console\Command;

class AppliquerVisitesAjouteesCommand extends Command
{
    protected $signature = 'ubdf:visites:appliquer';

    protected $description = 'Verse dans visit_stats les visites ajoutees dont l\'heure est passee';

    public function handle(VisitesAjoutees $visites): int
    {
        $this->info($visites->appliquer().' visite(s) versee(s).');

        return self::SUCCESS;
    }
}
