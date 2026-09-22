<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Legacy\LegacyFiles;
use App\Services\Legacy\LegacyMigrator;
use App\Services\Legacy\LegacySampler;
use App\Services\Legacy\LegacyUserResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyCommand extends Command
{
    protected $signature = 'ubdf:migrate-legacy
        {--users=100 : Nombre de comptes repris}
        {--fresh : Vide les tables cibles avant reprise}
        {--skip-files : N\'effectue pas la copie des visuels}';

    protected $description = 'Reprend les donnees de la base ub2020 vers 2026_ubdf (lecture seule sur la source)';

    public function handle(): int
    {
        $limit = (int) $this->option('users');

        $this->components->info("Reprise de {$limit} comptes depuis ub2020 (base source en lecture seule)");

        if ($this->option('fresh')) {
            $this->truncateTargets();
        }

        $files = new LegacyFiles(config('ubdf.legacy_books_path'));

        // Le sampler ecarte les comptes dont le dossier manque sur ce poste.
        $migrator = new LegacyMigrator(new LegacySampler($limit, $files));

        $this->components->task('comptes creatifs', fn () => $migrator->migrateUsers());

        // Le resolveur est construit apres les comptes : il indexe ce qui existe.
        $resolver = new LegacyUserResolver;

        $this->components->task('reglages des books', fn () => $migrator->migrateBookSettings($resolver));
        $this->components->task('rubriques', fn () => $migrator->migrateGalleries($resolver));
        $this->components->task('visuels', fn () => $migrator->migrateMedia($resolver));
        $this->components->task('pages et actualites', fn () => $migrator->migrateContent($resolver));
        $this->components->task('textes de theme', fn () => $migrator->migrateThemeTexts($resolver));
        $this->components->task('messagerie', fn () => $migrator->migrateMessaging($resolver));
        $this->components->task('factures', fn () => $migrator->migrateInvoices($resolver));
        $this->components->task('statistiques', fn () => $migrator->migrateStats($resolver));
        $this->components->task('parrainages', fn () => $migrator->migrateReferrals($resolver));
        $this->components->task('codes promo', fn () => $migrator->migratePromoCodes());
        $this->components->task('offres promotionnelles', fn () => $migrator->migrateMarketingOffers($resolver));

        $report = $migrator->counts();

        if (! $this->option('skip-files')) {
            $this->withProgressBar(User::whereNotNull('legacy_id')->get(), fn (User $user) => $files->copyFor($user));
            $this->newLine(2);

            $report += $files->report();
        }

        $this->table(['Element', 'Volume'], collect($report)->map(
            fn ($value, $key) => [str_replace('_', ' ', $key), $value]
        )->values());

        $this->components->info('Reprise terminee. La base ub2020 n\'a pas ete modifiee.');

        return self::SUCCESS;
    }

    /**
     * Vide uniquement les tables cibles. `categories` est preservee : elle
     * vient du seeder, pas du legacy.
     */
    private function truncateTargets(): void
    {
        $tables = [
            'messages', 'conversations', 'visit_stats', 'invoices',
            'book_articles', 'book_sections', 'media', 'galleries',
            'book_settings', 'selection_user', 'users',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->components->warn('Tables cibles videes (base 2026_ubdf uniquement).');
    }
}
