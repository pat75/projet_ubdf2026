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

    /** Corps echappe, chaque menu cite devenant un lien vers sa page. */
    public function corpsAvecLiens(): \Illuminate\Support\HtmlString
    {
        $html = e($this->corps);

        foreach (self::MENUS as $menu => $route) {
            $html = str_replace(e($menu), '<a href="'.e(lien($route)).'" class="font-semibold text-ub-accent-texte hover:underline">'.e($menu).'</a>', $html);
        }

        return new \Illuminate\Support\HtmlString($html);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
