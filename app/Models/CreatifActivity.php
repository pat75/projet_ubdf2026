<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Une operation d'un createur dans son espace (App\Observers\JournalCreatif). */
class CreatifActivity extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'subject_label', 'changes', 'ip'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verbe(): string
    {
        return ['created' => 'Ajouté', 'updated' => 'Modifié', 'deleted' => 'Supprimé', 'restored' => 'Restauré'][$this->action] ?? $this->action;
    }

    /** « Galerie « Mode » » : le type d'objet et son nom. */
    public function objet(): string
    {
        $type = ['User' => 'Compte', 'Gallery' => 'Galerie', 'Media' => 'Visuel', 'BookSetting' => 'Réglages du book',
            'BookSection' => 'Page', 'BookArticle' => 'Article', 'PageImage' => 'Image de page', 'DataExport' => 'Export'][class_basename($this->subject_type)] ?? class_basename($this->subject_type);

        return $type.($this->subject_label ? ' « '.$this->subject_label.' »' : '');
    }

    /** Ligne lisible : « Modifié Galerie « Mode » (title, description) ». */
    public function resume(): string
    {
        return trim($this->verbe().' '.$this->objet()
            .($this->changes ? ' ('.implode(', ', array_keys($this->changes)).')' : ''));
    }
}
