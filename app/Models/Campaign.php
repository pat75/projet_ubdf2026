<?php

namespace App\Models;

use App\Services\Espace\NettoyeurHtml;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'legacy_id', 'brand', 'type', 'name', 'subject', 'body', 'scheduled_at', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'sent_at' => 'datetime'];
    }

    /** Le contenu vient d'un editeur riche : il est filtre a l'ecriture. */
    protected function body(): Attribute
    {
        return Attribute::set(fn (?string $valeur) => app(NettoyeurHtml::class)->nettoyer($valeur));
    }

    public function sends(): HasMany
    {
        return $this->hasMany(CampaignSend::class);
    }
}
