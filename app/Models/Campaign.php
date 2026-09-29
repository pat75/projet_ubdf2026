<?php

namespace App\Models;

use App\Services\Espace\NettoyeurHtml;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'legacy_id', 'brand', 'type', 'cibles', 'name', 'subject', 'body',
        'scheduled_at', 'essai_at', 'sent_at', 'stats',
    ];

    protected function casts(): array
    {
        return [
            'cibles' => 'array',
            'stats' => 'array',
            'scheduled_at' => 'datetime',
            'essai_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /** Le contenu vient d'un editeur riche : il est filtre a l'ecriture. */
    protected function body(): Attribute
    {
        return Attribute::set(fn (?string $valeur) => app(NettoyeurHtml::class)->nettoyer($valeur));
    }

    /** Une newsletter partie ne se retouche plus : elle se duplique. */
    public function estEnvoyee(): bool
    {
        return $this->sent_at !== null;
    }

    public function sends(): HasMany
    {
        return $this->hasMany(CampaignSend::class);
    }
}
