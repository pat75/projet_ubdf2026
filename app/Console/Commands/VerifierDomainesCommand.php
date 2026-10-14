<?php

namespace App\Console\Commands;

use App\Models\Domaine;
use App\Services\Domaines\Dns;
use App\Services\Domaines\Ovh;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Fait avancer les domaines en attente :
 *  - un domaine achete chez OVH est pointe vers le VPS des qu'OVH l'a livre ;
 *  - un domaine dont le DNS pointe vers le VPS devient actif ;
 *  - un domaine actif dont le DNS ne pointe plus vers le VPS repasse en attente
 *    (le creatif a change son DNS : Caddy n'aurait plus de certificat valable).
 */
class VerifierDomainesCommand extends Command
{
    protected $signature = 'ubdf:verifier-domaines';

    protected $description = 'Verifie le DNS des domaines personnels et les active';

    public function handle(Dns $dns, Ovh $ovh): int
    {
        $ip = config('services.ovh.ip_vps');

        if (! $ip) {
            $this->error('VPS_IP non configure.');

            return self::FAILURE;
        }

        Domaine::whereIn('statut', ['en_attente', 'dns_ok', 'actif'])->each(function (Domaine $domaine) use ($dns, $ovh, $ip) {
            $pointe = in_array($ip, $dns->ipv4($domaine->nom), true);

            if (! $pointe && $domaine->source === 'ovh' && $domaine->statut === 'en_attente') {
                try {
                    $ovh->pointerVersVps($domaine);
                } catch (RuntimeException $e) {
                    // Pas encore livre par OVH : on reessaiera demain.
                    $this->line("{$domaine->nom} : {$e->getMessage()}");
                }
            }

            $domaine->update([
                'statut' => $pointe ? 'actif' : 'en_attente',
                'verifie_le' => now(),
            ]);
        });

        return self::SUCCESS;
    }
}
