<?php

namespace App\Filament\Resources\NewsletterMails;

use App\Filament\Resources\NewsletterMails\Pages\ListNewsletterMails;
use App\Models\NewsletterMail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Adresses inscrites a la newsletter : consultation seule pour l'instant. */
class NewsletterMailResource extends Resource
{
    protected static ?string $model = NewsletterMail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'MailNewsletter';

    protected static ?string $modelLabel = 'mail newsletter';

    protected static ?string $pluralModelLabel = 'mails newsletter';

    protected static ?string $recordTitleAttribute = 'email';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->label('Mail')->searchable()->copyable(),
                TextColumn::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc');
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
