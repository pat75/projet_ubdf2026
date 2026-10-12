<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Visites ajoutees par VisitesAjoutees, planifiees puis versees dans visit_stats. */
class VisiteAjoutee extends Model
{
    protected $table = 'visites_ajoutees';

    protected $fillable = ['lot', 'user_id', 'prevue_at', 'nb', 'appliquee_at'];

    protected $casts = ['prevue_at' => 'datetime', 'appliquee_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
