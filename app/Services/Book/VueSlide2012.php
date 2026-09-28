<?php

namespace App\Services\Book;

/**
 * Donnees du modele portfolio 2012-slide (dossier slide) : celui de 2012
 * (VueBase2012), le menu en haut (ex-ultrabook_2012_menu_top, mega-menu
 * jQuery) au lieu de la colonne, et le diaporama de Fotorama : vignettes
 * (ptf_type_vign) en haut ou en bas (ptf_position_vign).
 */
class VueSlide2012 extends VueBase2012
{
    public function menuHorizontal(): bool
    {
        return true;
    }

    /** Pas de colonne : les pages d'accueil a et b vont au-dessus et au-dessous des tuiles. */
    public function blocsColonne(): array
    {
        return ['', ''];
    }

    /** @return array{0: string, 1: string} Positions [au-dessus, au-dessous] des tuiles. */
    public function blocsAccueil(): array
    {
        return ['a', 'b'];
    }

    public function navigation(): string
    {
        $valeur = (string) ($this->pref->ptf_type_vign->ptf_type_vign ?? 'thumbs');

        return in_array($valeur, ['thumbs', 'dots'], true) ? $valeur : 'none';
    }

    public function navigationEnHaut(): bool
    {
        return ($this->pref->ptf_position_vign->ptf_position_vign ?? 'top') === 'top';
    }
}
