<?php

namespace App\Services\Legacy;

use App\Models\User;
use App\Support\DossierBook;
use Illuminate\Support\Facades\File;

/**
 * Copie des visuels des comptes repris.
 *
 * Le legacy eclate chaque book sur le disque en 12 dossiers de declinaisons
 * pre-generees (img_ptf_small/medium, img_iph_*, img_adm_*, img_front_*…),
 * sous users_2/<1re lettre>/<2e lettre>/<login>/. On ne reprend que les
 * originaux : les declinaisons seront regenerees a la demande (phase 4).
 */
final class LegacyFiles
{
    /** Dossiers du book contenant les originaux, par ordre de preference. */
    private const SOURCE_DIRS = ['img_', 'img_adm_medium', 'img_ptf_medium'];

    /** Vignettes et visuels de presentation, stockes a part dans le legacy. */
    private const EXTRA_DIRS = ['cms_pref'];

    /**
     * Images inserees dans le texte des pages via l'editeur (CKEditor), avec
     * leurs sous-dossiers. Copiees telles quelles dans <dossier du book>/img_cms/ (DossierBook) :
     * le HTML des pages les reference par ce chemin, que book_actu_txt()
     * reecrit a l'affichage.
     */
    private const CMS_DIR = 'img_cms';

    private int $copiedFiles = 0;

    private int $copiedBytes = 0;

    private int $missingBooks = 0;

    /**
     * Appele apres chaque fichier transfere : la commande de mise en
     * production y rafraichit sa progression pendant un gros book, au lieu
     * de la laisser figee jusqu'au book suivant.
     */
    public ?\Closure $apresFichier = null;

    /**
     * $deplacer : deplace les originaux au lieu de les copier (mise en
     * production, meme disque : instantane et sans doubler l'espace). Les
     * declinaisons et les fichiers ecartes restent dans la source.
     */
    public function __construct(
        private readonly ?string $legacyRoot,
        private readonly bool $deplacer = false,
    ) {}

    public function copyFor(User $user): void
    {
        $this->copyLogin($user->login);
    }

    /**
     * Copie les originaux d'un book vers son dossier segmente sur trois
     * lettres (DossierBook). Un fichier deja present n'est pas recopie :
     * relancer ne copie que ce qui manque.
     *
     * $ancien situe le dossier legacy (users_2/a/_/a_menguy), $nouveau le
     * dossier cible quand le login a ete converti (books/a/-/m/a-menguy).
     */
    public function copyLogin(string $ancien, ?string $nouveau = null): void
    {
        $source = $this->bookPath($ancien);

        if ($source === null || ! is_dir($source)) {
            $this->missingBooks++;

            return;
        }

        $target = DossierBook::chemin($nouveau ?? $ancien);
        File::ensureDirectoryExists($target);
        $pris = [];

        foreach ([...self::SOURCE_DIRS, ...self::EXTRA_DIRS] as $dir) {
            $path = $source.'/'.$dir;

            if (! is_dir($path)) {
                continue;
            }

            foreach (File::files($path) as $file) {
                $destination = $target.'/'.$file->getFilename();

                // Le premier dossier trouve fait foi : un fichier deja pris
                // dans cette passe n'est pas ecrase par un dossier suivant.
                if (isset($pris[$destination]) || ! $this->aTransferer($file, $destination)) {
                    continue;
                }

                $pris[$destination] = true;
                $this->transferer($file, $destination);
            }
        }

        $cms = $source.'/'.self::CMS_DIR;

        if (is_dir($cms)) {
            foreach (File::allFiles($cms) as $file) {
                // Rien d'executable, meme hors de public/ : ces dossiers ont
                // recu des depots de scripts (voir le .htaccess du legacy).
                if (preg_match('/\.(php\d?|phtml|phar|htaccess)$/i', $file->getFilename())) {
                    continue;
                }

                $destination = $target.'/'.self::CMS_DIR.'/'.$file->getRelativePathname();

                if (! $this->aTransferer($file, $destination)) {
                    continue;
                }

                File::ensureDirectoryExists(dirname($destination));
                $this->transferer($file, $destination);
            }
        }
    }

    /**
     * Fichier absent : on le prend. Fichier present : en copie seulement,
     * on le remplace s'il differe de la source (taille ou date), pour
     * qu'une nouvelle passe apres un `recuperer` repercute les visuels
     * modifies sur l'ancien serveur. En deplacement, ce qui est la reste.
     */
    private function aTransferer(\SplFileInfo $file, string $destination): bool
    {
        if (! file_exists($destination)) {
            return true;
        }

        if ($this->deplacer) {
            return false;
        }

        return filesize($destination) !== $file->getSize() || filemtime($destination) < $file->getMTime();
    }

    private function transferer(\SplFileInfo $file, string $destination): void
    {
        $taille = $file->getSize();

        if ($this->deplacer) {
            File::move($file->getPathname(), $destination);
        } else {
            File::copy($file->getPathname(), $destination);
            // Date de la source conservee : c'est elle qu'aTransferer()
            // compare a la passe suivante.
            touch($destination, $file->getMTime());
        }

        $this->copiedFiles++;
        $this->copiedBytes += $taille;

        if ($this->apresFichier) {
            ($this->apresFichier)();
        }
    }

    /** users_2/p/a/pat10 — sharding sur les deux premieres lettres du login. */
    public function bookPath(string $login): ?string
    {
        if ($this->legacyRoot === null || strlen($login) < 2) {
            return null;
        }

        return $this->legacyRoot.'/'.$login[0].'/'.$login[1].'/'.$login;
    }

    public function deplace(): bool
    {
        return $this->deplacer;
    }

    /** Fichiers et octets transferes depuis le debut (barre de progression). */
    public function fichiers(): int
    {
        return $this->copiedFiles;
    }

    public function octets(): int
    {
        return $this->copiedBytes;
    }

    /** @return array<string, int|string> */
    public function report(): array
    {
        $verbe = $this->deplacer ? 'deplace' : 'copie';

        return [
            'fichiers_'.$verbe.'s' => $this->copiedFiles,
            'volume_'.$verbe => $this->humanSize($this->copiedBytes),
            'books_introuvables' => $this->missingBooks,
        ];
    }

    public function humanSize(int|float $bytes): string
    {
        foreach (['o', 'Ko', 'Mo', 'Go'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 1).' To';
    }
}
