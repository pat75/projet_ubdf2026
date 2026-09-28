<?php

namespace App\Services\Book;

/**
 * Donnees du modele Classique 2015 (dossier classique2015), pour les vues
 * Blade/Tailwind/Alpine de resources/views/book/classique2015.
 *
 * Remplace ultrabook_2015_type, ultrabook_menutop, ultrabook_menugauche,
 * ultrabook_accueil, ultrabook_portfolio et ultrabook_news de
 * model_old/classique2015 (jQuery, Fotorama, meanMenu).
 *
 * Memes reglages que Responsive 2014 (cont_conf2012), plus :
 * us_pf_clas2015_visuel_top1…3 (bandeau de trois visuels servant de menu,
 * 290, 620 et 270 x 110 px) et us_pf_clas2015_visuel_accueil (grand visuel
 * de l'accueil, 1180 x 600) ; « deleted » : visuel retire, vide : image
 * par defaut du modele. Texte libre cont_acceuil_bas sous le visuel d'accueil.
 */
class VueClassique2015 extends VueResponsive2014
{
    private const DEFAUTS = '/2012_web/classique2015/img/';

    public function __construct(ContexteBook $b)
    {
        parent::__construct($b);

        // /portfolio sans rubrique : la premiere, comme ultrabook_menugauche.
        if ($b->page_type === 'portfolio' && ! $b->rub_id) {
            $b->rub_id = (int) ($b->menu['ptf'][0]['rub_id'] ?? 0);
        }
    }

    /**
     * Bandeau du haut : trois visuels cliquables (Accueil, Portfolio, Bio).
     *
     * @return list<array{url: string, src: ?string, libelle: string, largeur: int}>
     */
    public function bandeau(): array
    {
        $cases = [
            ['/', $this->b->cont_visuel2015_1, 'top_1.gif', __('Accueil'), 290],
            ['/portfolio', $this->b->cont_visuel2015_2, 'top_2.gif', $this->libelle('ub_menu_titre_ptf', __('Portfolio')), 620],
            ['/actualites', $this->b->cont_visuel2015_3, 'top_3.gif', $this->libelle('ub_menu_titre_actu', __('Bio')), 270],
        ];

        return array_map(fn (array $c) => [
            'url' => $c[0],
            'src' => $this->visuel($c[1], $c[2]),
            'libelle' => $c[3],
            'largeur' => $c[4],
        ], $cases);
    }

    /** L'accueil occupe toute la largeur, comme le legacy. */
    public function colonneSurAccueil(): bool
    {
        return false;
    }

    /** Texte libre sous le visuel d'accueil. */
    public function texteAccueil(): string
    {
        return $this->texteLibre('cont_acceuil_bas');
    }

    /** Grand visuel de l'accueil (1180 x 600), ou null s'il a ete retire. */
    public function visuelAccueil(): ?string
    {
        return $this->visuel($this->b->cont_visuel2015_accueil, 'ub_default_1180x600.gif');
    }

    /** Pas de visuel en haut de la colonne : le bandeau en tient lieu. */
    public function logo(): ?string
    {
        return null;
    }

    /**
     * Vignettes de la rubrique affichee, sous son nom dans la colonne
     * (ex-classique2015__ub_aff_thumbs) : elles pilotent le diaporama.
     *
     * @return list<array{index: int, src: string, titre: string}>
     */
    public function vignettesColonne(): array
    {
        if ($this->b->page_type !== 'portfolio') {
            return [];
        }

        return array_map(fn (array $d, int $i) => [
            'index' => $i,
            'src' => $this->carre(basename($d['moyen']), 183),
            'titre' => $d['titre'] ?: $d['nom_rubrique'],
        ], $diapos = $this->diapositives(), array_keys($diapos));
    }

    /** La navigation sous le diaporama : les vignettes sont deja dans la colonne. */
    public function navigation(): string
    {
        $valeur = (string) ($this->pref->ptf_type_vign->ptf_type_vign ?? 'none');

        return in_array($valeur, ['thumbs', 'dots'], true) ? $valeur : 'none';
    }

    private function libelle(string $cle, string $defaut): string
    {
        $titre = $this->titreMenu($cle, $defaut);

        return $titre['texte'] ?? $defaut;
    }

    private function visuel(?string $valeur, string $defaut): ?string
    {
        $valeur = trim((string) $valeur);

        return match (true) {
            $valeur === 'deleted' => null,
            $valeur === '' => self::DEFAUTS.$defaut,
            str_contains($valeur, 'http') => $valeur,
            default => $this->b->rep_pref.$valeur,
        };
    }
}
