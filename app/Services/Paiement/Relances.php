<?php

namespace App\Services\Paiement;

use App\Mail\RelanceFormule;
use App\Models\SubscriptionReminder;
use App\Models\User;
use App\Support\Marque;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;

/**
 * Relances d'abonnement (abo_relance_liste_2026.php du legacy).
 *
 * Le legacy se lancait a la main depuis une URL d'administration, par
 * tranches de 500 comptes, sans garder trace des envois. Ici la commande
 * `ubdf:relancer-formules` tourne chaque jour et chaque relance est notee :
 * un compte ne peut pas la recevoir deux fois.
 */
class Relances
{
    /** Jours avant l'echeance ou l'on ecrit, comme dans le legacy. */
    public const JALONS = [5, 0];

    /** @return array<int, int> jalon => nombre de messages mis en file */
    public function envoyer(): array
    {
        $resultat = [];

        foreach (self::JALONS as $jours) {
            $resultat[$jours] = $this->envoyerPour($jours);
        }

        return $resultat;
    }

    private function envoyerPour(int $jours): int
    {
        $echeance = now()->addDays($jours)->toDateString();
        $envoyes = 0;

        $this->aRelancer($echeance)->each(function (User $creatif) use ($jours, $echeance, &$envoyes) {
            if (! $creatif->email) {
                return;
            }

            try {
                // La cle unique fait foi : deux executions le meme jour
                // n'envoient qu'un message.
                SubscriptionReminder::create([
                    'user_id' => $creatif->id,
                    'expires_on' => $echeance,
                    'days_before' => $jours,
                    'sent_at' => now(),
                ]);
            } catch (QueryException) {
                return;
            }

            Mail::to($creatif->email)->queue(
                new RelanceFormule($creatif, Marque::depuisCode($creatif->brand ?: 'ub'), $jours)
            );

            $envoyes++;
        });

        return $envoyes;
    }

    /** Comptes payants dont la formule expire a cette date. */
    private function aRelancer(string $echeance)
    {
        return User::query()
            ->where('plan', '>', 0)
            ->whereDate('plan_expires_at', $echeance)
            ->cursor();
    }
}
