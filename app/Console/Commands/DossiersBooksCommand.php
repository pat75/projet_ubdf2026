<?php

namespace App\Console\Commands;

use App\Services\Legacy\LegacyFiles;
use App\Services\Legacy\LegacyLogins;
use App\Support\DossierBook;
use Illuminate\Console\Command;

/**
 * Mise en production : recree l'arborescence des books.
 *
 * Le legacy range chaque book sous users_2/<a>/<b>/<login> (deux premieres
 * lettres), le nouveau site sous books/<a>/<b>/<c>/<login> (trois, voir
 * App\Support\DossierBook). La commande parcourt tout l'arbre legacy, pas
 * seulement les comptes repris en base, et copie les originaux de chaque
 * book (LegacyFiles). La source n'est jamais modifiee.
 *
 * Un login a « _ » ou « . » (a_menguy) est range sous son login converti
 * (a-menguy, ou a--menguy si pris), selon la meme table que la reprise en
 * base (LegacyLogins). Le dossier d'un compte supprime n'est pas repris.
 *
 * Reprenable : un fichier deja present n'est pas recopie. `--depuis` repart
 * d'une lettre precise apres une interruption.
 */
class DossiersBooksCommand extends Command
{
    protected $signature = 'ubdf:prod:dossiers-books
        {--source= : Racine users_2 du legacy (defaut : LEGACY_BOOKS_PATH)}
        {--depuis= : Premiere lettre traitee (reprise apres interruption)}
        {--dry-run : Liste les correspondances sans rien copier}';

    protected $description = 'Recree les dossiers des books : users_2/<a>/<b>/<login> -> books/<a>/<b>/<c>/<login>';

    public function handle(): int
    {
        $source = rtrim((string) ($this->option('source') ?: config('ubdf.legacy_books_path')), '/');

        if ($source === '' || ! is_dir($source)) {
            $this->components->error("Racine legacy introuvable : '{$source}'");

            return self::FAILURE;
        }

        $depuis = mb_strtolower((string) $this->option('depuis'));
        $essai = (bool) $this->option('dry-run');
        $fichiers = new LegacyFiles($source);
        $logins = LegacyLogins::depuisLegacy();
        $books = 0;
        $renommes = 0;
        $sansCompte = 0;
        $ecartes = [];

        foreach (glob($source.'/*/*/*', GLOB_ONLYDIR) ?: [] as $dossier) {
            $login = mb_strtolower(basename($dossier));

            if ($depuis !== '' && strcmp(mb_strtolower($login[0]), $depuis) < 0) {
                continue;
            }

            // Meme regle de login que la reprise en base : le reste est du
            // bruit (sauvegardes, dossiers de test) ou un chemin dangereux.
            if (! preg_match('/'.LegacyLogins::SOURCE.'/', $login)
                || $fichiers->bookPath($login) !== $dossier) {
                $ecartes[] = $dossier;

                continue;
            }

            $nouveau = $logins->nouveau($login);

            // Compte supprime, ou login sans conversion possible.
            if ($nouveau === null) {
                $sansCompte++;

                continue;
            }

            if ($nouveau !== $login) {
                $renommes++;
            }

            if ($essai) {
                $this->line(substr($dossier, strlen($source) + 1).' -> '.DossierBook::relatif($nouveau));
            } else {
                $fichiers->copyLogin($login, $nouveau);
            }

            $books++;
        }

        $this->newLine();
        $this->components->twoColumnDetail('Books '.($essai ? 'a traiter' : 'traites'), (string) $books);
        $this->components->twoColumnDetail('dont logins convertis (_ ou . -> -)', (string) $renommes);
        $this->components->twoColumnDetail('Dossiers sans compte vivant (ignores)', (string) $sansCompte);

        if (! $essai) {
            foreach ($fichiers->report() as $cle => $valeur) {
                $this->components->twoColumnDetail(str_replace('_', ' ', $cle), (string) $valeur);
            }
        }

        if ($ecartes !== []) {
            $this->components->twoColumnDetail('Dossiers ecartes (login non conforme)', (string) count($ecartes));

            foreach (array_slice($ecartes, 0, 20) as $dossier) {
                $this->line('  '.$dossier);
            }
        }

        return self::SUCCESS;
    }
}
