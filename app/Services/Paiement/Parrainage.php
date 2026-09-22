<?php

namespace App\Services\Paiement;

use App\Models\PromoCode;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Parrainage et codes promo (inc/inc_user_parrainage.php du legacy).
 *
 * Code de parrainage : deux premieres lettres du login, un tiret,
 * l'identifiant legacy (« AN-46969 ») — le format imprime et partage
 * depuis 2012. Les comptes crees depuis ont un identifiant prefixe de N.
 * Parrain et filleul doivent avoir une formule payante ; chacun gagne
 * 1 mois (formule de 6 mois ou moins) ou 3 mois (au-dela).
 */
class Parrainage
{
    public function code(User $creatif): string
    {
        return Str::upper(Str::substr($creatif->login, 0, 2)).'-'.($creatif->legacy_id ?? 'N'.$creatif->id);
    }

    /** @return string message a afficher */
    public function utiliserCode(User $filleul, string $saisie): string
    {
        $saisie = Str::upper(trim($saisie));

        if (preg_match('/^[A-Z0-9_-]{2}-(N?)(\d+)$/', $saisie, $m)) {
            return $this->parrainer($filleul, $saisie, $m[1] === 'N', (int) $m[2]);
        }

        return $this->codePromo($filleul, $saisie);
    }

    private function parrainer(User $filleul, string $saisie, bool $nouveau, int $id): string
    {
        $parrain = $nouveau ? User::find($id) : User::where('legacy_id', $id)->first();

        if (! $parrain || $this->code($parrain) !== $saisie) {
            return __('Code non valide.');
        }

        if ($parrain->is($filleul)) {
            return __('Vous ne pouvez pas utiliser votre propre code.');
        }

        if (! $filleul->plan || ! $parrain->plan) {
            return __('Le parrainage est réservé aux formules payantes, pour le parrain comme pour le filleul.');
        }

        if (Referral::where('referred_id', $filleul->id)->exists()) {
            return __('Vous avez déjà utilisé un code de parrainage.');
        }

        $bonus = fn (User $u) => $u->plan_months > 6 ? 3 : 1;
        $moisFilleul = $bonus($filleul);

        DB::transaction(function () use ($parrain, $filleul, $moisFilleul, $bonus) {
            Referral::create(['sponsor_id' => $parrain->id, 'referred_id' => $filleul->id, 'status' => 'confirmed', 'confirmed_at' => now()]);
            $parrain->prolongerFormule($bonus($parrain));
            $filleul->prolongerFormule($moisFilleul);
        });

        return __('Code de parrainage de :login accepté : :n mois offerts sur votre formule.', ['login' => $parrain->login, 'n' => $moisFilleul]);
    }

    private function codePromo(User $creatif, string $saisie): string
    {
        return DB::transaction(function () use ($creatif, $saisie) {
            $promo = PromoCode::where('code', $saisie)->where('discount_type', PromoCode::MOIS)
                ->where('is_active', true)->lockForUpdate()->first();

            if (! $promo || ($promo->max_uses !== null && $promo->uses >= $promo->max_uses)
                || $promo->ends_at?->isPast() || $promo->starts_at?->isFuture()) {
                return __('Code non valide.');
            }

            $promo->increment('uses');
            $creatif->prolongerFormule((int) $promo->discount);

            return __('Félicitations, :n mois de formule ont été crédités.', ['n' => (int) $promo->discount]);
        });
    }
}
