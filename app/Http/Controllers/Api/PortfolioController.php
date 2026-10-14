<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrdreRequest;
use App\Http\Requests\Api\PortfolioRequest;
use App\Http\Resources\Api\PortfolioResource;
use App\Models\Gallery;
use App\Services\Espace\Portfolios;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Portfolios du porteur du jeton (droits images:*).
 *
 * Toute requete est limitee au creatif : le portfolio d'un autre repond 404.
 */
class PortfolioController extends Controller
{
    public function __construct(private readonly Portfolios $portfolios) {}

    public function index(Request $requete): AnonymousResourceCollection
    {
        return PortfolioResource::collection($this->portfolios->liste($requete->user())->loadCount('media'));
    }

    public function show(Request $requete, int $id): PortfolioResource
    {
        return $this->rendu($this->galerie($requete, $id));
    }

    public function store(PortfolioRequest $requete): JsonResponse
    {
        $galerie = $this->portfolios->creer($requete->user(), $requete->validated('nom'));
        $this->modifierGalerie($galerie, $requete);

        return $this->rendu($galerie)->response()->setStatusCode(201);
    }

    public function update(PortfolioRequest $requete, int $id): PortfolioResource
    {
        $galerie = $this->galerie($requete, $id);
        $this->modifierGalerie($galerie, $requete);

        return $this->rendu($galerie);
    }

    public function destroy(Request $requete, int $id): Response
    {
        $this->portfolios->supprimer($requete->user(), $this->galerie($requete, $id));

        return response()->noContent();
    }

    public function ordonner(OrdreRequest $requete): Response
    {
        $this->portfolios->ordonner($requete->user(), $requete->validated('ids'));

        return response()->noContent();
    }

    public function ordonnerVisuels(OrdreRequest $requete, int $id): Response
    {
        $this->portfolios->ranger($this->galerie($requete, $id), $requete->validated('ids'));

        return response()->noContent();
    }

    private function modifierGalerie(Gallery $galerie, PortfolioRequest $requete): void
    {
        $champs = $requete->validated();

        if (array_key_exists('nom', $champs)) {
            $galerie->name = trim($champs['nom']);
        }

        if (array_key_exists('publie', $champs)) {
            $galerie->status = $champs['publie'] ? 'published' : 'draft';
        }

        if (array_key_exists('mot_de_passe', $champs)) {
            $galerie->password = trim((string) $champs['mot_de_passe']) ?: null;
        }

        $galerie->save();
    }

    private function galerie(Request $requete, int $id): Gallery
    {
        return $requete->user()->galleries()->whereNull('parent_id')->findOrFail($id);
    }

    private function rendu(Gallery $galerie): PortfolioResource
    {
        $galerie->load('media');

        return new PortfolioResource($galerie->setRelation('visuelsOrdonnes', $this->portfolios->dansLOrdre($galerie)));
    }
}
