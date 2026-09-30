<?php

namespace App\Services\Espace;

use App\Models\User;
use RuntimeException;

/**
 * Les deux compteurs de l'espace : nombre de visuels et poids total,
 * chacun rapporte au plafond de la formule du createur.
 *
 * Ils s'affichaient sur « Ma formule », ou ils faisaient double emploi
 * avec la grille des offres ; ils sont desormais sur le tableau de bord,
 * a cote des autres chiffres du compte.
 */
class Quotas
{
    /** Refuse un nouveau visuel au-dela du plafond de la formule. */
    public function verifierAjout(User $creatif): void
    {
        $images = $this->pour($creatif)['images'];

        if ($images['valeur'] >= $images['plafond']) {
            throw new RuntimeException(__('Vous avez atteint les :n visuels de votre formule.', ['n' => $images['plafond']]));
        }
    }

    /** @return array<string, array{valeur: int, plafond: int, unite: string}> */
    public function pour(User $creatif): array
    {
        $limites = config('formules.limites.'.($creatif->plan ? 'payante' : 'gratuite'));

        return [
            'images' => [
                'valeur' => (int) $creatif->media_count,
                'plafond' => (int) $limites['visuels'],
                'unite' => '',
            ],
            'poids' => [
                'valeur' => (int) $creatif->storage_used,
                'plafond' => (int) $limites['poids_ko'],
                'unite' => 'Ko',
            ],
        ];
    }
}
