<?php

namespace App\Services\Book;

/**
 * Donnees du modele classique 2010 (dossier classique2010), pour les vues
 * Blade/Tailwind/Alpine de resources/views/book/classique2010, sur la mise
 * en page de Responsive 2014 — comme Classique 2015, dont il est l'aine.
 *
 * Remplace ultrabook_type, ultrabook_accueil, ultrabook_portfolio,
 * ultrabook_news et ultrabook_contact de model_old/_racine (jQuery 1.4,
 * Galleriffic, Facebox, vTip, pngFix, AddThis, socket.io), ainsi que ses
 * versions iPhone et iPad : la mise en page responsive les remplace.
 *
 * Reglages du legacy, anterieurs a cont_conf2012 :
 * us_pf_img1…5 (bandeau de cinq visuels, 290 + 3 x 166 + 62 x 110 px :
 * accueil, accueil, portfolio, actualites, et une case de fin sans lien),
 * fond (background_mode : 1 image, 2 couleur).
 * Accueil : toutes les pages de la premiere rubrique d'accueil, l'une sous
 * l'autre. Portfolio : diaporama et vignettes de la rubrique.
 */
class VueClassique2010 extends VueResponsive2014
{
    public function __construct(ContexteBook $b)
    {
        parent::__construct($b);

        // /portfolio sans rubrique : la premiere, comme le legacy.
        if ($b->page_type === 'portfolio' && ! $b->rub_id) {
            $b->rub_id = (int) ($b->menu['ptf'][0]['rub_id'] ?? 0);
        }
    }

    /**
     * Bandeau du haut : cinq visuels, les quatre premiers servant de menu.
     *
     * @return list<array{url: ?string, src: ?string, libelle: string, largeur: int}>
     */
    public function bandeau(): array
    {
        $cases = [
            ['/', __('Accueil'), 290],
            ['/', __('Accueil'), 166],
            ['/portfolio', __('Portfolio'), 166],
            ['/actualites', __('Actualités'), 166],
            [null, $this->nomCreateur(), 62],
        ];

        return array_map(function (array $c, int $i) {
            $fichier = trim((string) ($this->b->book->bookSetting?->legacy_payload['us_pf_img'.($i + 1)] ?? ''));

            return [
                'url' => $c[0],
                'src' => $fichier === '' || $fichier === 'deleted' ? null
                    : (str_contains($fichier, 'http') ? $fichier : $this->b->rep_pref.$fichier),
                'libelle' => $c[1],
                'largeur' => $c[2],
            ];
        }, $cases, array_keys($cases));
    }

    /** L'accueil occupe toute la largeur, sous le bandeau. */
    public function colonneSurAccueil(): bool
    {
        return false;
    }

    /** Pas de visuel en haut de la colonne : le bandeau en tient lieu. */
    public function logo(): ?string
    {
        return null;
    }

    /**
     * Pages de la premiere rubrique d'accueil, dans l'ordre, HTML pret a
     * afficher (ex-ultrabook_accueil).
     *
     * @return list<string>
     */
    public function pagesAccueil(): array
    {
        return array_values(array_filter(array_map(
            fn (array $page) => book_actu_txt($page['img_html'] ?? ''),
            $this->b->gal_cont_accueil ?? [],
        ), fn (string $html) => trim(strip_tags($html, '<img><iframe><video>')) !== ''));
    }

    /** Fond du legacy : image (mode 1) ou couleur (mode 2). */
    public function imageDeFond(): ?string
    {
        return $this->b->cont_bg_choix === '1' && $this->b->cont_bg ? $this->b->cont_bg : null;
    }

    public function couleurFond(): string
    {
        if (! $this->fond() && $this->b->cont_bg_choix === '2' && $this->b->cont_bgcoul) {
            return self::couleurCss($this->b->cont_bgcoul, '#ffffff');
        }

        return parent::couleurFond();
    }

    /** Diaporama de la rubrique, ses vignettes dessous et les legendes, comme Galleriffic. */
    public function presentation(): string
    {
        return 'slide';
    }

    public function navigation(): string
    {
        return 'thumbs';
    }

    public function navigationEnHaut(): bool
    {
        return false;
    }

    public function legendes(): bool
    {
        return true;
    }
}
