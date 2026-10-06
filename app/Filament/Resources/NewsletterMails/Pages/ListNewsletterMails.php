<?php

namespace App\Filament\Resources\NewsletterMails\Pages;

use App\Filament\Resources\NewsletterMails\NewsletterMailResource;
use App\Models\NewsletterMail;
use App\Services\Messagerie\DetecteurSpamIA;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

/**
 * Le bouton « Analyser avec Jev » lance l'analyse des adresses affichees,
 * trois par appel (la page reste fluide, la colonne « IA » se remplit au
 * fil de l'eau), comme la liste des conversations.
 */
class ListNewsletterMails extends ListRecords
{
    protected static string $resource = NewsletterMailResource::class;

    private const ANALYSES_PAR_APPEL = 3;

    public bool $analyseEnCours = false;

    /** Adresses dont l'analyse a echoue pendant cette visite : pas de nouvel essai en boucle. */
    public array $analyseEchouee = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('analyserJev')->label('Analyser avec Jev')
                ->icon('heroicon-o-shield-exclamation')
                ->visible(fn () => config('messagerie.spam_filter.active'))
                ->action(function () {
                    $this->analyseEchouee = [];
                    $this->analyseEnCours = true;
                }),
        ];
    }

    public function analyserSpamIA(DetecteurSpamIA $detecteur): void
    {
        if (! $detecteur->actif()) {
            $this->analyseEnCours = false;

            return;
        }

        $this->aAnalyser()->take(self::ANALYSES_PAR_APPEL)
            ->each(function (NewsletterMail $mail) use ($detecteur) {
                if (! $detecteur->analyserMail($mail)) {
                    $this->analyseEchouee[] = $mail->id;
                }
            });

        if ($this->aAnalyser()->isEmpty()) {
            $this->analyseEnCours = false;
        }

        $this->flushCachedTableRecords();
    }

    /** Jev injoignable : l'administrateur le voit en tete de liste. */
    public function getSubheading(): string|Htmlable|null
    {
        $panne = app(DetecteurSpamIA::class)->indisponibilite();

        if (! $panne || ! config('messagerie.spam_filter.active')) {
            return null;
        }

        return new HtmlString('<span style="color:rgb(220 38 38);font-weight:600">'
            .e('Analyse IA suspendue : Jev (OpenRouter) ne répond pas depuis le '.$panne['depuis']
                .'. Nouvel essai à '.$panne['reprise'].'.').'</span>'
            .'<br><span style="font-size:.875rem;color:rgb(107 114 128)">'.e($panne['erreur']).'</span>');
    }

    public function getFooter(): ?View
    {
        return view('filament.newsletter-mails.analyse-spam', [
            'reste' => $this->analyseEnCours ? $this->aAnalyser()->count() : 0,
            'echecs' => count($this->analyseEchouee),
        ]);
    }

    /** Adresses de la page affichee jamais analysees. */
    private function aAnalyser()
    {
        return collect($this->getTableRecords()->items())
            ->filter(fn (NewsletterMail $mail) => $mail->spam_ia_probabilite === null
                && ! in_array($mail->id, $this->analyseEchouee, true))
            ->values();
    }
}
