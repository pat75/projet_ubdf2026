<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Reinstalle dans `public/` les fichiers du front repris du site 2019.
 *
 * Ces 37 Mo ne sont pas versionnes : ce sont des fichiers d'origine, repris
 * tels quels. Ils etaient jusqu'ici recopies a la main, ce qui posait un
 * probleme des lors qu'il a fallu **corriger** l'un d'eux : la correction
 * vivait dans un fichier ignore par git, et disparaissait a la copie
 * suivante sans que rien ne le signale.
 *
 * Cette commande fait donc les deux : elle copie, puis elle applique les
 * retouches connues. Elle est idempotente.
 */
class InstallFrontAssetsCommand extends Command
{
    protected $signature = 'ubdf:install-front-assets {--force : Ecrase les dossiers deja presents}';

    protected $description = 'Copie les assets du front 2019 dans public/ et y applique les retouches';

    /** Dossiers repris tels quels depuis la racine du site 2019. */
    private const DOSSIERS = [
        'html_pages_v2018',
        'img_front',
        'img_front_df',
        'img_default',
        'js_jquery',
        '_video',

        // Themes des books (phase 4) : chaque habillage charge ses feuilles
        // et scripts depuis ces dossiers, a ces chemins exacts.
        '2012_web',
        '2012_js',
        '2012_css',
        '2012_img',
        '2011_css',
        '2011_img',
        '2010_js',
        '2010_css',
        '2010_images',

        // Versions iPhone des books (jaipho).
        '2011_iphone',
    ];

    /**
     * Sous-dossiers versionnes dans ce depot : la copie ne les ecrase pas.
     * js2019 est le JavaScript du portail, reecrit pour remplacer jQuery
     * (_doc/17_remplacement_jquery.md) ; la version du site 2019 n'est plus
     * la reference.
     */
    private const VERSIONNES = [
        'html_pages_v2018/_/js2019',
    ];

    /**
     * Retouches appliquees apres copie.
     *
     * `/front/ajax_2010.php` : nginx attribue toute URL en `.php` a PHP-FPM
     * avant que Laravel ne la voie. Le fichier n'existant plus, le serveur
     * repondait « File not found. » de lui-meme — sans qu'aucun test PHP ne
     * puisse le montrer, le client de test ne passant pas par nginx. Le
     * point d'entree de l'inscription est donc `/inscription`. Cette
     * retouche vivait ici pour js2019/js_core_inscription.js ; le fichier
     * est desormais versionne, corrige a la source.
     *
     * @var array<string, array<string, string>>
     */
    private const RETOUCHES = [];

    public function handle(): int
    {
        $source = rtrim((string) config('ubdf.legacy_path'), '/');

        if (! File::isDirectory($source)) {
            $this->error("Source introuvable : {$source}");

            return self::FAILURE;
        }

        foreach (self::DOSSIERS as $dossier) {
            $de = $source.'/'.$dossier;
            $vers = public_path($dossier);

            if (! File::isDirectory($de)) {
                $this->warn("Absent de la source, ignore : {$dossier}");

                continue;
            }

            if (File::isDirectory($vers) && ! $this->option('force')) {
                $this->line("Deja present, conserve : {$dossier}");

                continue;
            }

            $this->copierSansVersionnes($de, $vers, $dossier);
            $this->info("Copie : {$dossier}");
        }

        $this->neutraliserScripts();
        $this->retoucher();

        return self::SUCCESS;
    }

    /**
     * Copie un dossier sans toucher a ses sous-dossiers versionnes : ils
     * sont mis de cote le temps de la copie, puis remis en place.
     */
    private function copierSansVersionnes(string $de, string $vers, string $dossier): void
    {
        $abri = storage_path('framework/cache/front-versionne-'.uniqid());
        $proteges = array_filter(self::VERSIONNES, fn ($v) => str_starts_with($v, $dossier.'/') && File::isDirectory(public_path($v)));

        foreach ($proteges as $v) {
            File::moveDirectory(public_path($v), $abri.'/'.$v);
        }

        File::copyDirectory($de, $vers);

        foreach ($proteges as $v) {
            File::deleteDirectory(public_path($v));
            File::moveDirectory($abri.'/'.$v, public_path($v));
        }

        File::deleteDirectory($abri);
    }

    /**
     * Retire des copies tout fichier que PHP-FPM executerait.
     *
     * Les dossiers d'assets du site 2019 embarquent des scripts sans
     * rapport avec l'affichage : un plugin WordPress entier
     * (`2012_js/simple-social-bookmarks`), un `index.php` de demonstration
     * (`zoom2016/_/js/ish-master`), un gabarit Savant. Copies dans
     * `public/`, ils deviendraient des points d'entree executables par
     * nginx — hors de Laravel, de ses middlewares et de sa protection
     * CSRF. Ils sont supprimes des copies (la source, en lecture seule,
     * n'est pas touchee).
     */
    private function neutraliserScripts(): void
    {
        $extensions = ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar'];

        foreach (self::DOSSIERS as $dossier) {
            $racine = public_path($dossier);

            if (! File::isDirectory($racine)) {
                continue;
            }

            foreach (File::allFiles($racine, hidden: true) as $fichier) {
                $nom = strtolower($fichier->getFilename());
                $ext = strtolower($fichier->getExtension());

                if (in_array($ext, $extensions, true) || str_contains($nom, '.php.') || $nom === '.htaccess') {
                    File::delete($fichier->getPathname());
                    $this->line('Script retire : '.$dossier.'/'.$fichier->getRelativePathname());
                }
            }
        }
    }

    private function retoucher(): void
    {
        foreach (self::RETOUCHES as $chemin => $substitutions) {
            $fichier = public_path($chemin);

            if (! File::exists($fichier)) {
                $this->warn("Retouche impossible, fichier absent : {$chemin}");

                continue;
            }

            $contenu = File::get($fichier);
            $avant = $contenu;

            foreach ($substitutions as $cherche => $remplace) {
                $contenu = str_replace($cherche, $remplace, $contenu);
            }

            if ($contenu === $avant) {
                $this->line("Retouche deja appliquee : {$chemin}");

                continue;
            }

            File::put($fichier, $contenu);
            $this->info("Retouche : {$chemin}");
        }
    }
}
