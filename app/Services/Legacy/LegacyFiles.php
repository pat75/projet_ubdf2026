<?php

namespace App\Services\Legacy;

use App\Models\User;
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

    private int $copiedFiles = 0;

    private int $copiedBytes = 0;

    private int $missingBooks = 0;

    public function __construct(private readonly ?string $legacyRoot) {}

    public function copyFor(User $user): void
    {
        $source = $this->bookPath($user->login);

        if ($source === null || ! is_dir($source)) {
            $this->missingBooks++;

            return;
        }

        $target = storage_path('app/public/books/'.$user->login);
        File::ensureDirectoryExists($target);

        foreach (self::SOURCE_DIRS as $dir) {
            $path = $source.'/'.$dir;

            if (! is_dir($path)) {
                continue;
            }

            foreach (File::files($path) as $file) {
                $destination = $target.'/'.$file->getFilename();

                // Le premier dossier trouve fait foi : on n'ecrase pas.
                if (file_exists($destination)) {
                    continue;
                }

                File::copy($file->getPathname(), $destination);

                $this->copiedFiles++;
                $this->copiedBytes += $file->getSize();
            }
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

    /** @return array<string, int|string> */
    public function report(): array
    {
        return [
            'fichiers_copies' => $this->copiedFiles,
            'volume_copie' => $this->humanSize($this->copiedBytes),
            'books_introuvables' => $this->missingBooks,
        ];
    }

    private function humanSize(int $bytes): string
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
