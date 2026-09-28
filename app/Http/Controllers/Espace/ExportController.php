<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Models\DataExport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Telechargement de l'archive « Mes donnees », par son seul proprietaire. */
class ExportController extends Controller
{
    public function telecharger(DataExport $export): StreamedResponse
    {
        Gate::authorize('view', $export);
        abort_unless($export->telechargeable(), 404);

        $nom = ($export->user->brand === 'df' ? 'dustfolio' : 'ultra-book')
            .'_'.$export->user->login.'_donnees_'.$export->termine_at->format('Y-m-d').'.zip';

        return Storage::disk(DataExport::DISQUE)->download($export->fichier, $nom);
    }
}
