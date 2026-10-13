<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fiche de la base de connaissances du support. Proposee par l'IA apres
 * chaque reponse, inactive tant qu'un administrateur ne l'a pas relue.
 */
class SupportFiche extends Model
{
    protected $fillable = ['question', 'reponse', 'actif', 'support_ticket_id'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }
}
