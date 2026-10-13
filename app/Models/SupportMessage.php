<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class SupportMessage extends Model
{
    protected $fillable = ['support_ticket_id', 'auteur', 'admin_id', 'corps', 'piece', 'piece_nom'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** Adresse signee de la piece jointe : seul le lien la sert. */
    public function lienPiece(): ?string
    {
        return $this->piece ? URL::signedRoute(nom_route('support.piece'), ['message' => $this->id]) : null;
    }

    public function pieceEstImage(): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', (string) $this->piece);
    }
}
