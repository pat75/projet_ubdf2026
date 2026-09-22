<?php

namespace App\Livewire\Espace;

use App\Models\Gallery;
use App\Models\Media;
use App\Services\Espace\DepotVisuel;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

/**
 * Visuels d'une galerie (img_add_and_upload, img_mod_txt, img_del, img_ord
 * du legacy).
 */
class Visuels extends Component
{
    use WithFileUploads;

    public Gallery $galerie;

    /** @var list<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $fichiers = [];

    public function mount(Gallery $galerie): void
    {
        $this->authorize('update', $galerie);
        $this->galerie = $galerie;
    }

    public function updatedFichiers(DepotVisuel $depot): void
    {
        $this->validate([
            'fichiers' => 'array|max:50',
            'fichiers.*' => 'image|mimes:jpg,jpeg,png,gif|max:'.config('images.envoi_ko_max'),
        ]);

        foreach ($this->fichiers as $fichier) {
            try {
                $depot->deposer($this->galerie, $fichier);
            } catch (RuntimeException $e) {
                $this->addError('fichiers', $fichier->getClientOriginalName().' : '.$e->getMessage());
            }
        }

        $this->reset('fichiers');
    }

    public function modifier(int $id, string $champ, string $valeur): void
    {
        if (! in_array($champ, ['title', 'description'], true)) {
            return;
        }

        if (mb_strlen($valeur) > ($champ === 'title' ? 255 : 5000)) {
            $this->addError($champ.'.'.$id, __('Ce texte est trop long.'));

            return;
        }

        $this->visuel($id)->update([$champ => trim($valeur)]);
    }

    public function basculerPublication(int $id): void
    {
        $visuel = $this->visuel($id);
        $visuel->update(['status' => $visuel->status === 'published' ? 'draft' : 'published']);
    }

    public function supprimer(int $id): void
    {
        $visuel = $this->visuel($id);
        $visuel->delete();

        Auth::user()->decrement('media_count');
        Auth::user()->decrement('storage_used', (int) $visuel->size);
    }

    /**
     * L'ordre du book est la liste `media_order`, en identifiants legacy
     * pour les visuels repris : c'est ce que lit ContexteBook::ordonner().
     *
     * @param  list<int|string>  $ids
     */
    public function ordonner(array $ids): void
    {
        $visuels = $this->galerie->media()->whereIn('id', $ids)->get()->keyBy('id');

        $ordre = collect($ids)
            ->map(fn ($id) => $visuels->get((int) $id))
            ->filter()
            ->map(fn (Media $m) => (string) ($m->legacy_id ?? $m->id))
            ->values()->all();

        $this->galerie->update(['media_order' => $ordre]);
    }

    public function render(): View
    {
        $visuels = $this->galerie->media()->get()->keyBy(fn (Media $m) => (string) ($m->legacy_id ?? $m->id));
        $ordre = array_flip($this->galerie->media_order ?? []);

        return view('livewire.espace.visuels', [
            'visuels' => $visuels->sortBy(fn (Media $m, string $cle) => [$ordre[$cle] ?? PHP_INT_MAX, $m->legacy_id ?? $m->id])->values(),
        ]);
    }

    private function visuel(int $id): Media
    {
        return $this->galerie->media()->findOrFail($id);
    }
}
