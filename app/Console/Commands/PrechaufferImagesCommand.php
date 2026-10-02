<?php

namespace App\Console\Commands;

use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use App\Support\DossierBook;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Fabrique d'avance les declinaisons des books, en WebP (et, sur option,
 * dans le format d'origine).
 *
 * Le generateur produit deja tout a la demande : cette commande ne fait
 * que payer ce cout une fois, de nuit, au lieu de le faire payer au
 * premier visiteur de chaque book (un book de 200 visuels prend plusieurs
 * secondes a sa premiere ouverture). Idempotente : une declinaison deja a
 * jour est sautee, relancer ne refait que ce qui manque.
 *
 * Les sources ne sont pas touchees : elles restent la reference, bornees
 * a 1980x3600 a l'envoi.
 */
class PrechaufferImagesCommand extends Command
{
    /** Declinaisons vues a l'ouverture d'un book ou du portail. */
    private const PAR_DEFAUT = ['ptf_medium', 'iph_medium', 'front_desk', 'front_mob', 'carre_183', 'carre_335', 'carre_368'];

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    protected $signature = 'ubdf:prechauffer-images
        {--login=* : Seulement ces books}
        {--lettre=* : Seulement les books dont le login commence par ces lettres (lots paralleles)}
        {--declinaison=* : Declinaisons a produire (defaut : celles des pages)}
        {--origine : Produit aussi la version JPEG/PNG (clients sans WebP)}
        {--dry-run : Compte les fichiers sans rien produire}';

    protected $description = 'Pre-genere les declinaisons WebP des visuels de books';

    public function handle(GenerateurImages $generateur): int
    {
        $noms = $this->option('declinaison') ?: self::PAR_DEFAUT;
        $declinaisons = array_map(fn (string $nom) => Declinaison::nommee($nom) ?? $this->fail("Declinaison inconnue : {$nom}"), $noms);

        $sources = $this->sources();
        $this->info(count($sources).' visuels, declinaisons : '.implode(', ', $noms));

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $formats = $this->option('origine') ? [true, false] : [true];
        $produites = 0;
        $echecs = 0;

        $this->withProgressBar($sources, function (string $source) use ($generateur, $declinaisons, $formats, &$produites, &$echecs) {
            foreach ($declinaisons as $declinaison) {
                foreach ($formats as $webp) {
                    $generateur->produire($source, $declinaison, $webp) === null ? $echecs++ : $produites++;
                }
            }
        });

        $this->newLine();
        $this->info("Declinaisons a jour : {$produites}, sources illisibles ou trop grandes : {$echecs}.");

        return self::SUCCESS;
    }

    /**
     * Visuels de premier niveau de chaque dossier de book. Les images des
     * pages (cms/, img_cms/) sont servies telles quelles, sans declinaison.
     *
     * @return list<string>
     */
    private function sources(): array
    {
        $racine = storage_path('app/public/'.DossierBook::RACINE);
        $logins = $this->option('login');

        // Premier segment du dossier : la premiere lettre du login, ce qui
        // permet de lancer plusieurs commandes en parallele, une par lot.
        $racines = array_values(array_filter(
            array_map(fn (string $l) => $racine.'/'.mb_strtolower($l), $this->option('lettre')) ?: [$racine],
            'is_dir',
        ));
        $profondeur = $this->option('lettre') ? 2 : 3;

        $dossiers = match (true) {
            $logins !== [] => array_filter(array_map(fn (string $login) => DossierBook::chemin($login), $logins), 'is_dir'),
            $racines === [] => [],
            default => iterator_to_array((new Finder)->directories()->in($racines)->depth($profondeur), false),
        };

        if ($dossiers === []) {
            return [];
        }

        $fichiers = (new Finder)->files()->in(array_map(fn ($d) => (string) $d, $dossiers))->depth(0)
            ->name('/\.('.implode('|', self::EXTENSIONS).')$/i');

        return array_map(fn ($f) => $f->getPathname(), iterator_to_array($fichiers, false));
    }
}
