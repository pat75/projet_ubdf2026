<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mise en production : analyse la base legacy importee (connexion
 * `legacy`) AVANT la reprise, sans rien ecrire nulle part.
 *
 * Quatre controles, tous rapportes table par table et champ par champ :
 *
 *  - integrite : tables attendues presentes, lignes par table ;
 *  - encodage : double encodage (« Ã© ») et caractere de remplacement
 *    (U+FFFD) dans les champs texte repris. Les tables sont declarees
 *    latin1 mais contiennent de l'UTF-8 brut (voir config/database.php) :
 *    un dump mal exporte les abime ;
 *  - tables que la reprise ne lit pas : leurs lignes ne passeront pas ;
 *  - troncatures : valeurs plus longues que la colonne du schema 2026 qui
 *    les recevra. Estimation haute : la reprise nettoie certains textes
 *    (LegacyText) avant de les ecrire, et compte aussi les comptes
 *    supprimes, qu'elle ne reprend pas.
 *
 * Utilisee par deploy/transfert-base.sh (sous-commande analyser).
 */
class AnalyserLegacyCommand extends Command
{
    protected $signature = 'ubdf:prod:analyser-legacy
        {--rapport= : Ecrit le rapport dans ce fichier}';

    protected $description = 'Analyse la base legacy importee avant la reprise (lecture seule)';

    /** Tables lues par la reprise (LegacyMigrator, LegacyLogins), avec leur cle. */
    private const REPRISES = [
        'inc_user' => 'us_id',
        'inc_user_pref' => 'us_pref_id',
        'ub2_gal_rub' => 'rub_id',
        'ub2_gal_img' => 'img_id',
        'bn_ultranews_rub' => 'id',
        'bn_ultranews_art' => 'id',
        'bn_ultrabook_art_portefolio' => 'id',
        'ub2_edit_txt' => 'id',
        'ub2_contact_form' => 'cf_id',
        'ub2_intermediate_form' => 'mf_id',
        'ub2_fac' => 'fac_id',
        'df2_fac' => 'fac_id',
        'inc_stats' => 'st_id',
        'ub2_parrainage' => 'par_id',
        'ub2_codepromo' => 'pro_id',
        'inc_marketing' => 'id',
        'nl_newsletter' => 'nl_id',
    ];

    /** Tables presentes dans l'ancienne prod que la reprise ne lit pas. */
    private const NON_REPRISES = [
        'bn_ultranews_art_cmsfront' => 'CMS de l\'ancien portail',
        'bn_ultranews_art_tab_cmsfront' => 'CMS de l\'ancien portail',
        'bn_ultranews_rub_cmsfront' => 'CMS de l\'ancien portail',
        'bn_ultranews_art_tab_default' => 'corps des actualites d\'avant 2015 (ancien modele ; beaucoup de textes par defaut, mais aussi de vrais contenus)',
        'inc_user_token' => 'jetons de connexion (techniques)',
        'inc_comt' => 'commentaires',
        'df2_mail_relance' => 'historique de relances Dustfolio',
        'inc_auto_selection' => 'historique des selections automatiques',
        'inc_auto_selection_user' => 'historique des selections automatiques',
        'ub2_stats_mcles' => 'statistiques de mots-cles',
        'wp_import' => 'import WordPress (magazine)',
    ];

    /**
     * Champs texte repris : `table.colonne` legacy => `table.colonne` 2026.
     * Releves dans LegacyMigrator ; seules les cibles varchar sont bornees.
     */
    private const CHAMPS = [
        'inc_user.us_login' => 'users.login',
        'inc_user.us_mail' => 'users.email',
        'inc_user.us_prenom' => 'users.firstname',
        'inc_user.us_nom' => 'users.lastname',
        'inc_user.us_societe' => 'users.company',
        'inc_user.us_statut' => 'users.status',
        'inc_user.us_adresse' => 'users.address',
        'inc_user.us_cp' => 'users.zipcode',
        'inc_user.us_ville' => 'users.city',
        'inc_user.us_pays' => 'users.country',
        'inc_user.us_telephone' => 'users.phone',
        'inc_user.us_portable' => 'users.mobile',
        'inc_user.us_http' => 'users.website',
        'inc_user.us_facebook_url' => 'users.facebook_url',
        'inc_user.us_twitter_url' => 'users.twitter_url',
        'inc_user.us_instagram_url' => 'users.instagram_url',
        'inc_user.us_domaine' => 'users.custom_domain',
        'inc_user.us_referer' => 'users.signup_referer',
        'inc_user.us_commentaire' => 'users.admin_note',
        'inc_user_pref.us_pf_nom' => 'book_settings.title',
        'inc_user_pref.us_pf_descp_mobile' => 'book_settings.description_mobile',
        'inc_user_pref.us_pf_piedpage' => 'book_settings.footer',
        'inc_user_pref.us_pf_bg' => 'book_settings.background_image',
        'inc_user_pref.us_pf_img_vignette' => 'book_settings.thumbnail',
        'inc_user_pref.us_pf_img_photo_bio' => 'book_settings.bio_photo',
        'inc_user_pref.us_pf_analytic' => 'book_settings.analytics_id',
        'ub2_gal_rub.rub_nom' => 'galleries.name',
        'ub2_gal_img.img_fichier' => 'media.filename',
        'ub2_gal_img.img_titre' => 'media.title',
        'ub2_gal_img.img_titre_alt' => 'media.alt',
        'ub2_gal_img.img_link' => 'media.link',
        'ub2_gal_img.img_type' => 'media.mime',
        'bn_ultranews_rub.rub_titre' => 'book_sections.title',
        'bn_ultranews_art.art_titre' => 'book_articles.title',
        'ub2_contact_form.cf_nom' => 'conversations.sender_name',
        'ub2_contact_form.cf_mail' => 'conversations.sender_email',
        'ub2_intermediate_form.mf_nom' => 'conversations.sender_name',
        'ub2_intermediate_form.mf_mail' => 'conversations.sender_email',
        'ub2_codepromo.pro_code' => 'promo_codes.code',
        'nl_newsletter.nl_mail' => 'newsletter_mails.email',
        'nl_newsletter.nl_ip' => 'newsletter_mails.ip',
    ];

    /** Champs texte libres controles pour l'encodage (en plus de CHAMPS). */
    private const TEXTES_LONGS = [
        'inc_user_pref.us_pf_descp',
        'inc_user_pref.us_pf_experience',
        'ub2_gal_img.img_desc',
        'ub2_intermediate_form.mf_message',
        'ub2_contact_form.cf_message',
    ];

    /** @var list<string> */
    private array $lignes = [];

    private int $alertes = 0;

    public function handle(): int
    {
        $legacy = DB::connection('legacy');
        $tables = collect($legacy->select('SHOW TABLES'))->map(fn ($t) => array_values((array) $t)[0])->all();

        $this->titre('ANALYSE DE LA BASE LEGACY '.$legacy->getDatabaseName().' — '.now()->format('Y-m-d H:i'));

        // 1) Integrite.
        $this->titre('1) INTEGRITE (lignes par table)');
        $comptes = [];

        foreach ([...array_keys(self::REPRISES), ...array_keys(self::NON_REPRISES)] as $table) {
            if (! in_array($table, $tables, true)) {
                $this->alerte(sprintf('  %-32s ABSENTE', $table));

                continue;
            }

            $comptes[$table] = $legacy->table($table)->count();
            $this->ligne(sprintf('  %-32s %s', $table, number_format($comptes[$table], 0, ',', ' ')));
        }

        if (isset($comptes['inc_user'])) {
            $vivants = $legacy->table('inc_user')->where('us_delete', 'false')->count();
            $this->ligne(sprintf('  comptes vivants : %s, supprimes (non repris) : %s',
                number_format($vivants, 0, ',', ' '), number_format($comptes['inc_user'] - $vivants, 0, ',', ' ')));
        }

        // 2) Encodage.
        $this->titre('2) ENCODAGE (double encodage « Ã© », caractere de remplacement)');
        $sain = true;

        foreach ([...array_keys(self::CHAMPS), ...self::TEXTES_LONGS] as $champ) {
            [$table, $colonne] = explode('.', $champ);

            if (! isset($comptes[$table])) {
                continue;
            }

            // Octets d'un UTF-8 reencode (C3 83 C2 / C3 83 E2 / C3 82 C2)
            // et de U+FFFD (EF BF BD), cherches tels quels.
            $suspects = $legacy->table($table)
                ->whereRaw("LOCATE(UNHEX('C383C2'), `$colonne`) > 0 OR LOCATE(UNHEX('C383E2'), `$colonne`) > 0 OR LOCATE(UNHEX('C382C2'), `$colonne`) > 0 OR LOCATE(UNHEX('EFBFBD'), `$colonne`) > 0");
            $nombre = (clone $suspects)->count();

            if ($nombre > 0) {
                $sain = false;
                $exemples = $suspects->limit(3)->pluck(self::REPRISES[$table])->implode(', ');
                $this->alerte(sprintf('  %-36s %d valeurs suspectes (id %s)', $champ, $nombre, $exemples));
            }
        }

        if ($sain) {
            $this->ligne('  aucun double encodage detecte');
        }

        // 3) Tables non reprises.
        $this->titre('3) TABLES NON REPRISES (leurs lignes ne passent pas dans le nouveau site)');

        foreach (self::NON_REPRISES as $table => $role) {
            if (($comptes[$table] ?? 0) > 0) {
                $this->alerte(sprintf('  %-32s %8s lignes  %s', $table, number_format($comptes[$table], 0, ',', ' '), $role));
            }
        }

        // 4) Troncatures.
        $this->titre('4) TRONCATURES PREVISIBLES (valeur plus longue que la colonne 2026)');
        $aucune = true;

        foreach (self::CHAMPS as $source => $cible) {
            [$table, $colonne] = explode('.', $source);
            [$tableCible, $colonneCible] = explode('.', $cible);

            $max = $this->longueur($tableCible, $colonneCible);

            if ($max === null || ! isset($comptes[$table])) {
                continue;
            }

            // Longueur en caracteres : les octets latin1 relus comme UTF-8.
            $longueur = "CHAR_LENGTH(CONVERT(BINARY `$colonne` USING utf8mb4))";
            $trop = $legacy->table($table)->whereRaw("$longueur > ?", [$max]);
            $nombre = (clone $trop)->count();

            if ($nombre > 0) {
                $aucune = false;
                $exemples = $trop->limit(3)->pluck(self::REPRISES[$table])->implode(', ');
                $plusLong = (int) $legacy->table($table)->selectRaw("MAX($longueur) AS m")->value('m');
                $this->alerte(sprintf('  %-34s -> %-30s %d valeurs > %d car. (max %d ; id %s)',
                    $source, $cible, $nombre, $max, $plusLong, $exemples));
            }
        }

        if ($aucune) {
            $this->ligne('  aucune');
        }

        $this->titre($this->alertes === 0
            ? 'SYNTHESE : aucun risque detecte.'
            : "SYNTHESE : {$this->alertes} point(s) a lire ci-dessus avant de convertir.");

        if ($fichier = $this->option('rapport')) {
            @mkdir(dirname($fichier), 0o755, true);
            file_put_contents($fichier, implode(PHP_EOL, $this->lignes).PHP_EOL);
            $this->components->info("Rapport : {$fichier}");
        }

        return self::SUCCESS;
    }

    /** Longueur d'une colonne varchar du schema 2026, null si non bornee ou absente. */
    private function longueur(string $table, string $colonne): ?int
    {
        static $colonnes = [];

        $colonnes[$table] ??= Schema::hasTable($table)
            ? collect(Schema::getColumns($table))->keyBy('name')->all()
            : [];

        $type = $colonnes[$table][$colonne]['type'] ?? '';

        return preg_match('/^(?:var)?char\((\d+)\)/', $type, $m) ? (int) $m[1] : null;
    }

    private function titre(string $texte): void
    {
        $this->lignes[] = '';
        $this->lignes[] = $texte;
        $this->newLine();
        $this->line("<options=bold>{$texte}</>");
    }

    private function ligne(string $texte): void
    {
        $this->lignes[] = $texte;
        $this->line($texte);
    }

    private function alerte(string $texte): void
    {
        $this->alertes++;
        $this->lignes[] = '! '.ltrim($texte);
        $this->line("<fg=yellow>{$texte}</>");
    }
}
