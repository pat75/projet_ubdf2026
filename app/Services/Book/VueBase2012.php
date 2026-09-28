<?php

namespace App\Services\Book;

/**
 * Donnees du modele portfolio 2012 (dossier base), pour les vues
 * Blade/Tailwind/Alpine de resources/views/book/base, sur la mise en page
 * de Responsive 2014 et le cadre de Pinter.
 *
 * Remplace ultrabook_2012_type, ultrabook_2012_menugauche,
 * ultrabook_2012_accueil, ultrabook_2012_portfolio et ultrabook_2012_news
 * de model_old/base (jQuery, Galleriffic, NailThumb).
 *
 * Accueil : une tuile par rubrique (accueil_ptf_vignette_aff), pages
 * d'accueil au-dessus (c) et au-dessous (b), et dans la colonne (d, a).
 * Portfolio : diaporama, vignettes (ptf_vignette_aff) et legendes (ptf_titre_aff).
 */
class VueBase2012 extends VuePinter2013
{
    public function filtreColonne(): bool
    {
        return false;
    }

    public function blocsColonne(): array
    {
        return ['d', 'a'];
    }

    /** @return array{0: string, 1: string} Positions [au-dessus, au-dessous] des tuiles. */
    public function blocsAccueil(): array
    {
        return ['c', 'b'];
    }

    public function presentation(): string
    {
        return 'slide';
    }

    public function navigation(): string
    {
        return $this->actif('ptf_vignette_aff') ? 'thumbs' : 'none';
    }

    public function navigationEnHaut(): bool
    {
        return false;
    }

    public function legendes(): bool
    {
        return $this->actif('ptf_titre_aff');
    }

    /**
     * Tuiles de l'accueil : la rubrique et son premier visuel.
     *
     * @return list<array{nom: string, url: string, visuel: array}>
     */
    public function tuilesAccueil(): array
    {
        if (! $this->actif('accueil_ptf_vignette_aff')) {
            return [];
        }

        $visuels = collect($this->visuels());
        $tuiles = [];

        foreach ($this->rubriquesPortfolio() as $rubrique) {
            // Premier vrai visuel : pas l'image par defaut d'une rubrique vide.
            $visuel = $visuels->first(fn (array $v) => $v['rub_id'] === $rubrique['id']
                && ! preg_match('/^(ultra-book_default_|visuel_default_)/', basename($v['moyen'])));
            if ($visuel) {
                $tuiles[] = ['nom' => $rubrique['nom'], 'url' => $rubrique['url'], 'visuel' => $visuel];
            }
        }

        return $tuiles;
    }

    public function __construct(ContexteBook $b)
    {
        parent::__construct($b);

        // /portfolio sans rubrique : la premiere, comme ultrabook_2012_portfolio.
        if ($b->page_type === 'portfolio' && ! $b->rub_id) {
            $b->rub_id = (int) ($b->menu['ptf'][0]['rub_id'] ?? 0);
        }
    }
}
