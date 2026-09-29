<?php

namespace App\Console\Commands;

use App\Support\Robots;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Ecrit public/robots.txt (App\Support\Robots) : le serveur web sert ce
 * fichier tel quel, sans passer par Laravel. Planifiee chaque jour, et a
 * lancer apres un deploiement.
 */
class EcrireRobotsCommand extends Command
{
    protected $signature = 'ubdf:robots';

    protected $description = 'Écrit public/robots.txt (sitemaps des marques, espace fermé aux robots)';

    public function handle(): int
    {
        $chemin = public_path('robots.txt');
        $contenu = Robots::contenu();

        if (File::exists($chemin) && File::get($chemin) === $contenu) {
            $this->info('robots.txt déjà à jour.');

            return self::SUCCESS;
        }

        File::put($chemin, $contenu);
        $this->info('robots.txt écrit.');

        return self::SUCCESS;
    }
}
