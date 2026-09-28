<?php

namespace App\Services\Book;

/**
 * Donnees du modele Responsive 2014 (dossier responsive), pour les vues
 * Blade/Tailwind/Alpine de resources/views/book/responsive.
 *
 * Remplace ultrabook_2014_type, ultrabook_2012_menugauche,
 * ultrabook_2012_accueil, ultrabook_2012_portfolio et ultrabook_2012_news
 * de model_old/responsive, ainsi que ub_book_core_mdl2012.js (reglages),
 * Fotorama et meanMenu.
 *
 * Reglages lus dans cont_conf2012 (us_pf_conf2014_responsive) :
 * ub_menu_titre_accueil / _ptf / _actu (intitules ; « [accueil-noir] »
 * ou « [accueil-blanc] » : pictogramme maison ; vide : masque),
 * classes .ub_font_* et .ub_couleur_fond (typographie, voir cssReglages),
 * ptf_type_presentation (slide | full | image), ptf_titre_aff (legendes),
 * ptf_type_vign (thumbs | dots | none), ptf_position_vign (top | bottom),
 * ptf_choix_fond (coul | img), accueil_contenu_aff_a…d (pages d'accueil
 * placees dans la colonne ou au-dessus / au-dessous des vignettes).
 * Textes libres : cont_menu_gauche et cont_menu_gauche2 (colonne gauche).
 */
class VueResponsive2014 extends VueBook
{
    protected const LIMITE_PAR_RUBRIQUE = true;

    /** Fonds proposes dans le panneau (reglage « theme », comme Ultra-frais). */
    public const FONDS = ['theme_white' => '#ffffff', 'theme_gris' => '#f0eded', 'theme_black' => '#363535'];

    /** Fond choisi dans le panneau, ou null : couleur du legacy (.ub_couleur_fond). */
    public function fond(): ?string
    {
        $theme = (string) ($this->pref->theme ?? '');

        return isset(self::FONDS[$theme]) ? $theme : null;
    }

    public function couleurFond(): string
    {
        return $this->fond() ? self::FONDS[$this->fond()] : self::couleurCss($this->pref->{'.ub_couleur_fond'}->backgroundColor ?? null, '#ffffff');
    }

    /** Valeur brute d'un intitule du menu, pour le panneau. */
    public function intitule(string $cle, string $defaut): string
    {
        return self::brut($this->pref->{$cle}->form_text ?? $defaut);
    }

    public function texte(string $cle): string
    {
        return self::deslasher((string) ($this->pref->{$cle} ?? ''));
    }

    /** @return array<string, string> reseau => URL des profils du createur. */
    public function reseaux(): array
    {
        $liens = [];

        foreach (VueUltra2020::RESEAUX as $reseau) {
            $url = trim((string) ($this->pref->social_link->{'link_'.$reseau} ?? ''));
            if ($url !== '') {
                $liens[$reseau] = preg_match('~^(?:f|ht)tps?://~i', $url) ? $url : 'https://'.$url;
            }
        }

        return $liens;
    }

    /** Image de fond du book, si le createur l'a choisie (ptf_choix_fond = img). */
    public function imageDeFond(): ?string
    {
        return ($this->pref->ptf_choix_fond->ptf_choix_fond ?? 'coul') === 'img' && $this->b->cont_bg ? $this->b->cont_bg : null;
    }

    public function variables(): string
    {
        $sombre = self::sombre($this->couleurFond());

        return implode(';', [
            '--book-fond:'.$this->couleurFond(),
            '--book-texte:'.($sombre ? '#ffffff' : '#000000'),
            '--book-texte2:'.($sombre ? '#e5e5e5' : '#222222'),
            '--book-texte3:'.($sombre ? '#a3a3a3' : '#6b6b6b'),
            '--book-filet:'.($sombre ? '#6b6b6b' : '#d4d4d4'),
        ]);
    }

    /**
     * Intitule d'une entree du menu : ['type' => 'maison', 'clair' => bool],
     * ['type' => 'texte', 'texte' => …] ou null (masquee).
     */
    public function titreMenu(string $cle, string $defaut): ?array
    {
        $valeur = $this->pref->{$cle}->form_text ?? $defaut;
        $valeur = trim(self::brut($valeur));

        return match ($valeur) {
            '' => null,
            '[accueil-noir]' => ['type' => 'maison', 'clair' => false],
            '[accueil-blanc]' => ['type' => 'maison', 'clair' => true],
            default => ['type' => 'texte', 'texte' => $valeur],
        };
    }

    /** Visuel du haut de la colonne (us_pf_visuel2014), sauf image par defaut. */
    public function logo(): ?string
    {
        $visuel = (string) $this->b->cont_visuel2014;

        if ($visuel === '' || $visuel === 'deleted' || str_starts_with($visuel, 'ultra-book_default_')) {
            return null;
        }

        return (str_contains($visuel, 'http') ? '' : $this->b->rep_pref).$visuel;
    }

    public function texteLibre(string $cle): string
    {
        return trim((string) ($this->b->ed_dom_txt->{$cle} ?? ''));
    }

    /**
     * Page d'accueil placee a une position (a, b : colonne ; c : au-dessus ;
     * d : au-dessous des vignettes du portfolio), HTML pret a afficher.
     */
    public function blocAccueil(string $position): ?string
    {
        $id = (string) ($this->pref->{'accueil_contenu_aff_'.$position}->{'accueil_contenu_aff_'.$position} ?? 'false');

        if ($id === 'false' || $id === '') {
            return null;
        }

        $pages = $this->b->gal_cont_accueil;
        foreach ($pages as $page) {
            if ((string) ($page['img_id'] ?? '') === $id) {
                return book_actu_txt($page['img_html'] ?? '');
            }
        }

        // Page disparue : le legacy (recursive_array_search rend false,
        // soit l'indice 0) affiche alors la premiere page d'accueil.
        return isset($pages[0]) ? book_actu_txt($pages[0]['img_html'] ?? '') : null;
    }

    /**
     * Rubriques du portfolio pour le menu et l'accueil, bornees par la
     * formule comme le legacy (`$key > us_formule_img_rub_nb`).
     *
     * @return list<array{id: int, nom: string, url: string, active: bool, visuels: list<string>}>
     */
    public function rubriquesPortfolio(): array
    {
        $ptf = $this->b->menu['ptf'] ?? [];
        $rubriques = [];

        foreach ($ptf as $k => $rub) {
            if (! is_int($k) || ! ($rub['rub_id'] ?? null)) {
                continue;
            }
            if ($k > $this->b->us_formule_img_rub_nb) {
                break;
            }

            $fichiers = array_values(array_filter(array_column($ptf['img'][$rub['rub_id']] ?? [], 'img_fichier')));

            $rubriques[] = [
                'id' => (int) $rub['rub_id'],
                'cle' => self::cle($k, $rub['rub_nom']),
                'nom' => self::brut($rub['rub_nom']),
                'url' => wd_remove_accents($rub['rub_nom']).'-p'.$rub['rub_id'],
                'active' => (int) $rub['rub_id'] === (int) $this->b->rub_id && $this->b->page_type === 'portfolio',
                'visuels' => $fichiers,
            ];
        }

        return $rubriques;
    }

    /** Carre recadre d'un visuel (declinaisons carre_368 / carre_183). */
    public function carre(string $fichier, int $taille): string
    {
        return preg_match('/^(ultra-book_default_|visuel_default_)/', $fichier)
            ? '/img_default/ultra-book_default_'.$taille.'x'.$taille.'.gif'
            : '/books/'.$this->b->us_dir.'/carre_'.$taille.'/'.$fichier;
    }

    /** Diaporama : slide (defaut), full (plein ecran) ou image (a la suite). */
    public function presentation(): string
    {
        $valeur = (string) ($this->pref->ptf_type_presentation->ptf_type_presentation ?? 'slide');

        return in_array($valeur, ['slide', 'full', 'image'], true) ? $valeur : 'slide';
    }

    public function legendes(): bool
    {
        return $this->actif('ptf_titre_aff', false);
    }

    /** thumbs | dots | none. */
    public function navigation(): string
    {
        $valeur = (string) ($this->pref->ptf_type_vign->ptf_type_vign ?? 'thumbs');

        return in_array($valeur, ['thumbs', 'dots'], true) ? $valeur : 'none';
    }

    public function navigationEnHaut(): bool
    {
        return ($this->pref->ptf_position_vign->ptf_position_vign ?? 'bottom') === 'top';
    }

    public function actif(string $cle, bool $defaut = true): bool
    {
        $valeur = $this->pref->{$cle}->{$cle} ?? null;

        return $valeur === null ? $defaut : filter_var($valeur, FILTER_VALIDATE_BOOL);
    }

    /** Visuels de la rubrique affichee, pour le diaporama. */
    public function diapositives(): array
    {
        $rubId = (int) $this->b->rub_id;

        return array_values(array_filter($this->visuels(), fn (array $v) => $v['rub_id'] === $rubId));
    }

    /** Pied de page : celui du createur (formule payante), ou la mention de la plateforme. */
    public function piedDePage(): ?string
    {
        // Pied saisi dans le panneau d'edition, sinon celui du legacy.
        if (($pied = trim($this->texte('footer'))) !== '') {
            return $pied;
        }

        $pied = (string) $this->b->cont_piedpage;

        return $this->b->us_formule === 1 && $pied !== '' && $pied !== '[invisible]'
            ? htmlspecialchars_decode($pied, ENT_QUOTES)
            : null;
    }

    public function mentionPlateforme(): bool
    {
        return ! ($this->b->us_formule === 1 && ($this->b->cont_piedpage === '[invisible]' || (string) $this->b->cont_piedpage !== ''))
            && $this->b->inc_action_view !== 'df';
    }

    /** @return array<string, string> */
    public function partage(): array
    {
        if (! $this->b->us_partage_lien) {
            return [];
        }

        $url = rawurlencode($this->urlCanonique());
        $titre = rawurlencode($this->titrePage());

        return [
            'Facebook' => 'https://www.facebook.com/sharer.php?u='.$url,
            'X' => 'https://twitter.com/intent/tweet?url='.$url.'&text='.$titre,
            'LinkedIn' => 'https://www.linkedin.com/shareArticle?mini=true&url='.$url.'&title='.$titre,
            'Pinterest' => 'https://pinterest.com/pin/create/button/?url='.$url.'&description='.$titre,
        ];
    }

    /** Bandeau de visuels au-dessus de la page (Classique 2015). */
    public function bandeau(): array
    {
        return [];
    }

    /** Vignettes de la rubrique courante dans la colonne (Classique 2015). */
    public function vignettesColonne(): array
    {
        return [];
    }

    /** La colonne accompagne-t-elle l'accueil sur ordinateur ? */
    public function colonneSurAccueil(): bool
    {
        return true;
    }

    /** Menu en haut de page au lieu de la colonne (2012-slide), sur ordinateur. */
    public function menuHorizontal(): bool
    {
        return false;
    }

    /** La colonne filtre-t-elle la mosaique (Pinter) ? */
    public function filtreColonne(): bool
    {
        return false;
    }

    /** Positions des pages d'accueil dans la colonne : [en haut, en bas]. */
    public function blocsColonne(): array
    {
        return ['d', 'a'];
    }

    /** Couleur de la page encadree sur le fond (Pinter), ou null. */
    public function couleurCadre(): ?string
    {
        return null;
    }

    /** Polices des reglages et des textes libres. */
    public function urlPolicesBook(): ?string
    {
        $html = $this->texteLibre('cont_menu_gauche').$this->texteLibre('cont_menu_gauche2');
        $libres = array_filter(VueZoom2016::POLICES, fn ($p) => stripos($html, $p) !== false);

        return self::urlPolices([...$this->policesReglages(), ...$libres]);
    }
}
