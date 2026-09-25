<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Blocs d'accroche de la page d'accueil qu'un administrateur peut afficher
 * ou masquer (voir App\Filament\Pages\AccueilPage). Un bloc dont la cle
 * n'a pas de ligne en base est considere affiche.
 */
class AccueilBloc extends Model
{
    /** Cles possibles, dans leur ordre d'apparition sur la page. */
    public const CLES = [
        'mots_cles' => 'Illustration, graphisme, digital… (mots-clés de recherche)',
        'creer_portfolio' => 'Créer votre portfolio',
        'selection_qualite' => 'Une sélection de qualité',
        'site_pro' => 'Installer mon site internet PRO',
        'disponibilites' => 'Retrouver les illustrateurs/rices disponibles aujourd’hui',
    ];

    protected $fillable = ['cle', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    /** @return array<string, bool> cle => actif, pour toutes les cles connues. */
    public static function etats(): array
    {
        return Cache::remember('accueil_blocs', 3600, function () {
            $lignes = self::query()->pluck('actif', 'cle');

            return collect(self::CLES)->keys()->mapWithKeys(fn ($cle) => [$cle => (bool) ($lignes[$cle] ?? true)])->all();
        });
    }

    public static function actif(string $cle): bool
    {
        return self::etats()[$cle] ?? true;
    }

    public static function definir(string $cle, bool $actif): void
    {
        self::query()->updateOrCreate(['cle' => $cle], ['actif' => $actif]);
        Cache::forget('accueil_blocs');
    }
}
