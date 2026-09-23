<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\NouveauMotDePasse;
use App\Models\User;
use App\Services\Admin\MotDePasseTemporaire;
use App\Support\Marque;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Mot de passe qui vient d'etre tire, en clair.
     *
     * Il ne vit que dans l'etat Livewire de la page : affiche a
     * l'administrateur, propose a l'envoi, puis perdu au premier
     * rechargement. Rien n'en est conserve en base.
     */
    public ?string $motDePasseGenere = null;

    protected function getHeaderActions(): array
    {
        return [
            $this->actionPriseIdentite(),
            $this->actionReinitialiserMotDePasse(),
            $this->actionEnvoyerMotDePasse(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Ouvre l'espace du createur dans l'onglet courant, sous son identite.
     *
     * Un formulaire POST plutot qu'un lien : l'action change l'etat de la
     * session, et un GET serait rejouable depuis l'historique.
     */
    private function actionPriseIdentite(): Action
    {
        return Action::make('prise_identite')
            ->label('Se connecter en tant que')
            ->icon(Heroicon::OutlinedArrowRightOnRectangle)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Ouvrir l’espace de ce créatif')
            ->modalDescription('Vous verrez son espace exactement comme lui. Un bandeau rouge vous permettra de revenir ici. Votre session administrateur reste ouverte.')
            ->modalSubmitActionLabel('Ouvrir son espace')
            ->action(function () {
                // Filament ne sait pas soumettre un POST : on passe par une
                // page intermediaire qui poste le formulaire d'elle-meme.
                return redirect()->route('admin.prise-identite.relais', ['creatif' => $this->record]);
            });
    }

    private function actionReinitialiserMotDePasse(): Action
    {
        return Action::make('reinitialiser_mot_de_passe')
            ->label('Réinitialiser le mot de passe')
            ->icon(Heroicon::OutlinedKey)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Réinitialiser le mot de passe')
            ->modalDescription('Un mot de passe de huit caractères sera tiré au hasard. L’ancien cessera aussitôt de fonctionner et les sessions ouvertes du créatif seront fermées.')
            ->modalSubmitActionLabel('Tirer un mot de passe')
            ->action(function (MotDePasseTemporaire $service) {
                $this->motDePasseGenere = $service->appliquer($this->record);

                Notification::make()
                    ->title('Nouveau mot de passe : '.$this->motDePasseGenere)
                    ->body('Notez-le : il ne sera plus affiché après un rechargement de la page.')
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    /**
     * N'apparait qu'apres un tirage, le temps que le mot de passe est
     * encore lisible.
     */
    private function actionEnvoyerMotDePasse(): Action
    {
        return Action::make('envoyer_mot_de_passe')
            ->label(fn (): string => 'Envoyer « '.$this->motDePasseGenere.' » au créatif')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('success')
            ->visible(fn (): bool => filled($this->motDePasseGenere))
            ->requiresConfirmation()
            ->modalHeading('Envoyer le mot de passe')
            ->modalDescription(fn (): string => 'Le message partira à '.$this->record->email.'.')
            ->modalSubmitActionLabel('Envoyer')
            ->action(function () {
                /** @var User $creatif */
                $creatif = $this->record;

                Mail::to($creatif->email)->send(new NouveauMotDePasse(
                    $creatif,
                    Marque::depuisCode($creatif->brand ?: 'ub'),
                    $this->motDePasseGenere,
                ));

                $this->motDePasseGenere = null;

                Notification::make()->title('Mot de passe envoyé')->success()->send();
            });
    }
}
