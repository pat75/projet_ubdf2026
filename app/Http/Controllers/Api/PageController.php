<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrdreRequest;
use App\Http\Requests\Api\PageRequest;
use App\Http\Resources\Api\RubriqueResource;
use App\Models\BookArticle;
use App\Models\BookSection;
use App\Services\Espace\PagesBook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Rubriques et pages (bio) du porteur du jeton (droit pages:ecrire).
 *
 * Le contenu s'ecrit en HTML, nettoye comme dans l'espace. Ecrire le
 * contenu par l'API efface la version « blocs » de l'editeur par blocs :
 * le HTML fait foi.
 */
class PageController extends Controller
{
    public function __construct(private readonly PagesBook $pages) {}

    public function storeRubrique(PageRequest $requete): JsonResponse
    {
        $this->bornerRubrique($requete);
        $rubrique = $this->pages->creerRubrique($requete->user(), trim((string) $requete->validated('titre')));

        return $this->rubriqueRendue($requete, $rubrique->id)->response()->setStatusCode(201);
    }

    public function updateRubrique(PageRequest $requete, int $id): RubriqueResource
    {
        $this->bornerRubrique($requete);
        $rubrique = $this->rubrique($requete, $id);

        if ($requete->has('titre')) {
            $this->pages->nommerRubrique($rubrique, trim($requete->validated('titre')));
        }

        if ($requete->has('publiee')) {
            $rubrique->update(['is_published' => $requete->boolean('publiee')]);
        }

        return $this->rubriqueRendue($requete, $id);
    }

    public function destroyRubrique(Request $requete, int $id): Response
    {
        abort_unless($this->pages->supprimerRubrique($this->rubrique($requete, $id)), 409, __('Supprimez d’abord les pages de cette rubrique.'));

        return response()->noContent();
    }

    public function ordonner(OrdreRequest $requete, int $id): Response
    {
        $this->pages->ordonnerPages($this->rubrique($requete, $id), $requete->validated('ids'));

        return response()->noContent();
    }

    public function storePage(PageRequest $requete, int $rubrique): JsonResponse
    {
        $page = $this->pages->creerPage($this->rubrique($requete, $rubrique), trim((string) $requete->validated('titre')));
        $this->modifierPage($page, $requete);

        return $this->rubriqueRendue($requete, $rubrique)->response()->setStatusCode(201);
    }

    public function updatePage(PageRequest $requete, int $id): RubriqueResource
    {
        $page = $this->page($requete, $id);

        if ($requete->has('titre')) {
            $this->pages->nommerPage($page, trim($requete->validated('titre')));
        }

        $this->modifierPage($page, $requete);

        return $this->rubriqueRendue($requete, $page->book_section_id);
    }

    public function destroyPage(Request $requete, int $id): Response
    {
        $this->page($requete, $id)->delete();

        return response()->noContent();
    }

    private function modifierPage(BookArticle $page, PageRequest $requete): void
    {
        if ($requete->has('contenu')) {
            $this->pages->enregistrer($page, (string) $requete->validated('contenu'));
        }

        if ($requete->has('publiee')) {
            $page->update(['status' => $requete->boolean('publiee') ? 'published' : 'draft']);
        }
    }

    private function bornerRubrique(PageRequest $requete): void
    {
        if (mb_strlen((string) $requete->input('titre')) > 100) {
            throw ValidationException::withMessages(['titre' => __('validation.max.string', ['attribute' => 'titre', 'max' => 100])]);
        }
    }

    private function rubrique(Request $requete, int $id): BookSection
    {
        return $requete->user()->sections()->findOrFail($id);
    }

    private function page(Request $requete, int $id): BookArticle
    {
        return BookArticle::whereIn('book_section_id', $requete->user()->sections()->select('id'))->findOrFail($id);
    }

    /** La rubrique telle que GET /pages la rend, pages dans l'ordre. */
    private function rubriqueRendue(Request $requete, int $id): RubriqueResource
    {
        return new RubriqueResource($this->pages->rubriques($requete->user())->firstWhere('id', $id));
    }
}
