<?php

namespace App\Services\Espace;

use App\Models\BookSetting;

/**
 * Configuration des themes d'un book, au format du legacy.
 *
 * Chaque theme garde sa configuration propre : celle du theme actif est
 * dans `theme_settings`, celles des autres dans `legacy_payload`, sous la
 * colonne legacy du theme (us_pf_conf2016zoom…). Changer de theme range
 * donc la configuration courante et reprend celle du theme choisi — un
 * createur qui revient a un ancien theme retrouve ses reglages, comme dans
 * le legacy.
 */
class ReglagesTheme
{
    /** @return array<string, mixed> */
    public function configuration(BookSetting $reglages, string $theme): array
    {
        if ($theme === $reglages->theme && $reglages->theme_settings) {
            return $reglages->theme_settings;
        }

        $colonne = config("book_themes.{$theme}.colonne_legacy");
        $brut = $colonne ? ($reglages->legacy_payload[$colonne] ?? null) : null;

        return json_decode($brut ?: (string) config("book_themes.{$theme}.defaut"), true) ?: [];
    }

    public function changerTheme(BookSetting $reglages, string $theme): void
    {
        if ($theme === $reglages->theme) {
            return;
        }

        $payload = $reglages->legacy_payload ?? [];
        $colonne = config("book_themes.{$reglages->theme}.colonne_legacy");

        if ($colonne && $reglages->theme_settings) {
            $payload[$colonne] = json_encode($reglages->theme_settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $nouvelle = $this->configuration($reglages, $theme);

        $reglages->update([
            'legacy_payload' => $payload,
            'theme' => $theme,
            'theme_settings' => $nouvelle ?: null,
        ]);
    }

    /**
     * Champs modifiables d'une configuration : chaque feuille de `data`,
     * identifiee par son chemin pointe (".ub_couleur_fond.backgroundColor").
     *
     * @return list<array{chemin: string, libelle: string, type: string, valeur: mixed}>
     */
    public function champs(array $configuration): array
    {
        $champs = [];
        $this->parcourir($configuration['data'] ?? [], '', $champs);

        return $champs;
    }

    /** @param  array<string, mixed>  $valeurs  chemin => valeur saisie */
    public function appliquer(array $configuration, array $valeurs): array
    {
        $permis = collect($this->champs($configuration))->keyBy('chemin');

        foreach ($valeurs as $chemin => $valeur) {
            $champ = $permis->get($chemin);

            if (! $champ) {
                continue;
            }

            $valeur = match ($champ['type']) {
                'booleen' => $valeur ? 'true' : 'false',
                'nombre' => is_numeric($valeur) ? $valeur + 0 : $champ['valeur'],
                'couleur' => preg_match('/^#[0-9a-f]{3,8}$/i', (string) $valeur) ? $valeur : $champ['valeur'],
                default => mb_substr(strip_tags((string) $valeur), 0, 500),
            };

            $noeud = &$configuration['data'];
            foreach (explode("\x1F", $chemin) as $cle) {
                $noeud = &$noeud[$cle];
            }
            $noeud = $valeur;
            unset($noeud);
        }

        return $configuration;
    }

    private function parcourir(array $noeud, string $prefixe, array &$champs): void
    {
        foreach ($noeud as $cle => $valeur) {
            $chemin = $prefixe === '' ? (string) $cle : $prefixe."\x1F".$cle;

            if (is_array($valeur)) {
                $this->parcourir($valeur, $chemin, $champs);

                continue;
            }

            if ($cle === 'end' || str_starts_with((string) $cle, 'expert')) {
                continue;
            }

            $champs[] = [
                'chemin' => $chemin,
                'libelle' => $this->libelle($chemin),
                'type' => match (true) {
                    in_array($valeur, ['true', 'false'], true) => 'booleen',
                    is_int($valeur) || is_float($valeur) => 'nombre',
                    is_string($valeur) && preg_match('/^#[0-9a-f]{3,8}$/i', $valeur) === 1 => 'couleur',
                    default => 'texte',
                },
                'valeur' => $valeur,
            ];
        }
    }

    private function libelle(string $chemin): string
    {
        $morceaux = array_values(array_unique(explode("\x1F", $chemin)));
        $texte = implode(' — ', $morceaux);

        return ucfirst(trim(str_replace(['.ub_', 'ub_', '_', 'form text'], ['', '', ' ', 'texte'], $texte)));
    }
}
