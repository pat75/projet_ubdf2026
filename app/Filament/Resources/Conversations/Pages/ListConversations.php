<?php

namespace App\Filament\Resources\Conversations\Pages;

use App\Filament\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Services\Messagerie\DetecteurSpamIA;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

/**
 * Comme la messagerie du createur (App\Livewire\Espace\Messages), la
 * liste lance l'analyse IA des demandes affichees qui ne l'ont pas encore
 * ete, trois par appel, apres l'affichage : la page sort tout de suite,
 * la colonne « IA » se remplit au fil de l'eau.
 */
class ListConversations extends ListRecords
{
    protected static string $resource = ConversationResource::class;

    private const ANALYSES_PAR_APPEL = 3;

    /** Demandes dont l'analyse a echoue pendant cette visite : pas de nouvel essai en boucle. */
    public array $analyseEchouee = [];

    public function analyserSpamIA(DetecteurSpamIA $detecteur): void
    {
        if (! $detecteur->actif()) {
            return;
        }

        $this->aAnalyser()->take(self::ANALYSES_PAR_APPEL)
            ->each(function (Conversation $conversation) use ($detecteur) {
                if (! $detecteur->analyser($conversation)) {
                    $this->analyseEchouee[] = $conversation->id;
                }
            });

        // La page affichee a change : on relit ses lignes.
        $this->flushCachedTableRecords();
    }

    public function getFooter(): ?View
    {
        $reste = app(DetecteurSpamIA::class)->actif() ? $this->aAnalyser()->count() : 0;

        return view('filament.conversations.analyse-spam', [
            'reste' => $reste,
            'echecs' => count($this->analyseEchouee),
        ]);
    }

    /** Demandes de la page affichee jamais analysees, hors indesirables deja ecartes. */
    private function aAnalyser()
    {
        return collect($this->getTableRecords()->items())
            ->filter(fn (Conversation $c) => ! $c->is_spam
                && $c->spam_ia_probabilite === null
                && ! in_array($c->id, $this->analyseEchouee, true))
            ->values();
    }
}
