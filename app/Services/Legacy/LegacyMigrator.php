<?php

namespace App\Services\Legacy;

use App\Models\BookArticle;
use App\Models\BookSection;
use App\Models\BookSetting;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Gallery;
use App\Models\Invoice;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
use App\Models\VisitStat;
use App\Support\LegacyPassword;
use App\Support\LegacyText;
use App\Support\MotsCles;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
    private Collection $categories;

    /** @var array<string, int> */
    private array $counts = [];

    /** Mots de passe illisibles, remplaces par une valeur aleatoire. */
    private int $lostPasswords = 0;

    public function __construct(private readonly LegacySampler $sampler)
    {
        $this->categories = Category::pluck('id', 'slug');
    }

    /** @return array<string, int> */
    public function counts(): array
    {
        return $this->counts + ['mots_de_passe_perdus' => $this->lostPasswords];
    }

    public function migrateUsers(): Collection
    {
        $rows = $this->sampler->pick();

        foreach ($rows as $row) {
            $user = User::updateOrCreate(
                ['legacy_id' => $row->us_id],
                [
                    'login' => mb_strtolower(trim($row->us_login)),
                    'email' => $this->email($row),
                    'password' => $this->password($row),
                    'email_verified_at' => $row->us_confirm_mail === 'true' ? now() : null,
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

                    'is_published' => $row->us_affhome === 'true',
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
                    'created_at' => $this->date($row->us_date) ?? now(),
                ],
            );

            $user->save();
        }

        $this->counts['users'] = $rows->count();

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

            BookSetting::updateOrCreate(
                ['user_id' => $userId],
                [
                    'legacy_id' => $row->us_pref_id,
                    'title' => LegacyText::clean($row->us_pf_nom),
                    'description' => LegacyText::clean($row->us_pf_descp),
                    'description_mobile' => LegacyText::clean($row->us_pf_descp_mobile),
                    'experience' => LegacyText::clean($row->us_pf_experience),
                    'footer' => LegacyText::clean($row->us_pf_piedpage),

                    'theme' => $themes[mb_strtolower((string) $row->us_pf_version_web)] ?? 'mdl_default',
                    'theme_settings' => $this->themeSettings($row),
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

    public function migrateGalleries(LegacyUserResolver $resolver): void
    {
        $count = 0;

        foreach ($this->legacyChunks('ub2_gal_rub', 'rub_id_us', $resolver->legacyIds()) as $row) {
            $userId = $resolver->fromLegacyId((int) $row->rub_id_us);

            if ($userId === null) {
                continue;
            }

            Gallery::updateOrCreate(
                ['legacy_id' => $row->rub_id],
                [
                    'user_id' => $userId,
                    'name' => LegacyText::clean($row->rub_nom) ?: 'Sans titre',
                    'slug' => Str::slug(LegacyText::clean($row->rub_nom) ?? '') ?: null,
                    'status' => $this->status($row->rub_publier),
                    'position' => max(0, (int) $row->rub_ordre_rub),
                    'color' => $row->rub_coul ?: null,
                    'media_order' => $this->orderList($row->rub_ordre_img),
                    'created_at' => $this->date($row->rub_date_crea) ?? now(),
                ],
            );

            $count++;
        }

        // Le parent est resolu apres coup : l'ordre des lignes ne garantit pas
        // que la rubrique parente soit deja creee.
        $this->linkGalleryParents($resolver);

        $this->counts['galleries'] = $count;
    }

    public function migrateMedia(LegacyUserResolver $resolver): void
    {
        $galleries = Gallery::whereNotNull('legacy_id')->pluck('id', 'legacy_id');
        $count = 0;

        $orphans = 0;

        foreach ($this->legacyChunks('ub2_gal_img', 'img_id_us', $resolver->legacyIds()) as $row) {
            $userId = $resolver->fromLegacyId((int) $row->img_id_us);

            if ($userId === null) {
                continue;
            }

            // 248 746 lignes de ub2_gal_img (23 %) n'ont aucun nom de fichier :
            // enregistrements fantomes, sans visuel associe. On ne les reprend pas.
            if (trim((string) $row->img_fichier) === '') {
                $orphans++;

                continue;
            }

            Media::updateOrCreate(
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
                    'created_at' => $this->date($row->img_date_crea) ?? now(),
                ],
            );

            $count++;
        }

        $this->counts['media'] = $count;
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
                ['legacy_id' => $row->id],
                [
                    'user_id' => $userId,
                    'title' => LegacyText::clean($row->rub_titre) ?: 'Sans titre',
                    'slug' => Str::slug(LegacyText::clean($row->rub_titre) ?? '') ?: null,
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

        // Le corps des articles vit dans une table separee.
        $bodies = DB::connection('legacy')->table('bn_ultrabook_art_portefolio')
            ->pluck('art_texte', 'id_art');

        $sectionIds = BookSection::whereNotNull('legacy_id')->pluck('id', 'legacy_id');
        $articles = 0;

        foreach ($this->legacyChunks('bn_ultranews_art', 'id_util', $resolver->legacyIds()) as $row) {
            $userId = $resolver->fromLegacyId((int) $row->id_util);

            if ($userId === null) {
                continue;
            }

            BookArticle::updateOrCreate(
                ['legacy_id' => $row->id],
                [
                    'user_id' => $userId,
                    'book_section_id' => $sectionIds->get((int) $row->id_rub),
                    'title' => LegacyText::clean($row->art_titre) ?: 'Sans titre',
                    'slug' => Str::slug(LegacyText::clean($row->art_titre) ?? '') ?: null,
                    'body' => LegacyText::clean($bodies->get($row->id)),
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

        $this->counts['book_sections'] = $sections;
        $this->counts['book_articles'] = $articles;
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

            $conversation = Conversation::updateOrCreate(
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

            $conversation = Conversation::updateOrCreate(
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
            );

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
                        'status' => (int) $row->fac_stats === 1 ? 'paid' : 'pending',
                        'gateway' => $row->fac_paypaldata ? 'paypal' : null,
                        'gateway_payload' => $this->json($row->fac_paypaldata),
                        'issued_at' => $this->date($row->fac_date),
                        'paid_at' => (int) $row->fac_stats === 1 ? $this->date($row->fac_date) : null,
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

            if ($userId === null || $date === null) {
                continue;
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

    // ---------------------------------------------------------------- outils

    /** Parcourt une table legacy restreinte aux comptes repris. */
    private function legacyChunks(string $table, string $userColumn, array $legacyIds): \Generator
    {
        $query = DB::connection('legacy')->table($table)->whereIn($userColumn, $legacyIds);

        foreach ($query->cursor() as $row) {
            yield $row;
        }
    }

    private function rows(string $table): Collection
    {
        return collect(DB::connection('legacy')->table($table)->get());
    }

    private function linkGalleryParents(LegacyUserResolver $resolver): void
    {
        $map = Gallery::whereNotNull('legacy_id')->pluck('id', 'legacy_id');

        DB::connection('legacy')->table('ub2_gal_rub')
            ->whereIn('rub_id', $map->keys())
            ->where('rub_id_parent', '>', 0)
            ->orderBy('rub_id')
            ->each(function ($row) use ($map) {
                if ($parent = $map->get((int) $row->rub_id_parent)) {
                    Gallery::where('legacy_id', $row->rub_id)->update(['parent_id' => $parent]);
                }
            });
    }

    private function linkSectionParents(LegacyUserResolver $resolver): void
    {
        $map = BookSection::whereNotNull('legacy_id')->pluck('id', 'legacy_id');

        DB::connection('legacy')->table('bn_ultranews_rub')
            ->whereIn('id', $map->keys())
            ->where('id_parent', '>', 0)
            ->orderBy('id')
            ->each(function ($row) use ($map) {
                if ($parent = $map->get((int) $row->id_parent)) {
                    BookSection::where('legacy_id', $row->id)->update(['parent_id' => $parent]);
                }
            });
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
     */
    private function password(object $row): string
    {
        $plain = LegacyPassword::decrypt($row->us_pass);

        if ($plain === null) {
            $this->lostPasswords++;
            $plain = Str::random(32);
        }

        return Hash::make($plain);
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
    private function orderList(?string $value): ?array
    {
        $ids = array_values(array_filter(array_map('intval', explode(',', (string) $value))));

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
    private function themeSettings(object $row): ?array
    {
        foreach ([
            'us_pf_conf2020_ultra_zen', 'us_pf_conf2016_zoom', 'us_pf_conf2015_grid',
            'us_pf_conf2015_classique', 'us_pf_conf2014_responsive',
            'us_pf_conf2013_pinter', 'us_pf_conf2012_slide', 'us_pf_conf2012',
        ] as $column) {
            if (! empty($row->{$column})) {
                return $this->json($row->{$column});
            }
        }

        return null;
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
                || str_starts_with($key, 'us_pf_version'))
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
