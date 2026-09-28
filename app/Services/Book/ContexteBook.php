<?php

namespace App\Services\Book;

use App\Models\BookSection;
use App\Models\Gallery;
use App\Models\User;
use App\Support\DossierBook;
use App\Support\Marque;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Donnees d'un book, sous la forme exacte que lisaient les gabarits
 * Savant du legacy (`$tpl->menu`, `$tpl->gal_cont`, `$tpl->rep_img550`…).
 *
 * Adaptateur assume : les onze habillages sont portes en Blade ligne a
 * ligne depuis 2011_html_pages_v2/. Conserver les noms et les structures
 * d'origine permet de comparer chaque vue a son gabarit source et de
 * reprendre sa logique sans la reinterpreter — y compris ses bizarreries,
 * comme `menu['ptf']` qui melange les rubriques (cles numeriques) et leurs
 * visuels (cle `img`) dans un meme tableau.
 *
 * Les proprietes publiques portent donc les noms du legacy, en snake_case.
 */
class ContexteBook
{
    // Identite et reglages
    public string $us_dir;

    public string $prenom;

    public string $nom;

    public ?string $us_type;

    public int $us_formule;

    public ?string $us_ville;

    public ?string $us_http;

    public ?string $us_facebook_url;

    public ?string $us_twitter_url;

    public ?string $us_map = null;

    public bool $us_partage_lien;

    public string $modele_book;

    public string $url_mdl;

    /** Dossier des gabarits de Pinter, qui l'utilise au lieu de url_mdl. */
    public string $tpl_dir = '';

    public string $page_type = 'accueil';

    public bool $connection_admin_book = false;

    public bool $IsMobile = false;

    /** web | iphone | ipad — detection du legacy, a l'identique. */
    public string $navigateur_client = 'web';

    // Titres, metas, pied de page
    public string $cont_page_titre;

    public ?string $cont_book_titre;

    public string $cont_page_meta;

    public string $cont_page_key;

    public ?string $cont_analytic;

    public ?string $cont_piedpage;

    public ?string $cont_center;

    public ?string $cont_bg;

    public ?string $cont_bgcoul;

    public ?string $cont_bg_choix;

    // Configuration du theme
    public string $cont_conf2012;

    public object $obj_cont_data;

    public mixed $accueil_contenu_aff_a = null;

    public mixed $accueil_contenu_aff_b = null;

    public mixed $accueil_contenu_aff_c = null;

    public mixed $accueil_contenu_aff_d = null;

    public string $accueil_ptf_vignette_aff = 'true';

    public object $ed_dom_txt;

    // Visuels du theme
    public ?string $visuel_accueil = null;

    public ?string $cont_visuel2012 = null;

    public ?string $cont_visuel2014 = null;

    public ?string $cont_visuel2015_1 = null;

    public ?string $cont_visuel2015_2 = null;

    public ?string $cont_visuel2015_3 = null;

    public ?string $cont_visuel2015_accueil = null;

    public ?string $us_pf_img_vignette = null;

    // Quotas de la formule (conf/conf_site.php)
    public int $us_formule_img_nb;

    public int $us_formule_img_nb_mobil;

    public int $us_formule_img_rub_nb;

    // Chemins
    public string $url_abs_site;

    public string $rep_user;

    public string $rep_pref;

    public string $rep_img_;

    public string $rep_img40;

    public string $rep_img550;

    public string $rep_img900;

    public string $rep_img75;

    public string $rep_img320;

    public string $rep_img180;

    public string $icone;

    public string $icone_iphone;

    public string $icone_ipad;

    // Contenus
    public array $menu = ['ptf' => ['img' => []], 'act' => ['img' => []]];

    public array $gal_cont = ['gal' => [], 'img' => []];

    public array $gal_cont_accueil = [];

    public mixed $rub_id = 0;

    public mixed $pag_id = 0;

    public ?string $contact = null;

    // Marque
    public string $inc_action_view;

    public string $inc_site_name;

    public string $inc_url_dom_www;

    /** Quotas par formule, repris de conf/conf_site.php. */
    private const FORMULES = [
        0 => ['img_nb' => 12, 'img_nb_mobil' => 6, 'img_rub_nb' => 3],
        1 => ['img_nb' => 500, 'img_nb_mobil' => 100, 'img_rub_nb' => 24],
    ];

    public function __construct(public readonly User $book, public readonly Marque $marque)
    {
        $reglages = $book->bookSetting;

        $this->us_dir = $book->login;
        $this->prenom = self::texte($book->firstname);
        $this->nom = self::texte($book->lastname);
        $this->us_type = $book->category ? self::texte($book->category->name) : null;
        $this->us_formule = min(1, max(0, (int) $book->plan));
        $this->us_ville = $book->city ? self::texte($book->city) : null;
        $this->us_http = $book->website ? preg_replace('#^https?://#', '', $book->website) : null;
        $this->us_facebook_url = $book->facebook_url;
        $this->us_twitter_url = $book->twitter_url;
        $this->us_partage_lien = (bool) $book->shares_link;
        if ($book->latitude && $book->longitude) {
            $this->us_map = $book->latitude.','.$book->longitude;
        }

        // Detection du legacy, a l'identique : elle choisit la taille des
        // visuels servis par certains gabarits (550 au lieu de 900).
        $agent = (string) request()->userAgent();
        $this->IsMobile = (bool) preg_match('/Iphone|iemobile|htc|blackberry|android|Nokia|bb10/i', $agent);

        // action_book.php, l. 297 : Android n'en fait pas partie (commente
        // dans le legacy), il recoit la version web.
        if (stripos($agent, 'iPad') !== false) {
            $this->navigateur_client = 'ipad';
        }
        if (preg_match('/iPhone|blackberry|iemobile|htc/i', $agent)) {
            $this->navigateur_client = 'iphone';
        }

        /*
         | `mdl_default` — valeur par defaut de la colonne, donc celle de tout
         | nouvel inscrit — n'est pas un gabarit : le legacy l'affichait en
         | Responsive 2014. Idem pour une valeur inconnue.
         */
        $theme = $reglages?->theme;
        $this->modele_book = $theme && config("book_themes.{$theme}") ? $theme : 'mdl_2014_responsive';
        $this->url_mdl = config("book_themes.{$this->modele_book}.dossier", 'responsive');
        $this->tpl_dir = $this->url_mdl;

        $formule = self::FORMULES[$this->us_formule];
        $this->us_formule_img_nb = $formule['img_nb'];
        $this->us_formule_img_nb_mobil = $formule['img_nb_mobil'];
        $this->us_formule_img_rub_nb = $formule['img_rub_nb'];

        $this->cheminsDImages();
        $this->reglages($reglages);
        $this->marque();
    }

    /*
    |--------------------------------------------------------------------------
    | Chemins d'images
    |--------------------------------------------------------------------------
    | Le legacy concatenait `url_abs_site . rep_imgXXX . img_fichier`, chaque
    | rep_imgXXX designant un dossier pre-genere de users_2/. Ici chacun
    | designe une declinaison du service d'images (phase 4a) : la
    | concatenation des gabarits reste valable telle quelle.
    */
    private function cheminsDImages(): void
    {
        /*
         | Vide : chemins relatifs a l'hote du book. Le legacy ecrivait
         | l'adresse du portail parce que les books vivaient sur un autre
         | vhost. Ici le sous-domaine sert le meme public/ : les feuilles, les
         | polices et les visuels restent sur la meme origine, sans en-tete
         | CORS a gerer.
         */
        $this->url_abs_site = '';
        $base = '/books/'.$this->us_dir.'/';

        $this->rep_user = rtrim($base, '/');
        $this->rep_img_ = $base.'source/';
        $this->rep_img40 = $base.'ptf_small/';
        $this->rep_img550 = $base.'ptf_medium/';
        // Le legacy servait l'original borne pour le « 900 » (dossier img_).
        $this->rep_img900 = $base.'source/';
        $this->rep_img75 = $base.'iph_small/';
        $this->rep_img320 = $base.'iph_medium/';
        $this->rep_img180 = $base.'adm_medium/';
        // Visuels de reglage (cms_pref) : servis sans reduction.
        $this->rep_pref = $base.'source/';

        $this->icone = $this->url_abs_site.'/img_front/ultra-book_icon.png';
        $this->icone_iphone = $this->url_abs_site.'/img_front/touch-icon-iphone.png';
        $this->icone_ipad = $this->url_abs_site.'/img_front/touch-icon-ipad.png';
    }

    private function reglages($reglages): void
    {
        $payload = $reglages?->legacy_payload ?? [];

        $this->cont_book_titre = $reglages?->title !== null ? self::texte($reglages->title) : null;
        $this->cont_page_titre = ucfirst($this->prenom).' '.ucfirst($this->nom).' : ';
        if ($this->cont_book_titre) {
            $this->cont_page_titre = ucfirst($this->cont_book_titre);
        }
        $this->cont_page_meta = trim($this->us_type.', '.$reglages?->description, ', ');
        $this->cont_page_key = trim($this->us_type.', '.$reglages?->keywords, ', ');
        $this->cont_analytic = $reglages?->analytics_id;
        $this->cont_piedpage = $reglages?->footer;
        $this->cont_center = $reglages?->is_centered ? '1' : '0';
        $this->cont_bg = $reglages?->background_image ? $this->rep_pref.$reglages->background_image : null;
        $this->cont_bgcoul = $reglages?->background_color;
        $this->cont_bg_choix = $reglages?->background_mode !== null ? (string) $reglages->background_mode : null;
        $this->us_pf_img_vignette = $reglages?->thumbnail;

        $this->cont_visuel2012 = $payload['us_pf_visuel2012'] ?? 'ultra-book_default_980x200.gif';
        $this->cont_visuel2014 = $payload['us_pf_visuel2014'] ?? 'ultra-book_default_200x200.gif';
        $this->cont_visuel2015_1 = $payload['us_pf_clas2015_visuel_top1'] ?? null;
        $this->cont_visuel2015_2 = $payload['us_pf_clas2015_visuel_top2'] ?? null;
        $this->cont_visuel2015_3 = $payload['us_pf_clas2015_visuel_top3'] ?? null;
        $this->cont_visuel2015_accueil = $payload['us_pf_clas2015_visuel_accueil'] ?? null;
        $this->visuel_accueil = $reglages?->theme_home_image;

        // Configuration : celle du createur, sinon celle du theme par defaut.
        $conf = $reglages?->theme_settings
            ? json_encode($reglages->theme_settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $this->confParDefaut();

        $this->appliquerConf($conf);

        $textes = $reglages?->theme_texts[$this->modele_book] ?? [];
        $this->ed_dom_txt = (object) $textes;
    }

    /**
     * Change de theme en cours de requete, comme le legacy le fait pour
     * l'iPad (`us_pf_version_ipad`) : dossier, configuration et textes du
     * theme cible. Chaque theme a sa propre colonne de configuration, et
     * legacy_payload les conserve toutes.
     */
    public function changerTheme(string $theme): static
    {
        $this->modele_book = $theme;
        $this->url_mdl = $this->tpl_dir = config("book_themes.{$theme}.dossier");

        $colonne = config("book_themes.{$theme}.colonne_legacy");
        $brut = $colonne ? ($this->book->bookSetting?->legacy_payload[$colonne] ?? null) : null;
        $this->appliquerConf($brut ?: $this->confParDefaut());

        $this->ed_dom_txt = (object) ($this->book->bookSetting?->theme_texts[$theme] ?? []);

        return $this;
    }

    private function appliquerConf(string $conf): void
    {
        $this->cont_conf2012 = $conf;
        $data = json_decode($conf)?->data ?? new \stdClass;
        $this->obj_cont_data = $data;

        $this->accueil_contenu_aff_a = $data->accueil_contenu_aff_a->accueil_contenu_aff_a ?? null;
        $this->accueil_contenu_aff_b = $data->accueil_contenu_aff_b->accueil_contenu_aff_b ?? null;
        $this->accueil_contenu_aff_c = $data->accueil_contenu_aff_c->accueil_contenu_aff_c ?? null;
        $this->accueil_contenu_aff_d = $data->accueil_contenu_aff_d->accueil_contenu_aff_d ?? null;
        $vignettes = $data->ptf_vignette_aff->ptf_vignette_aff ?? '';
        $this->accueil_ptf_vignette_aff = $vignettes !== '' ? (string) $vignettes : 'true';
    }

    private function confParDefaut(): string
    {
        $defaut = config("book_themes.{$this->modele_book}.defaut") ?? '{"data":{}}';

        return strtr($defaut, [
            '%prenom%' => ucfirst($this->prenom),
            '%nom%' => ucfirst($this->nom),
            '%site_url%' => $this->marque->canonique,
            '%site_nom%' => $this->marque->nom,
        ]);
    }

    private function marque(): void
    {
        $this->inc_action_view = $this->marque->code;
        $this->inc_site_name = $this->marque->nom;
        $this->inc_url_dom_www = preg_replace('#^https?://#', '', $this->marque->canonique);
    }

    /*
    |--------------------------------------------------------------------------
    | Contenus — equivalents de inc/inc_user_book_modele.php
    |--------------------------------------------------------------------------
    */

    /** mod_ptf_2012_navptf : galeries du portfolio et leurs visuels. */
    public function chargerPortfolio(): static
    {
        $galeries = $this->book->galleries()->published()->orderBy('position')
            ->with(['media' => fn ($q) => $q->published()])
            ->get();

        // Un portfolio verrouille garde sa place dans le menu, sans visuel.
        $acces = app(AccesPortfolios::class);
        $this->menu['ptf'] = $this->rubriques($galeries, fn (Gallery $g) => $acces->ouvert($g) ? $this->visuels($g) : []);

        return $this;
    }

    /** mod_ptf_2012_navactu : rubriques de pages (bio, actus) et leurs pages. */
    public function chargerPages(): static
    {
        $this->menu['act'] = $this->rubriquesDePages(BookSection::PAGES);

        return $this;
    }

    /** mod_ptf_2012_accueil : rubriques d'accueil, et pages de la premiere. */
    public function chargerAccueil(): static
    {
        $accueil = $this->rubriquesDePages(BookSection::ACCUEIL);
        $premiere = collect($accueil)->first(fn ($r, $k) => is_int($k));

        $this->gal_cont['gal'] = $accueil;
        $this->gal_cont_accueil = $premiere ? ($accueil['img'][$premiere['rub_id']] ?? []) : [];

        return $this;
    }

    /** mod_ptf_2012_portfolio : la page portfolio, rubrique courante. */
    public function pagePortfolio(int $rubId = 0, bool $titre = true): static
    {
        $this->gal_cont['gal'] = $this->menu['ptf'];
        $this->gal_cont['img'][$rubId] = $this->menu['ptf']['img'][$rubId] ?? null;

        /*
         | Sans rubrique designee (l'accueil), la production n'ajoute pas de
         | nom de rubrique au titre : « <titre> Portfolio ». La copie locale
         | du code 2019 prenait celui de la premiere rubrique ; la production
         | a diverge depuis, et c'est elle qui fait foi (verifie sur quatre
         | books Zoom).
         */
        $nom = $rubId === 0 ? '' : (collect($this->menu['ptf'])->filter(fn ($r, $k) => is_int($k))
            ->first(fn ($r) => $r['rub_id'] == $rubId)['rub_nom'] ?? '');

        $this->rub_id = $rubId;

        if ($titre) {
            $this->cont_page_titre .= ' Portfolio '.($nom === '' ? '' : ':'.$nom);
        }

        return $this;
    }

    /** mod_ptf_2012_news : une rubrique de pages, page courante. */
    public function pageNews(int $rubId = 0, int $pagId = 0): static
    {
        $this->page_type = 'news';
        $this->gal_cont['gal'] = $this->menu['act'];
        $this->gal_cont['img'][$rubId] = $this->menu['act']['img'][$rubId] ?? null;

        if ($pagId === 0) {
            $pagId = (int) ($this->gal_cont['img'][$rubId][0]['img_id'] ?? 0);
        }

        /*
         | Titre : rubrique puis page. Sans rubrique designee, la production
         | n'ajoute rien (meme divergence que pour le portfolio, voir
         | pagePortfolio).
         */
        $titre = '';
        if ($rubId !== 0) {
            $titre = collect($this->menu['act'])->filter(fn ($r, $k) => is_int($k))
                ->first(fn ($r) => $r['rub_id'] == $rubId)['rub_nom'] ?? '';
            foreach ($this->gal_cont['img'][$rubId] ?? [] as $page) {
                if ($page['img_id'] == $pagId) {
                    $titre .= ' : '.$page['img_titre'];
                }
            }
        }

        $this->rub_id = $rubId;
        $this->pag_id = $pagId;
        $this->cont_page_titre .= $titre;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Theme classique 2010 — mod_classique_* (inc_user_book_modele.php)
    |--------------------------------------------------------------------------
    | Memes tables que les themes 2012+, autres structures : `gal_cont['gal']`
    | est la seule liste des rubriques (sans cle `img`), `gal_cont['img']`
    | est indexe par rubrique — ou aplati, pour l'accueil.
    */

    /** @return array{0: list<array>, 1: array<int, list<array>>} */
    private function classique(array $rubriques): array
    {
        $liste = [];
        $contenu = [];

        foreach ($rubriques as $cle => $rubrique) {
            if (is_int($cle)) {
                $liste[] = $rubrique;
                $contenu[$rubrique['rub_id']] = $rubriques['img'][$rubrique['rub_id']] ?? [];
            }
        }

        return [$liste, $contenu];
    }

    public function classiqueAccueil(): static
    {
        [$liste, $contenu] = $this->classique($this->rubriquesDePages(BookSection::ACCUEIL));

        $this->gal_cont['gal'] = $liste;
        $this->gal_cont['img'] = isset($liste[0]) ? $contenu[$liste[0]['rub_id']] : null;
        $this->cont_page_titre .= ' '.$this->inc_site_name;

        return $this;
    }

    public function classiquePortfolio(int $rubId = 0): static
    {
        [$liste, $contenu] = $this->classique($this->menu['ptf']);

        $this->gal_cont = ['gal' => $liste, 'img' => $contenu];
        $titre = $liste[0]['rub_nom'] ?? '';
        foreach ($liste as $r) {
            if ($r['rub_id'] == $rubId) {
                $titre = $r['rub_nom'];
            }
        }

        $this->rub_id = $rubId;
        $this->cont_page_titre .= ' Portfolio : '.$titre;

        return $this;
    }

    public function classiqueNews(int $rubId = 0, int $pagId = 0): static
    {
        [$liste, $contenu] = $this->classique($this->menu['act']);

        // Rubrique par defaut : la premiere, si ni rubrique ni page.
        if ($rubId === 0 && $pagId === 0 && isset($liste[0])) {
            $rubId = (int) $liste[0]['rub_id'];
        }
        if ($pagId === 0) {
            $pagId = (int) ($contenu[$rubId][0]['img_id'] ?? 0);
        }

        $this->gal_cont = ['gal' => $liste, 'img' => $contenu];
        $titre = $liste[0]['rub_nom'] ?? '';
        foreach ($liste as $r) {
            if ($r['rub_id'] == $rubId) {
                $titre = $r['rub_nom'];
            }
        }
        foreach ($contenu[$rubId] ?? [] as $p) {
            if ($p['img_id'] == $pagId) {
                $titre .= ' : '.$p['img_titre'];
            }
        }

        $this->page_type = 'news';
        $this->rub_id = $rubId;
        $this->pag_id = $pagId;
        $this->cont_page_titre .= $titre;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Versions mobiles — iPhone et iPad
    |--------------------------------------------------------------------------
    */

    /** Modeles servis en gabarits iPhone / iPad du legacy : plus aucun (voir versionMobile). */
    private const VERSIONS_MOBILES_LEGACY = [];

    /**
     * Gabarit mobile, ou null pour la version web.
     *
     * Les themes 2014 et suivants sont responsives : le legacy les sert en
     * version web a tous les terminaux. Les themes anciens (2010, 2012,
     * Slide, Pinter) passent sur iPhone et iPad par le reglage du createur
     * pour ce terminal (`us_pf_version_iphone` / `_ipad`), verifie en
     * production :
     *
     *   iPhone  « Modele mobile 2012 »  -> jQuery Mobile (ultrabook_2012_iphone)
     *           « … (poste fixe) »      -> ce theme, version bureau
     *           autre ou vide           -> classique mobile (ultrabook_iphone_portfolio*)
     *   iPad    « Modele mobile 2012 »  -> ultrabook_2012_ipad
     *           « Modele classique »    -> theme classique 2010
     *           « Modele portfolio 2012 » -> theme 2012
     *           vide                    -> le theme web
     *
     * @return array{mode: string, theme?: string}|null
     */
    public function versionMobile(): ?array
    {
        // Le modele classique, seul concerne, est passe en Blade responsive
        // (VueClassique2010) : il sert la meme page a tous les ecrans. Les
        // gabarits iPhone / iPad du legacy ne sont plus proposes.
        if ($this->navigateur_client === 'web'
            || ! in_array($this->modele_book, self::VERSIONS_MOBILES_LEGACY, true)) {
            return null;
        }

        $brut = (string) ($this->book->bookSetting?->legacy_payload['us_pf_version_'.$this->navigateur_client] ?? '');
        $posteFixe = str_contains($brut, '(poste fixe)');
        $reglage = trim(preg_replace('# \(poste fixe\)#', '', $brut));

        if (mb_strtolower($reglage) === 'modèle mobile 2012') {
            return ['mode' => '2012'];
        }

        if ($this->navigateur_client === 'iphone' && ! $posteFixe) {
            return ['mode' => 'classique'];
        }

        $theme = config('categories.legacy_theme_map')[mb_strtolower($reglage)] ?? null;

        return $theme && $theme !== 'mdl_2014_responsive' && $reglage !== '' ? ['mode' => 'theme', 'theme' => $theme] : null;
    }

    /**
     * mod_classique_iphone_accueil / mod_ptf_2012_iphone_accueil : toutes
     * les galeries, chacune avec sa premiere image en tete.
     */
    public function iphoneListe(string $type): static
    {
        [$liste, $contenu] = $this->classique($this->menu['ptf']);

        foreach ($liste as $rubrique) {
            $visuels = array_values($contenu[$rubrique['rub_id']] ?? []);
            $premiere = self::premiereImage($rubrique['rub_id'], $visuels);
            if ($visuels !== [] || $premiere !== null) {
                $visuels[0] = $premiere;
            }
            $contenu[$rubrique['rub_id']] = $visuels;
        }

        $this->gal_cont = ['gal' => $liste, 'img' => $contenu];
        $this->page_type = $type;
        $this->cont_page_titre .= 'Portfolio';

        return $this;
    }

    /** mod_classique_iphone_galerie / mod_ptf_2012_iphone_galerie. */
    public function iphoneGalerie(int $rubId, string $type): static
    {
        [$liste, $contenu] = $this->classique($this->menu['ptf']);
        $une = array_values(array_filter($liste, fn ($r) => $r['rub_id'] == $rubId));

        $this->gal_cont = ['gal' => $une, 'img' => $une ? [$rubId => $contenu[$rubId] ?? []] : []];
        $this->rub_id = $rubId;
        $this->page_type = $type;
        $this->cont_page_titre .= 'Portfolio';

        return $this;
    }

    /**
     * usbook2011_img_first : l'image dont un champ vaut le premier
     * identifiant de la liste d'ordre. Sans liste ou sans correspondance,
     * le legacy rendait `$img[null]`, soit null.
     */
    private function premiereImage(int|string $rubId, array $visuels): ?array
    {
        $galerie = $this->book->galleries->firstWhere(fn ($g) => ($g->legacy_id ?? $g->id) == $rubId);
        $premier = $galerie?->media_order[0] ?? null;

        foreach ($visuels as $visuel) {
            if ($premier !== null && in_array((string) $premier, array_map('strval', $visuel), true)) {
                return $visuel;
            }
        }

        return null;
    }

    /**
     * Page contact des themes 2012 a 2015.
     *
     * Leur aiguillage prevoit une page contact, mais le gabarit
     * (`ultrabook_contact.php`) n'a jamais existe : en production, /contact
     * y affiche une page vide. Le formulaire est presente comme une page de
     * rubrique, que le gabarit « news » de chaque theme habille dans son
     * style. Ecart assume : voir _doc/11_phase4_books.md, lot 4d.
     */
    public function pageContact(string $formulaire): static
    {
        $this->page_type = 'news';
        $rubrique = ['rub_id' => -1, 'rub_nom' => __('Contact'), 'rub_id_parent' => 0, 'rub_coul' => null, 'rub_link' => null, 'rub_publier' => 'publie'];
        $page = ['img_id' => -1, 'img_titre' => __('Contact'), 'img_titre_alt' => '', 'img_fichier' => '', 'img_desc' => '', 'img_legende' => '', 'img_link' => '', 'img_html' => '<div id="contact_box">'.$formulaire.'</div>'];

        $this->menu['act'][] = $rubrique;
        $this->menu['act']['img'][-1] = [$page];
        // `img` doit rester la derniere cle, comme dans le legacy.
        $img = $this->menu['act']['img'];
        unset($this->menu['act']['img']);
        $this->menu['act']['img'] = $img;

        $this->gal_cont['gal'] = $this->menu['act'];
        $this->gal_cont['img'][-1] = [$page];
        $this->rub_id = -1;
        $this->pag_id = -1;
        $this->cont_page_titre .= __('Contact');

        return $this;
    }

    /**
     * Greffons Savant appeles par les gabarits (`$this->splugin()`).
     *
     * Seul `ub_img_gestioncache` est utilise : il suffixe l'URL d'un visuel
     * de sa date de modification, pour contourner le cache du navigateur.
     */
    public function splugin(string $nom, ...$arguments): string
    {
        if ($nom !== 'ub_img_gestioncache') {
            return '';
        }

        [$img, $rep, $abs] = $arguments + ['', '', ''];

        if (preg_match('#http#', (string) $img)) {
            return (string) $img;
        }

        $fichier = DossierBook::chemin($this->us_dir, basename((string) $img));

        return $abs.$rep.$img.(is_file($fichier) ? '?'.filemtime($fichier) : '');
    }

    /**
     * Rubriques au format legacy : tableau a cles numeriques, plus une cle
     * `img` qui porte le contenu de chaque rubrique indexe par rub_id.
     */
    private function rubriques(Collection $rubriques, \Closure $contenu): array
    {
        $resultat = ['img' => []];

        foreach ($rubriques->values() as $i => $rubrique) {
            $ligne = $this->ligneRubrique($rubrique);
            $resultat[$i] = $ligne;
            $resultat['img'][$ligne['rub_id']] = $contenu($rubrique);
        }

        // Le legacy place `img` apres les rubriques : on respecte l'ordre
        // d'iteration, que certains gabarits parcourent avec foreach.
        $img = $resultat['img'];
        unset($resultat['img']);
        $resultat['img'] = $img;

        return $resultat;
    }

    private function rubriquesDePages(string $kind): array
    {
        $sections = $this->book->sections()
            ->where('kind', $kind)->where('is_published', true)
            ->orderBy('position')
            ->with(['articles' => fn ($q) => $q->published()])
            ->get();

        return $this->rubriques($sections, fn (BookSection $s) => $this->pages($s));
    }

    private function ligneRubrique(Gallery|BookSection $r): array
    {
        return [
            'rub_id' => $r->legacy_id ?? $r->id,
            'rub_nom' => self::texte($r instanceof Gallery ? $r->name : $r->title),
            'rub_id_parent' => $r->parent?->legacy_id ?? $r->parent_id ?? 0,
            'rub_coul' => $r->color,
            'rub_link' => null,
            'rub_publier' => 'publie',
        ];
    }

    /** Visuels d'une galerie, dans l'ordre du legacy. */
    private function visuels(Gallery $galerie): array
    {
        return self::ordonner($galerie->media->sortBy(fn ($m) => $m->legacy_id ?? $m->id)
            ->map(fn ($m) => [
                'img_id' => $m->legacy_id ?? $m->id,
                'img_titre' => self::texte($m->title),
                'img_titre_alt' => self::texte($m->alt),
                'img_fichier' => (string) $m->filename,
                'img_desc' => self::texte($m->description),
                'img_legende' => '',
                'img_link' => (string) $m->link,
                'img_html' => '',
                // Dimensions de l'original : les vues recentes reservent la
                // place du visuel avant son chargement.
                'img_largeur' => $m->width,
                'img_hauteur' => $m->height,
            ])->values()->all(), $galerie->media_order);
    }

    private function pages(BookSection $section): array
    {
        return self::ordonner($section->articles->sortBy(fn ($a) => $a->legacy_id ?? $a->id)
            ->map(fn ($a) => [
                'img_id' => $a->legacy_id ?? $a->id,
                'img_titre' => self::texte($a->title),
                'img_titre_alt' => '',
                'img_fichier' => (string) $a->image,
                'img_desc' => '',
                'img_legende' => '',
                'img_link' => '',
                'img_html' => (string) $a->body,
            ])->values()->all(), $section->page_order);
    }

    /**
     * usbook2011_img_ordre, a l'identique.
     *
     * Chaque element prend pour cle son rang dans la liste d'ordre. Ceux qui
     * n'y figurent pas recoivent tous la cle vide et s'ecrasent : un seul
     * survit, place en tete par ksort(). C'est un defaut du legacy, mais
     * c'est ce que voit le public — le reproduire garde les books
     * identiques. Sans liste, l'ordre est celui de la base (identifiant
     * croissant, l'ordre de fetchAll() sans ORDER BY sur la cle primaire).
     *
     * @param  list<array>  $elements
     * @param  list<int>|null  $ordre
     */
    public static function ordonner(array $elements, ?array $ordre): array
    {
        if (empty($ordre)) {
            return $elements;
        }

        $rang = array_flip(array_map('strval', $ordre));
        $resultat = [];

        foreach ($elements as $element) {
            $resultat[$rang[(string) $element['img_id']] ?? ''] = $element;
        }

        ksort($resultat);

        return $resultat;
    }

    /**
     * Texte brut destine a un gabarit qui l'affiche sans echappement.
     *
     * Le legacy stockait ces champs deja encodes en entites HTML (filtre()
     * a la saisie), et ses gabarits les affichaient tels quels. Les donnees
     * importees le sont encore : `double_encode = false` les laisse
     * intactes, la sortie est identique. Mais un texte saisi demain dans
     * l'espace creatif arrivera brut ; un `<` y est alors neutralise ici,
     * plutot que dans 92 gabarits.
     *
     * Les champs HTML voulus (pages, blocs editables, pied de page) ne
     * passent pas par la : ils relevent d'un filtrage a la saisie (phase 5).
     */
    public static function texte(?string $valeur): string
    {
        return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }

    /** Slug d'URL d'une rubrique ou d'une page, comme le legacy. */
    public static function slug(?string $texte): string
    {
        $s = mb_strtolower(preg_replace("/(\s|\/|&|\(|\)|\"|'|\.|\+|\||@|;|,|#|!)+/", '_', (string) $texte));

        return trim(Str::ascii($s), '_') ?: 'page';
    }
}
