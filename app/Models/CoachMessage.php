<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Message de coaching prepare par l'IA, envoye apres validation d'un administrateur. */
class CoachMessage extends Model
{
    protected $fillable = ['user_id', 'session_fin', 'diagnostic', 'objet', 'corps', 'modele', 'statut', 'traite_par', 'envoye_le'];

    protected function casts(): array
    {
        return ['session_fin' => 'datetime', 'envoye_le' => 'datetime', 'diagnostic' => 'array'];
    }

    /** Menu de l'espace cite par le conseil (resources/prompts/coach.md) => route de la page. */
    public const MENUS = [
        'Mon compte › Tableau de bord' => 'espace',
        'Mon compte › Mon compte' => 'espace.compte',
        'Mon compte › Ma formule' => 'espace.formule',
        'Mon compte › Mes messages' => 'espace.messages',
        'Mon compte › Mon mémoBook' => 'memobook',
        'Mon portfolio › Configurer' => 'espace.design',
        'Mon portfolio › Diffuser' => 'espace.diffusion',
        'Mon portfolio › Statistiques' => 'espace.statistiques',
        'Contenu du portfolio › Images' => 'espace.galeries',
        'Contenu du portfolio › Pages' => 'espace.pages',
        'Contenu du portfolio › Exporter' => 'espace.exporter',
    ];

    /**
     * Typographie du corps, ligne par ligne :
     * - espace insecable fine avant : ; ! ? » et apres « (jamais de ponctuation seule en debut de ligne) ;
     * - les deux derniers mots d'une ligne restent ensemble (pas de mot seul sur la derniere ligne).
     */
    public static function typographie(string $texte): string
    {
        return collect(preg_split('/\R/u', $texte))->map(function (string $ligne) {
            $ligne = preg_replace(['/\s+([:;!?»])/u', '/«\s+/u'], ["\u{202F}$1", "«\u{202F}"], rtrim($ligne));

            return preg_replace('/ ([^ ]{1,12})$/u', "\u{00A0}$1", $ligne);
        })->implode("\n");
    }

    /** Corps pour le mail (Markdown) : un retour a la ligne simple reste un retour a la ligne. */
    public function corpsPourMail(): string
    {
        $texte = preg_replace('/(?<=\S)\n(?=\S)(?![-*] )/u', "  \n", self::typographie($this->corps));

        // Le menu cite devient un lien, comme dans l'espace (corpsAvecLiens).
        return self::lierMenus($texte, fn (string $menu, string $url) => '['.$menu.']('.$url.')');
    }

    /** Remplace chaque menu cite par $lien($menu, $url), espace insecable toleree dans le nom. */
    private static function lierMenus(string $texte, \Closure $lien): string
    {
        foreach (self::MENUS as $menu => $route) {
            $texte = preg_replace_callback('/'.str_replace(' ', '[ \x{00A0}]', preg_quote($menu, '/')).'/u', fn () => $lien($menu, lien($route)), $texte);
        }

        return $texte;
    }

    /** Corps echappe, chaque menu cite devenant un lien vers sa page. */
    public function corpsAvecLiens(): \Illuminate\Support\HtmlString
    {
        return new \Illuminate\Support\HtmlString(self::lierMenus(e(self::typographie($this->corps)),
            fn (string $menu, string $url) => '<a href="'.e($url).'" class="font-semibold text-ub-accent-texte hover:underline">'.e($menu).'</a>'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
