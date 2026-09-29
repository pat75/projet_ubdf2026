<?php

namespace App\Filament\Resources\Visitors\Schemas;

use App\Models\Visitor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VisitorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Compte')->columns(2)->schema([
                // L'adresse est l'identifiant de connexion du visiteur, et
                // elle est unique dans cette table (elle ne l'est pas chez
                // les creatifs).
                TextInput::make('email')->label('Adresse mail')->email()->required()->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('brand')->label('Marque')->disabled()->dehydrated(false)
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book'),
                TextInput::make('firstname')->label('Prénom')->maxLength(100),
                TextInput::make('lastname')->label('Nom')->maxLength(100),
            ]),

            Section::make('Blocage')->columns(2)->schema([
                // En lecture seule : le blocage se pose depuis la liste, ou
                // le motif est demande et la date horodatee.
                TextInput::make('blocked_at')->label('Bloqué le')->disabled()->dehydrated(false)
                    ->formatStateUsing(fn (Visitor $record) => $record->blocked_at?->format('d/m/Y H:i') ?: 'Non bloqué'),
                Textarea::make('blocked_reason')->label('Motif')->rows(2)->maxLength(255)
                    ->visible(fn (Get $get) => filled($get('blocked_at')))
                    ->helperText('Note interne : le visiteur ne la voit pas.'),
            ]),
        ]);
    }
}
