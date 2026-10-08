<?php

namespace App\Services\Coach;

use App\Models\User;

/**
 * Ce qui manque au book, calcule en base : l'IA ne fait que le mettre en
 * mots, elle n'invente aucun manque.
 */
class Diagnostic
{
    public const VISUELS_MINIMUM = 12;

    /** @return list<string> conseils, du plus important au moins important */
    public function pour(User $creatif): array
    {
        $reglages = $creatif->bookSetting;
        $visuels = $creatif->media()->count();
        $conseils = [];

        if (blank($reglages?->thumbnail)) {
            $conseils[] = '[Mon portfolio › Configurer] Ajouter un visuel de profil.';
        }
        if (blank($reglages?->title)) {
            $conseils[] = '[Mon portfolio › Configurer] Donner un titre au book.';
        }
        if (blank($reglages?->description)) {
            $conseils[] = '[Mon portfolio › Configurer] Rédiger une présentation (bio).';
        }
        if ($creatif->galleries()->count() === 0) {
            $conseils[] = '[Contenu du portfolio › Images] Créer au moins une galerie pour organiser les travaux.';
        }
        if ($visuels < self::VISUELS_MINIMUM) {
            $conseils[] = "[Contenu du portfolio › Images] Le portfolio ne compte que {$visuels} visuel(s) : en ajouter pour atteindre au moins ".self::VISUELS_MINIMUM.'.';
        }

        // Book complet : les etapes suivantes.
        if ($conseils === []) {
            if (! $creatif->in_home_selection && ! $creatif->selection_requested_at?->gt(now()->subDays(30))) {
                $conseils[] = '[Mon portfolio › Diffuser] Le book est complet : demander à figurer dans la sélection.';
            }
            $conseils[] = '[Contenu du portfolio › Exporter] Exporter le book en PDF pour l’envoyer à des clients.';
        }

        return $conseils;
    }
}
