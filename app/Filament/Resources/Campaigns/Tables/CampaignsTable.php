<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Models\Campaign;
use App\Models\User;
use App\Services\Messagerie\EnvoiCampagne;
use App\Services\Newsletter\Destinataires;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('subject')->label('Objet')->limit(50)->toggleable(),
                TextColumn::make('brand')->label('Marque')->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book'),
                TextColumn::make('cibles')->label('Destinataires')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'creatifs' ? 'Créatifs' : 'Visiteurs')
                    ->placeholder('—'),
                TextColumn::make('sends_count')->label('Envois')->counts('sends'),
                TextColumn::make('essai_at')->label('Essai')->dateTime('d/m/Y H:i')->placeholder('—')->toggleable(),
                TextColumn::make('scheduled_at')->label('Programmée')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextColumn::make('sent_at')->label('Envoyée')->dateTime('d/m/Y H:i')->placeholder('—')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('brand')->label('Marque')->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
            ])
            ->recordActions([
                // Une newsletter partie ne se retouche plus : elle se duplique.
                EditAction::make()->visible(fn (Campaign $campagne) => ! $campagne->estEnvoyee()),

                ReplicateAction::make()->label('Dupliquer')->icon('heroicon-o-document-duplicate')
                    ->excludeAttributes(['sent_at', 'scheduled_at', 'essai_at', 'stats', 'legacy_id'])
                    ->beforeReplicaSaved(fn (Campaign $replica) => $replica->name = $replica->name.' (copie)'),

                Action::make('apercu')->label('Aperçu')->icon('heroicon-o-eye')
                    ->modalHeading(fn (Campaign $campagne) => $campagne->subject)
                    ->modalContent(fn (Campaign $campagne) => view('filament.newsletter.apercu', [
                        'campagne' => $campagne,
                    ]))
                    ->modalSubmitAction(false)->modalCancelActionLabel('Fermer'),

                Action::make('essai')->label('Envoi d’essai')->icon('heroicon-o-beaker')
                    ->visible(fn (Campaign $campagne) => ! $campagne->estEnvoyee())
                    ->schema([
                        TextInput::make('adresse')->label('Adresse d’essai')->email()->required()
                            ->default(fn () => auth('admin')->user()?->email),
                    ])
                    ->action(function (Campaign $campagne, array $data) {
                        $exemple = User::where('brand', $campagne->brand ?: 'ub')->first() ?? User::first();

                        app(EnvoiCampagne::class)->essai($campagne, $data['adresse'], $exemple);

                        Notification::make()->success()->title('Essai envoyé à '.$data['adresse'])->send();
                    }),

                Action::make('envoyer')->label('Envoyer')->icon('heroicon-o-paper-airplane')->color('danger')
                    // L'envoi reel ne s'ouvre qu'apres un essai : on ne
                    // decouvre pas une coquille sur des milliers d'adresses.
                    ->visible(fn (Campaign $campagne) => ! $campagne->estEnvoyee())
                    ->disabled(fn (Campaign $campagne) => $campagne->essai_at === null || ! $campagne->cibles)
                    ->tooltip(fn (Campaign $campagne) => $campagne->essai_at === null
                        ? 'Envoyez d’abord un essai.'
                        : (! $campagne->cibles ? 'Cochez au moins un destinataire.' : null))
                    ->requiresConfirmation()
                    ->modalDescription(fn (Campaign $campagne) => 'Le message part à '
                        .app(Destinataires::class)->compter($campagne)
                        .' destinataire(s). Ceux qui l’ont déjà reçu ne le recevront pas deux fois.')
                    ->action(function (Campaign $campagne) {
                        $nombre = app(EnvoiCampagne::class)->envoyer($campagne);

                        Notification::make()->success()
                            ->title($nombre.' message(s) mis en file d’envoi')->send();
                    }),
            ]);
    }
}
