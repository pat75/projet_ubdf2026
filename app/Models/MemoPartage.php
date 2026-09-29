<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Partage public d'un memoBook (table memo_partages). */
class MemoPartage extends Model
{
    protected $fillable = ['visitor_id', 'user_id', 'jeton', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Le proprietaire du memo partage, visiteur ou creatif. */
    public function proprietaire(): User|Visitor|null
    {
        return $this->visitor_id ? $this->visitor : $this->user;
    }

    public function url(): string
    {
        return lien('memobook.public', ['jeton' => $this->jeton]);
    }
}
