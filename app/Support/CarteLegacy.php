<?php

namespace App\Support;

use App\Models\User;

/**
 * Carte d'un book au format attendu par le JavaScript du front 2018.
 *
 * Les noms de cles viennent des colonnes de ub2020 (`us_*`) et sont lus tels
 * quels par `js2019/js_core_cards.js`. Ils ne doivent pas etre renommes tant
 * que ce JavaScript n'est pas reecrit (phase 9) — un test les verrouille.
 *
 * Deux routes servent ce format : le defilement de l'accueil
 * (`/accueil__…`) et la recherche (`/rechercher_submit`).
 */
final class CarteLegacy
{
    /**
     * @return array<string, mixed>
     */
    public static function depuis(User $book): array
    {
        return [
            'us_id' => (string) $book->id,
            'us_key' => $book->publicKey(),
            'us_formule' => $book->plan > 0,
            'us_statut' => $book->status,
            'us_prenom' => $book->firstname,
            'us_nom' => $book->lastname,
            'us_dir' => $book->login,
            'us_type' => $book->category?->name,
            'us_type_titre' => $book->status,
            'us_date' => $book->created_at?->toDateString(),
            'us_ville' => $book->city,
            'us_pays' => $book->country,
            'us_lat' => (string) ($book->latitude ?? ''),
            'us_lng' => (string) ($book->longitude ?? ''),
            'us_twitter_url' => $book->twitter_url,
            'us_facebook_url' => $book->facebook_url,
            'us_pf_img_vignette' => $book->thumbnailUrl(),
            'us_pf_diff_dispo' => $book->is_available ? 'true' : 'false',
            'us_pf_css' => $book->bookSetting?->keywords,
            'us_path' => '/books/'.$book->login,
            'stats_st_cles' => $book->publicKey(),
            'img' => $book->media->map(fn ($media) => [
                'img_id' => (string) $media->id,
                'img_titre' => $media->title,
                'img_fichier' => $media->url(),
            ])->values(),
            'slider' => $book->media->map(fn ($media) => [
                'fichier' => $media->url(),
                'title' => $media->title,
            ])->values(),
        ];
    }
}
