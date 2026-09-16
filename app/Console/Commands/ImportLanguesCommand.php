<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Convertit les catalogues gettext du site 2019 en fichiers JSON Laravel.
 *
 * Les deux systemes ont la meme convention — **la cle est la chaine
 * francaise** — ce qui rend la reprise directe : `msgid` devient la cle,
 * `msgstr` la traduction. Rien a reecrire dans les vues.
 */
class ImportLanguesCommand extends Command
{
    protected $signature = 'ubdf:import-langues {--source= : dossier languages/ du site 2019}';

    protected $description = 'Importe les catalogues gettext (.po) du site 2019 en JSON';

    public function handle(): int
    {
        $source = $this->option('source')
            ?: rtrim((string) config('ubdf.legacy_path'), '/').'/languages';

        if (! File::isDirectory($source)) {
            $this->error("Dossier introuvable : {$source}");

            return self::FAILURE;
        }

        File::ensureDirectoryExists(lang_path());

        foreach (config('langues.langues') as $code => $langue) {
            // Le francais est la langue des cles : son catalogue ne contient
            // que des msgstr vides, il n'a rien a traduire.
            if ($code === config('langues.defaut')) {
                continue;
            }

            $fichier = $source.'/'.$langue['posix'].'/LC_MESSAGES/messages.po';

            if (! File::isFile($fichier)) {
                $this->warn("  {$code} : catalogue absent ({$fichier})");

                continue;
            }

            $entrees = $this->lirePo($fichier);

            ksort($entrees);

            File::put(
                lang_path($code.'.json'),
                json_encode($entrees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n",
            );

            $this->line(sprintf('  %-4s %d chaines', $code, count($entrees)));
        }

        $this->info('Import termine.');

        return self::SUCCESS;
    }

    /**
     * Lecture d'un fichier .po.
     *
     * Le format autorise une chaine repartie sur plusieurs lignes, chacune
     * entre guillemets, a concatener. Les entrees marquees « fuzzy » sont
     * ecartees : ce sont des propositions non relues, que gettext lui-meme
     * n'utilise pas.
     *
     * @return array<string, string>
     */
    private function lirePo(string $fichier): array
    {
        $entrees = [];

        $id = null;
        $traduction = null;
        $courant = null;
        $fuzzy = false;

        $enregistrer = function () use (&$entrees, &$id, &$traduction, &$fuzzy): void {
            if ($id !== null && $id !== '' && $traduction !== null && $traduction !== '' && ! $fuzzy) {
                $entrees[$id] = $traduction;
            }

            $id = $traduction = null;
            $fuzzy = false;
        };

        foreach (file($fichier, FILE_IGNORE_NEW_LINES) ?: [] as $ligne) {
            $ligne = trim($ligne);

            if ($ligne === '') {
                $enregistrer();
                $courant = null;

                continue;
            }

            if (str_starts_with($ligne, '#')) {
                if (str_starts_with($ligne, '#,') && str_contains($ligne, 'fuzzy')) {
                    $fuzzy = true;
                }

                continue;
            }

            if (str_starts_with($ligne, 'msgid_plural') || str_starts_with($ligne, 'msgstr[')) {
                // Les formes plurielles de gettext n'ont pas d'equivalent
                // direct en JSON Laravel ; aucune n'est utilisee par le
                // front repris. Elles sont ignorees, entree comprise.
                $id = null;
                $courant = null;

                continue;
            }

            if (str_starts_with($ligne, 'msgid ')) {
                $enregistrer();
                $id = $this->decoder(substr($ligne, 6));
                $courant = 'id';

                continue;
            }

            if (str_starts_with($ligne, 'msgstr ')) {
                $traduction = $this->decoder(substr($ligne, 7));
                $courant = 'str';

                continue;
            }

            // Suite d'une chaine repartie sur plusieurs lignes.
            if (str_starts_with($ligne, '"') && $courant !== null) {
                $suite = $this->decoder($ligne);

                if ($courant === 'id') {
                    $id .= $suite;
                } else {
                    $traduction .= $suite;
                }
            }
        }

        $enregistrer();

        return $entrees;
    }

    private function decoder(string $brut): string
    {
        $brut = trim($brut);

        if (! str_starts_with($brut, '"') || ! str_ends_with($brut, '"')) {
            return '';
        }

        return stripcslashes(substr($brut, 1, -1));
    }
}
