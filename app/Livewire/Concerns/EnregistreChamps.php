<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Validator;

/**
 * Enregistrement d'un champ seul, a la sortie du champ : le pendant serveur
 * de x-espace.champ-auto (Alpine `champAuto`, resources/js/espace.js).
 *
 * Seuls les champs declares par champsAutoEnregistres() sont acceptes :
 * le nom arrive du navigateur, il ne doit pas permettre d'ecrire une autre
 * propriete du composant.
 */
trait EnregistreChamps
{
    /** @return array<string, string|array> nom du champ => regles de validation */
    abstract protected function champsAutoEnregistres(): array;

    /** Enregistre la valeur validee, et tient a jour la propriete du composant. */
    abstract protected function persisterChamp(string $nom, mixed $valeur): void;

    /** @return array{ok: true}|array{erreur: string} */
    public function enregistrerChamp(string $nom, mixed $valeur): array
    {
        $regles = $this->champsAutoEnregistres();

        abort_unless(array_key_exists($nom, $regles), 422);

        $validateur = Validator::make([$nom => $valeur], [$nom => $regles[$nom]]);

        if ($validateur->fails()) {
            return ['erreur' => $validateur->errors()->first($nom)];
        }

        $this->persisterChamp($nom, $valeur);

        return ['ok' => true];
    }
}
