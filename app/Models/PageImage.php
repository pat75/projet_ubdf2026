<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une image deposee dans le corps d'une page, via l'editeur Redactor
 * (App\Livewire\Espace\Pages). Le fichier vit dans img_cms/ du book du
 * proprietaire (App\Services\Espace\DepotImagePage) ; cette ligne n'en
 * garde que les metadonnees, pour la bibliotheque d'images du bouton
 * image de l'editeur.
 */
class PageImage extends Model
{
    protected $fillable = ['filename', 'original_name', 'size', 'width', 'height'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** URL publique, servie telle quelle par BookMediaController::cms. */
    public function url(): string
    {
        return route('book.media.cms', ['login' => $this->user->login, 'chemin' => $this->filename]);
    }
}
