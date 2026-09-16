<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Libelles des categories metier tels que les affiche le portail.
 *
 * Le front 2018 emploie trois formulations distinctes pour un meme metier :
 * le nom (« Illustrateur »), le titre du bloc d'accueil (« Illustration »)
 * et la forme du sous-titre (« Derniere selection illustrateur freelance »).
 * Elles sont declarees dans config/categories.php.
 */
final class Metier
{
    /** @return Collection<int, array<string, mixed>> blocs de l'accueil, dans l'ordre */
    public static function blocsAccueil(): Collection
    {
        return collect(config('categories.list'))
            ->filter(fn (array $categorie) => isset($categorie['accueil']))
            ->sortBy('accueil')
            ->values();
    }

    /** @return array<string, mixed>|null */
    public static function find(?string $slug): ?array
    {
        return collect(config('categories.list'))->firstWhere('slug', $slug);
    }

    public static function titreBloc(?string $slug): string
    {
        return self::find($slug)['titre_bloc'] ?? '';
    }

    public static function pluriel(?string $slug): string
    {
        return self::find($slug)['name_plural'] ?? '';
    }

    /** « Derniere selection illustrateur freelance » */
    public static function sousTitre(?string $slug): string
    {
        $freelance = self::find($slug)['freelance'] ?? '';

        return trim(__('Dernière sélection').' '.$freelance.' '.__('freelance'));
    }
}
