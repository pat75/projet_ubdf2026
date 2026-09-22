<?php

namespace App\Services\Paiement;

use App\Models\MarketingOffer;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Promotions ponctuelles des formules (inc/inc_user_marketing.php).
 *
 * - Black Friday : le 12 mois a 22 € pour tous, pendant les periodes de
 *   `formules.black_friday`. Prime sur le reabonnement.
 * - Promo auto 6 mois : le 6 mois a 11,90 € pour un createur qui n'a
 *   jamais paye, 24 h durant, 2 mois apres l'inscription puis tous les
 *   3 mois. L'offre est creee a la consultation de la page formule, comme
 *   dans le legacy.
 */
class Promotions
{
    public function blackFriday(?Carbon $maintenant = null): bool
    {
        $maintenant ??= now('Europe/Paris');

        foreach (config('formules.black_friday') as $debut => $jours) {
            $debut = Carbon::parse($debut, 'Europe/Paris')->startOfDay();

            if ($maintenant->between($debut, $debut->copy()->addDays($jours))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Promo 6 mois active pour ce createur ; en cree l'offre si son tour
     * est venu.
     */
    public function promo6Mois(User $creatif): bool
    {
        if ($creatif->invoices()->where('status', 'paid')->exists()) {
            return false;
        }

        $regles = config('formules.promo_6_mois');
        $derniere = MarketingOffer::where('user_id', $creatif->id)
            ->where('type', MarketingOffer::PROMO_6_MOIS)->latest('offered_at')->first();

        if ($derniere && $derniere->offered_at->copy()->addHours($regles['validite_heures'])->isFuture()) {
            return true;
        }

        $depart = $derniere
            ? $derniere->offered_at->copy()->addMonths($regles['intervalle_mois'])
            : $creatif->created_at?->copy()->addMonths($regles['premiere_apres_mois']);

        if (! $depart || $depart->isFuture()) {
            return false;
        }

        MarketingOffer::create(['user_id' => $creatif->id, 'type' => MarketingOffer::PROMO_6_MOIS, 'offered_at' => now()]);

        return true;
    }
}
