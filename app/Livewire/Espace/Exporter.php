<?php

namespace App\Livewire\Espace;

use App\Jobs\GenererExportCompte;
use App\Models\DataExport;
use App\Services\Espace\PdfBook;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Page « Exporter » : le book en PDF, et l'archive « Mes donnees »
 * (compte, portfolios, messages, images HD) preparee en file d'attente.
 *
 * Limitation : une demande par createur et par DataExport::DELAI_HEURES
 * (une demande en echec ne compte pas), jamais deux en cours ; cote serveur,
 * un seul export tourne a la fois (GenererExportCompte).
 */
class Exporter extends Component
{
    public ?string $refus = null;

    public function demanderArchive(): void
    {
        $this->refus = null;
        $creatif = Auth::user();

        if ($prochaine = $this->prochaineDemande()) {
            $this->refus = __('Vous pourrez demander une nouvelle archive le :date.', ['date' => $prochaine->format('d/m/Y à H:i')]);

            return;
        }

        $export = DataExport::query()->make();
        $export->user()->associate($creatif);
        $export->save();

        GenererExportCompte::dispatch($export);
    }

    /** Date a partir de laquelle une nouvelle demande est permise, ou null si elle l'est deja. */
    private function prochaineDemande(): ?\Illuminate\Support\Carbon
    {
        $derniere = $this->derniere();

        if (! $derniere || $derniere->status === DataExport::ECHEC) {
            return null;
        }

        $permise = $derniere->created_at->copy()->addHours(DataExport::DELAI_HEURES);

        return $permise->isFuture() ? $permise : null;
    }

    private function derniere(): ?DataExport
    {
        return DataExport::query()->where('user_id', Auth::id())->latest('id')->first();
    }

    public function render(): View
    {
        $creatif = Auth::user();

        return view('livewire.espace.exporter', [
            'archive' => $this->derniere(),
            'prochaine' => $this->prochaineDemande(),
            'pagesMax' => PdfBook::pagesMax($creatif),
            'payant' => (bool) $creatif->plan,
            'developpement' => app()->environment('local', 'development'),
            // Les requetes Livewire ne passent pas par ResoudreMarque : la marque du compte.
            'nomMarque' => \App\Support\Marque::depuisCode($creatif->brand ?: 'ub')->nom,
        ]);
    }
}
