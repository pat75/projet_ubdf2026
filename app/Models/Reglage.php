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
     * Message libre de l'administrateur, affiche sur la page de
     * maintenance sous le texte standard. Vide : rien de plus.
     */
    public const MESSAGE_MAINTENANCE = 'message_maintenance';

    /**
     * Bouton « Continuer avec Google » retire des fenetres de connexion et
     * de creation de compte (App\Filament\Pages\AccueilPage). Cle
     * negative : absente, Google reste propose, comme avant ce reglage.
     */
    public const GOOGLE_MASQUE = 'google_masque';

    /**
     * Listes de modeles OpenRouter par capacite et niveau de cout, en
     * JSON (App\Filament\Pages\ModelesIA). Absente : les defauts de
     * App\Services\IA\OpenRouterModelSelector::DEFAUTS.
     */
    public const MODELES_IA = 'modeles_ia';

    /**
     * API NVIDIA (App\Services\IA\Nvidia, App\Filament\Pages\ReglageNvidia) :
     * cle chiffree, date d'expiration (Y-m-d) et modele vision.
     */
    public const NVIDIA_CLE = 'nvidia_cle';

    public const NVIDIA_EXPIRATION = 'nvidia_expiration';

    public const NVIDIA_MODELE = 'nvidia_modele';

    /**
     * IA du Coach creatif (App\Services\Coach\Redacteur) : `nvidia`
     * (gratuit, defaut) ou un niveau de cout OpenRouter, de `1` a `4`.
     */
    public const COACH_IA = 'coach_ia';

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

    /** Valeur texte d'un reglage, null si absente ou vide. */
    public static function texte(string $cle): ?string
    {
        return Cache::remember(
            'reglage_texte_'.$cle,
            3600,
            fn () => self::query()->where('cle', $cle)->value('valeur'),
        ) ?: null;
    }

    public static function definirTexte(string $cle, ?string $valeur): void
    {
        self::query()->updateOrCreate(['cle' => $cle], ['valeur' => filled($valeur) ? trim($valeur) : null]);
        Cache::forget('reglage_texte_'.$cle);
    }

    /** Valeur JSON d'un reglage, null si absente. */
    public static function json(string $cle): ?array
    {
        return json_decode((string) self::texte($cle), true) ?: null;
    }

    public static function definirJson(string $cle, ?array $valeur): void
    {
        self::definirTexte($cle, $valeur === null ? null : json_encode($valeur));
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
