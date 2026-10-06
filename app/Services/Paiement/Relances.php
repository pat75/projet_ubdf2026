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

    /** Jalon des relances manuelles d'un abonnement deja echu. */
    public const JALON_ECHU = -1;

    /** Ecart minimal, en jours, entre deux relances d'un meme abonnement echu. */
    public const DELAI_RELANCE_ECHU = 7;

    /**
     * Relance manuelle d'un abonnement echu (back-office). Au plus une fois
     * tous les DELAI_RELANCE_ECHU jours : false si le compte a ete relance
     * trop recemment ou n'a pas d'adresse.
     */
    public function relancerEchu(User $creatif): bool
    {
        $echeance = $creatif->echeanceFormule();

        if (! $creatif->email || ! $echeance) {
            return false;
        }

        $recente = SubscriptionReminder::where('user_id', $creatif->id)
            ->where('days_before', self::JALON_ECHU)
            ->where('sent_at', '>', now()->subDays(self::DELAI_RELANCE_ECHU))
            ->exists();

        if ($recente) {
            return false;
        }

        try {
            SubscriptionReminder::create([
                'user_id' => $creatif->id,
                'expires_on' => $echeance->toDateString(),
                'days_before' => self::JALON_ECHU,
                'sent_at' => now(),
                'sent_on' => today(),
            ]);
        } catch (QueryException) {
            return false;
        }

        Mail::to($creatif->email)->queue(
            new RelanceFormule($creatif, Marque::depuisCode($creatif->brand ?: 'ub'), self::JALON_ECHU)
        );

        return true;
    }

    /**
     * Abonnements repris : comptes relances « echu » dont l'echeance
     * actuelle depasse celle qui avait ete relancee (ils ont renouvele).
     */
    public function reprises(): int
    {
        return User::query()
            ->whereExists(fn ($sous) => $sous->selectRaw('1')->from('subscription_reminders')
                ->whereColumn('subscription_reminders.user_id', 'users.id')
                ->where('subscription_reminders.days_before', self::JALON_ECHU)
                ->whereColumn('users.plan_expires_at', '>', 'subscription_reminders.expires_on'))
            ->where('plan_expires_at', '>', now())
            ->count();
    }

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
                    'sent_on' => today(),
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
