<?php

namespace App\Services\Espace;

use App\Models\User;
use App\Support\DossierBook;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

/**
 * Archive les images d'un book supprime par son createur : le dossier
 * books/a/d/o/<login> est compresse dans storage/books_effaces/, puis
 * retire du disque public. Le dossier n'est efface qu'une fois l'archive
 * fermee sans erreur : rien n'est perdu si la compression echoue.
 *
 * L'archive porte aussi `creatif.json` : la fiche du createur et le
 * contenu de son book (reglages, facturation, galeries, visuels, pages,
 * actualites, factures), de quoi le reconstituer. La messagerie et les
 * statistiques de visite n'y figurent pas.
 */
final class ArchiveBook
{
    public const DOSSIER = 'books_effaces';

    private const IMAGES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'bmp', 'tif', 'tiff', 'heic'];

    /** Chemin de l'archive creee. */
    public function archiver(User $creatif): string
    {
        $source = DossierBook::chemin($creatif->login);

        $images = File::isDirectory($source)
            ? collect(File::allFiles($source))
                ->filter(fn ($f) => in_array(mb_strtolower($f->getExtension()), self::IMAGES, true))
            : collect();

        File::ensureDirectoryExists(storage_path(self::DOSSIER));
        $archive = storage_path(self::DOSSIER.'/'.$creatif->login.'_'.now()->format('Ymd_His').'.zip');

        $zip = new ZipArchive;
        if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            throw new RuntimeException("Archive impossible : {$archive}");
        }
        $zip->addFromString('creatif.json', $this->informations($creatif));
        foreach ($images as $f) {
            $zip->addFile($f->getPathname(), $f->getRelativePathname());
        }
        if (! $zip->close()) {
            throw new RuntimeException("Archive incomplete : {$archive}");
        }

        File::deleteDirectory($source);

        return $archive;
    }

    private function informations(User $creatif): string
    {
        $creatif->loadMissing([
            'category', 'bookSetting', 'billingProfile', 'galleries', 'media',
            'sections', 'articles', 'pageImages', 'invoices',
        ]);

        return json_encode([
            'archive_le' => now()->toIso8601String(),
            'creatif' => $creatif->toArray(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
