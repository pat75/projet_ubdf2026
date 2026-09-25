<?php

namespace App\Console\Commands;

use App\Support\DossierBook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Range les dossiers de books a plat (books/adolie) dans leur dossier
 * segmente (books/a/d/o/adolie), voir App\Support\DossierBook.
 *
 * Deplacement (rename) sur le meme disque : ni copie ni suppression. Un
 * dossier cible deja present n'est jamais ecrase, il est signale. La
 * commande est idempotente : les dossiers de segment (un caractere) sont
 * ignores, relancer ne deplace que ce qui reste a plat.
 */
class SegmenterBooksCommand extends Command
{
    protected $signature = 'ubdf:segmenter-books {--dry-run : Affiche les deplacements sans les faire}';

    protected $description = 'Deplace books/<login> vers books/<a>/<b>/<c>/<login>';

    public function handle(): int
    {
        $racine = storage_path('app/public/'.DossierBook::RACINE);

        if (! is_dir($racine)) {
            $this->info('Aucun dossier de books.');

            return self::SUCCESS;
        }

        $deplaces = 0;
        $conflits = 0;

        foreach (File::directories($racine) as $dossier) {
            $login = basename($dossier);

            // Un seul caractere : c'est un dossier de segment, deja range.
            if (mb_strlen($login) === 1) {
                continue;
            }

            $cible = DossierBook::chemin($login);

            if (file_exists($cible)) {
                $this->warn("Conflit, laisse en place : {$login} ({$cible} existe deja)");
                $conflits++;

                continue;
            }

            $this->line(($this->option('dry-run') ? '[essai] ' : '').$login.' -> '.DossierBook::relatif($login));

            if (! $this->option('dry-run')) {
                File::ensureDirectoryExists(dirname($cible));
                rename($dossier, $cible);
            }

            $deplaces++;
        }

        $this->info(($this->option('dry-run') ? 'A deplacer' : 'Deplaces')." : {$deplaces}, conflits : {$conflits}.");

        return $conflits > 0 ? self::FAILURE : self::SUCCESS;
    }
}
