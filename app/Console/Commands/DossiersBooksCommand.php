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
 * book (LegacyFiles). Par defaut la source n'est pas modifiee.
 *
 * Un login a « _ » ou « . » (a_menguy) est range sous son login converti
 * (a-menguy, ou a--menguy si pris), selon la meme table que la reprise en
 * base (LegacyLogins). Le dossier d'un compte supprime n'est pas repris.
 *
 * `--deplacer` deplace les originaux au lieu de les copier (meme disque :
 * instantane, pas de doublement de l'espace) ; la source perd alors ce qui
 * est repris, les declinaisons y restent.
 *
 * Reprenable. En copie, un fichier deja present n'est recopie que s'il a
 * change dans la source (taille ou date) ; en deplacement, il reste tel
 * quel. `--depuis` et `--jusqua` bornent les premieres lettres traitees
 * (ex. un essai sur les books de a a f, ou une reprise apres coupure).
 *
 * Progression sur une seule ligne reecrite, comme `rsync --info=progress2`.
 */
class DossiersBooksCommand extends Command
{
    protected $signature = 'ubdf:prod:dossiers-books
        {--source= : Racine users_2 du legacy (defaut : LEGACY_BOOKS_PATH)}
        {--depuis= : Premiere lettre traitee (reprise apres interruption)}
        {--jusqua= : Derniere lettre traitee (ex. --depuis=a --jusqua=f)}
        {--deplacer : Deplace les originaux au lieu de les copier (la source est videe de ce qui est repris)}
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
        $jusqua = mb_strtolower((string) $this->option('jusqua'));
        $essai = (bool) $this->option('dry-run');
        $fichiers = new LegacyFiles($source, deplacer: (bool) $this->option('deplacer'));
        $logins = LegacyLogins::depuisLegacy();
        $books = 0;
        $renommes = 0;
        $sansCompte = 0;
        $ecartes = [];

        // Liste arretee d'avance : elle donne le total de la progression.
        $dossiers = array_values(array_filter(glob($source.'/*/*/*', GLOB_ONLYDIR) ?: [], function (string $dossier) use ($depuis, $jusqua) {
            $initiale = mb_strtolower(basename($dossier))[0];

            return ($depuis === '' || strcmp($initiale, $depuis) >= 0)
                && ($jusqua === '' || strcmp($initiale, $jusqua) <= 0);
        }));

        $this->components->info(($fichiers->deplace() ? 'DEPLACEMENT' : 'COPIE').' de '.count($dossiers).' dossiers'
            .($depuis.$jusqua !== '' ? ' (lettres '.($depuis ?: '…').' a '.($jusqua ?: '…').')' : ''));

        $debut = microtime(true);
        $dernier = 0.0;

        foreach ($dossiers as $rang => $dossier) {
            $login = mb_strtolower(basename($dossier));

            if (! $essai && microtime(true) - $dernier >= 0.5) {
                $dernier = microtime(true);
                $this->progression($fichiers, $rang, count($dossiers), $debut, $login);
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

        if (! $essai) {
            $this->progression($fichiers, count($dossiers), count($dossiers), $debut, '');
        }

        $this->newLine(2);
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

    /**
     * Une ligne reecrite sur place (retour chariot) :
     *   1 234/5 678 books  21%  18 412 fichiers  3,2 Go  42 Mo/s  reste 0:12:03  adolie
     * Le temps restant suit le nombre de books, pas le volume : il se
     * stabilise apres quelques minutes.
     */
    private function progression(LegacyFiles $fichiers, int $faits, int $total, float $debut, string $login): void
    {
        $ecoule = max(microtime(true) - $debut, 0.001);
        $reste = $faits > 0 ? (int) ($ecoule / $faits * ($total - $faits)) : 0;

        $ligne = sprintf(
            '%s/%s books  %3d%%  %s fichiers  %s  %s/s  reste %d:%02d:%02d  %s',
            number_format($faits, 0, ',', ' '),
            number_format($total, 0, ',', ' '),
            $total > 0 ? intdiv($faits * 100, $total) : 100,
            number_format($fichiers->fichiers(), 0, ',', ' '),
            $fichiers->humanSize($fichiers->octets()),
            $fichiers->humanSize($fichiers->octets() / $ecoule),
            intdiv($reste, 3600), intdiv($reste % 3600, 60), $reste % 60,
            $login,
        );

        $this->output->write("\r".str_pad(mb_substr($ligne, 0, 110), 110));
    }
}
