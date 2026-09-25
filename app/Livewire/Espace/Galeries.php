<?php

namespace App\Livewire\Espace;

use App\Livewire\Concerns\EnregistreChamps;
use App\Models\Gallery;
use App\Models\Media;
use App\Services\Espace\DepotVisuel;
use App\Services\Espace\Quotas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

/**
 * Les portfolios et leurs visuels, sur un seul ecran (rub_* et img_* du
 * legacy) : chaque portfolio se deplie sur ses images et videos, qui se
 * trient sur place ou passent d'un portfolio a l'autre.
 */
class Galeries extends Component
{
    use EnregistreChamps;
    use WithFileUploads;

    /** Portfolio qui recoit les images deposees et les videos ajoutees. */
    public ?int $cible = null;

    /** @var list<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $fichiers = [];

    /** Visuel dont l'image est remplacee par `remplacement`. */
    public ?int $remplace = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $remplacement = null;

    public function mount(): void
    {
        $this->cible = $this->portfolios()->first()?->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Portfolios
    |--------------------------------------------------------------------------
    */

    public function creer(): void
    {
        $creatif = Auth::user();
        $nom = __('Portfolio :n', ['n' => $creatif->galleries()->whereNull('parent_id')->count() + 1]);

        $galerie = $creatif->galleries()->create([
            'name' => $nom,
            'slug' => $this->slugLibre($nom),
            'status' => 'published',
            'position' => (int) $creatif->galleries()->whereNull('parent_id')->max('position') + 1,
        ]);

        $this->cible ??= $galerie->id;
    }

    public function basculerPublication(int $id): void
    {
        $galerie = $this->galerie($id);
        $galerie->update(['status' => $galerie->status === 'published' ? 'draft' : 'published']);
    }

    /**
     * Mot de passe demande aux visiteurs du book ; vide, il est retire.
     *
     * @return array{ok: true}|array{erreur: string}
     */
    public function definirMotDePasse(int $id, ?string $motDePasse): array
    {
        $motDePasse = trim((string) $motDePasse);
        $validateur = Validator::make(['mot_de_passe' => $motDePasse], ['mot_de_passe' => 'nullable|string|min:4|max:50']);

        if ($validateur->fails()) {
            return ['erreur' => $validateur->errors()->first('mot_de_passe')];
        }

        $this->galerie($id)->update(['password' => $motDePasse === '' ? null : $motDePasse]);

        return ['ok' => true];
    }

    public function supprimer(int $id): void
    {
        $galerie = $this->galerie($id);

        DB::transaction(function () use ($galerie) {
            $visuels = $galerie->media()->get(['id', 'size']);

            $galerie->media()->delete();
            $galerie->delete();

            Auth::user()->decrement('media_count', $visuels->count());
            Auth::user()->decrement('storage_used', (int) $visuels->sum('size'));
        });

        if ($this->cible === $id) {
            $this->cible = $this->portfolios()->first()?->id;
        }
    }

    /** @param  list<int|string>  $ids  ordre voulu, du premier au dernier */
    public function ordonner(array $ids): void
    {
        $galeries = Auth::user()->galleries()->whereIn('id', $ids)->get()->keyBy('id');

        foreach (array_values($ids) as $rang => $id) {
            $galeries->get((int) $id)?->update(['position' => $rang + 1]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Visuels
    |--------------------------------------------------------------------------
    */

    public function updatedFichiers(DepotVisuel $depot): void
    {
        $this->validate([
            'cible' => 'required|integer',
            'fichiers' => 'array|max:50',
            'fichiers.*' => 'image|mimes:jpg,jpeg,png,gif|max:'.config('images.envoi_ko_max'),
        ], ['cible.required' => __('Créez d’abord un portfolio.')]);

        $galerie = $this->galerie($this->cible);

        foreach ($this->fichiers as $fichier) {
            try {
                $depot->deposer($galerie, $fichier);
            } catch (RuntimeException $e) {
                $this->addError('fichiers', $fichier->getClientOriginalName().' : '.$e->getMessage());
            }
        }

        $this->reset('fichiers');
    }

    /** Image deposee sur l'apercu d'un visuel : elle remplace la sienne. */
    public function updatedRemplacement(DepotVisuel $depot): void
    {
        $this->validate([
            'remplace' => 'required|integer',
            'remplacement' => 'image|mimes:jpg,jpeg,png,gif|max:'.config('images.envoi_ko_max'),
        ]);

        try {
            $depot->remplacer($this->visuel($this->remplace), $this->remplacement);
        } catch (RuntimeException $e) {
            $this->addError('remplacement', $e->getMessage());
        }

        $this->reset('remplacement');
    }

    /** @return array{ok: true}|array{erreur: string} */
    public function ajouterVideo(string $lien, DepotVisuel $depot): array
    {
        if ($this->cible === null) {
            return ['erreur' => __('Créez d’abord un portfolio.')];
        }

        try {
            $depot->deposerVideo($this->galerie($this->cible), $lien);
        } catch (RuntimeException $e) {
            return ['erreur' => $e->getMessage()];
        }

        return ['ok' => true];
    }


    public function supprimerVisuel(int $id): void
    {
        $visuel = $this->visuel($id);
        $visuel->delete();

        Auth::user()->decrement('media_count');
        Auth::user()->decrement('storage_used', (int) $visuel->size);
    }

    /** @param  list<int|string>  $ids */
    public function ordonnerVisuels(int $galerieId, array $ids): void
    {
        $this->ranger($this->galerie($galerieId), $ids);
    }

    /**
     * Passe un visuel dans un autre portfolio.
     *
     * @param  list<int|string>  $ids  ordre du portfolio d'arrivee, visuel compris
     */
    public function deplacerVisuel(int $id, int $galerieId, array $ids): void
    {
        $visuel = $this->visuel($id);
        $depart = $visuel->gallery;
        $arrivee = $this->galerie($galerieId);

        if ($depart->is($arrivee)) {
            $this->ranger($arrivee, $ids);

            return;
        }

        DB::transaction(function () use ($visuel, $depart, $arrivee, $ids) {
            $visuel->update(['gallery_id' => $arrivee->id]);

            // Un visuel absent de la liste d'ordre est mal range par le
            // book (voir ContexteBook::ordonner) : on le retire du depart.
            if ($depart->media_order) {
                $cle = (string) ($visuel->legacy_id ?? $visuel->id);
                $depart->update(['media_order' => array_values(array_diff($depart->media_order, [$cle]))]);
            }

            $this->ranger($arrivee, $ids);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Textes edites sur place
    |--------------------------------------------------------------------------
    */

    protected function champsAutoEnregistres(): array
    {
        $creatif = Auth::user();
        $regles = [];

        foreach ($creatif->galleries()->pluck('id') as $id) {
            $regles['portfolio-'.$id] = ['required', 'string', 'max:100'];
        }

        foreach (Media::where('user_id', $creatif->id)->pluck('id') as $id) {
            $regles['titre-'.$id] = ['nullable', 'string', 'max:255'];
            $regles['legende-'.$id] = ['nullable', 'string', 'max:5000'];
        }

        return $regles;
    }

    protected function persisterChamp(string $nom, mixed $valeur): void
    {
        [$champ, $id] = explode('-', $nom, 2);
        $valeur = trim((string) $valeur);

        match ($champ) {
            'portfolio' => $this->galerie((int) $id)->update(['name' => $valeur]),
            'titre' => $this->visuel((int) $id)->update(['title' => $valeur]),
            'legende' => $this->visuel((int) $id)->update(['description' => $valeur]),
        };
    }

    public function render(Quotas $quotas): View
    {
        $portfolios = $this->portfolios()->load('media');

        return view('livewire.espace.galeries', [
            'portfolios' => $portfolios,
            'visuels' => $portfolios->mapWithKeys(fn (Gallery $g) => [$g->id => $this->dansLOrdre($g)]),
            'quota' => $quotas->pour(Auth::user())['images'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Outils
    |--------------------------------------------------------------------------
    */

    /** @return Collection<int, Gallery> */
    private function portfolios(): Collection
    {
        return Auth::user()->galleries()->whereNull('parent_id')->orderBy('position')->get();
    }

    /** Les visuels d'un portfolio, dans l'ordre du book (`media_order`). */
    private function dansLOrdre(Gallery $galerie): Collection
    {
        $ordre = array_flip($galerie->media_order ?? []);

        return $galerie->media
            ->sortBy(fn (Media $m) => [$ordre[(string) ($m->legacy_id ?? $m->id)] ?? PHP_INT_MAX, $m->legacy_id ?? $m->id])
            ->values();
    }

    /**
     * L'ordre du book est la liste `media_order`, en identifiants legacy
     * pour les visuels repris : c'est ce que lit ContexteBook::ordonner().
     *
     * @param  list<int|string>  $ids
     */
    private function ranger(Gallery $galerie, array $ids): void
    {
        $visuels = $galerie->media()->whereIn('id', $ids)->get()->keyBy('id');

        $galerie->update(['media_order' => collect($ids)
            ->map(fn ($id) => $visuels->get((int) $id))
            ->filter()
            ->map(fn (Media $m) => (string) ($m->legacy_id ?? $m->id))
            ->values()->all()]);
    }

    private function galerie(int $id): Gallery
    {
        $galerie = Gallery::findOrFail($id);
        $this->authorize('update', $galerie);

        return $galerie;
    }

    private function visuel(int $id): Media
    {
        return Media::where('user_id', Auth::id())->findOrFail($id);
    }

    private function slugLibre(string $nom): string
    {
        $base = Str::slug($nom) ?: 'galerie';
        $slug = $base;

        for ($i = 2; Auth::user()->galleries()->withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
