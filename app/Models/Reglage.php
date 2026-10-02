<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Reglages globaux du site, poses depuis le back-office
 * (App\Filament\Pages\AccueilPage).
 *
 * Contrairement a AccueilBloc, une cle absente de la table vaut « non » :
 * un site frais n'est pas en maintenance.
 */
class Reglage extends Model
{
    /** Le portail est ferme au public : voir App\Http\Middleware\Maintenance. */
    public const MAINTENANCE = 'maintenance';

    /**
     * Bouton « Continuer avec Google » retire des fenetres de connexion et
     * de creation de compte (App\Filament\Pages\ConfigurationPage). Cle
     * negative : absente, Google reste propose, comme avant ce reglage.
     */
    public const GOOGLE_MASQUE = 'google_masque';

    protected $table = 'reglages';

    protected $fillable = ['cle', 'valeur'];

    public static function actif(string $cle): bool
    {
        return Cache::remember(
            'reglage_'.$cle,
            3600,
            fn () => (bool) self::query()->where('cle', $cle)->value('valeur'),
        );
    }

    public static function definir(string $cle, bool $actif): void
    {
        self::query()->updateOrCreate(['cle' => $cle], ['valeur' => $actif ? '1' : '0']);
        Cache::forget('reglage_'.$cle);
    }

    /** Le site est-il ferme au public ? */
    public static function enMaintenance(): bool
    {
        return self::actif(self::MAINTENANCE);
    }

    /** La connexion et l'inscription par Google sont-elles proposees ? */
    public static function googleActif(): bool
    {
        return ! self::actif(self::GOOGLE_MASQUE);
    }
}
