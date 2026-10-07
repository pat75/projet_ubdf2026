<?php

namespace App\Repository;

use App\Models\Media;
use App\Models\User;
use App\Support\Recherche;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Selection des books affiches sur le portail.
 *
 * Reprend les trois modes de tri du front 2018, identifies dans les URL du
 * legacy par un code de trois lettres :
 *   sel — selection editoriale du moment
 *   ult — ultra-selection (comptes distingues)
 *   lub — les ultra-books (comptes abonnes)
 */
class BookRepository
{
    public const PER_PAGE = 10;

    /**
     * @return Collection<int, User>
     */
    public function portfolios(
        string $selection = 'sel',
        ?string $categorySlug = null,
        int $page = 0,
        string $brand = 'ub',
        ?int $perPage = null,
    ): Collection {
        $perPage ??= self::PER_PAGE;

        return $this->baseQuery($brand)
            ->when($categorySlug && $categorySlug !== 'all',
                fn (Builder $query) => $query->whereRelation('category', 'slug', $categorySlug))
            // « sel » n'est pas un filtre mais un tri : le legacy classait
            // par `user.us_affhome ASC`, ce qui remonte la selection
            // editoriale en tete sans ecarter les autres books.
            ->when($selection === 'ult', fn (Builder $query) => $query->where('is_selected', true))
            ->when($selection === 'lub', fn (Builder $query) => $query->where('plan', '>', 0))
            // « Dernieres selections » du legacy (front/action.php) :
            // `st.st_selection_date DESC`, reprise dans home_selection_at par
            // ubdf:legacy:accueil --dates. La selection d'abord, les plus
            // recentes en tete ; puis les books les plus fournis.
            ->orderByDesc('in_home_selection')
            ->orderByDesc('home_selection_at')
            ->orderByDesc('media_count')
            ->orderBy('users.id')
            ->skip($page * $perPage)
            ->take($perPage)
            ->get();
    }

    /**
     * Un book du portail par son login (ancre « #login » d'une page), aux
     * memes conditions de diffusion que les grilles.
     */
    public function parLogin(string $login, string $brand = 'ub'): ?User
    {
        return $this->baseQuery($brand)->where('login', $login)->first();
    }

    /**
     * Nombre de books par categorie, en une requete.
     *
     * Affiche sur la derniere carte de chaque bloc de l'accueil
     * (« 13 164 illustrateurs »).
     *
     * @return array<string, int>
     */
    public function countsByCategory(string $brand = 'ub'): array
    {
        return $this->baseQuery($brand)
            ->join('categories', 'categories.id', '=', 'users.category_id')
            ->groupBy('categories.slug')
            ->pluck(DB::raw('COUNT(*)'), 'categories.slug')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    public function count(?string $categorySlug = null, string $brand = 'ub'): int
    {
        return $this->baseQuery($brand)
            ->when($categorySlug && $categorySlug !== 'all',
                fn (Builder $query) => $query->whereRelation('category', 'slug', $categorySlug))
            ->count();
    }


    /**
     * Recherche du portail.
     *
     * Deux modes, ceux du front 2018 :
     *   mcles  — sur les mots-cles declares par le creatif
     *   pseudo — sur le login, le prenom et le nom
     *
     * Ecart assume avec le legacy sur le mode « mcles » : celui-ci exigeait
     * les deux premiers termes (AND) et ignorait les suivants, ce qui rendait
     * une requete de trois mots presque toujours vide. Ici un book ressort
     * des qu'il porte **un** des termes, et ceux qui en portent le plus
     * remontent en tete.
     *
     * @return Collection<int, User>
     */
    public function rechercher(Recherche $recherche): Collection
    {
        $query = $this->requeteRecherche($recherche);

        if (! $query instanceof Builder) {
            return new Collection;
        }

        return $query
            ->orderByDesc('pertinence')
            ->orderByDesc('users.is_selected')
            ->orderByDesc('users.media_count')
            ->skip($recherche->page * self::PER_PAGE)
            ->take(self::PER_PAGE)
            ->get();
    }

    public function compterRecherche(Recherche $recherche): int
    {
        $query = $this->requeteRecherche($recherche);

        // Le tri par pertinence n'a pas de sens ici, et MySQL refuse un
        // COUNT sur une selection qui porte des parametres lies.
        return $query instanceof Builder ? $query->reorder()->getQuery()->getCountForPagination() : 0;
    }

    /**
     * Requete commune au comptage et a la liste, ou null si la saisie ne
     * contient aucun terme exploitable.
     */
    private function requeteRecherche(Recherche $recherche): ?Builder
    {
        $termes = $recherche->termes();

        if ($termes === []) {
            return null;
        }

        // Colonnes interrogees selon le mode ; la pertinence est le nombre
        // de termes trouves, calculee par la base pour ne pas avoir a
        // rendre la requete une seconde fois en PHP.
        // Le mode mots-cles couvre aussi les noms : c'est celui d'une saisie
        // libre (Entree), ou l'on tape aussi bien « aquarelle » que « adolie ».
        $noms = ['users.login', 'users.firstname', 'users.lastname'];
        $colonnes = $recherche->mode === 'pseudo'
            ? $noms
            : ['book_settings.keywords', ...$noms];

        $query = $this->baseQuery($recherche->brand)
            ->when($recherche->mode !== 'pseudo', fn (Builder $q) => $q->join(
                'book_settings', 'book_settings.user_id', '=', 'users.id'
            ))
            ->when($recherche->categorie && $recherche->categorie !== 'tous',
                fn (Builder $q) => $q->whereRelation('category', 'slug', $recherche->categorie))
            ->when($recherche->selection, fn (Builder $q) => $q->where('users.is_selected', true))
            ->when($recherche->abonnes, fn (Builder $q) => $q->where('users.plan', '>', 0))
            ->select('users.*');

        $pertinence = [];
        $valeurs = [];

        foreach ($termes as $terme) {
            $motif = '%'.$this->echapper($terme).'%';

            $trouve = implode(' OR ', array_map(fn ($colonne) => "{$colonne} LIKE ?", $colonnes));
            array_push($valeurs, ...array_fill(0, count($colonnes), $motif));

            // En mode mots-cles, un book ressort aussi par les mots-cles IA
            // de ses visuels publies (MEDIA_TAG_EXISTE).
            if ($recherche->mode !== 'pseudo') {
                $trouve .= ' OR '.self::MEDIA_TAG_EXISTE;
                $valeurs[] = $motif;
            }

            $pertinence[] = "({$trouve})";
        }

        $conditions = implode(' + ', $pertinence);

        return $query
            ->selectRaw("({$conditions}) AS pertinence", $valeurs)
            ->whereRaw("({$conditions}) > 0", $valeurs);
    }

    /** Un visuel publie du book, hors portfolio protege, porte un mot-cle IA qui contient le terme. */
    private const MEDIA_TAG_EXISTE = "EXISTS (SELECT 1 FROM media m
        JOIN media_tag mt ON mt.media_id = m.id
        JOIN tags t ON t.id = mt.tag_id
        WHERE m.user_id = users.id AND m.status = 'published' AND m.deleted_at IS NULL AND t.label LIKE ?
        AND NOT EXISTS (SELECT 1 FROM galleries g WHERE g.id = m.gallery_id AND g.password IS NOT NULL))";

    /** Nombre de visuels montres dans le groupe « Images » d'une recherche. */
    public const IMAGES_PAR_RECHERCHE = 24;

    /**
     * Visuels dont le titre ou les mots-cles IA portent les termes, parmi
     * les books visibles. Mode mots-cles seulement : une recherche par nom
     * ne vise pas des images.
     *
     * ponytail: LIKE '%terme%' sans index ; passer au FULLTEXT (deja pose
     * sur tags.label et media.ai_title) si la table depasse ~100k tags.
     *
     * @return Collection<int, Media>
     */
    public function rechercherImages(Recherche $recherche): Collection
    {
        $termes = $recherche->termes();

        if ($termes === [] || $recherche->mode === 'pseudo') {
            return new Collection;
        }

        $pertinence = [];
        $valeurs = [];

        foreach ($termes as $terme) {
            $motif = '%'.$this->echapper($terme).'%';
            $pertinence[] = '(media.ai_title LIKE ? OR EXISTS (SELECT 1 FROM media_tag mt JOIN tags t ON t.id = mt.tag_id
                WHERE mt.media_id = media.id AND t.label LIKE ?))';
            array_push($valeurs, $motif, $motif);
        }

        $conditions = implode(' + ', $pertinence);
        $books = $this->baseQuery($recherche->brand)->setEagerLoads([])->select('users.id');

        return Media::query()
            ->with('user')
            ->published()
            ->horsProteges()
            ->whereNotNull('media.analysed_at')
            ->whereIn('media.user_id', $books)
            ->select('media.*')
            ->selectRaw("({$conditions}) AS pertinence", $valeurs)
            ->whereRaw("({$conditions}) > 0", $valeurs)
            ->orderByDesc('pertinence')
            ->orderByDesc('media.analysed_at')
            ->limit(self::IMAGES_PAR_RECHERCHE)
            ->get();
    }

    /** Neutralise les jokers de LIKE saisis par l'utilisateur. */
    private function echapper(string $terme): string
    {
        return addcslashes($terme, '%_\\');
    }

    /**
     * Books visibles : diffuses, et pourvus d'au moins un visuel.
     *
     * La visibilite tient aux **deux indicateurs de diffusion** du creatif,
     * comme dans le legacy :
     *
     *     AND user_pref.us_pf_diff_web = 'true' AND user_pref.us_pf_diff_ub = 'true'
     *
     * `us_affhome` — repris sous le nom `in_home_selection` — ne designe
     * que la selection editoriale mise en avant, et sert au tri et au
     * filtre « sel », pas a la visibilite. Les avoir confondus rendait
     * invisibles les 8 comptes Dustfolio de l'echantillon, tous diffuses
     * mais aucun en selection.
     */
    private function baseQuery(string $brand): Builder
    {
        return User::query()
            ->with([
                'category',
                'bookSetting',
                'media' => fn ($query) => $query->published()->horsProteges()->whereNot('filename', '')->orderBy('position')->limit(6),
            ])
            ->where('brand', $brand)
            ->whereHas('bookSetting', fn (Builder $query) => $query
                ->where('diffuse_web', true)
                ->where('diffuse_ub', true))
            ->whereHas('media', fn (Builder $query) => $query->published()->horsProteges()->whereNot('filename', ''));
    }
}
