<?php

namespace App\Services\Espace;

use App\Models\User;

/**
 * Vignette du createur : sa photo si deposee, sinon un medaillon
 * d'initiales a couleur stable (derivee de son identifiant).
 *
 * Reunit ici un calcul jusque-la duplique a l'identique dans
 * x-espace.vignette et barre/createur.
 */
class AffichageProfil
{
    public const PALETTE = [
        '#4285f4', '#ea4335', '#fbbc05', '#34a853', '#5e35b1', '#00897b',
        '#43a047', '#e53935', '#1e88e5', '#f4511e', '#3949ab', '#039be5',
    ];

    /**
     * Declinaison carree : la photo est recadree en carre dans l'espace,
     * puis affichee en rond. `front_desk` (250x136) la rognait une seconde
     * fois, et le rond ne ressemblait plus au recadrage choisi.
     */
    public function photoUrl(User $creatif): ?string
    {
        return $creatif->bookSetting?->thumbnail ? $creatif->thumbnailUrl('carre_368') : null;
    }

    public function initiales(User $creatif): string
    {
        $mots = preg_split('/\s+/', trim($creatif->fullName()), -1, PREG_SPLIT_NO_EMPTY) ?: [$creatif->login];

        return mb_strtoupper(count($mots) > 1
            ? mb_substr($mots[0], 0, 1).mb_substr(end($mots), 0, 1)
            : mb_substr($mots[0], 0, 2));
    }

    public function couleur(User $creatif): string
    {
        return self::PALETTE[crc32($creatif->login) % count(self::PALETTE)];
    }
    /**
     * Medaillon d'initiales en image, pour les contextes qui attendent une
     * URL et non du HTML — la colonne avatar du back-office, qui ne sait
     * afficher qu'une image quand la photo manque.
     *
     * Un SVG en data-URI plutot qu'un service de vignettes distant : rien a
     * telecharger, rien a mettre en cache, et la liste reste lisible meme
     * sur une page servie sans acces au stockage des books.
     */
    public function medaillon(string $initiales, string $couleur): string
    {
        $initiales = htmlspecialchars($initiales, ENT_QUOTES | ENT_XML1);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="'.$couleur.'"/>'
            .'<text x="32" y="33" fill="#fff" font-family="Helvetica,Arial,sans-serif"'
            .' font-size="26" font-weight="700" text-anchor="middle"'
            .' dominant-baseline="central">'.$initiales.'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /** Medaillon du creatif : ses initiales sur sa couleur. */
    public function medaillonCreatif(User $creatif): string
    {
        return $this->medaillon($this->initiales($creatif), $this->couleur($creatif));
    }
}
