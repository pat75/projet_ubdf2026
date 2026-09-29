<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Une demande d'export « Mes donnees » : archive ZIP du compte, des
 * portfolios, des messages et des images HD (App\Services\Espace\ExportCompte).
 */
class DataExport extends Model
{
    public const EN_ATTENTE = 'en_attente';

    public const EN_COURS = 'en_cours';

    public const PRET = 'pret';

    public const ECHEC = 'echec';

    public const EXPIRE = 'expire';

    /** Disque dedie (config/filesystems.php) : jamais servi en direct. */
    public const DISQUE = 'exports';

    public const DOSSIER = 'exports';

    /** Une demande par periode, echecs non compris. */
    public const DELAI_HEURES = 24;

    /** Duree de conservation d'une archive prete. */
    public const CONSERVATION_JOURS = 7;

    protected $fillable = ['status', 'fichier', 'taille', 'erreur', 'termine_at', 'expire_at'];

    protected function casts(): array
    {
        return ['termine_at' => 'datetime', 'expire_at' => 'datetime', 'taille' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enPreparation(): bool
    {
        return in_array($this->status, [self::EN_ATTENTE, self::EN_COURS], true);
    }

    public function telechargeable(): bool
    {
        return $this->status === self::PRET && $this->expire_at?->isFuture()
            && $this->fichier && Storage::disk(self::DISQUE)->exists($this->fichier);
    }
}
