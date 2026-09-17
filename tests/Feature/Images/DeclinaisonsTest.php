<?php

use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;

/**
 * Reference : les declinaisons pre-generees de `users_2/` du site 2019.
 * Les dimensions attendues viennent de `conf/conf_img.php`, verifiees sur
 * les fichiers reels.
 */
beforeEach(function () {
    $this->generateur = app(GenerateurImages::class);
    $this->dossier = sys_get_temp_dir().'/ubdf-images-'.getmypid();
    @mkdir($this->dossier, 0o755, true);
});

afterEach(function () {
    File::deleteDirectory($this->dossier);
});

function sourceDe(int $largeur, int $hauteur, string $dossier): string
{
    $chemin = $dossier.'/source-'.$largeur.'x'.$hauteur.'.jpg';

    app(ImageManager::class)
        ->createImage($largeur, $hauteur)
        ->save($chemin);

    return $chemin;
}

function dimensions(?string $chemin): ?string
{
    if ($chemin === null) {
        return null;
    }

    $taille = getimagesize($chemin);

    return $taille ? $taille[0].'x'.$taille[1] : null;
}

it('reproduit les dimensions du legacy pour une source portrait', function () {
    // Source 1240x1754, mesuree sur users_2/9/0/902lonelyb.
    $source = sourceDe(1240, 1754, $this->dossier);

    $attendu = [
        'ptf_small' => '34x48',
        'adm_small' => '24x34',
        'ptf_medium' => '550x778',
        'iph_small' => '75x75',
        'iph_medium' => '320x453',
        'adm_medium' => '127x180',
        'front_desk' => '250x136',
        'front_mob' => '140x76',
    ];

    foreach ($attendu as $nom => $taille) {
        expect(dimensions($this->generateur->produire($source, Declinaison::nommee($nom))))
            ->toBe($taille, $nom);
    }
});

it('reproduit les dimensions du legacy pour une source carree', function () {
    $source = sourceDe(500, 500, $this->dossier);

    $attendu = [
        'ptf_small' => '48x48',
        'adm_small' => '24x24',
        // 500 < 550 : la source garde sa taille, elle n'est pas agrandie.
        'ptf_medium' => '500x500',
        'iph_small' => '75x75',
        'iph_medium' => '320x320',
        'adm_medium' => '180x180',
        'front_desk' => '250x136',
        'front_mob' => '140x76',
    ];

    foreach ($attendu as $nom => $taille) {
        expect(dimensions($this->generateur->produire($source, Declinaison::nommee($nom))))
            ->toBe($taille, $nom);
    }
});

it('n agrandit jamais une source plus petite que la boite', function () {
    $source = sourceDe(30, 20, $this->dossier);

    expect(dimensions($this->generateur->produire($source, Declinaison::nommee('ptf_medium'))))
        ->toBe('30x20');
});

it('rogne pour les declinaisons cover et pas pour les autres', function () {
    $source = sourceDe(800, 200, $this->dossier);

    // `cover` remplit la boite exactement, quitte a couper.
    expect(dimensions($this->generateur->produire($source, Declinaison::nommee('front_desk'))))
        ->toBe('250x136')
        ->and(dimensions($this->generateur->produire($source, Declinaison::nommee('iph_small'))))
        ->toBe('75x75');

    // `contain` respecte le rapport : 800x200 tient dans 48x48 en 48x12.
    expect(dimensions($this->generateur->produire($source, Declinaison::nommee('ptf_small'))))
        ->toBe('48x12');
});

it('ne connait que les declinaisons declarees', function () {
    // phpThumb prenait ses dimensions dans l'URL : n'importe qui pouvait
    // faire fabriquer n'importe quelle image.
    expect(Declinaison::nommee('900x900'))->toBeNull()
        ->and(Declinaison::nommee('inconnue'))->toBeNull()
        ->and(Declinaison::nommee('ptf_medium'))->not->toBeNull();
});

it('retombe sur la declinaison par defaut sans nom', function () {
    expect(Declinaison::nommee(null)?->nom)->toBe(config('images.defaut'));
});

it('reutilise le cache tant que la source n a pas bouge', function () {
    $source = sourceDe(400, 400, $this->dossier);
    $format = Declinaison::nommee('ptf_small');

    $premier = $this->generateur->produire($source, $format);

    // Le cache vaut tant qu'il est plus recent que sa source. On recule la
    // source, puis on date le cache d'une valeur reconnaissable : si elle
    // survit, rien n'a ete refabrique.
    touch($source, time() - 400);
    touch($premier, $repere = time() - 200);
    clearstatcache();

    $second = $this->generateur->produire($source, $format);
    clearstatcache();

    expect($second)->toBe($premier)
        ->and(filemtime($second))->toBe($repere);
});

it('regenere quand la source est plus recente que le cache', function () {
    $source = sourceDe(400, 400, $this->dossier);
    $format = Declinaison::nommee('ptf_small');

    $cache = $this->generateur->produire($source, $format);
    touch($cache, time() - 3600);
    touch($source, time());

    $this->generateur->produire($source, $format);

    expect(filemtime($cache))->toBeGreaterThan(time() - 60);
});

it('rend null plutot que d exploser sur un fichier qui n est pas une image', function () {
    $faux = $this->dossier.'/faux.jpg';
    file_put_contents($faux, 'ceci n est pas une image');

    expect($this->generateur->produire($faux, Declinaison::nommee('ptf_small')))->toBeNull();
});

it('rend null sur une source absente', function () {
    expect($this->generateur->produire($this->dossier.'/absent.jpg', Declinaison::nommee('ptf_small')))
        ->toBeNull();
});

it('refuse une source dont la surface en pixels depasse la borne', function () {
    // Une image tres grande se compresse a quelques kilo-octets : c'est
    // l'ouvrir qui sature la memoire, pas la lire.
    config(['images.pixels_max' => 1000]);

    $source = sourceDe(200, 200, $this->dossier);

    expect($this->generateur->produire($source, Declinaison::nommee('ptf_small')))->toBeNull();
});

it('ne fait pas collisionner deux books au meme nom de fichier', function () {
    @mkdir($a = $this->dossier.'/a', 0o755, true);
    @mkdir($b = $this->dossier.'/b', 0o755, true);

    $format = Declinaison::nommee('ptf_small');

    expect($this->generateur->cheminCache($a.'/visuel.jpg', $format))
        ->not->toBe($this->generateur->cheminCache($b.'/visuel.jpg', $format));
});
