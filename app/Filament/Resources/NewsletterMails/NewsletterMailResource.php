<?php

namespace App\Filament\Resources\NewsletterMails;

use App\Filament\Resources\NewsletterMails\Pages\ListNewsletterMails;
use App\Models\NewsletterMail;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Adresses inscrites a la newsletter : analyse Jev et suppression par lot. */
class NewsletterMailResource extends Resource
{
    protected static ?string $model = NewsletterMail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Inscrits';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'mail newsletter';

    protected static ?string $pluralModelLabel = 'mails newsletter';

    protected static ?string $recordTitleAttribute = 'email';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->label('Mail')->searchable()->copyable(),
                // Probabilite de spam calculee par Jev (bouton « Analyser avec Jev ») :
                // en rouge au-dela du seuil, « … » tant qu'elle n'est pas calculee.
                TextColumn::make('spam_ia_probabilite')->label('IA')
                    ->formatStateUsing(fn ($state) => number_format((float) $state * 100, 0).' %')
                    ->placeholder('…')
                    ->badge()
                    ->color(fn (NewsletterMail $mail) => $mail->spam_ia ? 'danger' : 'gray')
                    ->sortable(),
                TextColumn::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(100)
            ->toolbarActions([
                // Hors groupe : le bouton apparait directement des qu'une case est cochee.
                DeleteBulkAction::make()->label('Supprimer la sélection')
                    ->modalHeading('Supprimer les adresses sélectionnées ?')
                    ->successNotificationTitle('Adresses supprimées'),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterMails::route('/'),
        ];
    }
}
