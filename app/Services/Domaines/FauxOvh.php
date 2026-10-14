<?php

namespace App\Services\Domaines;

use App\Models\Domaine;
use Illuminate\Support\Str;

/**
 * OVH simule (config domaines.ovh_mode = simulation) : aucun appel reseau,
 * aucune cle. Reponses previsibles pour la recette et les tests :
 *  - un nom qui contient « pris » est indisponible ;
 *  - un nom qui contient « echec » passe la verification mais echoue a la
 *    commande (pour recetter le remboursement).
 */
class FauxOvh extends Ovh
{
    public function disponibilite(string $nom): array
    {
        $nom = Domaine::normaliser($nom);
        $extension = substr(strrchr($nom, '.'), 1);

        return [
            'disponible' => ! str_contains($nom, 'pris'),
            'prix' => number_format((float) Domaine::prix($extension), 2, '.', '').' €',
        ];
    }

    public function verifierCommande(string $nom): ?float
    {
        $nom = Domaine::normaliser($nom);

        return str_contains($nom, 'pris') ? null : Domaine::prix(substr(strrchr($nom, '.'), 1));
    }

    public function commander(Domaine $domaine, array $titulaire): string
    {
        if (str_contains($domaine->nom, 'echec')) {
            throw new \RuntimeException('OVH simule : commande refusee.');
        }

        $commande = 'simulation-'.Str::upper(Str::random(8));
        $domaine->update(['source' => 'ovh', 'ovh_commande_id' => $commande]);

        return $commande;
    }

    public function pointerVersVps(Domaine $domaine): void {}

    public function authCode(Domaine $domaine): string
    {
        return 'SIMU-'.Str::upper(Str::random(12));
    }

    public function resilier(Domaine $domaine): void {}
}
