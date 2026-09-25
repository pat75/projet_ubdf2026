<?php

namespace App\Services\Book;

use Illuminate\Support\Str;

/**
 * Donnees des modeles Ultra-frais et Ultra-zen (dossier ultra2020), pour
 * les vues Blade/Tailwind/Alpine de resources/views/book/ultra2020.
 *
 * Remplace la logique etalee dans les gabarits legacy
 * (ultrabook_type, _ultrabook__layout, portfolio.tlp) et dans core.js
 * (choix de l'icone d'en-tete, taille) : la vue ne recoit plus que des
 * valeurs pretes a afficher.
 *
 * Reglages lus dans cont_conf2012 (JSON du createur, edite depuis
 * /espace/habillage) : theme (theme_white | theme_gris | theme_black),
 * header (1 = photo, 2+ = icone de la galerie), header_size (S | M | L),
 * visuel_size (small | normal | large), cursor, titre, description,
 * nav_link, social_link, footer, expert_css.
 */
class VueUltra2020
{
    /** Icones d'en-tete de la galerie legacy (__template_front.tpl), dans l'ordre : header = 2 designe la premiere. */
    public const ICONES = [
        '<path d="M3 21a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h7.414l2 2H20a1 1 0 0 1 1 1v3H4v9.996L6 11h16.5l-2.31 9.243a1 1 0 0 1-.97.757H3z"/>',
        '<path d="M13 10h7l-9 13v-9H4l9-13z"/>',
        '<path d="M12 1l9.5 5.5v11L12 23l-9.5-5.5v-11L12 1zm0 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>',
        '<path d="M9.827 21.763L14.31 14l3.532 6.117A9.955 9.955 0 0 1 12 22c-.746 0-1.473-.082-2.173-.237zM7.89 21.12A10.028 10.028 0 0 1 2.458 15h8.965L7.89 21.119zM2.05 13a9.964 9.964 0 0 1 2.583-7.761L9.112 13H2.05zm4.109-9.117A9.955 9.955 0 0 1 12 2c.746 0 1.473.082 2.173.237L9.69 10 6.159 3.883zM16.11 2.88A10.028 10.028 0 0 1 21.542 9h-8.965l3.533-6.119zM21.95 11a9.964 9.964 0 0 1-2.583 7.761L14.888 11h7.064z"/>',
        '<path d="M15.5 6.937A6.997 6.997 0 0 1 19 13v8h-4.17a3.001 3.001 0 0 1-5.66 0H5v-8a6.997 6.997 0 0 1 3.5-6.063A3.974 3.974 0 0 1 8.125 6H5V4h3.126a4.002 4.002 0 0 1 7.748 0H19v2h-3.126c-.085.33-.212.645-.373.937zM12 14a1 1 0 0 0-1 1v5a1 1 0 0 0 2 0v-5a1 1 0 0 0-1-1zm0-7a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>',
        '<path d="M17.084 15.812a7 7 0 1 0-10.168 0A5.996 5.996 0 0 1 12 13a5.996 5.996 0 0 1 5.084 2.812zM12 23.728l-6.364-6.364a9 9 0 1 1 12.728 0L12 23.728zM12 12a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>',
        '<path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm3.5-13.5l-5 2-2 5 5-2 2-5z"/>',
        '<path d="M2.8 5.2L7 8l4.186-5.86a1 1 0 0 1 1.628 0L17 8l4.2-2.8a1 1 0 0 1 1.547.95l-1.643 13.967a1 1 0 0 1-.993.883H3.889a1 1 0 0 1-.993-.883L1.253 6.149A1 1 0 0 1 2.8 5.2zM12 15a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>',
        '<path d="M4.873 3h14.254a1 1 0 0 1 .809.412l3.823 5.256a.5.5 0 0 1-.037.633L12.367 21.602a.5.5 0 0 1-.734 0L.278 9.302a.5.5 0 0 1-.037-.634l3.823-5.256A1 1 0 0 1 4.873 3z"/>',
        '<path d="M3 3h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm13.464 12.536L20 12l-3.536-3.536L15.05 9.88 17.172 12l-2.122 2.121 1.414 1.415zM6.828 12L8.95 9.879 7.536 8.464 4 12l3.536 3.536L8.95 14.12 6.828 12zm4.416 5l3.64-10h-2.128l-3.64 10h2.128z"/>',
        '<path d="M12 2c5.52 0 10 4.48 10 10s-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2zm0 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>',
        '<path d="M17 8H7v2h4v7h2v-7h4V8zM4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/>',
        '<path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm5.869 12h4.262l.82 2h2.216L13 7h-2L6.833 17H9.05l.82-2zm.82-2L12 9.8l1.311 3.2H10.69z"/>',
        '<path d="M19.228 18.732l1.768-1.768 1.767 1.768a2.5 2.5 0 1 1-3.535 0zM8.878 1.08l11.314 11.313a1 1 0 0 1 0 1.415l-8.485 8.485a1 1 0 0 1-1.414 0l-8.485-8.485a1 1 0 0 1 0-1.415l7.778-7.778-2.122-2.121L8.88 1.08zM11 6.03L3.929 13.1H18.07L11 6.03z"/>',
    ];

    public const RESEAUX = ['facebook', 'instagram', 'pinterest', 'twitter', 'linkedin'];

    /** Visuels charges d'emblee, en priorite haute : le premier ecran. */
    public const PRIORITAIRES = 6;

    public readonly object $pref;

    public function __construct(public readonly ContexteBook $b)
    {
        $pref = json_decode($b->cont_conf2012 ?: '{}');
        $this->pref = $pref->data ?? (object) [];
    }

    /** Le createur, connecte sur le sous-domaine de son book (EditionBookController). */
    public function edition(): bool
    {
        return auth()->check() && auth()->user()->login === $this->b->us_dir;
    }

    /** Espace du createur, sur le portail de sa marque. */
    public function urlEspace(): string
    {
        $hote = config('marques.marques.'.$this->b->book->brand.'.hotes')[0] ?? null;

        return $hote ? 'https://'.$hote.'/espace' : url('/espace');
    }

    public function zen(): bool
    {
        return $this->b->modele_book === 'mdl_2020_ultra_zen';
    }

    /** theme_white | theme_gris | theme_black. */
    public function couleur(): string
    {
        $theme = (string) ($this->pref->theme ?? 'theme_white');

        return in_array($theme, ['theme_white', 'theme_gris', 'theme_black'], true) ? $theme : 'theme_white';
    }

    public function texte(string $cle): string
    {
        return self::deslasher((string) ($this->pref->{$cle} ?? ''));
    }

    public function lien(string $cle, string $defaut): string
    {
        return self::deslasher((string) ($this->pref->nav_link->{$cle} ?? '')) ?: $defaut;
    }

    public function curseur(): bool
    {
        return filter_var($this->pref->cursor ?? false, FILTER_VALIDATE_BOOL);
    }

    /** small | normal | large : marge autour de chaque visuel. */
    public function tailleVisuels(): string
    {
        $taille = (string) ($this->pref->visuel_size ?? 'normal');

        return in_array($taille, ['small', 'normal', 'large'], true) ? $taille : 'normal';
    }

    /** S | M | L. */
    public function tailleEntete(): string
    {
        $taille = strtoupper((string) ($this->pref->header_size ?? 'S'));

        return in_array($taille, ['S', 'M', 'L'], true) ? $taille : 'S';
    }

    /**
     * En-tete : ['type' => 'photo', 'src' => …], ['type' => 'icone', 'trace' => …],
     * ou null (aucun). Un ancien reglage pouvait contenir du HTML brut.
     */
    public function entete(): ?array
    {
        $valeur = $this->pref->header ?? '';

        if (is_string($valeur) && str_contains($valeur, '<')) {
            return ['type' => 'html', 'html' => $valeur];
        }

        $id = (int) $valeur;

        if ($id === 1) {
            return ['type' => 'photo', 'src' => $this->photo()];
        }

        return isset(self::ICONES[$id - 2]) ? ['type' => 'icone', 'trace' => self::ICONES[$id - 2]] : null;
    }

    public function photo(): string
    {
        $visuel = (string) $this->b->visuel_accueil;

        if ($visuel === '' || preg_match('/deleted$|\/$/i', $visuel)) {
            return '/img_default/ultra-book_default_160x160.png';
        }

        return (str_contains($visuel, 'http') ? '' : $this->b->rep_pref).$visuel;
    }

    /** @return array<string, string> reseau => URL, liens renseignes seulement. */
    public function reseaux(): array
    {
        $liens = [];

        foreach (self::RESEAUX as $reseau) {
            $url = trim((string) ($this->pref->social_link->{'link_'.$reseau} ?? ''));

            if ($url !== '') {
                $liens[$reseau] = preg_match('~^(?:f|ht)tps?://~i', $url) ? $url : 'https://'.$url;
            }
        }

        return $liens;
    }

    /**
     * Rubriques du portfolio ayant au moins un visuel.
     *
     * @return list<array{cle: string, nom: string}>
     */
    public function rubriques(): array
    {
        $gal = $this->b->gal_cont['gal'] ?? [];
        $rubriques = [];

        foreach ($gal as $k => $rub) {
            if (is_int($k) && ! empty($gal['img'][$rub['rub_id']])) {
                $rubriques[] = ['cle' => self::cle($k, $rub['rub_nom']), 'nom' => self::brut($rub['rub_nom'])];
            }
        }

        return $rubriques;
    }

    /**
     * Visuels du portfolio, dans l'ordre des rubriques, bornes au nombre
     * permis par la formule (comme le legacy : `>=`, donc un de moins).
     *
     * @return list<array<string, mixed>>
     */
    public function visuels(): array
    {
        $gal = $this->b->gal_cont['gal'] ?? [];
        $visuels = [];
        $max = max(0, $this->b->us_formule_img_nb - 1);

        foreach ($gal as $k => $rub) {
            if (! is_int($k)) {
                continue;
            }

            foreach ($gal['img'][$rub['rub_id']] ?? [] as $img) {
                if (count($visuels) >= $max) {
                    break 2;
                }

                if (($img['img_fichier'] ?? '') === '') {
                    continue;
                }

                $defaut = (bool) preg_match('/^(ultra-book_default_|visuel_default_)/', $img['img_fichier']);
                $grand = ($defaut ? '/img_default/' : $this->b->rep_img900).$img['img_fichier'];
                $moyen = ($defaut ? '/img_default/' : $this->b->rep_img550).$img['img_fichier'];

                $visuels[] = [
                    'rubrique' => self::cle($k, $rub['rub_nom']),
                    'nom_rubrique' => self::brut($rub['rub_nom']),
                    'titre' => self::brut($img['img_titre']),
                    'description' => self::brut($img['img_desc']),
                    'grand' => $grand,
                    'moyen' => $moyen,
                    'largeur' => $img['img_largeur'] ?? null,
                    'hauteur' => $img['img_hauteur'] ?? null,
                ];
            }
        }

        return $visuels;
    }

    /**
     * Menu des pages (Bio, actualites), ex-ultra2020__front_nav_2020 :
     * rubriques et leurs pages, la page affichee et son contenu. Une seule
     * rubrique : ses pages sans intitule de rubrique, comme le legacy.
     *
     * @return array{rubriques: list<array{nom: string, url: string, active: bool, pages: list<array{titre: string, url: string, active: bool}>}>, seule: bool, page: ?array}
     */
    public function menuPages(): array
    {
        $act = $this->b->menu['act'] ?? [];
        $rubId = (int) $this->b->rub_id;
        $pagId = (int) $this->b->pag_id;
        $rubriques = [];
        $courante = null;

        foreach ($act as $k => $rub) {
            if (! is_int($k) || empty($act['img'][$rub['rub_id']])) {
                continue;
            }

            $pages = [];
            foreach ($act['img'][$rub['rub_id']] as $i => $pag) {
                $premiere = $rubriques === [] && $i === 0 && $rubId === 0 && $pagId === 0;
                $active = (int) $pag['img_id'] === $pagId || $premiere;
                $pages[] = [
                    'titre' => self::brut($pag['img_titre']),
                    'url' => wd_remove_accents($pag['img_titre']).'-r'.$rub['rub_id'].'-c'.$pag['img_id'],
                    'active' => $active,
                ];
                if ($active && ! $courante) {
                    $courante = $pag;
                }
            }

            $rubriques[] = [
                'nom' => self::brut($rub['rub_nom']),
                'url' => $pages[0]['url'],
                'active' => (int) $rub['rub_id'] === $rubId || ($rubId === 0 && $rubriques === []),
                'pages' => $pages,
            ];
        }

        return [
            'rubriques' => $rubriques,
            'seule' => count($rubriques) === 1,
            'page' => $courante,
        ];
    }

    /** Identifiant de filtre d'une rubrique : rang + nom, comme le legacy (`0__illustrations`). */
    public static function cle(int $rang, string $nom): string
    {
        return $rang.'__'.(Str::slug($nom) ?: 'rubrique');
    }

    /**
     * Texte a afficher tel quel : les titres sont stockes encodes par
     * l'ancien editeur (&#039;, &quot;). Decode ici, echappe une seule
     * fois par Blade.
     */
    public static function brut(mixed $texte): string
    {
        return html_entity_decode(strip_tags((string) $texte), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** ultra2020__stripslashes_ : entites laissees par l'ancien editeur. */
    public static function deslasher(string $v): string
    {
        return str_replace(['&apos;', '&quot;', '&amp;'], ["'", '"', '&'], $v);
    }
}
