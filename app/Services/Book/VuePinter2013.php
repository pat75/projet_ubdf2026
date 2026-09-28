<?php

namespace App\Services\Book;

/**
 * Donnees du modele Pinter 2013 (dossier pinter), pour les vues
 * Blade/Tailwind/Alpine de resources/views/book/pinter, sur la mise en
 * page de Responsive 2014.
 *
 * Remplace ultrabook_2012_type, ultrabook_2012_menugauche,
 * ultrabook_2012_portfolio et ultrabook_2012_news de model_old/pinter
 * (jQuery, Isotope, Fancybox).
 *
 * Page encadree (.ub_ptf_couleur_fond_page) sur le fond (.ub_couleur_fond),
 * bandeau us_pf_visuel2012 (980 x 200), colonne de menu dont les rubriques
 * filtrent la mosaique de tout le portfolio, pages d'accueil en haut (a) et
 * en bas (b) de la colonne.
 */
class VuePinter2013 extends VueResponsive2014
{
    /** @return list<array{url: string, src: ?string, libelle: string, largeur: int, hauteur: int}> */
    public function bandeau(): array
    {
        $visuel = trim((string) $this->b->cont_visuel2012);

        if ($visuel === '' || $visuel === 'deleted' || str_starts_with($visuel, 'ultra-book_default_')) {
            return [];
        }

        return [[
            'url' => '/',
            'src' => (str_contains($visuel, 'http') ? '' : $this->b->rep_pref).$visuel,
            'libelle' => $this->nomCreateur(),
            'largeur' => 980,
            'hauteur' => 200,
        ]];
    }

    public function logo(): ?string
    {
        return null;
    }

    public function filtreColonne(): bool
    {
        return true;
    }

    public function blocsColonne(): array
    {
        return ['a', 'b'];
    }

    public function couleurCadre(): ?string
    {
        if ($this->fond()) {
            return null;
        }

        return self::couleurCss($this->pref->{'.ub_ptf_couleur_fond_page'}->ub_ptf_couleur_fond_page ?? null, '#ffffff');
    }

    /** Couleurs de texte deduites de la page (cadre), pas du fond. */
    public function variables(): string
    {
        $page = $this->couleurCadre() ?? $this->couleurFond();
        $sombre = self::sombre($page);

        return implode(';', [
            '--book-fond:'.$this->couleurFond(),
            '--book-texte:'.($sombre ? '#ffffff' : '#000000'),
            '--book-texte2:'.($sombre ? '#e5e5e5' : '#222222'),
            '--book-texte3:'.($sombre ? '#a3a3a3' : '#6b6b6b'),
            '--book-filet:'.($sombre ? '#6b6b6b' : '#d4d4d4'),
        ]);
    }
}
