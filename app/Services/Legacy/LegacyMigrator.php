<?php

namespace App\Services\Legacy;

use App\Models\BookArticle;
use App\Models\BookSection;
use App\Models\BookSetting;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Gallery;
use App\Models\Invoice;
use App\Models\MarketingOffer;
use App\Models\Media;
use App\Models\Message;
use App\Models\NewsletterMail;
use App\Models\PromoCode;
use App\Models\Referral;
use App\Models\User;
use App\Models\VisitStat;
use App\Support\LegacyPassword;
use App\Support\LegacyText;
use App\Support\MotsCles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Transfert de ub2020 vers 2026_ubdf.
 *
 * Chaque methode est idempotente : elle s'appuie sur les colonnes legacy_id
 * et peut etre relancee sans creer de doublon. La base source n'est jamais
 * ecrite — la connexion « legacy » le garantit techniquement.
 */
final class LegacyMigrator
{
    /** `ub2_gal_rub.rub_id_categorie` des rubriques qui portent des pages. */
    private const KIND_RUBRIQUE = [1 => 'accueil', 3 => 'pages'];

    private Collection $categories;

    /** @var array<string, int> */
    private array $counts = [];

    /** Mots de passe illisibles, remplaces par une valeur aleatoire. */
    private int $lostPasswords = 0;

    /** Taille des listes d'identifiants passees a un whereIn. */
    private const LOT = 1000;

    /** Cout bcrypt des mots de passe repris (voir password()). */
    private const COUT_BCRYPT_REPRISE = 10;

    /** @var array<string, array<string, int>> table => colonne texte => longueur max */
    private static array $longueurs = [];

    private static bool $ecoute = false;

    /** Valeurs coupees a la longueur de leur colonne, toutes instances confondues. */
    private static int $tronquees = 0;

    /** @var array<string, array{nombre: int, exemples: list<string>}> troncatures par table.colonne */
    private static array $detailTronquees = [];

    /**
     * @param  LegacyLogins|null  $logins  conversion des logins a « _ » / « . » (mise en production)
     */
    public function __construct(
        private readonly LegacySampler $sampler,
        private readonly ?LegacyLogins $logins = null,
    ) {
        $this->categories = Category::pluck('id', 'slug');

        /*
         | Le legacy n'imposait aucune longueur (« +33 6 … / +49 1 … » dans
         | un telephone) : sur les 60 000 comptes, une seule valeur trop
         | longue arretait la reprise. Toute chaine plus longue que sa
         | colonne est coupee avant ecriture, et comptee.
         */
        if (! self::$ecoute) {
            self::$ecoute = true;
            Event::listen('eloquent.saving: *', fn (string $evenement, array $donnees) => self::ajuster($donnees[0]));
        }
    }

    /** @return array<string, int> */
    public function counts(): array
    {
        return $this->counts + ['mots_de_passe_perdus' => $this->lostPasswords];
    }

    public static function valeursTronquees(): int
    {
        return self::$tronquees;
    }

    /**
     * Troncatures par `table.colonne`, avec trois exemples (identifiant
     * legacy de la ligne quand il existe) : de quoi retrouver la valeur
     * d'origine dans la base legacy.
     *
     * @return array<string, array{nombre: int, exemples: list<string>}>
     */
    public static function detailTroncatures(): array
    {
        ksort(self::$detailTronquees);

        return self::$detailTronquees;
    }

    private static function ajuster(Model $modele): void
    {
        $table = $modele->getTable();

        self::$longueurs[$table] ??= collect(Schema::getColumns($table))
            ->filter(fn (array $c) => preg_match('/^(var)?char\(\d+\)/', $c['type']))
            ->mapWithKeys(fn (array $c) => [$c['name'] => (int) preg_replace('/\D/', '', $c['type'])])
            ->all();

        /*
         | Champs recopies sans LegacyText (code postal, telephone, liens) :
         | un varchar legacy coupe au milieu d'un caractere (« Paris 19 \xC3 »)
         | y laisse un octet orphelin que MySQL refuse, ce qui arretait la
         | reprise. Toute chaine invalide est reparee ici, quelle que soit
         | la colonne, et comptee avec les troncatures.
         */
        foreach ($modele->getAttributes() as $colonne => $valeur) {
            if (is_string($valeur) && ! mb_check_encoding($valeur, 'UTF-8')) {
                $modele->setAttribute($colonne, rtrim((string) LegacyText::clean($valeur)));
                self::noter($modele, $table.'.'.$colonne.' (octets invalides repares)');
            }
        }

        foreach (self::$longueurs[$table] as $colonne => $max) {
            $valeur = $modele->getAttributes()[$colonne] ?? null;

            if (is_string($valeur) && mb_strlen($valeur) > $max) {
                $modele->setAttribute($colonne, rtrim(mb_substr($valeur, 0, $max)));
                self::$tronquees++;

                self::noter($modele, $table.'.'.$colonne);
            }
        }
    }

    /** Compte une valeur modifiee pour `$cle`, avec trois exemples d'id. */
    private static function noter(Model $modele, string $cle): void
    {
        self::$detailTronquees[$cle] ??= ['nombre' => 0, 'exemples' => []];
        self::$detailTronquees[$cle]['nombre']++;

        if (count(self::$detailTronquees[$cle]['exemples']) < 3) {
            $id = $modele->getAttribute('legacy_id') ?? $modele->getAttribute('email') ?? $modele->getKey();
            self::$detailTronquees[$cle]['exemples'][] = (string) $id;
        }
    }

    public function migrateUsers(): Collection
    {
        $rows = $this->sampler->pick();

        $repris = 0;

        foreach ($rows as $row) {
            $login = $this->logins
                ? $this->logins->nouveau($row->us_login)
                : mb_strtolower(trim($row->us_login));

            // Login sans conversion possible : signale par LegacyLogins.
            if ($login === null) {
                continue;
            }

            $user = User::updateOrCreate(
                ['legacy_id' => $row->us_id],
                [
                    'login' => $login,
                    'email' => $this->email($row),
                    'password' => $this->password($row),
                    'category_id' => $this->categoryId($row->us_type),
                    'brand' => in_array($row->us_view, ['ub', 'df'], true) ? $row->us_view : 'ub',
                    'locale' => $this->locale($row->us_lang),

                    'firstname' => LegacyText::clean($row->us_prenom),
                    'lastname' => LegacyText::clean($row->us_nom),
                    'company' => LegacyText::clean($row->us_societe),
                    'civility' => $row->us_titre ?: null,
                    'status' => LegacyText::clean($row->us_statut),

                    'address' => LegacyText::clean($row->us_adresse),
                    'zipcode' => $row->us_cp ?: null,
                    'city' => LegacyText::clean($row->us_ville),
                    'country' => LegacyText::clean($row->us_pays),
                    'phone' => $row->us_telephone ?: null,
                    'mobile' => $row->us_portable ?: null,
                    'latitude' => is_numeric($row->us_lat) ? (float) $row->us_lat : null,
                    'longitude' => is_numeric($row->us_lng) ? (float) $row->us_lng : null,

                    'website' => LegacyText::clean($row->us_http),
                    'facebook_url' => $row->us_facebook_url ?: null,
                    'twitter_url' => $row->us_twitter_url ?: null,
                    'instagram_url' => $row->us_instagram_url ?: null,
                    'custom_domain' => $row->us_domaine ?: null,

                    'in_home_selection' => $row->us_affhome === 'true',
                    'in_directory' => $row->us_anu === 'true',
                    'is_selected' => $row->us_ultraselection === 'true',
                    'is_available' => $row->us_dispo === '1',
                    'accepts_sms' => $row->us_pf_sms_contact === 'true',
                    'shares_link' => $row->us_partage_lien === '1',

                    'plan' => (int) $row->us_formule,
                    'plan_started_at' => $this->date($row->us_formule_date),
                    // 8 comptes portent un nombre de mois negatif et 108 un
                    // volume de stockage negatif : compteurs derives du
                    // legacy, ramenes a zero plutot que rejetes.
                    'plan_months' => max(0, (int) $row->us_formule_nbmois) ?: null,

                    'storage_used' => max(0, (int) $row->us_img_size),
                    'media_count' => max(0, (int) $row->us_img_nb),

                    'signup_ip' => Str::limit((string) $row->us_ip, 45, ''),
                    'signup_referer' => LegacyText::clean($row->us_referer),
                    'admin_note' => LegacyText::clean($row->us_commentaire),
                ],
            );

            // Horodatages hors $fillable (ils ne se remplissent pas en masse) :
            // updateOrCreate les ignorait, tous les comptes prenaient la date
            // de l'import et aucun mail n'etait marque comme confirme.
            $user->forceFill([
                'created_at' => $this->date($row->us_date) ?? $user->created_at ?? now(),
                'email_verified_at' => $row->us_confirm_mail === 'true' ? ($user->email_verified_at ?? now()) : null,
                // Le hook `saving` de User date la selection de l'instant :
                // a l'import, chaque compte selectionne prenait l'heure de
                // son paquet, et les derniers us_id passaient en tete de
                // l'accueil. La vraie date vient de inc_stats (migrateStats).
                'home_selection_at' => null,
            ]);
            $user->save();
            $repris++;
        }

        $this->counts['users'] = $repris;

        return $rows;
    }

    public function migrateBookSettings(LegacyUserResolver $resolver): void
    {
        $themes = config('categories.legacy_theme_map');
        $count = 0;

        foreach ($this->legacyChunks('inc_user_pref', 'us_id', $resolver->legacyIds()) as $row) {
            $userId = $resolver->fromLegacyId((int) $row->us_id);

            if ($userId === null) {
                continue;
            }

            // Demande de selection : 72 000 comptes portent la date de mise
            // en service du champ (2019-09-24 10:20:57), pas une vraie demande.
            $demande = $row->us_formule_ask_date ?? null;
            if ($demande && ! str_starts_with($demande, '2019-09-24 10:20:57') && ($date = $this->date($demande))) {
                User::whereKey($userId)->update(['selection_requested_at' => $date]);
            }

            BookSetting::updateOrCreate(
                ['user_id' => $userId],
                [
                    'legacy_id' => $row->us_pref_id,
                    'title' => LegacyText::clean($row->us_pf_nom),
                    'description' => LegacyText::clean($row->us_pf_descp),
                    'description_mobile' => LegacyText::clean($row->us_pf_descp_mobile),
                    'experience' => LegacyText::clean($row->us_pf_experience),
                    'footer' => LegacyText::clean($row->us_pf_piedpage),

                    'theme' => $theme = $themes[mb_strtolower(trim((string) $row->us_pf_version_web))] ?? 'mdl_2014_responsive',
                    'theme_settings' => $this->themeSettings($row, $theme),
                    'theme_home_image' => $row->us_pf_ultra_zen_2020_visuel_accueil
                        ?: $row->us_pf_zoom2016_visuel_accueil
                        ?: $row->us_pf_grid2015_visuel_accueil
                        ?: $row->us_pf_clas2015_visuel_accueil
                        ?: null,

                    'background_image' => $row->us_pf_bg ?: null,
                    'background_color' => $row->us_pf_fdcoul ?: null,
                    'background_mode' => $row->us_pf_fdoption ?: null,
                    'is_centered' => $row->us_pf_center === '1',
                    'thumbnail' => $row->us_pf_img_vignette ?: null,
                    'bio_photo' => $row->us_pf_img_photo_bio ?: null,

                    // us_pf_css ne contient pas de CSS mais les mots-cles
                    // du book : voir la migration de renommage.
                    'keywords' => MotsCles::normaliser(LegacyText::clean($row->us_pf_css)),
                    'custom_js' => $row->us_pf_js ?: null,
                    'analytics_id' => $row->us_pf_analytic ?: null,

                    'diffuse_ub' => $row->us_pf_diff_ub === 'true',
                    'diffuse_web' => $row->us_pf_diff_web === 'true',
                    'diffuse_newsletter' => $row->us_pf_diff_newsletter === 'true',
                    'diffuse_availability' => $row->us_pf_diff_dispo === 'true',

                    'legacy_payload' => $this->legacyThemePayload($row),
                ],
            );

            $count++;
        }

        $this->counts['book_settings'] = $count;
    }

    /**
     * Rubriques des themes 2012 et suivants (`ub2_gal_rub`).
     *
     * `rub_id_categorie` en fait trois choses distinctes : 2 = galerie du
     * portfolio, 1 = pages d'accueil, 3 = pages (bio, actualites). Seules
     * les galeries portent des visuels ; les deux autres portent des pages
     * de texte, reprises comme sections (voir migratePages).
     */
    public function migrateGalleries(LegacyUserResolver $resolver): void
    {
        $count = 0;
        $sections = 0;

        foreach ($this->legacyChunks('ub2_gal_rub', 'rub_id_us', $resolver->legacyIds()) as $row) {
            $userId = $resolver->fromLegacyId((int) $row->rub_id_us);

            if ($userId === null) {
                continue;
            }

            $kind = self::KIND_RUBRIQUE[(int) $row->rub_id_categorie] ?? null;

            if ($kind !== null) {
                BookSection::updateOrCreate(
                    ['legacy_source' => 'ub2_gal_rub', 'legacy_id' => $row->rub_id],
                    [
                        'user_id' => $userId,
                        'kind' => $kind,
                        'title' => LegacyText::clean($row->rub_nom) ?: 'Sans titre',
                        'slug' => Str::slug(LegacyText::clean($row->rub_nom) ?? '') ?: null,
                        'is_published' => $row->rub_publier === 'publie',
                        'is_private' => false,
                        'position' => max(0, (int) $row->rub_ordre_rub),
                        'color' => $row->rub_coul ?: null,
                        'page_order' => $this->orderList($row->rub_ordre_img),
                    ],
                );

                // Un import anterieur l'avait prise pour une galerie.
                Gallery::where('legacy_id', $row->rub_id)->delete();

                $sections++;

                continue;
            }

            $galerie = Gallery::updateOrCreate(
                ['legacy_id' => $row->rub_id],
                [
                    'user_id' => $userId,
                    'name' => LegacyText::clean($row->rub_nom) ?: 'Sans titre',
                    'slug' => Str::slug(LegacyText::clean($row->rub_nom) ?? '') ?: null,
                    'status' => $this->status($row->rub_publier),
                    'position' => max(0, (int) $row->rub_ordre_rub),
                    'color' => $row->rub_coul ?: null,
                    'media_order' => $this->orderList($row->rub_ordre_img),
                ],
            );

            // created_at hors $fillable : pose a part (voir migrateUsers).
            $galerie->forceFill(['created_at' => $this->date($row->rub_date_crea) ?? $galerie->created_at])->save();

            $count++;
        }

        // Le parent est resolu apres coup : l'ordre des lignes ne garantit pas
        // que la rubrique parente soit deja creee.
        $this->linkGalleryParents($resolver);

        $this->counts['galleries'] = $count;
        $this->counts['rubriques_de_pages'] = $sections;
    }

    public function migrateMedia(LegacyUserResolver $resolver): void
    {
        // Restreint au paquet : 239 000 rubriques en production.
        $galleries = Gallery::whereNotNull('legacy_id')->whereIn('user_id', $resolver->localIds())->pluck('id', 'legacy_id');
        $sectionsDePages = BookSection::where('legacy_source', 'ub2_gal_rub')->whereIn('user_id', $resolver->localIds())->pluck('id', 'legacy_id');
        $count = 0;
        $pages = 0;

        $orphans = 0;

        foreach ($this->legacyChunks('ub2_gal_img', 'img_id_us', $resolver->legacyIds()) as $row) {
            $userId = $resolver->fromLegacyId((int) $row->img_id_us);

            if ($userId === null) {
                continue;
            }

            /*
             | Une ligne d'une rubrique d'accueil ou de pages est une page de
             | texte : son contenu est dans `img_html`, elle n'a pas de
             | fichier. L'import initial la confondait avec un visuel
             | fantome et l'ecartait.
             */
            if ($section = $sectionsDePages->get((int) $row->fk_rub_id)) {
                BookArticle::updateOrCreate(
                    ['legacy_source' => 'ub2_gal_img', 'legacy_id' => $row->img_id],
                    [
                        'user_id' => $userId,
                        'book_section_id' => $section,
                        'title' => LegacyText::clean($row->img_titre) ?: 'Sans titre',
                        'slug' => Str::slug(LegacyText::clean($row->img_titre) ?? '') ?: null,
                        'body' => LegacyText::clean($row->img_html),
                        'image' => trim((string) $row->img_fichier) ?: null,
                        'status' => $this->status($row->img_publier) === 'published' ? 'published' : 'draft',
                        'published_at' => $this->date($row->img_date_crea),
                    ],
                );

                $pages++;

                continue;
            }

            // Les lignes restantes sans nom de fichier sont des enregistrements
            // fantomes, sans visuel associe. On ne les reprend pas.
            if (trim((string) $row->img_fichier) === '') {
                $orphans++;

                continue;
            }

            $media = Media::updateOrCreate(
                ['legacy_id' => $row->img_id],
                [
                    'user_id' => $userId,
                    'gallery_id' => $galleries->get((int) $row->fk_rub_id),
                    'filename' => $row->img_fichier,
                    'title' => LegacyText::clean($row->img_titre),
                    'alt' => LegacyText::clean($row->img_titre_alt),
                    'link' => $row->img_link ?: null,
                    'description' => LegacyText::clean($row->img_desc),
                    'mime' => $row->img_type ?: null,
                    'size' => max(0, (int) $row->img_poids),
                    'status' => $this->status($row->img_publier),
                ],
            );

            // created_at hors $fillable : pose a part (voir migrateUsers).
            $media->forceFill(['created_at' => $this->date($row->img_date_crea) ?? $media->created_at])->save();

            $count++;
        }

        $this->counts['media'] = $count;
        $this->counts['pages_de_texte'] = $pages;
        $this->counts['visuels_sans_fichier_ignores'] = $orphans;
    }

    public function migrateContent(LegacyUserResolver $resolver): void
    {
        $sections = 0;

        foreach ($this->legacyChunks('bn_ultranews_rub', 'id_util', $resolver->legacyIds()) as $row) {
            $userId = $resolver->fromLegacyId((int) $row->id_util);

            if ($userId === null) {
                continue;
            }

            BookSection::updateOrCreate(
                ['legacy_source' => 'bn_ultranews_rub', 'legacy_id' => $row->id],
                [
                    'user_id' => $userId,
                    'kind' => 'news',
                    'title' => LegacyText::clean($row->rub_titre) ?: 'Sans titre',
                    'slug' => Str::slug(LegacyText::clean($row->rub_titre) ?? '') ?: null,
                    // `is_published` : le renommage de la phase 3
                    // (us_affhome -> in_home_selection) avait atteint cette
                    // ligne par erreur. BookSection n'a pas cette colonne ;
                    // la valeur etait ignoree et toute rubrique reimportee
                    // restait non publiee.
                    'is_published' => $row->rub_pub === 'on',
                    'is_private' => $row->rub_type === 'prive',
                    'position' => max(0, (int) $row->rub_ord),
                    'color' => $row->rub_color ?: null,
                    'icon' => $row->rub_icon ?: null,
                ],
            );

            $sections++;
        }

        $this->linkSectionParents($resolver);

        $sectionIds = BookSection::where('legacy_source', 'bn_ultranews_rub')
            ->whereIn('user_id', $resolver->localIds())->pluck('id', 'legacy_id');
        $articles = 0;

        foreach ($this->parLots($this->legacyChunks('bn_ultranews_art', 'id_util', $resolver->legacyIds())) as $lot) {
            // Le corps des articles vit dans une table separee (694 000
            // lignes) : lu par lot, jamais en entier.
            $bodies = DB::connection('legacy')->table('bn_ultrabook_art_portefolio')
                ->whereIn('id_art', array_map(fn ($row) => $row->id, $lot))
                ->pluck('art_texte', 'id_art');

            foreach ($lot as $row) {
                $userId = $resolver->fromLegacyId((int) $row->id_util);

                if ($userId === null) {
                    continue;
                }

                BookArticle::updateOrCreate(
                    ['legacy_source' => 'bn_ultranews_art', 'legacy_id' => $row->id],
                    [
                        'user_id' => $userId,
                        'book_section_id' => $sectionIds->get((int) $row->id_rub),
                        'title' => LegacyText::clean($row->art_titre) ?: 'Sans titre',
                        'slug' => Str::slug(LegacyText::clean($row->art_titre) ?? '') ?: null,
                        'body' => $this->reecrireChemins(LegacyText::clean($bodies->get($row->id))),
                        'status' => match ($row->art_pub) {
                            'on' => 'published',
                            'arch' => 'archived',
                            default => 'draft',
                        },
                        'position' => max(0, (int) $row->art_ordre),
                        'published_at' => $this->date($row->art_date),
                    ],
                );

                $articles++;
            }
        }

        $this->counts['book_sections'] = $sections;
        $this->counts['book_articles'] = $articles;
    }

    /**
     * Blocs de texte editables en place (`ub2_edit_txt`), par theme.
     *
     * Indexes par login et non par identifiant de compte. Le JSON est
     * conserve tel quel : ses cles (`cont_menu_gauche2`…) sont celles que
     * lisent les gabarits.
     */
    public function migrateThemeTexts(LegacyUserResolver $resolver): void
    {
        // Cle : l'ANCIEN login (ed_us_login), qui peut differer du nouveau
        // (a_menguy -> a-menguy).
        $logins = collect();

        foreach (array_chunk($resolver->legacyIds(), self::LOT) as $ids) {
            DB::connection('legacy')->table('inc_user')->whereIn('us_id', $ids)
                ->pluck('us_login', 'us_id')
                ->each(function ($login, $usId) use ($logins, $resolver) {
                    if ($userId = $resolver->fromLegacyId((int) $usId)) {
                        $logins->put(mb_strtolower($login), $userId);
                    }
                });
        }

        $textes = [];

        foreach ($logins->keys()->chunk(self::LOT) as $lot) {
            DB::connection('legacy')->table('ub2_edit_txt')
                ->whereIn('ed_us_login', $lot->values())
                ->orderBy('id')
                ->each(function ($row) use (&$textes, $logins) {
                    $userId = $logins->get(mb_strtolower((string) $row->ed_us_login));
                    $blocs = json_decode((string) $row->ed_dom_txt, true);

                    if ($userId && is_array($blocs)) {
                        // Les blocs sont stockes encodes en entites HTML ; le
                        // legacy les decodait a l'affichage
                        // (mod_ptf_2014_ed_champs_modif).
                        $textes[$userId][$row->ed_mdl] = array_map(
                            fn ($valeur) => is_string($valeur) ? html_entity_decode($valeur, ENT_QUOTES, 'UTF-8') : $valeur,
                            $blocs,
                        );
                    }
                });
        }

        foreach ($textes as $userId => $parTheme) {
            BookSetting::where('user_id', $userId)->update(['theme_texts' => json_encode($parTheme)]);
        }

        $this->counts['textes_de_theme'] = count($textes);
    }

    public function migrateMessaging(LegacyUserResolver $resolver): void
    {
        $conversations = 0;
        $messages = 0;

        // Formulaire de contact simple : une conversation, un message.
        foreach ($this->rows('ub2_contact_form') as $row) {
            $userId = $resolver->resolve($row->cf_us_id);

            if ($userId === null) {
                continue;
            }

            $conversation = Conversation::withTrashed()->updateOrCreate(
                ['legacy_id' => $row->cf_id],
                [
                    'user_id' => $userId,
                    'channel' => 'contact',
                    'sender_name' => LegacyText::clean($row->cf_nom),
                    'sender_email' => $row->cf_mail ?: null,
                    'last_message_at' => $this->date($row->cf_date),
                    'deleted_at' => $row->cf_del === 'true' ? now() : null,
                ],
            );

            Message::updateOrCreate(
                ['legacy_id' => $row->cf_id],
                [
                    'conversation_id' => $conversation->id,
                    'from_owner' => false,
                    'body' => LegacyText::clean($row->cf_message) ?? '',
                    'read_at' => $row->cf_lu === 'true' ? $this->date($row->cf_date) : null,
                    'created_at' => $this->date($row->cf_date) ?? now(),
                ],
            );

            $conversations++;
            $messages++;
        }

        // Messagerie intermediee : mf_id_parent enchaine les reponses.
        foreach ($this->rows('ub2_intermediate_form')->sortBy('mf_id') as $row) {
            $userId = $resolver->resolve($row->mf_us_id);

            if ($userId === null) {
                continue;
            }

            $rootId = (int) $row->mf_id_parent ?: (int) $row->mf_id;
            $estRacine = $rootId === (int) $row->mf_id;

            /*
             | L'identite de l'emetteur (nom, mail, societe, telephone),
             | le sujet et le detail de la demande ne viennent que du
             | message racine : une reponse du createur (mf_is_my_msg)
             | n'a pas ces champs renseignes, et les ecraser a chaque
             | reponse viderait la conversation de son expediteur.
             */
            $conversation = $estRacine
                ? Conversation::withTrashed()->updateOrCreate(
                    ['legacy_id' => $rootId],
                    [
                        'user_id' => $userId,
                        'channel' => 'intermediate',
                        'subject' => LegacyText::clean($row->mf_action),
                        'request_detail' => LegacyText::clean($row->mf_request_detail),
                        'sender_name' => $this->sansMarqueurSpam(LegacyText::clean($row->mf_nom)),
                        'sender_company' => LegacyText::clean($row->mf_societe),
                        'sender_email' => $row->mf_mail ?: null,
                        'sender_phone' => $row->mf_tel ?: null,
                        // Jetons conserves pour la trace seulement : le schema
                        // de 2019 les rendait derivables l'un de l'autre, les
                        // anciens liens ne sont plus honores.
                        'legacy_token' => $row->mf_token ?: null,
                        'selector' => $row->mf_selector ?: null,
                        'is_spam' => $this->estSpamLegacy($row),
                        'book_image' => $row->mf_book_visuel ?: null,
                        'last_message_at' => $this->date($row->mf_update) ?? $this->date($row->mf_date),
                        'deleted_at' => $row->mf_del === 'true' ? now() : null,
                    ],
                )
                : Conversation::withTrashed()->where('legacy_id', $rootId)->first()
                    // Racine hors echantillon : la reponse cree la conversation a minima.
                    ?? Conversation::create(['legacy_id' => $rootId, 'user_id' => $userId, 'channel' => 'intermediate']);

            if (! $estRacine) {
                $conversation->forceFill([
                    'last_message_at' => $this->date($row->mf_update) ?? $this->date($row->mf_date) ?? $conversation->last_message_at,
                    'deleted_at' => $row->mf_del === 'true' ? now() : $conversation->deleted_at,
                ])->save();
            }

            Message::updateOrCreate(
                ['legacy_id' => $row->mf_id],
                [
                    'conversation_id' => $conversation->id,
                    'from_owner' => $row->mf_is_my_msg === 'true',
                    'body' => $this->sansBanniereSpam(LegacyText::clean($row->mf_message)) ?? '',
                    'ip' => Str::limit((string) $row->mf_ip, 45, ''),
                    'read_at' => $row->mf_lu === 'true' ? $this->date($row->mf_date) : null,
                    'created_at' => $this->date($row->mf_date) ?? now(),
                ],
            );

            $conversations++;
            $messages++;
        }

        $this->counts['conversations'] = Conversation::count();
        $this->counts['messages'] = $messages;
        $this->counts['messages_hors_echantillon'] = $resolver->outOfSampleCount();
        $this->counts['references_orphelines'] = $resolver->orphanCount();
    }

    /**
     * Le legacy n'ecrivait une facture qu'une fois le paiement recu :
     * `fac_stats` vaut 0 ou NULL sur les 7 886 lignes et ne dit rien. Seule
     * une annulation, notee a la main dans la trace de paiement, en fait
     * une facture non due.
     */
    private function statutFacture(?string $trace): string
    {
        return str_starts_with(strtoupper(trim((string) $trace)), 'ANNULATI') ? 'cancelled' : 'paid';
    }

    /** Moyen de paiement, deduit de la trace brute (objet Payplug, IPN PayPal serialisee, note manuelle). */
    private function moyenPaiement(?string $trace): ?string
    {
        $trace = strtolower(ltrim((string) $trace, '= '));

        return match (true) {
            $trace === '' => null,
            str_starts_with($trace, 'payplug') => 'payplug',
            str_starts_with($trace, 'a:'), str_starts_with($trace, 'paypal') => 'paypal',
            str_starts_with($trace, 'cheque') => 'cheque',
            str_starts_with($trace, 'virement') => 'virement',
            default => 'autre',
        };
    }

    public function migrateInvoices(LegacyUserResolver $resolver): void
    {
        $count = 0;

        foreach ([['ub2_fac', 'ub'], ['df2_fac', 'df']] as [$table, $brand]) {
            foreach ($this->legacyChunks($table, 'fac_us_id', $resolver->legacyIds()) as $row) {
                $userId = $resolver->fromLegacyId((int) $row->fac_us_id);

                if ($userId === null) {
                    continue;
                }

                Invoice::updateOrCreate(
                    ['legacy_source' => $table, 'legacy_id' => $row->fac_id],
                    [
                        'user_id' => $userId,
                        'brand' => $brand,
                        'number' => $brand.'-'.$row->fac_id,
                        'label' => LegacyText::clean($row->fac_titre),
                        'designation' => LegacyText::clean($row->fac_designation),
                        'amount' => (float) $row->fac_total,
                        'vat' => (float) $row->fac_tva,
                        'status' => $statut = $this->statutFacture($row->fac_paypaldata),
                        'gateway' => $this->moyenPaiement($row->fac_paypaldata),
                        'gateway_payload' => $this->json($row->fac_paypaldata),
                        'issued_at' => $this->date($row->fac_date),
                        'paid_at' => $statut === 'paid' ? $this->date($row->fac_date) : null,
                    ],
                );

                $count++;
            }
        }

        $this->counts['invoices'] = $count;
    }

    public function migrateStats(LegacyUserResolver $resolver): void
    {
        $count = 0;

        foreach ($this->legacyChunks('inc_stats', 'st_id_user', $resolver->legacyIds()) as $row) {
            $userId = $resolver->fromLegacyId((int) $row->st_id_user);
            $date = $this->date($row->st_public_date) ?? $this->date($row->st_admin_date);

            // Date de selection : le legacy triait l'accueil sur
            // st_selection_date DESC. Sans evenement, pour que le hook de
            // User ne la remplace pas par l'heure de l'import.
            if ($userId !== null && $selection = $this->date($row->st_selection_date)) {
                User::whereKey($userId)->where('in_home_selection', true)
                    ->where(fn ($q) => $q->whereNull('home_selection_at')->orWhere('home_selection_at', '<', $selection))
                    ->update(['home_selection_at' => $selection]);
            }

            if ($userId === null || $date === null) {
                continue;
            }

            // Derniere connexion du createur a son espace.
            if ($acces = $this->date($row->st_admin_date)) {
                User::whereKey($userId)
                    ->where(fn ($q) => $q->whereNull('last_login_at')->orWhere('last_login_at', '<', $acces))
                    ->update(['last_login_at' => $acces]);
            }

            VisitStat::updateOrCreate(
                ['user_id' => $userId, 'date' => $date->toDateString()],
                [
                    'public_views' => max(0, (int) $row->st_public_nb),
                    'admin_views' => max(0, (int) $row->st_admin_nb),
                ],
            );

            $count++;
        }

        $this->counts['visit_stats'] = $count;
    }

    /**
     * Parrainages (ub2_parrainage) : `par_us_send` est le parrain,
     * `par_us_id` le filleul. Seuls ceux dont les deux comptes sont repris.
     */
    public function migrateReferrals(LegacyUserResolver $resolver): void
    {
        $count = 0;

        foreach ($this->legacyChunks('ub2_parrainage', 'par_us_send', $resolver->legacyIds()) as $row) {
            $parrain = $resolver->fromLegacyId((int) $row->par_us_send);
            $filleul = $resolver->fromLegacyId((int) $row->par_us_id);

            if ($parrain === null || $filleul === null) {
                continue;
            }

            Referral::updateOrCreate(['legacy_id' => $row->par_id], [
                'sponsor_id' => $parrain,
                'referred_id' => $filleul,
                'status' => 'confirmed',
                'confirmed_at' => $this->date($row->par_date),
            ]);

            $count++;
        }

        $this->counts['referrals'] = $count;
    }

    /**
     * Codes promo (ub2_codepromo) : chacun credite un nombre de mois de
     * formule, une seule fois. `discount` porte ce nombre de mois.
     */
    public function migratePromoCodes(): void
    {
        $count = 0;

        foreach ($this->rows('ub2_codepromo') as $row) {
            PromoCode::updateOrCreate(['legacy_id' => $row->pro_id], [
                'code' => strtoupper(trim($row->pro_code)),
                'discount' => (int) $row->pro_nbmois,
                'discount_type' => PromoCode::MOIS,
                'max_uses' => 1,
                'uses' => $row->pro_etat === 'on' ? 0 : 1,
                'is_active' => $row->pro_etat === 'on',
            ]);

            $count++;
        }

        $this->counts['promo_codes'] = $count;
    }

    /**
     * Abonnes de la newsletter de l'ancien site (nl_newsletter), seuls
     * abonnes repris : `--fresh` vide la table avant. Une adresse invalide
     * ou deja vue est ecartee et comptee ; `nl_envoi_etat = off` donne un
     * abonne desinscrit. Tous sont rattaches a Ultra-book, la seule marque
     * qui avait une newsletter.
     */
    public function migrateNewsletter(): void
    {
        $repris = 0;
        $invalides = 0;
        $doublons = 0;
        $vues = [];

        foreach ($this->rows('nl_newsletter') as $row) {
            $email = mb_strtolower(trim((string) $row->nl_mail));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalides++;

                continue;
            }

            if (isset($vues[$email])) {
                $doublons++;

                continue;
            }

            $vues[$email] = true;
            $inscription = $this->date($row->nl_date_insc) ?? now();

            $abonne = NewsletterMail::updateOrCreate(['email' => $email], [
                'brand' => 'ub',
                'ip' => $row->nl_ip ?: null,
                'desabonne_at' => $row->nl_envoi_etat === 'off' ? $inscription : null,
            ]);
            $abonne->forceFill(['created_at' => $inscription])->saveQuietly();

            $repris++;
        }

        $this->counts['abonnes_newsletter'] = $repris;
        $this->counts['abonnes_adresse_invalide'] = $invalides;
        $this->counts['abonnes_doublons'] = $doublons;
    }

    /**
     * Derniere offre « promo auto 6 mois » de chaque createur (inc_marketing) :
     * elle cale le rythme des offres suivantes. Les autres types (soldes,
     * Black Friday) ne dependaient pas de l'historique.
     */
    public function migrateMarketingOffers(LegacyUserResolver $resolver): void
    {
        $count = 0;

        $dernieres = collect(array_chunk($resolver->legacyIds(), self::LOT))->flatMap(
            fn (array $ids) => DB::connection('legacy')->table('inc_marketing')
                ->where('us_type', MarketingOffer::PROMO_6_MOIS)
                ->whereIn('us_id', $ids)
                ->selectRaw('MAX(id) AS id, us_id, MAX(us_date) AS us_date')
                ->groupBy('us_id')->get()
        );

        foreach ($dernieres as $row) {
            $userId = $resolver->fromLegacyId((int) $row->us_id);

            if ($userId === null || ! ($date = $this->date($row->us_date))) {
                continue;
            }

            MarketingOffer::updateOrCreate(['legacy_id' => $row->id], [
                'user_id' => $userId,
                'type' => MarketingOffer::PROMO_6_MOIS,
                'offered_at' => $date,
            ]);

            $count++;
        }

        $this->counts['marketing_offers'] = $count;
    }

    // ---------------------------------------------------------------- outils

    /**
     * Parcourt une table legacy restreinte aux comptes repris, par lots
     * d'identifiants : un whereIn sur 60 000 comptes depasserait la limite
     * de 65 535 parametres de MySQL.
     */
    private function legacyChunks(string $table, string $userColumn, array $legacyIds): \Generator
    {
        foreach (array_chunk($legacyIds, self::LOT) as $ids) {
            foreach (DB::connection('legacy')->table($table)->whereIn($userColumn, $ids)->cursor() as $row) {
                yield $row;
            }
        }
    }

    /**
     * Regroupe un flux de lignes en lots de self::LOT.
     *
     * @return \Generator<int, list<object>>
     */
    private function parLots(iterable $lignes): \Generator
    {
        $lot = [];

        foreach ($lignes as $ligne) {
            $lot[] = $ligne;

            if (count($lot) === self::LOT) {
                yield $lot;
                $lot = [];
            }
        }

        if ($lot !== []) {
            yield $lot;
        }
    }

    private function reecrireChemins(?string $html): ?string
    {
        return $this->logins ? $this->logins->reecrireChemins($html) : $html;
    }

    private function rows(string $table): Collection
    {
        return collect(DB::connection('legacy')->table($table)->get());
    }

    private function linkGalleryParents(LegacyUserResolver $resolver): void
    {
        // Parent et enfant appartiennent au meme compte : le paquet suffit.
        $map = Gallery::whereNotNull('legacy_id')->whereIn('user_id', $resolver->localIds())->pluck('id', 'legacy_id');

        foreach ($map->keys()->chunk(self::LOT) as $lot) {
            DB::connection('legacy')->table('ub2_gal_rub')
                ->whereIn('rub_id', $lot->values())
                ->where('rub_id_parent', '>', 0)
                ->orderBy('rub_id')
                ->each(function ($row) use ($map) {
                    if ($parent = $map->get((int) $row->rub_id_parent)) {
                        Gallery::where('legacy_id', $row->rub_id)->update(['parent_id' => $parent]);
                    }
                });
        }
    }

    private function linkSectionParents(LegacyUserResolver $resolver): void
    {
        $map = BookSection::where('legacy_source', 'bn_ultranews_rub')
            ->whereIn('user_id', $resolver->localIds())->pluck('id', 'legacy_id');

        foreach ($map->keys()->chunk(self::LOT) as $lot) {
            DB::connection('legacy')->table('bn_ultranews_rub')
                ->whereIn('id', $lot->values())
                ->where('id_parent', '>', 0)
                ->orderBy('id')
                ->each(function ($row) use ($map) {
                    if ($parent = $map->get((int) $row->id_parent)) {
                        BookSection::where('legacy_source', 'bn_ultranews_rub')->where('legacy_id', $row->id)->update(['parent_id' => $parent]);
                    }
                });
        }
    }

    /** Un compte sans adresse valide recoit une adresse locale inexploitable. */
    private function email(object $row): string
    {
        $email = trim((string) $row->us_mail);

        return filter_var($email, FILTER_VALIDATE_EMAIL)
            ? mb_strtolower($email)
            : mb_strtolower($row->us_login).'@invalide.ultra-book.test';
    }

    /**
     * Le legacy chiffre les mots de passe de facon reversible : on les
     * rehashe en bcrypt. Les rares valeurs illisibles sont remplacees par
     * une valeur aleatoire, le compte passant alors par « mot de passe oublie ».
     *
     * Cout bcrypt 10 et non celui de la config (12) : 60 ms au lieu de 310 ms
     * par compte sur O2switch, soit 1 h au lieu de 6 h pour 67 000 comptes.
     * IdentifierCompte le remet au cout de la config a la premiere connexion.
     */
    private function password(object $row): string
    {
        $plain = LegacyPassword::decrypt($row->us_pass);

        if ($plain === null) {
            $this->lostPasswords++;
            $plain = Str::random(32);
        }

        return Hash::make($plain, ['rounds' => self::COUT_BCRYPT_REPRISE]);
    }

    private function categoryId(?string $type): ?int
    {
        $slug = config('categories.legacy_map')[mb_strtolower(trim((string) $type))] ?? 'autre';

        return $this->categories->get($slug);
    }

    private function locale(?string $lang): string
    {
        return match (substr((string) $lang, 0, 2)) {
            'en' => 'en',
            'ja' => 'ja',
            default => 'fr',
        };
    }

    private function status(?string $value): string
    {
        return match ($value) {
            'publie' => 'published',
            'efface' => 'deleted',
            default => 'draft',
        };
    }

    private function date(?string $value): ?Carbon
    {
        if (empty($value) || str_starts_with($value, '0000')) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /** rub_ordre_img est une liste d'ids separes par des virgules. */
    /**
     * rub_ordre_img : identifiants separes par des tirets bas, avec un tiret
     * en tete et en fin (`_1548552_1548542_…_`). La version precedente
     * decoupait sur la virgule : intval() du premier segment rendait 0, et
     * l'ordre de toutes les galeries etait perdu.
     *
     * La liste est conservee telle quelle, doublons compris : l'algorithme
     * d'affichage du legacy (usbook2011_img_ordre) en depend.
     */
    private function orderList(?string $value): ?array
    {
        $ids = array_map('intval', preg_split('/[_,]/', (string) $value, -1, PREG_SPLIT_NO_EMPTY));

        return $ids ?: null;
    }

    private function json(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : ['raw' => LegacyText::clean($value)];
    }

    /** Configuration du theme actif, extraite de la colonne du bon millesime. */
    /**
     * Configuration du theme **actif**.
     *
     * La version precedente retenait la premiere colonne non vide dans un
     * ordre fixe : un createur passe de Zoom a Grid recuperait ses reglages
     * Zoom, puisque le legacy conserve la configuration de chaque theme
     * essaye. Seule compte celle du theme en cours.
     */
    private function themeSettings(object $row, string $theme): ?array
    {
        $colonne = config('categories.legacy_theme_settings_column.'.$theme);

        return $colonne && ! empty($row->{$colonne}) ? $this->json($row->{$colonne}) : null;
    }

    /** Toutes les configurations de themes, conservees sans perte. */
    private function legacyThemePayload(object $row): array
    {
        return collect((array) $row)
            ->filter(fn ($value, string $key) => str_starts_with($key, 'us_pf_conf')
                || str_starts_with($key, 'us_pf_visuel')
                || str_starts_with($key, 'us_pf_clas')
                || str_starts_with($key, 'us_pf_grid')
                || str_starts_with($key, 'us_pf_zoom')
                || str_starts_with($key, 'us_pf_version')
                // Modele classique : les cinq visuels du bandeau (us_pf_img1…5).
                || preg_match('/^us_pf_img[1-5]$/', $key))
            ->reject(fn ($value) => $value === null || $value === '')
            ->map(fn ($value) => LegacyText::clean((string) $value))
            ->all();
    }

    /**
     * Le legacy ne portait pas d'indicateur de spam : il prefixait le nom de
     * l'expediteur et le corps du message. Le marqueur est remonte en
     * colonne, et la donnee rendue a son etat d'origine.
     */
    private function estSpamLegacy(object $row): bool
    {
        return str_contains((string) $row->mf_nom, '[ ALERTE ]')
            || str_contains((string) $row->mf_message, 'ALERTE : message de type SPAM');
    }

    private function sansMarqueurSpam(?string $nom): ?string
    {
        return $nom === null ? null : (trim(str_replace('[ ALERTE ]', '', $nom)) ?: null);
    }

    private function sansBanniereSpam(?string $corps): ?string
    {
        if ($corps === null || ! str_contains($corps, 'ALERTE : message de type SPAM')) {
            return $corps;
        }

        return trim(preg_replace('#^<strong[^>]*>.*?</strong><br/>\s*#s', '', $corps) ?? $corps);
    }
}
