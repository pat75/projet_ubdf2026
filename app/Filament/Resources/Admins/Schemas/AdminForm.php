<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nom')->required()->maxLength(255),
            TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord: true),
            // Laisser vide a la modification garde le mot de passe en place.
            TextInput::make('password')->label('Mot de passe')->password()->revealable()
                ->required(fn (string $operation) => $operation === 'create')
                ->minLength(12)->dehydrated(fn (?string $state) => filled($state)),
            Toggle::make('is_active')->label('Actif')->default(true),
        ]);
    }
}
