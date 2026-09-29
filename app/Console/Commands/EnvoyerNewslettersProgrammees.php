<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\Messagerie\EnvoiCampagne;
use Illuminate\Console\Command;

/**
 * Newsletters dont l'heure d'envoi est passee et qui ne sont pas parties.
 * Lancee chaque minute : l'ecart entre l'heure choisie et le depart reste
 * sous la minute.
 */
class EnvoyerNewslettersProgrammees extends Command
{
    protected $signature = 'ubdf:envoyer-newsletters';

    protected $description = 'Envoie les newsletters programmées dont l’heure est passée';

    public function handle(EnvoiCampagne $envoi): int
    {
        $campagnes = Campaign::query()
            ->where('type', 'newsletter')
            ->whereNull('sent_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($campagnes as $campagne) {
            $nombre = $envoi->envoyer($campagne);

            $this->info($campagne->name.' : '.$nombre.' message(s) mis en file.');
        }

        return self::SUCCESS;
    }
}
