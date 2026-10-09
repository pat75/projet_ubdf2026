<?php

namespace App\Console\Commands;

use App\Services\Images\Declinaison;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;

/**
 * Ramene les originaux des books aux dimensions d'un depot dans l'espace :
 * declinaison `source` (1980 x 3600) et qualite `images.qualite` (82), lues
 * dans config/images.php pour suivre DepotVisuel si le reglage change.
 *
 * Le legacy acceptait n'importe quelle taille : des illustrations de
 * 8268 x 8268 px (68 Mpx) depassent `images.pixels_max` et ne s'affichent
 * pas. Un visuel depose aujourd'hui ne depasse jamais `source`.
 *
 * L'original est deplace dans storage/app/originaux/<meme chemin> avant
 * d'etre remplace : rien n'est perdu, le dossier se supprime a la main une
 * fois le resultat verifie. La nouvelle date du fichier perime seule les
 * declinaisons en cache (GenerateurImages compare les dates).
 *
 * Reprenable : le dernier book termine et les compteurs sont notes dans
 * storage/app/reduire-images-<essai|reduction>.json apres chaque book ; une
 * relance repart du book suivant, sans reparcourir le disque (lent sur
 * O2switch). Fichier efface en fin de passe. `--recommencer` l'ignore.
 *
 * Progression sur une ligne reecrite : books traites / total, pourcentage.
 * Les GIF sont laisses tels quels (souvent animes).
 */
class ReduireImagesCommand extends Command
{
    protected $signature = 'ubdf:images:reduire
        {--dry-run : Liste les images a reduire sans rien modifier}
        {--recommencer : Ignore la reprise et repart du premier book}';

    protected $description = 'Reduit les originaux des books aux dimensions d un depot (1980 x 3600, original sauvegarde, reprenable)';

    public function handle(ImageManager $manager): int
    {
        $source = Declinaison::nommee('source');
        $qualite = (int) config('images.qualite');
        $essai = (bool) $this->option('dry-run');
        $racine = storage_path('app/public/books');
        $sauvegarde = storage_path('app/originaux');
        $etat = storage_path('app/reduire-images-'.($essai ? 'essai' : 'reduction').'.json');

        // books/<a>/<b>/<c>/<login> : glob() rend la liste triee, l'ordre
        // est donc stable d'une passe a l'autre (condition de la reprise).
        $books = glob($racine.'/*/*/*/*', GLOB_ONLYDIR) ?: [];
        $total = count($books);

        $c = ['dernier' => null, 'lues' => 0, 'trouvees' => 0, 'erreurs' => 0];

        if (! $this->option('recommencer') && is_file($etat)) {
            $c = array_merge($c, json_decode((string) file_get_contents($etat), true) ?: []);
            $this->components->info("Reprise apres {$c['dernier']}");
        }

        foreach ($books as $i => $book) {
            $relatifBook = substr($book, strlen($racine) + 1);

            if ($c['dernier'] !== null && strcmp($relatifBook, $c['dernier']) <= 0) {
                continue;
            }

            // Les originaux seulement, pas les sous-dossiers (img_cms, cms).
            foreach (new \DirectoryIterator($book) as $f) {
                if (! $f->isFile() || ! preg_match('/\.(jpe?g|png|webp)$/i', $f->getFilename())) {
                    continue;
                }

                $c['lues']++;
                $fichier = $f->getPathname();
                $taille = @getimagesize($fichier);

                if ($taille === false || ($taille[0] <= $source->largeur && $taille[1] <= $source->hauteur)) {
                    continue;
                }

                $relatif = $relatifBook.'/'.$f->getFilename();
                $this->ecrire("{$taille[0]}x{$taille[1]}  {$relatif}", true);

                if ($essai) {
                    $c['trouvees']++;

                    continue;
                }

                $temporaire = $fichier.'.reduction.'.pathinfo($fichier, PATHINFO_EXTENSION);

                try {
                    $manager->decodePath($fichier)->scaleDown($source->largeur, $source->hauteur)->save($temporaire, quality: $qualite);

                    File::ensureDirectoryExists(dirname($sauvegarde.'/'.$relatif));
                    File::move($fichier, $sauvegarde.'/'.$relatif);
                    File::move($temporaire, $fichier);
                    $c['trouvees']++;
                } catch (\Throwable $e) {
                    @unlink($temporaire);
                    $c['erreurs']++;
                    $this->ecrire("ERREUR {$relatif} : {$e->getMessage()}", true);
                }
            }

            $c['dernier'] = $relatifBook;
            file_put_contents($etat, json_encode($c));

            $this->ecrire(sprintf('[%d/%d] %5.1f %%  %d lues, %d %s, %d erreurs  %s',
                $i + 1, $total, ($i + 1) * 100 / max(1, $total), $c['lues'], $c['trouvees'],
                $essai ? 'a reduire' : 'reduites', $c['erreurs'], $relatifBook));
        }

        @unlink($etat);
        $this->newLine();
        $verbe = $essai ? 'a reduire' : 'reduites';
        $this->components->info("{$c['lues']} images lues, {$c['trouvees']} {$verbe}, {$c['erreurs']} erreurs.");

        return $c['erreurs'] ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Ligne de progression reecrite sur place ; $garder la fige au-dessus
     * (image trouvee, erreur) pour qu'elle reste dans le journal.
     */
    private function ecrire(string $texte, bool $garder = false): void
    {
        $this->output->write("\r\033[K".$texte.($garder ? PHP_EOL : ''));
    }
}
