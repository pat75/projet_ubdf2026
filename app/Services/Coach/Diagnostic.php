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
            $conseils[] = '[Mon portfolio › Configurer] Personnaliser l’icône de profil du portfolio, la première image que les visiteurs associent au nom.';
        }
        if (blank($reglages?->title)) {
            $conseils[] = '[Mon portfolio › Configurer] Donner au book un titre qui dit le métier (ex. « Illustratrice jeunesse »), affiché en tête de chaque page.';
        }
        if (blank($reglages?->description)) {
            $conseils[] = '[Mon portfolio › Configurer] Rédiger une courte présentation : parcours, pratique, clients visés.';
        }
        if ($creatif->galleries()->count() === 0) {
            $conseils[] = '[Contenu du portfolio › Images] Ranger les travaux dans une première galerie (par projet, technique ou client).';
        }
        if ($visuels < self::VISUELS_MINIMUM) {
            $conseils[] = "[Contenu du portfolio › Images] Le portfolio ne compte que {$visuels} visuel(s) : ajouter des visuels pour atteindre au moins ".self::VISUELS_MINIMUM.' et montrer l’étendue du travail.';
        }

        // Book complet : les etapes suivantes.
        if ($conseils === []) {
            if (! $creatif->in_home_selection && ! $creatif->selection_requested_at?->gt(now()->subDays(30))) {
                $conseils[] = '[Mon portfolio › Diffuser] Le book est complet : proposer le book pour la sélection mise en avant sur la page d’accueil.';
            }
            $conseils[] = '[Contenu du portfolio › Exporter] Exporter le book en PDF, prêt à joindre à une candidature ou un devis.';
        }

        return $conseils;
    }
}
