<?php

namespace App\Services\Book;

use Illuminate\Support\Str;

/**
 * Donnees communes aux modeles de book passes en Blade/Tailwind/Alpine
 * (resources/views/book/<dossier>) : visuels du portfolio, rubriques,
 * menu des pages, referencement. Chaque modele en derive
 * (VueUltra2020, VueZoom2016…) pour ses reglages propres.
 *
 * La classe est designee par la cle `vue` de config/book_themes.php.
 */
class VueBook
{
    /** Visuels charges d'emblee, en priorite haute : le premier ecran. */
    public const PRIORITAIRES = 6;

    public readonly object $pref;

    public function __construct(public readonly ContexteBook $b)
    {
        $pref = json_decode($b->cont_conf2012 ?: '{}');
        // Une configuration vide s'encode `[]` : ce n'est pas un objet.
        $this->pref = is_object($pref->data ?? null) ? $pref->data : (object) [];
    }

    /**
     * Modeles dont le menu montre un portfolio protege avec son cadenas.
     * Les autres (Classique 2010, Base, Slide, Pinter) partagent parfois le
     * gabarit Responsive, mais ne sont pas encore declines.
     */
    public const MODELES_VERROU = [
        'mdl_2014_responsive', 'mdl_2015_classique', 'mdl_2015_grid',
        'mdl_2016_zoom', 'mdl_2020_ultra_frais', 'mdl_2020_ultra_zen',
    ];

    /** Portfolio protege et ferme a ce visiteur, si ce modele le montre. */
    protected function verrouille(array $rubrique): bool
    {
        return ($rubrique['rub_verrou'] ?? false) && in_array($this->b->modele_book, self::MODELES_VERROU, true);
    }

    /** Page a ne pas indexer (ex. mot de passe d'un portfolio) : pose par le controleur. */
    public bool $sansIndex = false;

    /** Le createur, connecte sur le sous-domaine de son book (EditionBookController). */
    public function edition(): bool
    {
        return auth()->check() && auth()->user()->login === $this->b->us_dir;
    }

    /** Dossier des vues du modele (config book_themes.<modele>.dossier). */
    public function dossier(): string
    {
        return (string) config('book_themes.'.$this->b->modele_book.'.dossier');
    }

    /** Page Habillage de l'espace du createur, sur le portail de sa marque. */
    public function urlEspace(): string
    {
        $marque = config('marques.marques.'.$this->b->book->brand);

        // En production, l'hote canonique (www.) : c'est la que le createur
        // s'est connecte, et le cookie de session n'est pose que sur lui.
        // ultra-book.com sans www renvoyait vers la connexion puis l'accueil.
        if (app()->isProduction() && ! empty($marque['canonique'])) {
            return rtrim($marque['canonique'], '/').'/espace/habillage';
        }

        $hote = $marque['hotes'][0] ?? null;

        return $hote ? 'https://'.$hote.'/espace/habillage' : url('/espace/habillage');
    }

    /**
     * Photo de profil : visuel d'accueil du theme, sinon la photo reglee
     * dans l'espace (Habillage, meme declinaison que l'espace), sinon
     * l'image par defaut.
     */
    public function photo(): string
    {
        $visuel = (string) $this->b->visuel_accueil;

        if ($visuel === '' || preg_match('/deleted$|\/$/i', $visuel)) {
            return $this->b->book->thumbnailUrl('carre_368') ?? '/img_default/ultra-book_default_160x160.png';
        }

        return (str_contains($visuel, 'http') ? '' : $this->b->rep_pref).$visuel;
    }

    public function aPhoto(): bool
    {
        return ! str_starts_with($this->photo(), '/img_default/');
    }

    /**
     * Rubriques du portfolio ayant au moins un visuel (menu['ptf'] : la
     * meme source sur toutes les pages, accueil de pages compris).
     *
     * @return list<array{cle: string, nom: string}>
     */
    public function rubriques(): array
    {
        $gal = $this->b->menu['ptf'] ?? [];
        $rubriques = [];

        foreach ($gal as $k => $rub) {
            if (is_int($k) && ! empty($gal['img'][$rub['rub_id']])) {
                $rubriques[] = ['cle' => self::cle($k, $rub['rub_nom']), 'nom' => self::brut($rub['rub_nom'])];
            }
        }

        return $rubriques;
    }

    /**
     * Entrees d'un menu de portfolio en mosaique (« les projets » d'Ultra
     * 2020, filtre de Zoom 2016) : les rubriques qui ont des visuels,
     * et les portfolios verrouilles, qui n'en montrent aucun. Ces derniers
     * mènent a la page du mot de passe (`verrou` + `url`) au lieu de filtrer.
     *
     * @return list<array{rub_id: int, cle: string, nom: string, verrou: bool, url: ?string}>
     */
    public function menuProjets(): array
    {
        $gal = $this->b->menu['ptf'] ?? [];
        $entrees = [];

        foreach ($gal as $k => $rub) {
            if (! is_int($k)) {
                continue;
            }

            $verrou = $this->verrouille($rub);

            if (! $verrou && empty($gal['img'][$rub['rub_id']])) {
                continue;
            }

            $entrees[] = [
                'rub_id' => (int) $rub['rub_id'],
                'cle' => self::cle($k, $rub['rub_nom']),
                'nom' => self::brut($rub['rub_nom']),
                'verrou' => $verrou,
                'url' => $verrou ? '/'.wd_remove_accents($rub['rub_nom']).'-p'.$rub['rub_id'] : null,
            ];
        }

        return $entrees;
    }

    /**
     * Filtre pose a l'ouverture : la rubrique designee par l'URL
     * (`/<titre>-p<id>`), `null` sur la page portfolio entiere.
     *
     * @return array{cle: string, nom: string}|null
     */
    public function filtreInitial(): ?array
    {
        $rubId = (int) $this->b->rub_id;

        if ($rubId === 0) {
            return null;
        }

        foreach ($this->menuProjets() as $entree) {
            if ($entree['rub_id'] === $rubId && ! $entree['verrou']) {
                return ['cle' => $entree['cle'], 'nom' => $entree['nom']];
            }
        }

        return null;
    }

    /**
     * Borne de la formule : sur tout le portfolio (Ultra 2020) ou par
     * rubrique (Zoom 2016 : `if ($key >= us_formule_img_nb) break;` dans la
     * boucle de chaque rubrique).
     */
    protected const LIMITE_PAR_RUBRIQUE = false;

    /**
     * Visuels du portfolio, dans l'ordre des rubriques, bornes au nombre
     * permis par la formule. Sur tout le portfolio, le legacy s'arretait a
     * `>=` apres l'ajout : un de moins que la formule.
     * Trois declinaisons pour `srcset` : petit (320), moyen (550), grand (1980).
     *
     * @return list<array<string, mixed>>
     */
    public function visuels(): array
    {
        return $this->memo('visuels', function () {
            $gal = $this->b->menu['ptf'] ?? [];
            $visuels = [];
            $max = max(0, $this->b->us_formule_img_nb - 1);

            foreach ($gal as $k => $rub) {
                if (! is_int($k)) {
                    continue;
                }

                foreach (array_values($gal['img'][$rub['rub_id']] ?? []) as $rang => $img) {
                    if (static::LIMITE_PAR_RUBRIQUE && $rang >= $this->b->us_formule_img_nb) {
                        break;
                    }

                    if (! static::LIMITE_PAR_RUBRIQUE && count($visuels) >= $max) {
                        break 2;
                    }

                    if (($img['img_fichier'] ?? '') === '') {
                        continue;
                    }

                    $defaut = (bool) preg_match('/^(ultra-book_default_|visuel_default_)/', $img['img_fichier']);
                    $dossier = fn (string $rep) => ($defaut ? '/img_default/' : $rep).$img['img_fichier'];

                    $visuels[] = [
                        'rub_id' => (int) $rub['rub_id'],
                        'rubrique' => self::cle($k, $rub['rub_nom']),
                        'nom_rubrique' => self::brut($rub['rub_nom']),
                        'titre' => self::titreVisuel($img['img_titre']),
                        'description' => self::brut($img['img_desc']),
                        'petit' => $dossier($this->b->rep_img320),
                        'moyen' => $dossier($this->b->rep_img550),
                        'grand' => $dossier($this->b->rep_img900),
                        'largeur' => $img['img_largeur'] ?? null,
                        'hauteur' => $img['img_hauteur'] ?? null,
                    ];
                }
            }

            return $visuels;
        });
    }

    /**
     * Menu des pages (Bio, actualites), ex-ultra2020__front_nav_2020 et
     * zoom2016__front_nav_2015 : rubriques et leurs pages, la page affichee
     * et son contenu. Une seule rubrique : ses pages sans intitule de
     * rubrique, comme le legacy.
     *
     * @return array{rubriques: list<array{nom: string, url: string, active: bool, pages: list<array{titre: string, url: string, active: bool}>}>, seule: bool, page: ?array}
     */
    public function menuPages(): array
    {
        return $this->memo('menuPages', function () {
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
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Referencement (SEO) et moteurs generatifs (GEO)
    |--------------------------------------------------------------------------
    */

    /** Nom affiche du createur : titre du book, sinon prenom et nom. */
    public function nomCreateur(): string
    {
        return trim(ucfirst($this->b->prenom).' '.ucfirst($this->b->nom));
    }

    /**
     * Titre de la page. Un titre de book generique herite du legacy
     * (« Ultra-book de monlogin », « Book de alicia ») est remplace par le
     * nom du createur et son metier, qui sont ce que l'on cherche. La marque
     * n'apparait qu'une fois (le suffixe « : Ultra-book » des books gratuits
     * doublait un titre qui la contenait deja).
     */
    public function titrePage(): string
    {
        $marque = $this->b->inc_site_name;
        $nom = $this->nomCreateur() ?: $this->b->us_dir;

        // Titre saisi, sans les mentions de la marque heritees du legacy
        // (« Nom : Ultra-book », « Nom | Ultra-book Portfolio »).
        $corps = trim(self::brut(ucfirst($this->b->cont_page_titre)));
        $corps = trim(preg_replace('/[\s:|—-]*'.preg_quote($marque, '/').'(?:\s+portfolio)?[\s:|—-]*/iu', ' ', $corps), " :|—-\t\n");
        // « Portfolio » ajoute en fin de titre par le legacy, sans nom de rubrique.
        $corps = trim(preg_replace('/(?:\s*\bportfolio\b)+[\s:]*$/iu', '', $corps));

        // La marque est deja retiree : « Ultra-book de login » est devenu « de login ».
        $generique = '/^(?:(?:book|portfolio)\s+)?(?:de\s+|d[\'’]\s*)\S+$|^(?:book|portfolio)$/iu';
        if ($corps === '' || preg_match($generique, $corps) === 1 || mb_strtolower($corps) === mb_strtolower($nom)) {
            // Ce que l'on cherche : le nom, le metier, la ville.
            $corps = $nom.($this->metier() ? ' — '.__(':metier freelance', ['metier' => $this->metier()]) : '');
            if ($this->b->us_ville && mb_strlen($corps.$this->b->us_ville) < 50) {
                $corps .= ' '.__('à :ville', ['ville' => mb_convert_case($this->b->us_ville, MB_CASE_TITLE)]);
            }
        }

        return $corps.' | '.$marque;
    }

    /**
     * Description de la page, 160 caracteres au plus : celle du book si elle
     * dit quelque chose, sinon une phrase construite a partir du metier, de
     * la ville et des specialites (mots-cles IA, a defaut les rubriques).
     */
    public function descriptionPage(): string
    {
        $description = trim(self::brut($this->b->cont_page_meta), " ,\t\n");

        if (mb_strlen($description) < 40) {
            $specialites = $this->motsCles()->pluck('label')->all() ?: $this->rubriquesParlantes();
            $phrase = $this->phraseCreateur();
            $description = trim(implode(' ', array_filter([
                $phrase,
                // Le texte saisi est souvent le metier seul : deja dans la phrase.
                mb_stripos($phrase, $description) === false ? $description : null,
                $specialites ? __('Portfolio : :liste.', ['liste' => implode(', ', array_slice($specialites, 0, 6))]) : null,
            ])));
        }

        return Str::limit(preg_replace('/\s+/', ' ', $description), 157, '…');
    }

    /** « Mathilde Cotillon, illustrateur freelance à Lyon. » */
    public function phraseCreateur(): string
    {
        $nom = $this->nomCreateur() ?: $this->b->us_dir;
        $metier = $this->metier() ? ', '.__(':metier freelance', ['metier' => mb_strtolower($this->metier())]) : '';
        $ville = $this->b->us_ville ? ' '.__('à :ville', ['ville' => mb_convert_case($this->b->us_ville, MB_CASE_TITLE)]) : '';

        return $nom.$metier.$ville.'.';
    }

    /** Metier du createur (sa categorie), sauf « Autre » qui ne dit rien. */
    private function metier(): ?string
    {
        $metier = self::brut((string) $this->b->us_type);

        return $metier !== '' && mb_strtolower($metier) !== 'autre' ? $metier : null;
    }

    /** Noms de rubriques, sans ceux poses par defaut (« Galerie 2 », « Nouvelle rubrique »). */
    private function rubriquesParlantes(): array
    {
        // Noms nettoyes de la ponctuation saisie autour (« - Nous ! », « . Tous les albums »).
        $noms = array_map(fn ($nom) => trim(self::brut((string) $nom), " \t\n-–—.,;:!?•*"), array_column($this->rubriques(), 'nom'));

        return array_values(array_unique(array_filter($noms,
            fn ($nom) => mb_strlen($nom) >= 3
                && preg_match('/^(?:nouvelle rubrique|galerie|portfolio|rubrique|sans titre|projets?)\s*\d*$/iu', $nom) !== 1)));
    }

    private ?\Illuminate\Support\Collection $motsCles = null;

    /**
     * Mots-cles IA les plus frequents parmi les visuels publies du createur
     * (hors portfolios proteges) : ses specialites, telles que l'analyse
     * les a reconnues. Vide si le createur n'a pas ouvert l'analyse IA.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Tag>
     */
    public function motsCles(int $nombre = 6): \Illuminate\Support\Collection
    {
        return $this->motsCles ??= \App\Models\Tag::principauxDe($this->b->book->id, $nombre);
    }

    /** Adresse canonique : sans parametre de requete, accueil = racine. */
    public function urlCanonique(): string
    {
        $chemin = '/'.ltrim(request()->path(), '/');

        return rtrim(request()->getSchemeAndHttpHost().(in_array($chemin, ['/', '/accueil', '/portfolio'], true) ? '/' : $chemin), '/') ?: '/';
    }

    /** Image de partage : la photo du createur, sinon le premier visuel. */
    public function imagePartage(): ?array
    {
        if ($this->aPhoto()) {
            return ['src' => url($this->photo()), 'largeur' => null, 'hauteur' => null];
        }

        $visuel = $this->visuels()[0] ?? null;

        return $visuel ? ['src' => url($visuel['grand']), 'largeur' => $visuel['largeur'], 'hauteur' => $visuel['hauteur']] : null;
    }

    /**
     * Donnees structurees schema.org (JSON-LD) : le createur (Person), son
     * book (ProfilePage) et, sur le portfolio, la galerie d'images. Lues
     * par les moteurs de recherche comme par les moteurs generatifs.
     */
    public function donneesStructurees(): array
    {
        $racine = request()->getSchemeAndHttpHost().'/';
        $personne = array_filter([
            '@type' => 'Person',
            '@id' => $racine.'#createur',
            'name' => $this->nomCreateur(),
            'jobTitle' => self::brut($this->b->us_type) ?: null,
            'url' => $racine,
            'image' => $this->aPhoto() ? url($this->photo()) : null,
            'address' => $this->b->us_ville ? ['@type' => 'PostalAddress', 'addressLocality' => $this->b->us_ville] : null,
            'sameAs' => array_values($this->reseaux()) ?: null,
        ]);

        $graphe = [
            $personne,
            [
                '@type' => 'ProfilePage',
                '@id' => $this->urlCanonique().'#page',
                'url' => $this->urlCanonique(),
                'name' => $this->titrePage(),
                'description' => $this->descriptionPage(),
                'inLanguage' => str_replace('_', '-', app()->getLocale()),
                'mainEntity' => ['@id' => $racine.'#createur'],
                // Le book appartient a la plateforme : lien vers son site.
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => $this->b->inc_site_name,
                    'url' => rtrim($this->b->marque->canonique, '/').'/',
                ],
            ],
        ];

        if (in_array($this->b->page_type, ['accueil', 'portfolio'], true) && $this->visuels() !== []) {
            $graphe[] = [
                '@type' => 'ImageGallery',
                'name' => __('Portfolio de :nom', ['nom' => $this->nomCreateur()]),
                'author' => ['@id' => $racine.'#createur'],
                'image' => array_map(fn (array $v) => array_filter([
                    '@type' => 'ImageObject',
                    'contentUrl' => url($v['grand']),
                    'thumbnailUrl' => url($v['moyen']),
                    // Titre sans extension : « note book vert.jpg » -> « note book vert ».
                    'name' => $v['titre'] ? (texte_seo(preg_replace('/\.(jpe?g|png|gif|webp)$/i', '', $v['titre'])) ?: null) : null,
                    'description' => $v['description'] ?: null,
                    'genre' => $v['nom_rubrique'] ?: null,
                    'width' => $v['largeur'] ?: null,
                    'height' => $v['hauteur'] ?: null,
                    'creator' => ['@id' => $racine.'#createur'],
                    // Mentions lues par Google Images (credit et droits d'auteur).
                    'creditText' => $this->nomCreateur() ?: null,
                    'copyrightNotice' => $this->nomCreateur() ? '© '.$this->nomCreateur() : null,
                ]), array_slice($this->visuels(), 0, 30)),
            ];
        }

        return ['@context' => 'https://schema.org', '@graph' => $graphe];
    }

    /**
     * Liens vers les reseaux du createur, pour `sameAs`. Chaque modele
     * les lit a sa facon.
     *
     * @return array<string, string>
     */
    public function reseaux(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Reglages de typographie des modeles 2012 a 2015
    |--------------------------------------------------------------------------
    | Le createur regle police, couleur et taille par classe CSS
    | (`.ub_font_menut: {fontFamily, color, fontSize}`) ; core.js les
    | appliquait en jQuery au chargement. Ils deviennent ici une feuille de
    | style, proprietes et valeurs filtrees.
    */

    /** Proprietes CSS acceptees, nom camelCase du legacy => nom CSS. */
    private const PROPRIETES = [
        'fontFamily' => 'font-family',
        'color' => 'color',
        'fontSize' => 'font-size',
        'backgroundColor' => 'background-color',
        'fontWeight' => 'font-weight',
        'fontStyle' => 'font-style',
        'textTransform' => 'text-transform',
        'letterSpacing' => 'letter-spacing',
    ];

    /** Classes `.ub_*` du reglage, qui pilotent des elements de la page. */
    public function cssReglages(): string
    {
        $regles = [];

        foreach ((array) $this->pref as $selecteur => $valeurs) {
            if (! preg_match('/^\.[a-z0-9_]+$/i', $selecteur) || ! is_object($valeurs)) {
                continue;
            }

            $declarations = [];
            foreach ((array) $valeurs as $propriete => $valeur) {
                $css = self::PROPRIETES[$propriete] ?? null;
                $valeur = $css ? self::valeurCss($css, (string) $valeur) : null;
                if ($valeur !== null) {
                    $declarations[] = $css.':'.$valeur;
                }
            }

            if ($declarations) {
                $regles[] = $selecteur.'{'.implode(';', $declarations).'}';
            }
        }

        return implode("\n", $regles);
    }

    /** @return list<string> polices Google choisies dans les reglages. */
    public function policesReglages(): array
    {
        $polices = [];

        foreach ((array) $this->pref as $valeurs) {
            $police = is_object($valeurs) ? trim((string) ($valeurs->fontFamily ?? ''), " '\"") : '';
            $police = trim(explode(',', $police)[0], " '\"");
            if ($police !== '' && preg_match('/^[a-z0-9 ]+$/i', $police)) {
                $polices[] = $police;
            }
        }

        return array_values(array_unique($polices));
    }

    /** Parametre `family=` de Google Fonts pour une liste de polices. */
    public static function urlPolices(array $polices): ?string
    {
        $polices = array_values(array_unique(array_filter($polices)));

        return $polices ? 'https://fonts.googleapis.com/css2?'.implode('&', array_map(fn ($p) => 'family='.str_replace(' ', '+', $p), $polices)).'&display=swap' : null;
    }

    private static function valeurCss(string $propriete, string $valeur): ?string
    {
        $valeur = trim($valeur);

        return match ($propriete) {
            'color', 'background-color' => ($c = self::couleurCss($valeur, '')) !== '' ? $c : null,
            'font-family' => preg_match('/^[a-z0-9 ,\'"-]+$/i', $valeur) ? $valeur : null,
            'font-size', 'letter-spacing' => preg_match('/^\d+(\.\d+)?(px|em|rem|%)$/', $valeur) ? $valeur : null,
            default => preg_match('/^[a-z0-9-]+$/i', $valeur) ? $valeur : null,
        };
    }

    /** Couleur CSS acceptee telle quelle (#hex ou rgb[a]), sinon le defaut. */
    public static function couleurCss(mixed $valeur, string $defaut): string
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

    /**
     * Titre d'un visuel ; vide quand l'ancien envoi a garde le nom du
     * fichier (« fb1cae…_rw_1200.png ») : ce n'est pas un texte a montrer
     * ni un texte alternatif utile.
     */
    public static function titreVisuel(mixed $titre): string
    {
        $titre = trim(self::brut($titre));

        return preg_match('/^[\w.\-]+\.(jpe?g|png|gif|webp|avif|bmp|tiff?)$/i', $titre) ? '' : $titre;
    }

    /** ultra2020__stripslashes_ : entites laissees par l'ancien editeur. */
    public static function deslasher(string $v): string
    {
        return str_replace(['&apos;', '&quot;', '&amp;'], ["'", '"', '&'], $v);
    }

    /** @var array<string, mixed> */
    private array $memo = [];

    private function memo(string $cle, \Closure $calcul): mixed
    {
        return $this->memo[$cle] ??= $calcul();
    }
}
