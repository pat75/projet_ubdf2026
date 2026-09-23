<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivity extends Model
{
    protected $fillable = [
        'admin_id', 'admin_name', 'action', 'subject_type', 'subject_id',
        'subject_label', 'changes', 'ip',
    ];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** Nom lisible du type d'objet touche. */
    public function sujet(): string
    {
        return class_basename($this->subject_type);
    }
}
