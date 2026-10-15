<?php

namespace App\Services\Domaines;

use App\Models\Domaine;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Domaine deja possede par le createur, chez un autre registrar (source
 * externe). Il le fait pointer vers le VPS et prouve qu'il en a la main
 * par un enregistrement TXT : sans cette preuve, n'importe qui pourrait
 * declarer le domaine d'un autre et l'attacher a son book.
 */
class BrancherDomaine
{
    public function __construct(private readonly Dns $dns) {}

    /** @throws RuntimeException message affichable au createur */
    public function declarer(User $creatif, string $saisie): Domaine
    {
        $nom = Domaine::normaliser(preg_replace('#^https?://|/.*$#i', '', trim($saisie)));

        if (! Domaine::nomValide($nom)) {
            throw new RuntimeException(__('Ce nom de domaine n’est pas valide (exemple : jeanne-dupont.fr).'));
        }

        if ($this->estANous($nom)) {
            throw new RuntimeException(__('Ce domaine appartient à la plateforme.'));
        }

        if (Domaine::where('user_id', $creatif->id)->whereIn('statut', Domaine::EN_COURS)->exists()) {
            throw new RuntimeException(__('Vous avez déjà un nom de domaine.'));
        }

        $existant = Domaine::where('nom', $nom)->first();

        // Une declaration jamais prouvee ne bloque pas le vrai proprietaire.
        if ($existant && ! ($existant->source === 'externe' && $existant->statut === 'a_verifier')) {
            throw new RuntimeException(__('Ce domaine est déjà utilisé sur la plateforme.'));
        }
        $existant?->delete();

        return Domaine::create([
            'user_id' => $creatif->id,
            'nom' => $nom,
            'extension' => substr(strrchr($nom, '.'), 1),
            'source' => 'externe',
            'statut' => 'a_verifier',
            'jeton' => Str::lower(Str::random(32)),
        ]);
    }

    /**
     * TXT publie ? Le domaine passe alors en attente (puis actif des que son
     * DNS vise le VPS, voir VerifierDomainesCommand).
     */
    public function verifier(Domaine $domaine): bool
    {
        if ($domaine->statut !== 'a_verifier') {
            return true;
        }

        $txt = $domaine->enregistrementTxt();
        $prouve = in_array($txt['valeur'], array_map('trim', $this->dns->txt($txt['nom'])), true);

        $domaine->update(['verifie_le' => now()] + ($prouve ? ['statut' => 'en_attente'] : []));

        return $prouve;
    }

    /** Nos propres domaines (portails, books) ne se declarent pas. */
    private function estANous(string $nom): bool
    {
        foreach (config('marques.marques') as $marque) {
            foreach ([...$marque['hotes'], $marque['domaine_books'] ?? null] as $domaine) {
                $domaine = strtolower((string) $domaine);

                if ($domaine !== '' && ($nom === $domaine || str_ends_with($nom, '.'.$domaine))) {
                    return true;
                }
            }
        }

        return false;
    }
}
