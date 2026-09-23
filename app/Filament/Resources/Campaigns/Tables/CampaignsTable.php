<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Models\Campaign;
use App\Models\User;
use App\Services\Messagerie\EnvoiCampagne;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
                TextColumn::make('type')->label('Type')->badge()->toggleable(),
                TextColumn::make('sends_count')->label('Envois')->counts('sends'),
                TextColumn::make('scheduled_at')->label('Programmée')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextColumn::make('sent_at')->label('Envoyée')->dateTime('d/m/Y H:i')->placeholder('—')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('brand')->label('Marque')->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('essai')->label('Envoi d’essai')->icon('heroicon-o-beaker')
                    ->schema([
                        TextInput::make('adresse')->label('Adresse d’essai')->email()->required()
                            ->default(fn () => auth('admin')->user()?->email),
                    ])
                    ->action(function (Campaign $campagne, array $data) {
                        $exemple = User::where('brand', $campagne->brand ?: 'ub')->first() ?? User::first();

                        if (! $exemple) {
                            Notification::make()->warning()->title('Aucun créatif pour servir d’exemple.')->send();

                            return;
                        }

                        app(EnvoiCampagne::class)->essai($campagne, $data['adresse'], $exemple);

                        Notification::make()->success()->title('Essai envoyé à '.$data['adresse'])->send();
                    }),

                Action::make('envoyer')->label('Envoyer')->icon('heroicon-o-paper-airplane')->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(fn (Campaign $campagne) => 'Le message part à tous les créatifs '
                        .($campagne->brand === 'df' ? 'Dustfolio' : 'Ultra-book')
                        .' abonnés à la newsletter. Ceux qui l’ont déjà reçu ne le recevront pas deux fois.')
                    ->action(function (Campaign $campagne) {
                        $nombre = app(EnvoiCampagne::class)->envoyer($campagne);

                        Notification::make()->success()
                            ->title($nombre.' message(s) mis en file d’envoi')->send();
                    }),
            ]);
    }
}
