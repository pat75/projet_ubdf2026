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

    /**
     * Segment d'URL de la page metier dans une langue : « illustrator » en
     * anglais, le slug interne (« illustrateur ») sinon.
     */
    public static function slugUrl(string $slug, ?string $langue = null): string
    {
        $langue ??= app()->getLocale();

        return $langue === 'en' ? (self::find($slug)['slug_en'] ?? $slug) : $slug;
    }

    /** Slug interne d'apres le segment d'URL (anglais ou francais). */
    public static function depuisSlugUrl(string $segment): string
    {
        return collect(config('categories.list'))->firstWhere('slug_en', $segment)['slug'] ?? $segment;
    }

    /** @return list<string> segments d'URL des pages metier dans une langue */
    public static function slugsUrl(?string $langue): array
    {
        return array_map(fn (array $c) => self::slugUrl($c['slug'], $langue ?? 'fr'), config('categories.list'));
    }

    /** @return array<string, mixed>|null */
    public static function find(?string $slug): ?array
    {
        return collect(config('categories.list'))->firstWhere('slug', $slug);
    }

    public static function titreBloc(?string $slug): string
    {
        return __(self::find($slug)['titre_bloc'] ?? '');
    }

    public static function pluriel(?string $slug): string
    {
        return __(self::find($slug)['name_plural'] ?? '');
    }

    /** « Derniere selection illustrateur freelance » */
    /**
     * Titre de la page metier pour les moteurs : le metier au pluriel, le mot
     * « freelance » et « portfolios », que cherchent les visiteurs. « Illustration
     * | Ultra-book » ne contenait aucun des trois.
     */
    public static function titreSeo(?string $slug, string $marque): string
    {
        return __(':metiers freelance : portfolios et books | :marque', [
            'metiers' => ucfirst(self::pluriel($slug)),
            'marque' => $marque,
        ]);
    }

    /** Description de la page metier, propre a chacune (160 caracteres au plus). */
    public static function descriptionSeo(?string $slug, string $marque): string
    {
        return __('Les portfolios des :metiers freelance sélectionnés sur :marque : parcourez leurs books et contactez directement le créatif qui vous correspond.', [
            'metiers' => self::pluriel($slug),
            'marque' => $marque,
        ]);
    }

    /** Introduction visible de la page metier (config/seo_contenus.php). */
    public static function intro(?string $slug, string $marque = 'Ultra-book'): ?string
    {
        $texte = config('seo_contenus.metiers.'.$slug);

        return $texte ? __($texte, ['marque' => $marque]) : null;
    }

    /**
     * Questions frequentes de la page metier, adaptees au metier. Affichees
     * sur la page et reprises en donnees structurees FAQPage : les moteurs
     * generatifs citent volontiers ce format question / reponse.
     *
     * @return list<array{question: string, reponse: string}>
     */
    public static function faq(?string $slug, string $marque = 'Ultra-book'): array
    {
        $remplacements = [
            'metiers' => self::pluriel($slug),
            'metier' => mb_strtolower(__(self::find($slug)['name'] ?? '')),
            'marque' => $marque,
        ];

        return array_map(fn (array $entree) => [
            'question' => __($entree['question'], $remplacements),
            'reponse' => __($entree['reponse'], $remplacements),
        ], config('seo_contenus.faq', []));
    }

    public static function sousTitre(?string $slug): string
    {
        return __('Dernière sélection :metier freelance', [
            'metier' => mb_strtolower(__(self::find($slug)['freelance'] ?? '')),
        ]);
    }
}
