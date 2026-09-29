<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Mise en production : compare la structure de ub2020 (connexion `legacy`)
 * a celle de la nouvelle base, et ecrit deux fichiers SQL.
 *
 *  - comparaison_structure.sql : rapport commente, table par table
 *    (correspondances, colonnes, tables sans equivalent) ;
 *  - conversion_structure.sql : le script qui fait passer une base au
 *    schema de l'ancienne a celui de la nouvelle — ALTER pour les tables de
 *    meme nom, CREATE pour les nouvelles, DROP (commentes) pour les
 *    abandonnees.
 *
 * Le schema cible reste celui des migrations Laravel (`php artisan
 * migrate`) ; ce script sert a relire et a auditer l'ecart. Les DONNEES ne
 * passent pas par SQL : mots de passe a rechiffrer, identifiants a
 * resoudre, textes a nettoyer — c'est `ubdf:migrate-legacy --tous`.
 *
 * Lecture seule sur les deux bases : seul information_schema est
 * interroge.
 */
class ComparerStructuresCommand extends Command
{
    protected $signature = 'ubdf:prod:comparer-structures
        {--sortie=_mise_en_production/sql : Dossier des fichiers generes}';

    protected $description = 'Compare les structures ub2020 / nouvelle base et genere les fichiers SQL de conversion';

    /**
     * Correspondances ancienne table -> nouvelle(s), relevees dans
     * App\Services\Legacy\LegacyMigrator.
     */
    private const CORRESPONDANCES = [
        'inc_user' => ['users'],
        'inc_user_pref' => ['book_settings'],
        'ub2_gal_rub' => ['book_sections', 'galleries'],
        'ub2_gal_img' => ['media', 'book_articles'],
        'bn_ultranews_rub' => ['book_sections'],
        'bn_ultranews_art' => ['book_articles'],
        'bn_ultrabook_art_portefolio' => ['book_articles'],
        'ub2_edit_txt' => ['book_settings'],
        'ub2_contact_form' => ['conversations', 'messages'],
        'ub2_intermediate_form' => ['conversations', 'messages'],
        'ub2_fac' => ['invoices'],
        'df2_fac' => ['invoices'],
        'inc_stats' => ['visit_stats'],
        'ub2_parrainage' => ['referrals'],
        'ub2_codepromo' => ['promo_codes'],
        'inc_marketing' => ['marketing_offers'],
    ];

    public function handle(): int
    {
        $ancienne = $this->schema('legacy');
        $nouvelle = $this->schema(config('database.default'));
        $nomAncienne = DB::connection('legacy')->getDatabaseName();
        $nomNouvelle = DB::connection()->getDatabaseName();

        $dossier = base_path($this->option('sortie'));
        File::ensureDirectoryExists($dossier);

        $entete = sprintf("-- Genere le %s par `php artisan ubdf:prod:comparer-structures`\n-- Ancienne base : %s (%d tables) — nouvelle base : %s (%d tables)\n\n",
            now()->format('Y-m-d H:i'), $nomAncienne, $ancienne->count(), $nomNouvelle, $nouvelle->count());

        File::put($dossier.'/comparaison_structure.sql', $entete.$this->rapport($ancienne, $nouvelle));
        File::put($dossier.'/conversion_structure.sql', $entete.$this->conversion($ancienne, $nouvelle));

        $this->components->info("Fichiers ecrits dans {$this->option('sortie')}/");
        $this->components->twoColumnDetail('Tables reprises (correspondance)', (string) count(array_intersect_key(self::CORRESPONDANCES, $ancienne->all())));
        $this->components->twoColumnDetail('Tables de meme nom', (string) $ancienne->keys()->intersect($nouvelle->keys())->count());
        $this->components->twoColumnDetail('Tables nouvelles', (string) $nouvelle->keys()->diff($ancienne->keys())->count());

        return self::SUCCESS;
    }

    /**
     * @return Collection<string, Collection<string, object>> table => colonne => definition
     */
    private function schema(string $connexion): Collection
    {
        $base = DB::connection($connexion)->getDatabaseName();

        return collect(DB::connection($connexion)->select(
            'SELECT TABLE_NAME AS t, COLUMN_NAME AS c, COLUMN_TYPE AS type, IS_NULLABLE AS nul,
                    COLUMN_DEFAULT AS defaut, EXTRA AS extra, COLUMN_KEY AS cle
               FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ?
              ORDER BY TABLE_NAME, ORDINAL_POSITION', [$base]
        ))->groupBy('t')->map(fn (Collection $colonnes) => $colonnes->keyBy('c'));
    }

    private function rapport(Collection $ancienne, Collection $nouvelle): string
    {
        $sql = "-- ==================================================================\n"
            ."-- 1. Tables reprises : ancienne -> nouvelle(s)\n"
            ."-- ==================================================================\n";

        foreach (self::CORRESPONDANCES as $source => $cibles) {
            $presente = $ancienne->has($source) ? '' : '  [ABSENTE de la base source]';
            $sql .= "\n-- {$source} -> ".implode(', ', $cibles)."{$presente}\n";

            if ($ancienne->has($source)) {
                $sql .= '--   ancienne : '.$ancienne[$source]->keys()->implode(', ')."\n";
            }

            foreach ($cibles as $cible) {
                $sql .= "--   {$cible} : ".($nouvelle->get($cible)?->keys()->implode(', ') ?? '[ABSENTE]')."\n";
            }
        }

        $communes = $ancienne->keys()->intersect($nouvelle->keys());

        $sql .= "\n-- ==================================================================\n"
            ."-- 2. Tables de meme nom dans les deux bases\n"
            ."-- ==================================================================\n";

        foreach ($communes as $table) {
            $sql .= "\n-- {$table}\n";

            foreach ($this->ecarts($ancienne[$table], $nouvelle[$table]) as $ligne) {
                $sql .= "--   {$ligne}\n";
            }
        }

        $abandonnees = $ancienne->keys()->diff($nouvelle->keys())->diff(array_keys(self::CORRESPONDANCES));

        $sql .= "\n-- ==================================================================\n"
            ."-- 3. Tables anciennes sans reprise ({$abandonnees->count()})\n"
            ."-- ==================================================================\n";

        foreach ($abandonnees as $table) {
            $sql .= "--   {$table} (".$ancienne[$table]->count()." colonnes)\n";
        }

        $sources = collect(self::CORRESPONDANCES)->flatten()->unique();
        $nouvelles = $nouvelle->keys()->diff($ancienne->keys());

        $sql .= "\n-- ==================================================================\n"
            ."-- 4. Tables nouvelles ({$nouvelles->count()})\n"
            ."-- ==================================================================\n";

        foreach ($nouvelles as $table) {
            $sql .= "--   {$table}".($sources->contains($table) ? ' (alimentee par la reprise)' : ' (vide au depart)')."\n";
        }

        return $sql;
    }

    /** @return list<string> */
    private function ecarts(Collection $avant, Collection $apres): array
    {
        $lignes = [];

        foreach ($apres as $nom => $col) {
            if (! $avant->has($nom)) {
                $lignes[] = "+ {$nom} {$col->type}";
            } elseif ($this->definition($avant[$nom]) !== $this->definition($col)) {
                $lignes[] = "~ {$nom} : {$this->definition($avant[$nom])} -> {$this->definition($col)}";
            }
        }

        foreach ($avant->keys()->diff($apres->keys()) as $nom) {
            $lignes[] = "- {$nom}";
        }

        return $lignes ?: ['identique'];
    }

    private function conversion(Collection $ancienne, Collection $nouvelle): string
    {
        $sql = "-- A executer sur une COPIE de la base ancienne, jamais sur ub2020.\n"
            ."-- Les donnees se reprennent ensuite par `php artisan ubdf:migrate-legacy --tous`.\n\n"
            ."SET FOREIGN_KEY_CHECKS = 0;\n";

        foreach ($ancienne->keys()->intersect($nouvelle->keys()) as $table) {
            $avant = $ancienne[$table];
            $apres = $nouvelle[$table];
            $ordres = [];

            foreach ($apres as $nom => $col) {
                if (! $avant->has($nom)) {
                    $ordres[] = "ADD COLUMN `{$nom}` {$this->definition($col)}";
                } elseif ($this->definition($avant[$nom]) !== $this->definition($col)) {
                    $ordres[] = "MODIFY COLUMN `{$nom}` {$this->definition($col)}";
                }
            }

            foreach ($avant->keys()->diff($apres->keys()) as $nom) {
                $ordres[] = "DROP COLUMN `{$nom}`";
            }

            if ($ordres !== []) {
                $sql .= "\nALTER TABLE `{$table}`\n  ".implode(",\n  ", $ordres).";\n";
            }
        }

        foreach ($nouvelle->keys()->diff($ancienne->keys()) as $table) {
            $create = (array) DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $sql .= "\n".preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', array_values($create)[1]).";\n";
        }

        $sql .= "\n-- Tables abandonnees : a supprimer une fois la reprise validee.\n";

        foreach ($ancienne->keys()->diff($nouvelle->keys()) as $table) {
            $sql .= "-- DROP TABLE `{$table}`;\n";
        }

        return $sql."\nSET FOREIGN_KEY_CHECKS = 1;\n";
    }

    private function definition(object $col): string
    {
        $def = $col->type.($col->nul === 'NO' ? ' NOT NULL' : ' NULL');

        if ($col->defaut !== null) {
            $def .= ' DEFAULT '.(is_numeric($col->defaut) || str_contains($col->defaut, '(') || $col->defaut === 'CURRENT_TIMESTAMP'
                ? $col->defaut : "'".str_replace("'", "''", trim($col->defaut, "'"))."'");
        }

        return trim($def.' '.str_replace('DEFAULT_GENERATED', '', $col->extra));
    }
}
