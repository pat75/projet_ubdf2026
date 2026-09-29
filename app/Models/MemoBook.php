<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un book mis de cote par un visiteur ou un creatif (table memo_books). */
class MemoBook extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['visitor_id', 'user_id', 'book_id', 'created_at'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(User::class, 'book_id');
    }
}
