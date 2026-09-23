<?php

namespace App\Services\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export CSV d'une liste du back-office (export_diffusion.php du legacy,
 * qui chargeait tout en memoire avant d'ecrire le fichier).
 *
 * Les lignes sont ecrites au fil de la lecture : une table de 80 000
 * createurs passe sans saturer la memoire.
 */
class ExportCsv
{
    /**
     * @param  array<string, callable(Model): (string|int|float|null)>  $colonnes  entete => valeur
     */
    public function reponse(Builder $requete, array $colonnes, string $nom): StreamedResponse
    {
        $fichier = $nom.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($requete, $colonnes) {
            $sortie = fopen('php://output', 'w');

            // BOM : sans lui, Excel lit les accents en latin1.
            fwrite($sortie, "\xEF\xBB\xBF");
            fputcsv($sortie, array_keys($colonnes), ';');

            $requete->chunk(500, function ($lignes) use ($sortie, $colonnes) {
                foreach ($lignes as $ligne) {
                    fputcsv($sortie, array_map(fn (callable $valeur) => $valeur($ligne), array_values($colonnes)), ';');
                }
            });

            fclose($sortie);
        }, $fichier, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
