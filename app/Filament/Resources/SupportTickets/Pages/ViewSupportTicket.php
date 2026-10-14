<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Jobs\ProposerFicheSupport;
use App\Mail\SupportReponse;
use App\Models\SupportTicket;
use App\Services\IA\CorrectionMessage;
use App\Services\Support\BaseConnaissance;
use App\Support\Marque;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Fil d'une demande et fenetre de reponse : correction orthographique et
 * suggestion IA (base de connaissances), puis envoi direct par mail avec
 * le lien qui rouvre la discussion.
 *
 * @property SupportTicket $record
 */
class ViewSupportTicket extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SupportTicketResource::class;

    protected string $view = 'filament.support.ticket';

    public string $reponse = '';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        if (! $this->record->lu_admin_at) {
            $this->record->update(['lu_admin_at' => now()]);
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Demande de '.($this->record->user?->login ?? $this->record->email);
    }

    public function corriger(CorrectionMessage $correction): void
    {
        if (trim($this->reponse) === '') {
            return;
        }

        try {
            $this->reponse = $correction->corriger($this->reponse);
        } catch (Throwable $e) {
            Notification::make()->title('Correction indisponible')->body($e->getMessage())->danger()->send();
        }
    }

    /** Signature collee en fin de reponse, selon la marque de la demande. */
    public function signer(string $qui): void
    {
        $marque = Marque::depuisCode($this->record->brand ?: 'ub');
        $dustfolio = $marque->code !== 'ub';

        $signature = match (true) {
            $qui === 'patrice' && $dustfolio => "Have a nice day,\n\nPatrice Tardif\n{$marque->nom}",
            $qui === 'patrice' => "Bonne journée\n\nPatrice Tardif\n{$marque->nom}",
            $dustfolio => "Have a nice day,\n\nThe {$marque->nom} team",
            default => "Bonne journée.\n\nL’équipe {$marque->nom}",
        };

        $this->reponse = rtrim($this->reponse)."\n\n".$signature;
    }

    public function suggerer(BaseConnaissance $base): void
    {
        try {
            $this->reponse = $base->suggerer($this->record->load('messages'));
        } catch (Throwable $e) {
            Notification::make()->title('Suggestion indisponible')->body($e->getMessage())->danger()->send();
        }
    }

    public function envoyer(): void
    {
        $this->validate(['reponse' => ['required', 'string', 'min:2', 'max:10000']]);

        $this->record->ajouter('admin', trim($this->reponse), Auth::guard('admin')->id());
        $this->reponse = '';
        $this->record->load('messages');

        // Envoi immediat, sans dependre d'un worker : l'admin sait tout de
        // suite si le mail est parti. La fiche IA, plus lente, suit la reponse HTTP.
        ProposerFicheSupport::dispatchAfterResponse($this->record);

        try {
            Mail::to($this->record->adresse())->send(new SupportReponse($this->record));
            Notification::make()->title('Réponse envoyée à '.$this->record->adresse())->success()->send();
        } catch (Throwable $e) {
            report($e);
            Notification::make()->title('Réponse enregistrée, mais l’e-mail n’est pas parti')
                ->body($e->getMessage())->danger()->persistent()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        $user = $this->record->user;

        return [
            SupportTicketResource::priseIdentite(),
            SupportTicketResource::selection(),
            Action::make('book')->label('Voir le book')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                ->visible((bool) $user)
                ->url(fn () => $user->bookUrl(), shouldOpenInNewTab: true),
            ActionGroup::make([
                Action::make('clore')->label($this->record->statut === 'clos' ? 'Rouvrir' : 'Clore')->icon('heroicon-o-archive-box')
                    ->action(fn () => $this->record->update($this->record->statut === 'clos' ? ['statut' => 'nouveau'] : ['statut' => 'clos', 'clos_at' => now()])),
                Action::make('spam')->label($this->record->is_spam ? 'Pas un spam' : 'Marquer comme spam')->icon('heroicon-o-no-symbol')
                    ->action(fn () => $this->record->update(['is_spam' => ! $this->record->is_spam])),
            ]),
        ];
    }
}
