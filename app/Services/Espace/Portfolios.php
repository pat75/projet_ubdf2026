<?php

namespace App\Services\Espace;

use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Portfolios et visuels d'un creatif : partage par l'espace (Livewire
 * Galeries) et l'API des applications.
 *
 * Les modeles recus sont deja autorises par l'appelant (policy, ou
 * requete limitee au creatif) : ce service ne fait que les regles metier.
 */
class Portfolios
{
    /** @return Collection<int, Gallery> */
    public function liste(User $creatif): Collection
    {
        return $creatif->galleries()->whereNull('parent_id')->orderBy('position')->get();
    }

    public function creer(User $creatif, ?string $nom = null): Gallery
    {
        $nom ??= __('Portfolio :n', ['n' => $creatif->galleries()->whereNull('parent_id')->count() + 1]);

        return $creatif->galleries()->create([
            'name' => $nom,
            'slug' => $this->slugLibre($creatif, $nom),
            'status' => 'published',
            // En tete de liste : avant le premier portfolio existant.
            'position' => (int) $creatif->galleries()->whereNull('parent_id')->min('position') - 1,
        ]);
    }

    public function basculerPublication(Gallery $galerie): void
    {
        $galerie->update(['status' => $galerie->status === 'published' ? 'draft' : 'published']);
    }

    public function supprimer(User $creatif, Gallery $galerie): void
    {
        DB::transaction(function () use ($creatif, $galerie) {
            $visuels = $galerie->media()->get(['id', 'size']);

            $galerie->media()->delete();
            $galerie->delete();

            $creatif->decrement('media_count', $visuels->count());
            $creatif->decrement('storage_used', (int) $visuels->sum('size'));
        });
    }

    /** @param  list<int|string>  $ids  ordre voulu, du premier au dernier */
    public function ordonner(User $creatif, array $ids): void
    {
        $galeries = $creatif->galleries()->whereIn('id', $ids)->get()->keyBy('id');

        foreach (array_values($ids) as $rang => $id) {
            $galeries->get((int) $id)?->update(['position' => $rang + 1]);
        }
    }

    public function supprimerVisuel(User $creatif, Media $visuel): void
    {
        $visuel->delete();

        $creatif->decrement('media_count');
        $creatif->decrement('storage_used', (int) $visuel->size);
    }

    /**
     * Passe un visuel dans un autre portfolio (ou le reclasse dans le sien).
     *
     * @param  list<int|string>  $ids  ordre du portfolio d'arrivee, visuel compris
     */
    public function deplacerVisuel(Media $visuel, Gallery $arrivee, array $ids): void
    {
        $depart = $visuel->gallery;

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

    /**
     * L'ordre du book est la liste `media_order`, en identifiants legacy
     * pour les visuels repris : c'est ce que lit ContexteBook::ordonner().
     *
     * @param  list<int|string>  $ids
     */
    public function ranger(Gallery $galerie, array $ids): void
    {
        $visuels = $galerie->media()->whereIn('id', $ids)->get()->keyBy('id');

        $galerie->update(['media_order' => collect($ids)
            ->map(fn ($id) => $visuels->get((int) $id))
            ->filter()
            ->map(fn (Media $m) => (string) ($m->legacy_id ?? $m->id))
            ->values()->all()]);
    }

    /** Les visuels d'un portfolio, dans l'ordre du book (`media_order`). */
    public function dansLOrdre(Gallery $galerie): Collection
    {
        $ordre = array_flip($galerie->media_order ?? []);

        return $galerie->media
            ->sortBy(fn (Media $m) => [$ordre[(string) ($m->legacy_id ?? $m->id)] ?? PHP_INT_MAX, $m->legacy_id ?? $m->id])
            ->values();
    }

    private function slugLibre(User $creatif, string $nom): string
    {
        $base = Str::slug($nom) ?: 'galerie';
        $slug = $base;

        for ($i = 2; $creatif->galleries()->withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
