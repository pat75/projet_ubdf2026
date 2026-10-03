<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Legacy\LegacyFiles;
use App\Services\Legacy\LegacyLogins;
use App\Services\Legacy\LegacyMigrator;
use App\Services\Legacy\LegacySampler;
use App\Services\Legacy\LegacyUserResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyCommand extends Command
{
    protected $signature = 'ubdf:migrate-legacy
        {--users=100 : Nombre de comptes repris}
        {--tous : Reprend tous les comptes vivants, par paquets (mise en production)}
        {--paquet=2000 : Avec --tous, nombre de comptes par paquet}
        {--depuis=0 : Avec --tous, reprend apres ce us_id (relance apres interruption)}
        {--max-paquets=0 : Avec --tous, s\'arrete apres N paquets (essai ; 0 = tous)}
        {--fresh : Vide les tables cibles avant reprise}
        {--skip-files : N\'effectue pas la copie des visuels}
        {--rapport= : Ecrit le rapport (volumes, troncatures par champ, comptes ecartes) dans ce fichier}';

    protected $description = 'Reprend les donnees de la base ub2020 vers 2026_ubdf (lecture seule sur la source)';

    /** @var array<string, int|string> */
    private array $rapport = [];

    public function handle(): int
    {
        // 60 000 comptes : sans cela, chaque requete s'accumule en memoire.
        DB::disableQueryLog();

        if ($this->option('fresh')) {
            $this->truncateTargets();
        }

        $files = new LegacyFiles(config('ubdf.legacy_books_path'));
        $logins = LegacyLogins::depuisLegacy();

        if ($this->option('tous')) {
            $this->parPaquets($files, $logins);
        } else {
            $this->echantillon($files, $logins);
        }

        if ($logins->ecartes() !== []) {
            $this->components->warn(count($logins->ecartes()).' login(s) sans conversion possible, comptes ecartes : '
                .implode(', ', array_slice($logins->ecartes(), 0, 20)));
        }

        $this->rapport['valeurs_tronquees'] = LegacyMigrator::valeursTronquees();

        $this->table(['Element', 'Volume'], collect($this->rapport)->map(
            fn ($value, $key) => [str_replace('_', ' ', $key), $value]
        )->values());

        $troncatures = LegacyMigrator::detailTroncatures();

        if ($troncatures !== []) {
            $this->components->warn('Valeurs coupees a la longueur de leur colonne :');
            $this->table(['Champ', 'Valeurs', 'Exemples (id legacy)'], collect($troncatures)
                ->map(fn (array $t, string $champ) => [$champ, $t['nombre'], implode(', ', $t['exemples'])])->values());
        }

        if ($fichier = $this->option('rapport')) {
            $this->ecrireRapport($fichier, $troncatures, $logins->ecartes());
        }

        $this->components->info('Reprise terminee. La base ub2020 n\'a pas ete modifiee.');

        return self::SUCCESS;
    }

    /** Developpement : un echantillon compose par LegacySampler. */
    private function echantillon(LegacyFiles $files, LegacyLogins $logins): void
    {
        $limit = (int) $this->option('users');
        $this->components->info("Reprise de {$limit} comptes depuis ub2020 (base source en lecture seule)");

        // Le sampler ecarte les comptes dont le dossier manque sur ce poste.
        $migrator = new LegacyMigrator(new LegacySampler($limit, $files), $logins);

        $this->components->task('comptes creatifs', fn () => $migrator->migrateUsers());

        // Le resolveur est construit apres les comptes : il indexe ce qui existe.
        $resolver = new LegacyUserResolver($logins);

        $this->etapesParCompte($migrator, $resolver);
        $this->etapesGlobales($migrator, $resolver);
        $this->cumuler($migrator->counts());

        if (! $this->option('skip-files')) {
            $this->withProgressBar(User::whereNotNull('legacy_id')->get(), fn (User $user) => $files->copyFor($user));
            $this->newLine(2);

            $this->rapport += $files->report();
        }
    }

    /**
     * Production : tous les comptes, par paquets successifs de us_id
     * croissants. Chaque paquet est complet (compte, book, visuels, pages,
     * factures, fichiers) avant le suivant, et la memoire est liberee entre
     * deux paquets. Apres une interruption, `--depuis=<dernier us_id
     * affiche>` repart au paquet suivant ; toutes les etapes etant
     * idempotentes, rejouer un paquet ne cree pas de doublon.
     *
     * Les etapes qui relient deux comptes (messagerie, parrainages) passent
     * a la fin, une fois tous les comptes crees.
     */
    private function parPaquets(LegacyFiles $files, LegacyLogins $logins): void
    {
        $paquet = max(100, min(5000, (int) $this->option('paquet')));
        $depuis = (int) $this->option('depuis');
        $numero = 0;

        $this->components->info("Reprise de tous les comptes, par paquets de {$paquet}, apres us_id {$depuis}");

        $max = (int) $this->option('max-paquets');

        while ($max === 0 || $numero < $max) {
            $sampler = new LegacySampler($paquet, null, true, $depuis);
            $migrator = new LegacyMigrator($sampler, $logins);

            $lignes = $migrator->migrateUsers();

            if ($lignes->isEmpty()) {
                break;
            }

            $numero++;
            $ids = $lignes->pluck('us_id')->map(fn ($id) => (int) $id)->all();
            $resolver = (new LegacyUserResolver($logins))->limiterAuPaquet($ids);

            $this->etapesParCompte($migrator, $resolver, silencieux: true);

            if (! $this->option('skip-files')) {
                foreach ($lignes as $ligne) {
                    if ($nouveau = $logins->nouveau($ligne->us_login)) {
                        $files->copyLogin(mb_strtolower(trim($ligne->us_login)), $nouveau);
                    }
                }
            }

            $this->cumuler($migrator->counts());
            $depuis = (int) $lignes->last()->us_id;

            $this->line(sprintf('  paquet %d : %d comptes, jusqu\'a us_id %d — memoire %d Mo — relance : --depuis=%d',
                $numero, count($ids), $depuis, memory_get_usage(true) / 1048576, $depuis));

            unset($lignes, $migrator, $resolver, $sampler);
            gc_collect_cycles();
        }

        $migrator = new LegacyMigrator(new LegacySampler(0, null, true), $logins);
        $this->etapesGlobales($migrator, new LegacyUserResolver($logins));
        $this->cumuler($migrator->counts());

        if (! $this->option('skip-files')) {
            $this->rapport += $files->report();
        }
    }

    private function etapesParCompte(LegacyMigrator $migrator, LegacyUserResolver $resolver, bool $silencieux = false): void
    {
        $etapes = [
            'reglages des books' => fn () => $migrator->migrateBookSettings($resolver),
            'rubriques' => fn () => $migrator->migrateGalleries($resolver),
            'visuels' => fn () => $migrator->migrateMedia($resolver),
            'pages et actualites' => fn () => $migrator->migrateContent($resolver),
            'textes de theme' => fn () => $migrator->migrateThemeTexts($resolver),
            'factures' => fn () => $migrator->migrateInvoices($resolver),
            'statistiques' => fn () => $migrator->migrateStats($resolver),
            'offres promotionnelles' => fn () => $migrator->migrateMarketingOffers($resolver),
        ];

        foreach ($etapes as $libelle => $etape) {
            $silencieux ? $etape() : $this->components->task($libelle, $etape);
        }
    }

    private function etapesGlobales(LegacyMigrator $migrator, LegacyUserResolver $resolver): void
    {
        $this->components->task('messagerie', fn () => $migrator->migrateMessaging($resolver));
        $this->components->task('parrainages', fn () => $migrator->migrateReferrals($resolver));
        $this->components->task('codes promo', fn () => $migrator->migratePromoCodes());
        $this->components->task('abonnes newsletter', fn () => $migrator->migrateNewsletter());
    }

    /**
     * Rapport lisible hors du terminal : volumes, troncatures par champ,
     * logins ecartes. Lu par deploy/transfert-base.sh pour la synthese.
     *
     * @param  array<string, array{nombre: int, exemples: list<string>}>  $troncatures
     * @param  list<string>  $ecartes
     */
    private function ecrireRapport(string $fichier, array $troncatures, array $ecartes): void
    {
        $lignes = ['REPRISE LEGACY '.now()->format('Y-m-d H:i'), '', 'VOLUMES'];

        foreach ($this->rapport as $cle => $valeur) {
            $lignes[] = sprintf('  %-34s %s', str_replace('_', ' ', $cle), $valeur);
        }

        $lignes[] = '';
        $lignes[] = 'VALEURS COUPEES (table.colonne : nombre, exemples d\'id legacy)';

        foreach ($troncatures as $champ => $t) {
            $lignes[] = sprintf('  %-34s %d  (%s)', $champ, $t['nombre'], implode(', ', $t['exemples']));
        }

        if ($troncatures === []) {
            $lignes[] = '  aucune';
        }

        $lignes[] = '';
        $lignes[] = 'LOGINS SANS CONVERSION POSSIBLE (comptes non repris) : '.count($ecartes);

        foreach (array_chunk($ecartes, 10) as $paquet) {
            $lignes[] = '  '.implode(', ', $paquet);
        }

        @mkdir(dirname($fichier), 0o755, true);
        file_put_contents($fichier, implode(PHP_EOL, $lignes).PHP_EOL);
        $this->components->info("Rapport : {$fichier}");
    }

    /** Additionne les compteurs d'un paquet au rapport general. */
    private function cumuler(array $compteurs): void
    {
        foreach ($compteurs as $cle => $valeur) {
            $this->rapport[$cle] = ($this->rapport[$cle] ?? 0) + $valeur;
        }
    }

    /**
     * Vide uniquement les tables cibles. `categories` est preservee : elle
     * vient du seeder, pas du legacy.
     */
    private function truncateTargets(): void
    {
        /*
         | Tout ce qui pointe vers un compte createur, plus ce que la reprise
         | recharge (codes promo, offres, abonnes) : sans cela il resterait
         | des lignes orphelines ou des doublons. Le back-office (admins,
         | reglages, pages CMS, actualites, campagnes, categories,
         | selections) n'est pas touche ; l'historique d'envoi des campagnes
         | (campaign_sends) pointe vers les comptes, il est vide avec eux.
         */
        $tables = [
            'messages', 'conversations', 'visit_stats', 'invoices',
            'book_articles', 'book_sections', 'media', 'galleries',
            'book_settings', 'selection_user', 'users',
            'billing_profiles', 'visitor_book_visits', 'visitors',
            'memo_partages', 'memo_books', 'referrals', 'data_exports',
            'page_images', 'subscription_reminders', 'user_password_resets',
            'campaign_sends',
            'promo_codes', 'marketing_offers', 'newsletter_mails',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->components->warn('Tables cibles videes (base 2026_ubdf uniquement).');
    }
}
