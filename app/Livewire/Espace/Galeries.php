<?php

namespace App\Livewire\Espace;

use App\Models\Gallery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Liste des galeries du portfolio (rub_add, rub_mod, rub_del, rub_ord du
 * legacy).
 */
class Galeries extends Component
{
    #[Validate('required|string|max:100')]
    public string $nom = '';

    public function creer(): void
    {
        $this->validate();

        $creatif = Auth::user();

        $creatif->galleries()->create([
            'name' => $this->nom,
            'slug' => $this->slugLibre($this->nom),
            'status' => 'published',
            'position' => (int) $creatif->galleries()->whereNull('parent_id')->max('position') + 1,
        ]);

        $this->reset('nom');
    }

    public function renommer(int $id, string $nom): void
    {
        $nom = trim($nom);

        if ($nom === '' || mb_strlen($nom) > 100) {
            $this->addError('renommer.'.$id, __('Le nom doit faire entre 1 et 100 caractères.'));

            return;
        }

        $this->galerie($id)->update(['name' => $nom]);
    }

    public function basculerPublication(int $id): void
    {
        $galerie = $this->galerie($id);
        $galerie->update(['status' => $galerie->status === 'published' ? 'draft' : 'published']);
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
    }

    /** @param  list<int|string>  $ids  ordre voulu, du premier au dernier */
    public function ordonner(array $ids): void
    {
        $galeries = Auth::user()->galleries()->whereIn('id', $ids)->get()->keyBy('id');

        foreach (array_values($ids) as $rang => $id) {
            $galeries->get((int) $id)?->update(['position' => $rang + 1]);
        }
    }

    public function render(): View
    {
        return view('livewire.espace.galeries', [
            'galeries' => Auth::user()->galleries()->whereNull('parent_id')
                ->withCount('media')->orderBy('position')->get(),
        ]);
    }

    private function galerie(int $id): Gallery
    {
        $galerie = Gallery::findOrFail($id);
        $this->authorize('update', $galerie);

        return $galerie;
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
