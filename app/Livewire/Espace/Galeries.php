<?php

namespace App\Livewire\Espace;

use App\Livewire\Concerns\EnregistreChamps;
use App\Models\Gallery;
use App\Models\Media;
use App\Services\Espace\DepotVisuel;
use App\Services\Espace\Portfolios;
use App\Services\Espace\Quotas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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

    protected Portfolios $portfolios;

    public function boot(Portfolios $portfolios): void
    {
        $this->portfolios = $portfolios;
    }

    public function mount(): void
    {
        $this->cible = $this->portfolios->liste(Auth::user())->first()?->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Portfolios
    |--------------------------------------------------------------------------
    */

    public function creer(): void
    {
        $galerie = $this->portfolios->creer(Auth::user());

        $this->cible ??= $galerie->id;
    }

    public function basculerPublication(int $id): void
    {
        $this->portfolios->basculerPublication($this->galerie($id));
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
        $this->portfolios->supprimer(Auth::user(), $this->galerie($id));

        if ($this->cible === $id) {
            $this->cible = $this->portfolios->liste(Auth::user())->first()?->id;
        }
    }

    /** @param  list<int|string>  $ids  ordre voulu, du premier au dernier */
    public function ordonner(array $ids): void
    {
        $this->portfolios->ordonner(Auth::user(), $ids);
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
        $this->portfolios->supprimerVisuel(Auth::user(), $this->visuel($id));
    }

    /** @param  list<int|string>  $ids */
    public function ordonnerVisuels(int $galerieId, array $ids): void
    {
        $this->portfolios->ranger($this->galerie($galerieId), $ids);
    }

    /**
     * Passe un visuel dans un autre portfolio.
     *
     * @param  list<int|string>  $ids  ordre du portfolio d'arrivee, visuel compris
     */
    public function deplacerVisuel(int $id, int $galerieId, array $ids): void
    {
        $this->portfolios->deplacerVisuel($this->visuel($id), $this->galerie($galerieId), $ids);
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
        $portfolios = $this->portfolios->liste(Auth::user())->load('media');

        return view('livewire.espace.galeries', [
            'portfolios' => $portfolios,
            'visuels' => $portfolios->mapWithKeys(fn (Gallery $g) => [$g->id => $this->portfolios->dansLOrdre($g)]),
            'quota' => $quotas->pour(Auth::user())['images'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Outils
    |--------------------------------------------------------------------------
    */

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

}
