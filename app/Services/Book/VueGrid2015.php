<?php

namespace App\Services\Book;

/**
 * Donnees du modele Grid 2015 (dossier grid2015), pour les vues
 * Blade/Tailwind/Alpine de resources/views/book/grid.
 *
 * Remplace ultrabook_2015_type, _ultrabook__header, ultrabook_menugauche,
 * ultrabook_accueil, ultrabook_portfolio, ultrabook_news et ultrabook_footer
 * de model_old/grid2015 (jQuery, djax, Fotorama, iScroll, mCustomScrollbar).
 *
 * En-tete a gauche (visuel du book, bouton MENU, texte libre
 * cont_menu_gauche), grille de tuiles : une grande par rubrique du
 * portfolio (carre_335), une petite par page (une seule rubrique de pages)
 * ou par rubrique de pages. Reglages de cont_conf2012 comme Responsive 2014.
 */
class VueGrid2015 extends VueResponsive2014
{
    /** Nombre maximal de tuiles de pages, comme front_nav_2015_accueil. */
    private const TUILES_PAGES = 50;

    /**
     * Visuel du haut de l'en-tete : le visuel de profil regle dans
     * l'espace (Habillage), sinon l'ancien visuel d'accueil du theme.
     */
    public function logo(): ?string
    {
        if ($profil = $this->b->book->thumbnailUrl('carre_368')) {
            return $profil;
        }

        $visuel = trim((string) $this->b->visuel_accueil);

        if ($visuel === '' || $visuel === 'deleted' || str_starts_with($visuel, 'ultra-book_default_')) {
            return null;
        }

        return (str_contains($visuel, 'http') ? '' : $this->b->rep_pref).$visuel;
    }

    /**
     * Tuiles de pages de l'accueil : les pages s'il n'y a qu'une rubrique,
     * sinon une tuile par rubrique.
     *
     * @return list<array{titre: string, url: string}>
     */
    public function tuilesPages(): array
    {
        $menu = $this->menuPages();

        if ($menu['seule']) {
            $tuiles = array_map(fn (array $p) => ['titre' => $p['titre'], 'url' => $p['url']], $menu['rubriques'][0]['pages']);
        } else {
            $tuiles = array_map(fn (array $r) => ['titre' => $r['nom'], 'url' => $r['url']], $menu['rubriques']);
        }

        return array_slice($tuiles, 0, self::TUILES_PAGES);
    }

    /** Nom de la rubrique affichee dans le diaporama. */
    public function nomRubrique(): string
    {
        return collect($this->rubriquesPortfolio())->firstWhere('id', (int) $this->b->rub_id)['nom'] ?? '';
    }

    public function __construct(ContexteBook $b)
    {
        parent::__construct($b);

        // /portfolio sans rubrique : la premiere, comme ultrabook_menugauche.
        if ($b->page_type === 'portfolio' && ! $b->rub_id) {
            $b->rub_id = (int) ($b->menu['ptf'][0]['rub_id'] ?? 0);
        }
    }
}
