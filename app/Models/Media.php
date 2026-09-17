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
     * URL publique du visuel, dans la declinaison demandee.
     *
     * Le legacy pre-generait neuf declinaisons sur le disque a
     * l'enregistrement. Ici une seule copie est stockee et les declinaisons
     * naissent a la demande — les noms possibles sont ceux de
     * `config/images.php`.
     */
    public function url(?string $declinaison = null): string
    {
        $parametres = [
            'login' => $this->user->login,
            // Un nom vide est possible : la table source compte 23 % de
            // lignes sans fichier. La route rend alors l'image par defaut.
            'file' => $this->filename ?: 'introuvable',
        ];

        return $declinaison === null
            ? route('book.media', $parametres)
            : route('book.media.declinaison', $parametres + ['declinaison' => $declinaison]);
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
