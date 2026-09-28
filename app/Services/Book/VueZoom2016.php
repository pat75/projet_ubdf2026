<?php

namespace App\Services\Book;

/**
 * Donnees du modele Zoom 2016 (dossier zoom2016), pour les vues
 * Blade/Tailwind/Alpine de resources/views/book/zoom2016.
 *
 * Remplace _ultrabook__header, ultrabook_portfolio, ultrabook_news,
 * ultrabook_contact et ultrabook_footer* de themes/zoom2016, ainsi que
 * mdl_zoom.js (Isotope, Fotorama, colourBrightness, djax, TweenMax).
 *
 * Reglages lus dans cont_conf2012 (us_pf_conf2016_zoom) :
 * .ub_couleur_fond (fond de page), .ub_couleur_nav (bandeau d'en-tete),
 * link_accueil / link_bio / link_contact (intitules du menu),
 * ptf_activer_sociaux, ptf_activer_contact, ptf_activer_gmap,
 * ptf_activer_iso_category (portfolio groupe par rubrique).
 * Textes libres (theme_texts) : cont_menu_gauche (presentation, sous le
 * logo) et cont_menu_gauche2 (texte de la page contact).
 */
class VueZoom2016 extends VueBook
{
    protected const LIMITE_PAR_RUBRIQUE = true;

    /** Polices proposees par l'ancien editeur de texte (« Font B » du legacy). */
    public const POLICES = [
        'Concert One', 'Belleza', 'Belgrano', 'Quantico', 'Vollkorn', 'Codystar', 'Oleo Script',
        'Averia Sans Libre', 'Ubuntu Mono', 'Economica', 'Dosis', 'Ruda', 'Signika', 'Oswald',
        'Amatic SC', 'Jockey One', 'Philosopher', 'Duru Sans', 'Rationale', 'Medula One',
        'Sansita One', 'Patua One', 'Ubuntu Condensed', 'Open Sans',
    ];

    public function couleurFond(): string
    {
        return self::couleur($this->pref->{'.ub_couleur_fond'}->backgroundColor ?? null, '#ffffff');
    }

    public function couleurBandeau(): string
    {
        return self::couleur($this->pref->{'.ub_couleur_nav'}->color ?? null, '#292929');
    }

    /**
     * Variables CSS du book : fond, bandeau, et couleurs de texte choisies
     * selon la clarte de chacun (ex-plugin jQuery colourBrightness).
     */
    public function variables(): string
    {
        $fondSombre = self::sombre($this->couleurFond());
        $bandeauSombre = self::sombre($this->couleurBandeau());

        return implode(';', [
            '--book-fond:'.$this->couleurFond(),
            '--book-texte:'.($fondSombre ? '#ffffff' : '#000000'),
            '--book-texte2:'.($fondSombre ? '#e5e5e5' : '#222222'),
            '--book-texte3:'.($fondSombre ? '#a3a3a3' : '#6b6b6b'),
            '--book-filet:'.($fondSombre ? '#6b6b6b' : '#c8c8c8'),
            '--book-bandeau:'.$this->couleurBandeau(),
            '--book-bandeau-texte:'.($bandeauSombre ? '#ffffff' : '#000000'),
        ]);
    }

    public function lien(string $cle, string $defaut): string
    {
        return self::brut($this->pref->{$cle}->form_text ?? '') ?: $defaut;
    }

    public function actif(string $cle, bool $defaut = true): bool
    {
        $valeur = $this->pref->{$cle}->{$cle} ?? null;

        return $valeur === null ? $defaut : filter_var($valeur, FILTER_VALIDATE_BOOL);
    }

    /** Portfolio groupe par rubrique, un intertitre par rubrique. */
    public function parRubrique(): bool
    {
        return $this->actif('ptf_activer_iso_category', false);
    }

    /** Presentation sous le logo (HTML de l'ancien editeur). */
    public function presentation(): string
    {
        return trim((string) ($this->b->ed_dom_txt->cont_menu_gauche ?? ''));
    }

    /** Texte de la page contact (HTML de l'ancien editeur). */
    public function texteContact(): string
    {
        return trim((string) ($this->b->ed_dom_txt->cont_menu_gauche2 ?? ''));
    }

    /** Carte de la page contact : « lat,lng » du createur, si le reglage l'active. */
    public function carte(): ?string
    {
        return $this->actif('ptf_activer_gmap', false) && $this->b->us_map ? $this->b->us_map : null;
    }

    /**
     * Pied de page du createur : reserve a la formule payante, masque par
     * « [invisible] » (ultrabook_footer_section).
     */
    public function piedDePage(): ?string
    {
        $pied = (string) $this->b->cont_piedpage;

        return $this->b->us_formule === 1 && $pied !== '' && $pied !== '[invisible]'
            ? htmlspecialchars_decode($pied, ENT_QUOTES)
            : null;
    }

    /** Lien « Fonctionne avec », sauf formule payante qui le masque, ou marque DF. */
    public function mentionPlateforme(): bool
    {
        return ! ($this->b->us_formule === 1 && $this->b->cont_piedpage === '[invisible]')
            && $this->b->inc_action_view !== 'df';
    }

    /**
     * Polices Google reellement employees par les textes du createur :
     * le legacy chargeait les vingt-quatre sur chaque page.
     *
     * @return list<string>
     */
    public function policesUtilisees(): array
    {
        $html = $this->presentation().$this->texteContact().($this->menuPages()['page']['img_html'] ?? '');

        return array_values(array_filter(self::POLICES, fn (string $police) => stripos($html, $police) !== false && $police !== 'Dosis'));
    }

    /**
     * Liens de partage de la page (ultrabook_partage.tlp) : Facebook, X,
     * LinkedIn, Pinterest.
     *
     * @return array<string, string>
     */
    public function partage(): array
    {
        $url = rawurlencode($this->urlCanonique());
        $titre = rawurlencode($this->titrePage());

        return [
            'Facebook' => 'https://www.facebook.com/sharer.php?u='.$url,
            'X' => 'https://twitter.com/intent/tweet?url='.$url.'&text='.$titre,
            'LinkedIn' => 'https://www.linkedin.com/shareArticle?mini=true&url='.$url.'&title='.$titre,
            'Pinterest' => 'https://pinterest.com/pin/create/button/?url='.$url.'&description='.$titre,
        ];
    }

    /** Couleur CSS acceptee telle quelle (#hex ou rgb[a]), sinon le defaut. */
    public static function couleur(mixed $valeur, string $defaut): string
    {
        $valeur = trim((string) $valeur);

        return preg_match('/^(#[0-9a-f]{3,8}|rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\))$/i', $valeur) ? $valeur : $defaut;
    }

    /** Couleur sombre : luminance relative (WCAG) sous 0,179 (meme contraste avec le blanc et le noir). */
    public static function sombre(string $couleur): bool
    {
        if (preg_match('/^#([0-9a-f]{3})$/i', $couleur, $m)) {
            $couleur = '#'.preg_replace('/(.)/', '$1$1', $m[1]);
        }

        if (preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})/i', $couleur, $m)) {
            $rvb = array_map('hexdec', [$m[1], $m[2], $m[3]]);
        } elseif (preg_match('/^rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)/i', $couleur, $m)) {
            $rvb = [(float) $m[1], (float) $m[2], (float) $m[3]];
        } else {
            return false;
        }

        [$r, $v, $b] = array_map(function ($c) {
            $c /= 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, $rvb);

        return 0.2126 * $r + 0.7152 * $v + 0.0722 * $b < 0.179;
    }
}
