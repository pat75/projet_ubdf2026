<?php

namespace App\Console\Commands;

use App\Mail\RelanceDomaine;
use App\Models\Domaine;
use App\Support\Marque;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Relance le createur avant l'echeance de son nom de domaine : J-30, J-15,
 * J-5 (Domaine::RELANCES). Un domaine resilie ou en transfert n'est pas
 * relance. Chaque palier n'est envoye qu'une fois : un domaine decouvert
 * tard (a J-10) recoit une seule relance, pas les deux en retard.
 */
class RelancerDomainesCommand extends Command
{
    protected $signature = 'ubdf:relancer-domaines';

    protected $description = 'Relance les createurs avant l echeance de leur nom de domaine';

    public function handle(): int
    {
        $envoyees = 0;

        Domaine::with('user')
            ->whereIn('statut', ['commande', 'en_attente', 'dns_ok', 'actif'])
            ->whereNotNull('expire_le')
            ->whereDate('expire_le', '>=', today())
            ->whereDate('expire_le', '<=', today()->addDays(max(Domaine::RELANCES)))
            ->each(function (Domaine $domaine) use (&$envoyees) {
                $jours = (int) today()->diffInDays($domaine->expire_le, false);
                // Paliers atteints (J-30 puis J-15 puis J-5) : 1, 2 ou 3.
                $palier = count(array_filter(Domaine::RELANCES, fn (int $j) => $jours <= $j));

                if ($palier <= $domaine->relances_envoyees || ! $domaine->user) {
                    return;
                }

                $destinataire = $domaine->titulaire['email'] ?? $domaine->user->email;
                $marque = Marque::depuisCode((string) ($domaine->user->brand ?: 'ub'));
                Mail::to($destinataire)->locale($marque->locale())->send(new RelanceDomaine($domaine, $marque, $jours));

                $domaine->update(['relances_envoyees' => $palier, 'derniere_relance_le' => now()]);
                $envoyees++;
            });

        $this->info("{$envoyees} relance(s) envoyee(s).");

        return self::SUCCESS;
    }
}
