<?php

namespace App\Jobs;

use App\Models\DataExport;
use App\Services\Espace\ExportCompte;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Prepare l'archive « Mes donnees » d'une demande (DataExport).
 *
 * Un export copie tous les fichiers d'un book : pour ne pas saturer le
 * serveur, un seul tourne a la fois, tous createurs confondus
 * (WithoutOverlapping sur une cle commune) ; les suivants patientent dans la
 * file. Le nombre de demandes est borne en amont (une par createur et par
 * DataExport::DELAI_HEURES, voir Livewire\Espace\Exporter).
 */
class GenererExportCompte implements ShouldQueue
{
    use Queueable;

    // Relache par WithoutOverlapping tant qu'un autre export tourne : ce
    // ne sont pas des echecs. Une seule exception suffit a abandonner.
    public int $maxExceptions = 1;

    public int $timeout = 1800;

    public function __construct(public DataExport $export) {}

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(6);
    }

    /** @return list<object> */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('export-compte'))->releaseAfter(60)->expireAfter($this->timeout + 60)];
    }

    public function handle(ExportCompte $service): void
    {
        $this->export->update(['status' => DataExport::EN_COURS]);

        $fichier = $service->construire($this->export->user);

        $this->export->update([
            'status' => DataExport::PRET,
            'fichier' => $fichier,
            'taille' => filesize(Storage::disk(DataExport::DISQUE)->path($fichier)) ?: null,
            'termine_at' => now(),
            'expire_at' => now()->addDays(DataExport::CONSERVATION_JOURS),
        ]);
    }

    public function failed(?Throwable $erreur): void
    {
        $this->export->update([
            'status' => DataExport::ECHEC,
            'erreur' => $erreur ? mb_substr($erreur->getMessage(), 0, 1000) : null,
            'termine_at' => now(),
        ]);
    }
}
