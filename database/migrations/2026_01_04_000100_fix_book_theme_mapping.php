<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige le theme et sa configuration des books deja importes.
 *
 * Deux defauts de l'import initial (LegacyMigrator) :
 *
 *   1. « Modele classique » — le theme de 2010 — etait mappe sur
 *      `mdl_2015_classique`, un autre gabarit ;
 *   2. `theme_settings` prenait la premiere configuration non vide, pas
 *      celle du theme actif.
 *
 * Tout se recalcule depuis `legacy_payload`, qui conserve la valeur brute
 * du theme et chaque configuration JSON : la base legacy n'est pas relue.
 */
return new class extends Migration
{
    public function up(): void
    {
        $themes = config('categories.legacy_theme_map');
        $colonnes = config('categories.legacy_theme_settings_column');

        DB::table('book_settings')->orderBy('id')->chunkById(500, function ($lignes) use ($themes, $colonnes) {
            foreach ($lignes as $ligne) {
                $payload = json_decode((string) $ligne->legacy_payload, true) ?: [];
                $brut = mb_strtolower(trim((string) ($payload['us_pf_version_web'] ?? '')));
                $theme = $themes[$brut] ?? 'mdl_2014_responsive';

                $colonne = $colonnes[$theme] ?? null;
                $reglages = $colonne && ! empty($payload[$colonne]) ? $payload[$colonne] : null;

                DB::table('book_settings')->where('id', $ligne->id)->update([
                    'theme' => $theme,
                    // Le payload stocke le JSON du legacy tel quel (chaine).
                    'theme_settings' => $reglages === null ? null
                        : (is_string($reglages) ? $reglages : json_encode($reglages)),
                ]);
            }
        });
    }

    /**
     * Retour a l'etat anterieur : l'ancienne table de correspondance et
     * l'ancienne regle « premiere configuration non vide ».
     */
    public function down(): void
    {
        $ancienneTable = [
            '' => 'mdl_default', 'mdl_default' => 'mdl_default',
            'modèle classique' => 'mdl_2015_classique', 'mdl_classique' => 'mdl_2015_classique',
            'modelo clásico' => 'mdl_2015_classique',
        ] + config('categories.legacy_theme_map');

        $ordre = [
            'us_pf_conf2020_ultra_zen', 'us_pf_conf2016_zoom', 'us_pf_conf2015_grid',
            'us_pf_conf2015_classique', 'us_pf_conf2014_responsive',
            'us_pf_conf2013_pinter', 'us_pf_conf2012_slide', 'us_pf_conf2012',
        ];

        DB::table('book_settings')->orderBy('id')->chunkById(500, function ($lignes) use ($ancienneTable, $ordre) {
            foreach ($lignes as $ligne) {
                $payload = json_decode((string) $ligne->legacy_payload, true) ?: [];
                $brut = mb_strtolower((string) ($payload['us_pf_version_web'] ?? ''));

                $reglages = null;
                foreach ($ordre as $colonne) {
                    if (! empty($payload[$colonne])) {
                        $reglages = $payload[$colonne];
                        break;
                    }
                }

                DB::table('book_settings')->where('id', $ligne->id)->update([
                    'theme' => $ancienneTable[$brut] ?? 'mdl_default',
                    'theme_settings' => $reglages,
                ]);
            }
        });
    }
};
