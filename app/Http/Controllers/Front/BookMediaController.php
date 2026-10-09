<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Book\AccesPortfolios;
use App\Services\Espace\DepotImagePage;
use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use App\Support\DossierBook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sert les visuels des books, dans la declinaison demandee.
 *
 * Remplace le lien symbolique vers storage pour trois raisons :
 *
 *   - la base reference des visuels dont le fichier a disparu du disque. Le
 *     legacy traitait le cas dans son .htaccess, en renvoyant une image par
 *     defaut plutot qu'une erreur. On fait la meme chose, sinon une carte
 *     affiche une icone cassee ;
 *   - c'est ici que se produisent les declinaisons, a la demande, la ou le
 *     legacy en pre-generait neuf par visuel a l'enregistrement ;
 *   - les noms de declinaison sont valides contre `config/images.php`, ce
 *     que phpThumb ne faisait pas : il prenait ses dimensions dans l'URL.
 */
class BookMediaController extends Controller
{
    public function __construct(private readonly GenerateurImages $generateur) {}

    /** `/books/{login}/{file}` — declinaison par defaut. */
    public function show(Request $requete, string $login, string $file): BinaryFileResponse|Response
    {
        return $this->servir($requete, $login, $file, null);
    }

    /**
     * `/books/{login}/{declinaison}/{file}` — declinaison nommee.
     *
     * Methode distincte, et non un troisieme argument optionnel de `show()`.
     * Laravel passe les parametres de route scalaires **dans l'ordre de
     * l'URI**, jamais par leur nom : une signature
     * `(login, file, declinaison)` recevait la declinaison dans `$file` et
     * le fichier dans `$declinaison`. La route s'appariait correctement et
     * le service produisait bien l'image — la permutation avait lieu entre
     * les deux, et se voyait seulement en HTTP reel.
     */
    public function showDeclinaison(Request $requete, string $login, string $declinaison, string $file): BinaryFileResponse|Response
    {
        return $this->servir($requete, $login, $file, $declinaison);
    }

    private function servir(Request $requete, string $login, string $file, ?string $declinaison): BinaryFileResponse|Response
    {
        // Ces segments viennent de l'URL : ils ne doivent pas permettre de
        // remonter l'arborescence.
        if (str_contains($file, '..') || str_contains($login, '..')) {
            abort(404);
        }

        // Visuel d'un portfolio protege, encore ferme a ce visiteur : on
        // repond comme pour un fichier absent, sans rien en devoiler.
        // Rien n'est mis en cache : le meme visiteur le verra une fois le
        // portfolio deverrouille.
        $acces = app(AccesPortfolios::class)->fichier($login, $file);

        if ($acces === AccesPortfolios::FERME) {
            return $this->prive($this->parDefaut());
        }

        $format = Declinaison::nommee($declinaison);

        if ($format === null) {
            abort(404);
        }

        $source = DossierBook::chemin($login, $file);
        /*
         | WebP par negociation : meme URL, le navigateur qui annonce
         | `image/webp` dans son en-tete Accept le recoit ; les autres
         | (clients mail, certains robots de partage) gardent le JPEG/PNG.
         | Les gabarits n'ont donc rien a changer.
         */
        $webp = config('images.webp') && str_contains((string) $requete->header('Accept'), 'image/webp');
        $produite = $this->generateur->produire($source, $format, $webp);

        if ($produite === null) {
            return $this->parDefaut();
        }

        $reponse = response()->file($produite, [
            // Un an : remplacer un visuel dans l'espace creatif produira une
            // nouvelle entree de cache, l'URL portant le nom du fichier.
            'Cache-Control' => 'public, max-age=31536000, immutable',
            // Deux reponses possibles pour une URL : un cache intermediaire
            // doit les distinguer selon l'Accept du demandeur.
            'Vary' => 'Accept',
        ]);

        // Un visuel protege, meme ouvert a ce visiteur, ne doit pas finir
        // dans un cache partage.
        return $acces === AccesPortfolios::OUVERT ? $this->prive($reponse) : $reponse;
    }

    /**
     * `/books/{login}/cms/{chemin}` — images inserees dans les pages.
     *
     * Servies telles quelles, sans declinaison : elles sont deja a la
     * taille choisie par le createur dans l'editeur.
     */
    public function cms(string $login, string $chemin): BinaryFileResponse|Response
    {
        // cms/ : images deposees depuis l'editeur ; img_cms/ : celles du legacy.
        foreach ([DepotImagePage::DOSSIER, DepotImagePage::DOSSIER_LEGACY] as $dossier) {
            $racine = realpath(DossierBook::chemin($login, $dossier));
            $fichier = $racine ? realpath($racine.'/'.rawurldecode($chemin)) : false;

            // realpath() resout les « .. » : le fichier doit rester sous la racine.
            if ($fichier && str_starts_with($fichier, $racine.DIRECTORY_SEPARATOR) && is_file($fichier)) {
                return response()->file($fichier, ['Cache-Control' => 'public, max-age=604800']);
            }
        }

        return $this->parDefaut();
    }

    /** Trame grise du legacy, affichee a la place d'un visuel manquant. */
    private function prive(BinaryFileResponse|Response $reponse): BinaryFileResponse|Response
    {
        $reponse->headers->set('Cache-Control', 'no-store, private');

        return $reponse;
    }

    private function parDefaut(): BinaryFileResponse|Response
    {
        $defaut = public_path('img_default/ultra-book_default_trame_91x91.gif');

        return is_file($defaut)
            // Cache court : le visuel peut arriver ensuite (transfert en
            // cours, depot) et doit alors s'afficher sans attendre une semaine.
            ? response()->file($defaut, ['Cache-Control' => 'public, max-age=7200'])
            : response('', 404);
    }
}
