<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DepotRequest;
use App\Http\Requests\Api\VisuelRequest;
use App\Http\Resources\Api\VisuelResource;
use App\Models\Media;
use App\Services\Espace\DepotVisuel;
use App\Services\Espace\Portfolios;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Visuels du porteur du jeton (droit images:ecrire) : depot, textes,
 * deplacement, remplacement, suppression. Memes services que l'espace,
 * donc memes quotas, memes compteurs, meme ordre du book.
 */
class VisuelController extends Controller
{
    public function __construct(
        private readonly Portfolios $portfolios,
        private readonly DepotVisuel $depot,
    ) {}

    public function store(DepotRequest $requete, int $portfolio): JsonResponse
    {
        $galerie = $requete->user()->galleries()->whereNull('parent_id')->findOrFail($portfolio);

        $visuel = $this->tenter('fichier', fn () => $requete->hasFile('fichier')
            ? $this->depot->deposer($galerie, $requete->file('fichier'))
            : $this->depot->deposerVideo($galerie, $requete->validated('video')));

        return (new VisuelResource($visuel))->response()->setStatusCode(201);
    }

    public function update(VisuelRequest $requete, int $id): VisuelResource
    {
        $visuel = $this->visuel($requete, $id);
        $champs = $requete->validated();

        if (isset($champs['portfolio_id']) && $champs['portfolio_id'] !== $visuel->gallery_id) {
            $arrivee = $requete->user()->galleries()->whereNull('parent_id')->findOrFail($champs['portfolio_id']);
            // Le visuel arrive en tete du portfolio, comme un depot.
            $ordre = $this->portfolios->dansLOrdre($arrivee->load('media'))->pluck('id')->all();
            $this->portfolios->deplacerVisuel($visuel, $arrivee, [$visuel->id, ...$ordre]);
        }

        $visuel->fill(array_filter([
            'title' => array_key_exists('titre', $champs) ? trim((string) $champs['titre']) : null,
            'description' => array_key_exists('legende', $champs) ? trim((string) $champs['legende']) : null,
        ], fn ($v) => $v !== null))->save();

        return new VisuelResource($visuel->fresh());
    }

    public function remplacer(Request $requete, int $id): VisuelResource
    {
        $requete->validate(['fichier' => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max:'.config('images.envoi_ko_max')]]);
        $visuel = $this->visuel($requete, $id);

        return new VisuelResource($this->tenter('fichier', fn () => $this->depot->remplacer($visuel, $requete->file('fichier'))));
    }

    public function destroy(Request $requete, int $id): Response
    {
        $this->portfolios->supprimerVisuel($requete->user(), $this->visuel($requete, $id));

        return response()->noContent();
    }

    private function visuel(Request $requete, int $id): Media
    {
        return Media::where('user_id', $requete->user()->id)->findOrFail($id);
    }

    /** Les refus metier (quota, image illisible, video introuvable) deviennent une 422. */
    private function tenter(string $champ, \Closure $action): Media
    {
        try {
            return $action();
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([$champ => $e->getMessage()]);
        }
    }
}
