<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'legacy_id', 'user_id', 'gallery_id', 'filename', 'title', 'alt', 'link',
        'description', 'mime', 'size', 'width', 'height', 'status', 'position',
    ];

    /**
     * URL publique du visuel.
     *
     * Le legacy pre-generait 12 declinaisons sur le disque (img_front_desk,
     * img_ptf_medium, img_iph_small…). Ici une seule copie est stockee : les
     * declinaisons seront produites a la demande en phase 4, ce qui rend le
     * parametre $variant sans effet pour l'instant.
     */
    public function url(?string $variant = null): string
    {
        return route('book.media', [
            'login' => $this->user->login,
            // Un nom vide est possible : la table source compte 23 % de
            // lignes sans fichier. La route rend alors l'image par defaut.
            'file' => $this->filename ?: 'introuvable',
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}
